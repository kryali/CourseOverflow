<?php
/******************************************************************************		
 * $Id: html4nntp.cfg.php,v 1.7 2004/10/19 21:29:12 svanpo Exp $
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

/******************************************************************/
/*	SERVER SETTINGS                                               */
/*	This part configurate the server settings                     */
/******************************************************************/
	// NNTP Server setting
//	$nntp_server = "news.toutprogrammer.com";
	$nntp_server = "localhost";
	$user = "";
	$pass = "";
	
	// Proxy Server settings. Set it to empty string for not using it
	$proxy_server = "";
	$proxy_port = "";
	$proxy_user = "";
	$proxy_pass = "";
	
	// Session name. Set it to a unique string that can represent your site.
	$session_name = "html4nntp";

	// List of subscribed newsgroups
	$newsgroups_list = array("*");
	$default_group = "test";

	// Group or not group newsgroups on the table of contents
	$group_newsgroups = true;
	
	// Groups defined on the table of contents (no wildcard here)
	$groups_toc = array("class");
	
	// Display or not the table of contents
	$display_toc = true;

	// Name of the main page
	$main_page = "newsgroups.php";

/******************************************************************/
/*	SECURITY SETTINGS                                             */
/*	This part configurate the security settings                   */
/******************************************************************/
	// auth_level = 1  ------  No need to perform authentication
	// auth_level = 2  ------  Perform authentication only when posting message
	// auth_level = 3  ------  Perform authentication in any operation
	$auth_level = 3;

	// The URL of the page shown after user logout
	// It can be a relative or absolute address
	// If protocol other than HTTP or HTTPS is used, please use absolute path
	// You can also use the variable "$_SERVER['HTTP_HOST']" to extract the current host name
	// e.g. $logout_url = "ftp://".$_SERVER['HTTP_HOST']."/mypath";
	$logout_url = "newsgroups.php";
	
	// Realm to be used in the user authetication
	$realm = "html4nntp";

/******************************************************************/
/*	PAGE DISPLAY SETTINGS                                         */
/*	This part set the limit constants                             */
/******************************************************************/
	// Page splitting settings
	$message_per_page = 25;
	$message_per_page_choice = array(25, 50, 75, 100, "all");
	$pages_per_page = 10;

	// 	Default language
	//$text_ini = "config/messages_en_us.ini";
	$text_ini = "config/messages_fr_fr.ini";

	$locale_list = array("en_us" => "English (US)",
						"fr_fr" => "Français (FR)",
						);
						
	// Charset to use 
	$charset = "iso-8859-1";
						
	// Filter the javascript or jscript
	$filter_script = true;

/******************************************************************/
/*	DEFAULT/LIMIT VALUES SETTINGS                                 */
/*	This part set the the default values or limits                */
/******************************************************************/
	// TRUE if the message tree is all expanded when first loaded, FALSE otherwise
	$default_expanded = TRUE;
	
	// TRUE if posting across several subscribed newsgroups is allowed
	$allow_cross_post = FALSE;

	// TRUE if posting files is allowed
	$can_post_file = FALSE;

	// Upload file size limit
	$upload_file_limit = 1048576;	//1M

	// The length limit for the subject and sender
	$subject_length_limit = 100;
	$sender_length_limit = 20;

	// Path to the images
	$image_base = "images/html4nntp/";	
	
/******************************************************************/
/*	MISC SETTINGS                                                 */
/*	This part set miscellaneous settings                          */
/******************************************************************/
	// Display or not display W3C validators logo
	$show_validate = true;


/******************************************************************/
/*	FEED SETTINGS                                                 */
/*	This part configurate the feed settings                       */
/******************************************************************/
	// Name of the cache
	$feed_cache_dir = "cache";
	
	// Full URL to the logo
	$feed_logo = "http://html4nntp.sourceforge.net/images/html4nntp.gif";

/******************************************************************/
/*	TEMPLATE SETTINGS                                             */
/******************************************************************/
	// The template script should contain at least 3 statement as:
	//
	// ob_start();
	// include($content_page);
	// ob_end_flush();
	//
	// If you want to support autoscroll, please also include the following in the BODY tag
	//
	// if (isset($on_load_script)) {
	//		echo "onLoad=\"$on_load_script\"";
	//	}
	$template = "html4nntp/template.php";

//	template2.php includes a fancy welcome header
//	$template = "template2.php";
?>
