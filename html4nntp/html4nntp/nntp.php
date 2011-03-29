<?php
/******************************************************************************		
 * $Id: nntp.php,v 1.6 2004/10/19 21:29:12 svanpo Exp $
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

	require("html4nntp/util.php");
	require("html4nntp/MIME_Message.php");

	define("NNTP_PORT", 119);

	// Define the return status code
	define("SERVER_READY", 200);
	define("SERVER_READY_NO_POST", 201);
	
	define("GROUP_SELECTED", 211);
	
	define("INFORMATION_FOLLOWS", 215);
	
	define("ARTICLE_HEAD_BODY", 220);
	define("ARTICLE_HEAD", 221);
	define("ARTICLE_BODY", 222);
	define("ARTICLE_OVERVIEW", 224);
	
	define("ARTICLE_POST_OK", 240);
	define("ARTICLE_POST_READY", 340);

	define("AUTH_ACCEPT", 281);
	define("MORE_AUTH_INFO", 381);
	define("AUTH_REQUIRED", 480);
	define("AUTH_REJECTED", 482);
	define("NO_PERMISSION", 502);


	class NNTP {
	
		var $nntp;
		var $server;
		var $user;
		var $pass;
		var $proxy_server;
		var $proxy_port;
		var $proxy_user;
		var $proxy_pass;
		var $use_proxy;
		var $error_number;
		var $error_message;
		
		
		function NNTP($server, $user = "", $pass = "", $proxy_server = "", $proxy_port = "", $proxy_user = "", $proxy_pass = "") {
			$this->server = $server;
			$this->user = $user;
			$this->pass = $pass;
			$this->proxy_server = $proxy_server;
			$this->proxy_port = $proxy_port;
			$this->proxy_user = $proxy_user;
			$this->proxy_pass = $proxy_pass;

			if ((strcmp($this->proxy_server, "") != 0) && (strcmp($this->proxy_port, "") != 0)) {
				$this->use_proxy = TRUE;
			} else {
				$this->use_proxy = FALSE;
			}
		}


		/* Open a TCP connection to the specific server
			Return:	TRUE - open succeeded
					FALSE - open failed
		*/
		function connect() {
			if ($this->nntp) {	// We won't try to re-connect an already opened connection
				return TRUE;
			}
			
			if ($this->use_proxy) {
				$this->nntp = @fsockopen($this->proxy_server, $this->proxy_port, $this->error_number, $this->error_message);
			} else {
				$this->nntp = @fsockopen($this->server, NNTP_PORT, $this->error_number, $this->error_message);
			}
			
			if ($this->nntp) {
				if ($this->use_proxy) {
					$response = "CONNECT ".$this->server.":".NNTP_PORT." HTTP/1.0\r\n";
					if ((strcmp($this->proxy_user, "") != 0) && (strcmp($this->proxy_pass, "") != 0)) {
						$response .= "Proxy-Authorization: Basic ";		// Only support Basic authentication type
						$response .= base64_encode($this->proxy_user.":".$this->proxy_pass);
						$response .= "\r\n";
					}
					$response = $this->send_request($response);
					if (strstr($response, "200 Connection established")) {
						fgets($this->nntp, 4096);	// Skip an empty line
						$response = $this->parse_response(fgets($this->nntp, 4096));
					} else {
						$response["status"] = NO_PERMISSION;	// Assign it to something dummy
						$response["message"] = "No permission";
					}
				} else {
					$response = $this->parse_response(fgets($this->nntp, 4096));
				}
				
				if (($response["status"] == SERVER_READY) || ($response["status"] == SERVER_READY_NO_POST)) {
					$this->send_request("mode reader");
					if (strcmp($this->user, "") != 0) {
						$response = $this->parse_response($this->send_request("authinfo user ".$this->user));
						
						if ($response["status"] == MORE_AUTH_INFO) {
							$response = $this->parse_response($this->send_request("authinfo pass ".$this->pass));
							
							if ($response["status"] == AUTH_ACCEPT) {
								return TRUE;
							}
						}
					} else {
						return TRUE;
					}
				}
				
				$this->error_number = $response["status"];
				$this->error_message = $response["message"];
			}
	
			return FALSE;
		}
		

		/* Close the TCP Connection
		*/
		function quit() {
			if ($this->nntp) {			
				$this->send_request("quit");
				fclose($this->nntp);

				$this->nntp = NULL;
			}
		}
		
		
		function parse_response($response) {
			$status = substr($response, 0, 3);
			$message = str_replace("\r\n", "", substr($response, 4));
			
			return array("status" => intval($status),
						"message" => $message);
		}
		
		
		function send_request($request) {
			if ($this->nntp) {
				fputs($this->nntp, $request."\r\n");
				fflush($this->nntp);
				
				return fgets($this->nntp, 4096);
			}
		}
		
		
		function read_response_body() {
			if ($this->nntp) {			
				$result = "";
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {
					$result .= $buf;
					$buf = fgets($this->nntp, 4096);
				}
				
				return $result;
			}
		}
	
		
		function join_group($group) {
			if ($this->nntp) {			
				$buf = $this->send_request("group ".$group);
				
				$response = $this->parse_response($buf);
				if ($response["status"] == GROUP_SELECTED) {
					$result = preg_split("/\s/", $response["message"]);
					
					return array("count" => $result[0],
								"start_id" => $result[1],
								"end_id" => $result[2],
								"group" => $result[3]);
				}
			}

			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return NULL;
		}
		
		
		function get_article_list($group) {
			if ($this->nntp) {			
				$buf = $this->send_request("listgroup ".$group);
				
				$response = $this->parse_response($buf);
				if ($response["status"] == GROUP_SELECTED) {
					$body = $this->read_response_body();
					return explode("\r\n", substr($body, 0, strlen($body) - 2));	// Cut the last \r\n
				}
			}

			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return false;
		}
		

		function get_group_list($group_pattern) {
			$response = $this->parse_response($this->send_request("list active ".$group_pattern));
			if ($response["status"] == INFORMATION_FOLLOWS) {
				$result = array();
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {
					list($group, $last, $first, $post) = preg_split("/\s+/", $buf, 4);
					$result[] = array($group,$last,$first,$post);
					$buf = fgets($this->nntp, 4096);
				}
				
				return $result;
			}
			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return FALSE;
		}		



		// The $group can have wildcard like comp.lang.*
		function get_groups_description($groups) {
			$response = $this->parse_response($this->send_request("list newsgroups ".$groups));
			if ($response["status"] == INFORMATION_FOLLOWS) {
				$result = array();
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {					
					list($key, $value) = preg_split("/\s+/", $buf, 2);
					$result[$key] = trim($value);
					$buf = fgets($this->nntp, 4096);
				}
				
				return $result;
			}
				
			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return FALSE;
		}


		// Get a message summary tree. The subject and sender will be matched with the reg_pat using regular expression
		function get_message_summary($start_id, $end_id, $reg_pat="//", $flat_tree=FALSE) {
			$buf = $this->send_request("xover ".$start_id."-".$end_id);
			$response = $this->parse_response($buf);
			$message_tree_root = new MessageTreeNode(NULL);
			$message_tree_root->set_show_children(TRUE);
			
			$ref_list = array();
			
			if ($response["status"] == ARTICLE_OVERVIEW) {
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {
					$elements = preg_split("/\t/", $buf);
					$elements[1] = decode_MIME_header($elements[1]);	// Decode subject
					$elements[2] = decode_MIME_header($elements[2]);	// Decode from
					if (preg_match($reg_pat, $elements[1]) || preg_match($reg_pat, $elements[2])) {
						$message_info = new MessageInfo();
						$message_info->nntp_message_id = $elements[0];
						$message_info->subject = $elements[1];
						$message_info->from = decode_sender($elements[2]);
						$message_info->date = strtotime($elements[3]);
						if ($message_info->date == -1) {
							$message_info->date = $elements[3];
						}
						$message_info->message_id = $elements[4];
						if (strlen($elements[5]) != 0) {
							$message_info->references = preg_split("/\s+/", trim($elements[5]));
						} else {
							$message_info->references = array();
						}
						$message_info->byte_count = $elements[6];
						$message_info->line_count = $elements[7];
						
						$message_tree_root->insert_message_info($message_info, $flat_tree);
						
						$ref_list[$message_info->nntp_message_id] = array($message_info->message_id, $message_info->references);
					}

					$buf = fgets($this->nntp, 4096);
				}
				
				return array($message_tree_root, $ref_list);
			}			

			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return NULL;
		}


		// Similar to the get_message_summary function, except that the processing is much
		// lightweight with the return is just an array of message summaries instead of
		// a tree plus a reference list.
		function get_summary($start_id, $end_id) {			
			$buf = $this->send_request("xover ".$start_id."-".$end_id);
			$response = $this->parse_response($buf);
			
			if ($response["status"] == ARTICLE_OVERVIEW) {
				$buf = fgets($this->nntp, 4096);
				$result = array();
				while (!preg_match("/^\.\s*$/", $buf)) {
					$elements = preg_split("/\t/", $buf);

					$nntp_id = $elements[0];
					$result[$nntp_id]["subject"] = decode_MIME_header($elements[1]);

					$from = decode_sender(decode_MIME_header($elements[2]));
					$result[$nntp_id]["from_name"] = $from["name"];
					$result[$nntp_id]["from_email"] = $from["email"];
					
					$result[$nntp_id]["date"] = strtotime($elements[3]);
					if ($result[$nntp_id]["date"] == -1) {
						$result[$nntp_id]["date"] = $elements[3];
					}
					
					$result[$nntp_id]["message_id"] = $elements[4];					
					$result[$nntp_id]["references"] = trim($elements[5]);
					$result[$nntp_id]["byte_count"] = $elements[6];
					$result[$nntp_id]["line_count"] = $elements[7];

					$buf = fgets($this->nntp, 4096);
				}
				
				return $result;
			}			

			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return NULL;
		}



		function get_header($message_id) {
			$response = $this->parse_response($this->send_request("head ".$message_id));
			if (($response["status"] == ARTICLE_HEAD) || ($response["status"] == ARTICLE_HEAD_BODY)) {
				$header = "";
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {
					$header .= $buf;
					$buf = fgets($this->nntp, 4096);
				}
				
				return new MIME_message($header);
			}
			
			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return NULL;
		}
		


		function get_article($message_id) {
			$response = $this->parse_response($this->send_request("article ".$message_id));
			if (($response["status"] == ARTICLE_BODY) || ($response["status"] == ARTICLE_HEAD_BODY)) {
				$message = "";
				$buf = fgets($this->nntp, 4096);
				while (!preg_match("/^\.\s*$/", $buf)) {
					$message .= $buf;
					$buf = fgets($this->nntp, 4096);
				}
				
				return new MIME_Message($message);
			}

			$this->error_number = $response["status"];
			$this->error_message = $response["message"];
			return NULL;
		}
		
		
		function post_article($subject, $name, $email, $newsgroups, $references, $message, $files) {
			global $messages_ini;
			
			$from = encode_MIME_header($name)." <".$email.">";
			$groups = "";
			foreach ($newsgroups as $news) {
				$groups = $groups.",".$news;
			}
			$groups = substr($groups, 1);
			$current_time = date("D, d M Y H:i:s O", time());
			
			if (strlen($groups) != 0) {
				$response = $this->parse_response($this->send_request("post"));
				
				if ($response["status"] == ARTICLE_POST_READY) {
					$send_message = "";

					// Send the header
					$send_message .= "Subject: ".encode_MIME_header($subject)."\r\n";
					$send_message .= "From: ".$from."\r\n";
					$send_message .= "Newsgroups: ".$groups."\r\n";
					$send_message .= "Date: ".$current_time."\r\n";
					$send_message .= "User-Agent: html4nntp http://html4nntp.sourceforge.net/\r\n";
					$send_message .= "X-Trace-html4nntp: ".gethostbyaddr(getIP())." ".getIP()."\r\n";
					$send_message .= "Mime-Version: 1.0\r\n";
					
					if (sizeof($files) != 0) {	// Handling uploaded files
						srand();
						$boundary = "----------".rand().time();
						$send_message .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\r\n";
						$boundary = "--".$boundary;
					} else {
						$boundary = "";
						$send_message .= "Content-Type: text/plain\r\n";
					}

					if ($references && (strlen($references) != 0)) {
						$send_message .= "References: ".$references."\r\n";
					}

					$send_message .= "\r\n";	// Header body separator

					$send_message .= create_message_body($message, $files, $boundary);
					
					// Send the body
					fputs($this->nntp, $send_message);

					$response = $this->parse_response($this->send_request("\r\n."));

					if ($response["status"] == ARTICLE_POST_OK) {
						// Return the message sent with all the attachments stripped
						if (sizeof($files) != 0) {	// There is attachment, strip it
							$len = strpos($send_message, $boundary, strpos($send_message, $boundary) + strlen($boundary));
							$send_message = substr($send_message, 0, $len);
							
							$send_message .= "\r\n";
							$send_message .= sizeof($files);
							$send_message .= $messages_ini["text"]["post_attachments"];
							$send_message .= "\r\n".$boundary."--";
						}
						
						return new MIME_Message($send_message);
					} else {
						$this->error_number = $response["status"];
						$this->error_message = $response["message"];
					}
				}
			}
			return NULL;
		}
		
		
		function get_error_number() {
			return $this->error_number;
		}
		
		
		function get_error_message() {
			return $this->error_message;
		}
	}
	
	
	class MessageInfo {
		var $nntp_message_id;
		var $subject;
		var $from;
		var $date;
		var $message_id;
		var $references;
		var $byte_count;
		var $line_count;
	}
	
	
	class MessageTreeNode {
		var $message_info;
		var $children;
		var $show_children;
		
		function MessageTreeNode($message_info) {
			$this->message_info = $message_info;
			$this->children = array();
			$this->show_children = FALSE;
		}


		function set_show_children($show) {
			$this->show_children = $show;
		}
		

		function set_show_all_children($show) {
			$this->set_show_children($show);
			
			$keys = $this->get_children_keys();
			foreach ($keys as $key) {
				$child =& $this->get_child($key);
				$child->set_show_all_children($show);
			}
		}


		function is_show_children() {
			return $this->show_children;
		}


		function set_message_info($message_info) {
			$this->message_info = $message_info;
		}
		
		
		function get_message_info() {
			return $this->message_info;
		}
		
		
		function set_child($key, &$child) {
			$this->children[$key] = $child;
		}
		
		
		function &get_child($key) {
			if (isset($this->children[$key])) {
				return $this->children[$key];
			} else {
				return NULL;
			}
		}
		
		
		function count_children() {
			return sizeof($this->children);
		}


		function get_children_keys() {
			return array_keys($this->children);
		}
		
		
		function get_children($start = 0, $length = -1) {
			if ($length == -1) {
				return array_slice($this->children, $start);
			} else {
				return array_slice($this->children, $start, $length);
			}
		}


		function insert_message_info($message_info, $flat_tree) {
			$node =& $this;

			if (!$flat_tree) {
				foreach ($message_info->references as $ref_no) {
					$tmpnode =& $node->get_child($ref_no);
					
					if ($tmpnode != NULL) {
						$node =& $tmpnode;
					} else {
						$tmp_info = new MessageInfo();
						$tmp_info->nntp_message_id = -1;
						$tmp_info->message_id = $ref_no;
						$tmp_info->date = 0;
						$newnode = new MessageTreeNode($tmp_info);
						$node->set_child($ref_no, $newnode);
						
						$node =& $node->get_child($ref_no);
					}
				}
			}
			
			$child =& $node->get_child($message_info->message_id);
			
			if ($child == NULL) {
				$child = new MessageTreeNode($message_info);
			} else {
				$child->set_message_info($message_info);
			}

			$node->set_child($message_info->message_id, $child);
		}
		
		
		function merge_tree($root_node) {
			// If 2 children have the same key, the new one will replace the current one
			$keys = $root_node->get_children_keys();
			
			foreach ($keys as $key) {
				$child =& $root_node->get_child($key);
				$message_info = $child->get_message_info();
				$ref_list = $message_info->references;
				$node =& $this;
				
				if (sizeof($ref_list) != 0) {
					foreach ($ref_list as $ref) {
						$tmp =& $node->get_child($ref);
						if ($tmp != NULL) {
							$node =& $tmp;
						}
					}
				}
				
				$node->set_child($key, $child);
			}
		}
		

		function compact_tree() {

			$children_keys = $this->get_children_keys();
			
			foreach ($children_keys as $child_key) {
				$child =& $this->get_child($child_key);
				$child->compact_tree();
				
				$info = $child->get_message_info();
				if ($info->nntp_message_id == -1) {					
					// Need to remove this child and promote it's children
					$keys = $child->get_children_keys();
					
					foreach ($keys as $key) {
						$tmp_node =& $child->get_child($key);
						$this->set_child($key, $tmp_node);
					}
					unset($this->children[$child_key]);
				}
			}
		}


		function sort_message($field, $asc) {
			$function_name = "compare_by_".$field;
			
			if ($asc) {
				$function_name .= "_asc";
			} else {
				$function_name .= "_desc";
			}
			
			if (method_exists($this, $function_name)) {
				if (sizeof($this->children) != 0) {
					uasort($this->children, array($this, $function_name));			
				}	
			}
		}


		function deep_sort_message($field, $asc) {
			$this->sort_message($field, $asc);
			
			if (sizeof($this->children) != 0) {
				$keys = $this->get_children_keys();
				
				foreach ($keys as $key) {
					$child =& $this->get_child($key);
					$child->deep_sort_message($field, $asc);
				}
			}	
		}
			
			
		function compare_by_subject_asc($node_1, $node_2) {
			$subject_1 = $node_1->get_message_info();
			$subject_2 = $node_2->get_message_info();
			
			$subject_1 = $subject_1->subject;
			$subject_2 = $subject_2->subject;
			
			return strcasecmp($subject_1, $subject_2);
		}


		function compare_by_subject_desc($node_1, $node_2) {
			$subject_1 = $node_1->get_message_info();
			$subject_2 = $node_2->get_message_info();
			
			$subject_1 = $subject_1->subject;
			$subject_2 = $subject_2->subject;
			
			return strcasecmp($subject_2, $subject_1);
		}


		function compare_by_from_asc($node_1, $node_2) {
			$from_1 = $node_1->get_message_info();
			$from_2 = $node_2->get_message_info();
			
			$from_1 = $from_1->from["name"];
			$from_2 = $from_2->from["name"];
			
			return strcasecmp($from_1, $from_2);
		}


		function compare_by_from_desc($node_1, $node_2) {
			$from_1 = $node_1->get_message_info();
			$from_2 = $node_2->get_message_info();
			
			$from_1 = $from_1->from["name"];
			$from_2 = $from_2->from["name"];
			
			return strcasecmp($from_2, $from_1);
		}


		function compare_by_date_asc($node_1, $node_2) {
			$date_1 = $node_1->get_message_info();
			$date_2 = $node_2->get_message_info();
			
			$date_1 = $date_1->date;
			$date_2 = $date_2->date;
			
			if ($date_1 < $date_2) {
				return -1;
			} else if ($date_1 > $date_2) {
				return 1;
			} else {
				return 0;
			}
		}


		function compare_by_date_desc($node_1, $node_2) {
			$date_1 = $node_1->get_message_info();
			$date_2 = $node_2->get_message_info();
			
			$date_1 = $date_1->date;
			$date_2 = $date_2->date;
			
			if ($date_1 > $date_2) {
				return -1;
			} else if ($date_1 < $date_2) {
				return 1;
			} else {
				return 0;
			}
		}		
	}		
?>
