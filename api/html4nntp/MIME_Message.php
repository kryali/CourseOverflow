<?php
/******************************************************************************		
 * $Id: MIME_Message.php,v 1.2 2004/09/26 17:41:52 svanpo Exp $
 *
 * Authors: Stéphane Vanpoperynghe  (svanpoperynghe@toutprogrammer.com)
 *          Terence Yim             (chtyim@gmail.com)
 *
 * Copyright 2004 Terence Yim, Stéphane Vanpoperynghe
 ******************************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA	        
 ******************************************************************************/

	// Class for encode and decode MIME Message
	// This class can also be used to decode only the header part
	class MIME_Message {
		
		var $MIME_message;
		var $content_map;
		var $main_header;
		var $original_header;

		// Constructor
		// $message stores the raw MIME message (header + body)
		function MIME_Message($message) {
			$this->MIME_message = array();
			$this->content_map = array();
			unset($this->main_header);
			$this->decode_article($message);
			
			list($header, $body) = split("\r\n\r\n", $message, 2);
			$this->original_header = $header;
		}
		
		
		function get_content_map() {
			return $this->content_map;
		}

		
		function get_main_header() {
			return $this->main_header;
		}


		function get_total_part() {
			return sizeof($this->MIME_message);
		}


		function get_all_parts() {
			return $this->MIME_message;
		}


		function get_part($i) {
			return $this->MIME_message[$i];
		}
		
		
		function get_part_header($i) {
			return $this->MIME_message[$i]["header"];
		}
		
		
		function get_part_body($i) {
			return $this->MIME_message[$i]["body"];
		}
		
		function get_original_header() {
			return($this->original_header);
		}

		function decode_header($headers) {
			$header_want = "/^(From|Subject|Date|Newsgroups|References|Message-ID|Content-Type|Content-Transfer-Encoding|Content-Disposition|Content-ID): (.*$)/i";
			$headers = split("\r\n", $headers);

			// Parse the header
			for ($line_count=0; $line_count<count($headers); $line_count++) {
				if (ereg("^([-a-zA-Z]+):(.*)$", $headers[$line_count], $tab)) {
					$name = strtolower($tab[1]); 
					$result[$name] = $tab[2];
				} else {
					$result[$name] .= str_replace("\r\n", "", $headers[$line_count]);
				}
			}
			// Decode MIME headers
			foreach ($result as $key=>$value) $result[$key] = trim(decode_MIME_header($value));
			
			return $result;
		}


		// An article is a raw MIME message
		function decode_article($article) {
			list($header, $body) = split("\r\n\r\n", $article, 2);
						
			$header = $this->decode_header($header);

			$body = preg_replace("/^\.\.(.*)/m", ".$1", $body);	//Replace the line starts with .. to .

			if (isset($header["from"])) {
				$header["from"] = decode_sender($header["from"]);
			}

			if (!isset($header["content-type"])) {
				$header["content-type"] = "text/plain";		// If no content type, default set it to plain text
			}
	
			if (!isset($this->main_header)) {
				$this->main_header = $header;
			}

			if (stristr($header["content-type"], "multipart")) {
				// Extract boundary from the header
				preg_match("/boundary=\"(.*?)\"/i", $header["content-type"], $boundary);
				$boundary = "--".str_replace("\"", "", $boundary[1]);
				$this->decode_multipart_message($body, $boundary);				
			} else {
				// Check for any UUEncoded attachment
				if (preg_match("/^begin\s+[0-9][0-9][0-9]\s+(.+?)\s*\r?\n/m", $body)) {
//					$parts = preg_split("/^begin\s+[0-9][0-9][0-9]\s+(.+?)\s*\r?\n/m", $body, -1, PREG_SPLIT_DELIM_CAPTURE);
					$parts = preg_split("/^begin\s+[0-9][0-9][0-9]\s+(.+?)\s*\r?\n(.*?)end/ms", $body, -1, PREG_SPLIT_DELIM_CAPTURE);

					// Create the message structure same as Multipart message
					$this->MIME_message[] = array(
												"header"=>array("content-type"=>$header["content-type"],
																"content-transfer-encoding"=>$header["content-transfer-encoding"]),
												"body"=>$parts[0]
											);
					$text_index = sizeof($this->MIME_message) - 1;
					for ($i = 1;$i < sizeof($parts);$i += 3) {
						$this->MIME_message[] = array(
													"header"=>array("content-type"=>get_content_type($parts[$i]),
																	"content-transfer-encoding"=>"uuencode"),
													"body"=>$parts[$i + 1],
													"filename"=>$parts[$i]
												);
						if (strlen(trim($parts[$i + 2])) > 0) {
							$this->MIME_message[$text_index]["body"] .= $parts[$i + 2];
						}
					}
				} else {
					unset($filename);
					if (preg_match("/name=(['|\"])?(.*?)(?(1)['|\"])\s*/i", $header["content-type"], $matches)) {
						$filename = str_replace("\"", "", $matches[2]);
					} elseif (preg_match("/filename=(['|\"])?(.*?)(?(1)['|\"])\s*/i", $header["content-disposition"], $matches)) {
						$filename = str_replace("\"", "", $matches[2]);
					}
					if (isset($filename)) {
						$this->MIME_message[] = array("header"=>$header, "body"=>$body, "filename"=>$filename);
					} else {
						$this->MIME_message[] = array("header"=>$header, "body"=>$body);
					}
					if (isset($header["content-id"])) {
						$this->content_map[$header["content-id"]] = sizeof($this->MIME_message) - 1;
					}
				}
			}
		}
	
	
		function decode_multipart_message($message, $boundary) {
			$parts = preg_split("/$boundary-?-?\s*/m", $message);
			
			array_shift($parts);	// Drop the "This is a multi-part message in MIME format." message
			array_pop($parts);		// Drop the last part after the boundary end
	
			foreach ($parts as $part) {
				$this->decode_article($part);
			}
		}
	}
?>