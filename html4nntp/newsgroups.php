<?php
/******************************************************************************		
 * $Id: newsgroups.php,v 1.12 2004/11/25 06:02:58 svanpo Exp $
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

	$content = 4;

	// Import the NNTP Utility
	require("html4nntp/nntp.php");
	require("config/html4nntp.cfg.php");
	
	// Increase the maximum running time to 2 mins (default is 30sec)
	set_time_limit(120);

	// Start the session before output anything
	session_name($session_name);
	session_start();
	
	// Set charset encoding and document type
	header("Content-Type: text/html; charset=$charset");

	if (is_requested("set")) {	// Save the advanced options into cookies
		$expire = 2147483647;	// Maximum integer
		setcookie("wn_pref_lang", get_request("language"), $expire);
		setcookie("wn_pref_mpp", get_request("msg_per_page"), $expire);
		
		if ($_COOKIE["wn_pref_mpp"] != get_request("msg_per_page")) {
			$change_mpp = TRUE;
		} else {
			$change_mpp = FALSE;
		}

		$_COOKIE["wn_pref_lang"] = get_request("language");
		$_COOKIE["wn_pref_mpp"] = get_request("msg_per_page");
	}

	// Read the messages file
	if (isset($_COOKIE["wn_pref_lang"])) {
		$text_ini = "config/messages_".$_COOKIE["wn_pref_lang"].".ini";
	}
	$messages_ini = read_ini_file($text_ini, true);
	$messages_ini_normal = $messages_ini;
	foreach ($messages_ini as $groupname => $data) { // Encode HTML entities
		foreach ($messages_ini[$groupname] as $key => $value) { // Encode HTML entities
			$messages_ini[$groupname][$key] = htmlentities($value);
		}
	}

	// Check if register_global is activated
	if (ini_get('register_globals')) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["active_rg"]."</div>\n";
		return;
	}

	// Check if this file as the same name as parameter $main_page (need for attachments)
	if (basename($_SERVER["SCRIPT_NAME"])!=$main_page) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["bad_main_page"]."</div>\n";
		return;
	}

	// Perform logout
	if (is_requested("logout")) {
		$user = "";
		$pass = "";
		unset($_SESSION["auth"]);
		$_SESSION["logout"] = TRUE;
		unset($_SESSION["result"]);		// Destroy the subject tree.
			
		header("Location: ".construct_url($logout_url));
		exit;
	} else if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
		$user = $_SERVER['PHP_AUTH_USER'];
		$pass = $_SERVER['PHP_AUTH_PW'];
	}

	if ($auth_level > 1) {
		if (($auth_level == 3) || (is_requested("compose") && ($auth_level == 2))) {
			// Do HTTP Basic authentication
			if ($_SESSION["logout"] || !isset($_SERVER['PHP_AUTH_USER'])) {
				unset($_SESSION["logout"]);
				header('WWW-Authenticate: Basic realm="'.$realm.'"');
				header('HTTP/1.0 401 Unauthorized');
				echo $messages_ini["authorization"]["login"];
				exit;
			} else {
				// $_SESSION["auth"] must be checked firsr to avoid making too many connections
				if ($_SESSION["auth"] || verify_login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
					$user = $_SERVER['PHP_AUTH_USER'];
					$pass = $_SERVER['PHP_AUTH_PW'];
					$_SESSION["auth"] = TRUE;
				} else {
					header('WWW-Authenticate: Basic realm="'.$realm.'"');
					header('HTTP/1.0 401 Unauthorized');
					echo $messages_ini["authorization"]["login"];
					exit;
				}
			}
			// Authentication done
		}
	} else {
		$_SESSION["auth"] = TRUE;
	}

	// Create the NNTP object
	$nntp = new NNTP($nntp_server, $user, $pass, $proxy_server, $proxy_port, $proxy_user, $proxy_pass);


	// Authenticate with CourseOverflow API
	$json = getJSONFromAPI("?action=authenticate&netid=".$user."&password=".$pass);

	// Load the newsgroups_list
	$connected = FALSE;
	if (!isset($_SESSION["newsgroups_list"])) {	// Need to update the newsgroups_list first
		$_SESSION["newsgroups_list"] = array();
		foreach ($newsgroups_list as $group) {
			if (strpos($group, "*") !== FALSE) {	// Group name have wildmat, expand it.
				if (!$nntp->connect()) {
					$nntp->quit();
					$content_page = "html4nntp/show_error.php";
					$nntp_usermsg = $messages_ini["error"]["nntp_fail"];
					$nntp_error = $nntp->get_error_message();
					include ($template);
					exit;
				}				

				$connected = TRUE;
				$group_list = $nntp->get_group_list($group);
				if ($group_list !== FALSE) {
					for ($i=0;$i<count($group_list); $i++) $_SESSION["newsgroups_list"][] = $group_list[$i][0];
				}
			} else {
				$group_list = $nntp->get_group_list($group);
				if ($group_list!==FALSE) $_SESSION["newsgroups_list"][] = $group;
			}
		}		
	}
	if ($connected) {
		$nntp->quit();
	}
	$newsgroups_list = $_SESSION["newsgroups_list"];
	sort($newsgroups_list);
	
	if (isset($_REQUEST["art_group"])) $_SESSION["newsgroup"]=$_REQUEST["art_group"]; 
	if (is_requested("cancel")) {
		// Back to show header
		$renew = 0;
		unset($_REQUEST["compose"]);
		$content_page = "html4nntp/show_header.php";
	} elseif (is_requested("attachment_id") && is_requested("message_id")) {
		$attachment_id = get_request("attachment_id");
		$message_id = get_request("message_id");
		$content_page = "html4nntp/attachment_handler.php";
	} elseif (is_requested("compose")) {
		$compose = get_request("compose");
		if (strcasecmp($compose, "post") == 0) {
			// Do add_file or post
			$content_page = "html4nntp/post_message.php";
		} else {
			$content_page = "html4nntp/compose_message.php";
		}
	} elseif (is_requested("preferences")) {
		$content_page = "html4nntp/preferences.php";
	} else {
		$renew = 0;
		if (is_requested("group") 
				&& in_array(get_request("group"), $newsgroups_list) 
				&& strcmp(get_request("group"), $_SESSION["newsgroup"])) {
			$_SESSION["newsgroup"] = get_request("group");
			$renew = 1;
		} else {
			if ((is_requested("home")) && ($display_toc)) {
				unset($_SESSION["newsgroup"]);
			} elseif (!isset($_SESSION["newsgroup"])) {
				if (!$display_toc) {
					$_SESSION["newsgroup"] = $default_group;
					$renew = 1;
				}
			}
		}
		
		if (is_requested("article_id")) {
			$article_id = get_request("article_id");
			$content_page = "html4nntp/show_article.php";
		} elseif ((isset($_SESSION["newsgroup"])) || (!$display_toc)) {
			$content_page = "html4nntp/show_header.php";

			if (is_requested("renew")) {
				$renew = get_request("renew");
			} elseif ($change_mpp && !isset($_SESSION["search_txt"])) {
				$renew = 1;
			}

			if (is_requested("mid")) {
				$mid = get_request("mid");
				$on_load_script = "location = '#".$mid."';";
			}
		} else {
			$content_page = "html4nntp/show_toc.php";
		}
	}

	include ($template);
?>
