<?php
/******************************************************************************		
 * $Id: compose_message.php,v 1.3 2004/09/26 17:41:51 svanpo Exp $
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

	unset($_SESSION["attach_count"]);
	if ((strcasecmp($compose,"reply") == 0) && is_requested("mid")) {
//		$nntp = new NNTP($nntp_server, $user, $pass);
		$reply_id = get_request("mid");
		
		if (!$nntp->connect()) {
			echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["nntp_fail"]."</div>";
			echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
		} else {
			$group_info = $nntp->join_group($_SESSION["newsgroup"]);
			
			if ($group_info == NULL) {				
				echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </div>";
				echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
			} else {
				$MIME_Message = $nntp->get_article($reply_id);
				$header = $MIME_Message->get_main_header();
				
				$subject = htmlescape($header["subject"]);
				if (strcasecmp(substr($subject, 0, 3), "Re:") != 0) {
					$subject = "Re: ".$subject;
				}

				$message = "";
				foreach ($MIME_Message->get_all_parts() as $part) {
					if (stristr($part["header"]["content-type"], "text")) {
						$message .= decode_message_content($part);
					}
				}

				$message = preg_replace("/(.*\r\n)/", "&gt; $1", htmlescape($message));
				$message = $header["from"]["name"]." ".$messages_ini["text"]["wrote"].":\r\n\r\n".$message;
			}
		
			$nntp->quit();
		}
	}
	
	$name = $_SESSION["wn_name"];
	$email = $_SESSION["wn_email"];
	$save_name_mail = $_SESSION["wn_save_name_mail"];

/*
	if (strcmp($_COOKIE["wn_pref_sign".$user], "1") == 0) {
		if (strcmp($_COOKIE["wn_pref_sign_txt".$user], "") != 0) {
			$message .= "\r\n\r\n--\r\n".$_COOKIE["wn_pref_sign_txt".$user];
		}
	}
*/	

	include("html4nntp/compose_template.php");
?>
