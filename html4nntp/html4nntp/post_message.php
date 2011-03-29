<?php
/******************************************************************************		
 * $Id: post_message.php,v 1.7 2004/10/19 21:29:12 svanpo Exp $
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
	$reply_references = "";

	if (!$nntp->connect()) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["nntp_fail"]."</div>";
		echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
	} else {
		if (is_requested("reply_id")) {	
			$reply_id = get_request("reply_id");
			if (isset($_SESSION["result"]) && $_SESSION["result"]) {				
				$ref_list = $_SESSION["result"][1];
				
				foreach ($ref_list[$reply_id][1] as $ref) $reply_references = $reply_references." ".$ref;
				
				$reply_references = $reply_references." ".$ref_list[$reply_id][0];
			} else {
				$group_info = $nntp->join_group($_SESSION["newsgroup"]);
				
				if ($group_info == NULL) {					
					$error_messages[] = "<div class=\"nntp-usererror\">".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]."</div><div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
				} else {
					$MIME_Message = $nntp->get_header($reply_id);
					$header = $MIME_Message->get_main_header();
		
					if ($header == NULL) {
						$error_messages[] = $messages_ini["error"]["header_fail"]."$reply_id. ".$nntp->get_error_message();
					} else {
						$reply_references = $header["references"]." ".$header["message-id"];
					}	
				}
			}
			$reply_references = trim($reply_references);
		}
		
		$header = array();
		
		// Copy the request parameter
		if (is_requested("subject")) {
			$subject = get_request("subject");
		}
		if (is_requested("groups")) {
			$groups = get_request("groups");
		}
		if (is_requested("name")) {
			$name = get_request("name");
		}
		if (is_requested("email")) {
			$email = get_request("email");
		}
		if (is_requested("attachment")) {
			$attachment = get_request("attachment");
		}
		if (is_requested("message")) {
			$message = get_request("message");
		}
		if (is_requested("save_name_mail")) {
			$save_name_mail = get_request("save_name_mail");
		}
		// Done

		if (is_requested("post")) {
			if (!isset($subject) || (strlen($subject) == 0)) {
				$subject = "(no subject)";
			}
	
			if (isset($groups) && (sizeof($groups) != 0)) {
				foreach ($groups as $group) {
					if (in_array($group, $newsgroups_list)) {
						$news[] = $group;
					}
				}
			} else {
				$error_messages[] = $messages_ini["error"]["no_newsgroup"];
			}
	
			if (!isset($name) || (strlen($name) == 0)) {
				$error_messages[] = $messages_ini["error"]["no_name"];
			}
			
			if (!isset($email) || (strlen($email) == 0) || !validate_email($email)) {
				$error_messages[] = $messages_ini["error"]["no_email"];
			} /*else if (!check_email_list($email)) {
				$error_messages[] = "Your e-mail address is not in the authorized list. Please contact the administrator.";
			}
	*/
			$files = array();
			if ((isset($attachment)) && ($can_post_file)) {
				$file_size = 0;
				foreach ($_FILES as $file) {
					if (is_uploaded_file($file['tmp_name'])) {
						$files[] = $file;
						$file_size += filesize($file['tmp_name']);
						if ($file_size > $upload_file_limit) {
							$error_messages[] = $messages_ini["error"]["exceed_size"].($upload_file_limit >> 10)."Kb";
							break;
						}
					}
				}
			}
			
			// Strip all the slashes
			if (get_magic_quotes_gpc()) {
				$subject = stripslashes($subject);
				$name = stripslashes($name);
				$email = stripslashes($email);
				$message = stripslashes($message);
			}
	
			if (sizeof($error_messages) == 0) {
?>
			<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>">
				<input type="hidden" name="renew" value="1" />
				<input type="submit" value="<?=$messages_ini["control"]["return"] ?>" />
<?php
				// Save the name and email in the session
				if ($save_name_mail) {
					$_SESSION["wn_name"] = $name;
					$_SESSION["wn_email"] = $email;
					$_SESSION["wn_save_name_mail"] = $save_name_mail;
				} else {
					unset($_SESSION["wn_name"]);
					unset($_SESSION["wn_email"]);
					unset($_SESSION["wn_save_name_mail"]);
				}
				
				if ($MIME_Message = $nntp->post_article($subject, $name, $email, $news, $reply_references, $message, $files)) {
					echo "<center><b>".$messages_ini["text"]["posted"]."</b></center><br>";

					include("html4nntp/article_template.php");
				} else {
					echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["post_fail"]."</div>";
					echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
				}
				
				unset($_SESSION["attach_count"]);
			}
?>
			</form>
<?php
		}
		if (is_requested("add_file") || (sizeof($error_messages) != 0)) {
			$subject = htmlescape($subject);
			$name = htmlescape($name);
			$email = htmlescape($email);
			include("html4nntp/compose_template.php");
		}

		$nntp->quit();
	}
?>
