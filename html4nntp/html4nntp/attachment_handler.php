<?php
/******************************************************************************		
 * $Id: attachment_handler.php,v 1.2 2004/09/26 17:41:52 svanpo Exp $
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

//	$nntp = new NNTP($nntp_server, $user, $pass);
	
	if (!$nntp->connect()) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["nntp_fail"]."</div>";
		echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
	} else {
		if (is_requested("art_group")) {
			$group_info = $nntp->join_group(get_request("art_group"));
		} else {
			$group_info = $nntp->join_group($_SESSION["newsgroup"]);
		}
		
		if ($group_info == NULL) {
			echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </div>";
			echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
		} else if (isset($attachment_id) && isset($message_id)) {
			$MIME_Message = $nntp->get_article($message_id);
			$nntp->quit();

			if ($MIME_Message->get_total_part() > $attachment_id) {
				$header = $MIME_Message->get_part_header($attachment_id);
				$body = $MIME_Message->get_part_body($attachment_id);
				
				if (strcmp($header["content-type"],"") == 0) {
					header("Content-Type: text/html");
					echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["request_fail"]."</div>";
				} else {
					ob_end_clean();
					
					$pos = strpos($header["content-type"], ";");
					if ($pos !== FALSE) {
						$header["content-type"] = substr($header["content-type"], 0, $pos);
					}
						
					header("Content-Type: ".$header["content-type"]);
					header("Content-Disposition: ".$header["content-disposition"]);
					decode_message_content_output($MIME_Message->get_part($attachment_id));
					exit(0);
				}
			} else {
				header("Content-Type: text/html");
				echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["multipart_fail"]."</div>";
			}
		} else {
			header("Content-Type: text/html");
			echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["request_fail"]."</div>";
		}
		$nntp->quit();
	}
?>
