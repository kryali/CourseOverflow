<?php
/******************************************************************************		
 * $Id: util.php,v 1.10 2004/11/01 08:32:37 svanpo Exp $
 *
 * Authors: St�phane Vanpoperynghe  (svanpoperynghe@toutprogrammer.com)
 *          Terence Yim             (chtyim@gmail.com)
 *
 * Copyright 2004 Terence Yim, St�phane Vanpoperynghe
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

require("html4nntp/uucoder.php");

$MIME_TYPE_MAP = array("txt"=>"text/plain",
						"html"=>"text/html",
						"htm"=>"text/html",
						"aif"=>"audio/x-aiff",
						"aiff"=>"audio/x-aiff",
						"aifc"=>"audio/x-aiff",
						"wav"=>"audio/wav",
						"gif"=>"image/gif",
						"jpg"=>"image/jpeg",
						"jpeg"=>"image/jpeg",
						"tif"=>"image/tiff",
						"tiff"=>"image/tiff",
						"png"=>"image/x-png",
						"xbm"=>"image/x-xbitmap",
						"bmp"=>"image/bmp",
						"avi"=>"video/x-msvideo",
						"mpg"=>"video/mpeg",
						"mpeg"=>"video/mpeg",
						"mpe"=>"video/mpeg",
						"ai"=>"application/postscript",
						"eps"=>"application/postscript",
						"ps"=>"application/postscript",
						"hqx"=>"application/mac-binhex40",
						"pdf"=>"application/pdf",
						"zip"=>"application/x-zip-compressed",
						"gz"=>"application/x-gzip-compressed",
						"doc"=>"application/msword",
						"xls"=>"application/vnd.ms-excel",
						"ppt"=>"application/vnd.ms-powerpoint");

function getIP() {
	if ($_SERVER[HTTP_X_FORWARDED_FOR]!="") return($_SERVER[HTTP_X_FORWARDED_FOR]);
	return($_SERVER[REMOTE_ADDR]);
}
	
function decode_MIME_header($str) {
	while (preg_match("/(.*)=\?.*\?q\?(.*)\?=(.*)/i", $str, $matches)) {
		$str = str_replace("_", " ", $matches[2]);
		$str = $matches[1].quoted_printable_decode($str).$matches[3];
	}
	while (preg_match("/=\?.*\?b\?.*\?=/i", $str)) {
		$str = preg_replace("/(.*)=\?.*\?b\?(.*)\?=(.*)/ie", "'$1'.base64_decode('$2').'$3'", $str);
	}

	return $str;
}


function encode_MIME_header($str) {
	if (is_non_ASCII($str)) {
		$result = "=?ISO-8859-1?Q?";
		for ($i = 0;$i < strlen($str);$i++) {
			$ascii = ord($str{$i});
			if ($ascii == 0x20) {	// Space
				$result .= "_";
			} else if (($ascii == 0x3D) || ($ascii == 0x3F) || ($ascii == 0x5F) || ($ascii > 0x7F)) {	// =, ?, _, 8 bit
				$result .= "=".dechex($ascii);
			} else {
				$result .= $str{$i};
			}
		}
		$result .= "?=";
	} else {
		$result = $str;
	}
	
	return $result;
}
	
function is_non_ASCII($str) {
	for ($i = 0;$i < strlen($str);$i++) {
		if (ord($str{$i}) > 0x7f) {
			return true;
		}
	}
	
	return FALSE;
}

function htmlescape($str) {
	$str = htmlspecialchars($str);
	return preg_replace("/&amp;#(x?[0-9A-F]+);/", "&#\\1;", $str);
}

function chop_str($str, $len) {
	if (strlen($str) > $len) {
		$str = substr($str, 0, $len - 3)."...";
	}
	
	return $str;
}
	
function format_date($date) {
	global $messages_ini;

	$current = time();
	$current_date = getdate($current);
	
	$today = mktime(0, 0, 0, $current_date["mon"], $current_date["mday"], $current_date["year"]);
	$last_week = $today - 518400;

	if ($date >= $today) {
		// Today
		return "<span class=\"today\">".htmlentities(date($messages_ini["text"]["date_today"], $date))."</span>";
	} elseif ($date >= $last_week) {
		// Within one week
		$label_date = date($messages_ini["text"]["date_week"], $date);
		$label_date = str_replace("Mon", $messages_ini["text"]["day_short_monday"], $label_date);
		$label_date = str_replace("Tue", $messages_ini["text"]["day_short_tuesday"], $label_date);
		$label_date = str_replace("Wed", $messages_ini["text"]["day_short_wednesday"], $label_date);
		$label_date = str_replace("Thu", $messages_ini["text"]["day_short_thursday"], $label_date);
		$label_date = str_replace("Fri", $messages_ini["text"]["day_short_friday"], $label_date);
		$label_date = str_replace("Sat", $messages_ini["text"]["day_short_saturday"], $label_date);
		$label_date = str_replace("Sun", $messages_ini["text"]["day_short_sunday"], $label_date);
		return "<span class=\"week\">".htmlentities($label_date)."</span>";
	} else {
	
		$label_date = date($messages_ini["text"]["date_month"], $date);
		$label_date = str_replace("Jan", $messages_ini["text"]["month_short_jan"], $label_date);
		$label_date = str_replace("Feb", $messages_ini["text"]["month_short_feb"], $label_date);
		$label_date = str_replace("Mar", $messages_ini["text"]["month_short_mar"], $label_date);
		$label_date = str_replace("Apr", $messages_ini["text"]["month_short_apr"], $label_date);
		$label_date = str_replace("May", $messages_ini["text"]["month_short_may"], $label_date);
		$label_date = str_replace("Jun", $messages_ini["text"]["month_short_jun"], $label_date);
		$label_date = str_replace("Jul", $messages_ini["text"]["month_short_jul"], $label_date);
		$label_date = str_replace("Aug", $messages_ini["text"]["month_short_aug"], $label_date);
		$label_date = str_replace("Sep", $messages_ini["text"]["month_short_sep"], $label_date);
		$label_date = str_replace("Oct", $messages_ini["text"]["month_short_oct"], $label_date);
		$label_date = str_replace("Nov", $messages_ini["text"]["month_short_nov"], $label_date);
		$label_date = str_replace("Dec", $messages_ini["text"]["month_short_dec"], $label_date);
		return htmlentities($label_date);
	}
}
	
function decode_sender($sender) {
	if (preg_match("/(['|\"])?(.*)(?(1)['|\"]) <([\w\-=!#$%^*'+\\.={}|?~]+@[\w\-=!#$%^*'+\\.={}|?~]+[\w\-=!#$%^*'+\\={}|?~])>/", $sender, $matches)) {
		// Match address in the form: Name <email@host>
		$result["name"] = $matches[2];
		$result["email"] = $matches[sizeof($matches) - 1];
	} elseif (preg_match("/([\w\-=!#$%^*'+\\.={}|?~]+@[\w\-=!#$%^*'+\\.={}|?~]+[\w\-=!#$%^*'+\\={}|?~]) \((.*)\)/", $sender, $matches)) {
		// Match address in the form: email@host (Name)
		$result["email"] = $matches[1];
		$result["name"] = $matches[2];
	} else {
		// Only the email address present
		$result["name"] = $sender;
		$result["email"] = $sender;
	}
	
	$result["name"] = str_replace("\"", "", $result["name"]);
	$result["name"] = str_replace("'", "", $result["name"]);

	return $result;
}
	
function replace_links($matches) {
	if (!preg_match("/^(?:http|https|ftp|ftps|news):\/\//i", $matches[1])) {
		return "<a href=\"mailto:$matches[2]\">$matches[2]</a>";
	} else {
		return $matches[1].$matches[2];
	}
}

function add_html_links($str) {
	// Add link for e-mail address
	$str = preg_replace_callback("/((?:http|https|ftp|ftps|news):\/\/.*)?([\w\-=!#$%^*'+\\.={}|?~]+@[\w\-=!#$%^*'+\\.={}|?~]+[\w\-=!#$%^*'+\\={}|?~])/i", "replace_links", $str);

	// Add link for web and newsgroup
	$str = preg_replace("/(http|https|ftp|ftps|news)(:\/\/[\w;\/?:@&=+$\-\.!~*'()%#&]+)/i", "<a href=\"$1$2\">$1$2</a>", $str);

	return $str;
}

function validate_email($email) {
	return preg_match("/[\w\-=!#$%^*'+\\.={}|?~]+@[\w\-=!#$%^*'+\\.={}|?~]+[\w\-=!#$%^*'+\\={}|?~]/", $email);
}

function decode_message_content($part) {
	$encoding = $part["header"]["content-transfer-encoding"];
	
	if (stristr($encoding, "quoted-printable")) {
		return quoted_printable_decode($part["body"]);
	} else if (stristr($encoding, "base64")) {
		return base64_decode($part["body"]);
	} else if (stristr($encoding, "uuencode")) {
		return uudecode($part["body"]);
	} else {	// No need to decode
		return $part["body"];
	}
}

function decode_message_content_output($part) {
	$encoding = $part["header"]["content-transfer-encoding"];
	
	if (stristr($encoding, "quoted-printable")) {
		echo quoted_printable_decode($part["body"]);
	} else if (stristr($encoding, "base64")) {
		echo base64_decode($part["body"]);
	} else if (stristr($encoding, "uuencode")) {
		uudecode_output($part["body"]);
	} else {	// No need to decode
		echo $part["body"];
	}
}		

// This function return an appropriately encoded message body.
function create_message_body($message, $files, $boundary = "") {
	$message_body = "";
	
	// Need to process the message to change line begin with . to ..
	$message = preg_replace(array("/\r\n/","/^\.(.*)/m", "/\n/"), array("\n","..$1", "\r\n"), $message);

	if (sizeof($files) != 0) {	// Handling uploaded files. Format it as MIME multipart message
		// Read the content of each file
		$counter = 0;
		$message_body .= "This is a multi-part message in MIME format\r\n";
		$message_body .= $boundary."\r\n";
		$message_body .= "Content-Type: text/plain\r\n";
		$message_body .= "\r\n";
		$message_body .= $message;
		$message_body .= "\r\n\r\n";

		foreach ($files as $file) {
			$message_body .= $boundary."\r\n";
			$message_body .= "Content-Type: ".$file['type']."\r\n";
			$message_body .= "Content-Transfer-Encoding: base64\r\n";
			$message_body .= "Content-Disposition: inline; filename=\"".$file['name']."\"\r\n";
			$message_body .= "\r\n";

			$fd = fopen($file['tmp_name'], "rb");
			$tmp_buf = "";
			while ($buf = fread($fd, 1024)) {
				$tmp_buf .= $buf;
			}
			fclose($fd);
			$tmp_buf = base64_encode($tmp_buf);
			$offset = 0;
			while ($offset < strlen($tmp_buf)) {
				$message_body .= substr($tmp_buf, $offset, 72)."\r\n";
				$offset += 72;
			}
		}
		
		$message_body .= $boundary."--\r\n";
	} else {	// Write the plain text only
		$message_body .= $message;
	}
	
	return $message_body;
}

function filter_html($body) {
	global $filter_script;
	
	// rename the body tag
	$body = preg_replace("/<(\s*)(\/?)(\s*)(body)(.*?)>/is", "<\\2x\\4\\5>", $body);
	
	// Filter the unwanted tag block
	$filter_list = "(style";
	if ($filter_script) {
		$filter_list .= "|script";
	}
	$filter_list .= ")";
	return preg_replace("/<(\s*)".$filter_list."(.*?)>(.*?)<(\s*)\/(\s*)".$filter_list."(\s*)>/si", "", $body);
}

/*
	function check_email_list($email) {
		global $namelist;

		clearstatcache();
		if (isset($namelist) && file_exists($namelist)) {
			$db = dba_open($namelist, "r", "gdbm");
			return dba_exists($email, $db);
		} else {
			return TRUE;
		}
	}
*/
	
function get_content_type($file) {
	global $MIME_TYPE_MAP;
	$extension = strtolower(substr(strrchr($file, '.'), 1));
	
	if (array_key_exists($extension, $MIME_TYPE_MAP)) {
		return $MIME_TYPE_MAP[$extension];
	}	
	
	return "application/octet-stream";
}	

function is_requested($name) {
	return (isset($_GET[$name]) || isset($_POST[$name]));
}

function get_request($name) {
	if (isset($_GET[$name])) {
		return $_GET[$name];
	} else if (isset($_POST[$name])) {
		return $_POST[$name];
	} else {
		return "";
	}
}
	
function verify_login($username, $password) {
	global $nntp_server;
	global $proxy_server;
	global $proxy_port;
	global $proxy_user;
	global $proxy_pass;

	if (strlen($username) > 0) {	// Won't allow empty user name
		// Create a dummy connection for authentication
		$nntp = new NNTP($nntp_server, $username, $password, $proxy_server, $proxy_port, $proxy_user, $proxy_pass);
		$result = $nntp->connect();
		
		$nntp->quit();
		
		return $result;
	} else {
		return FALSE;
	}
}

// ADDED BY BRYAN MISHKIN
function get_author($message_id) {
	global $nntp_server;
	global $proxy_server;
	global $proxy_port;
	global $proxy_user;
	global $proxy_pass;
	global $_SESSION;
	$username = $_SESSION["username"];
	$password = $_SESSION["password"];

	print "<p>username is $username</p>";
	if (strlen($username) > 0) {	// Won't allow empty user name
		// Create a dummy connection for authentication
		$nntp = new NNTP($nntp_server, $username, $password, $proxy_server, $proxy_port, $proxy_user, $proxy_pass);
		$nntp->connect();

		$msg = $nntp->get_article($message_id);
		print_r($msg);
		
		$nntp->quit();
		
		return $msg;
	} else {
		return null;
	}
}

	
function construct_url($name) {
	$result = parse_url($name);
	$url = "";
	$mark = FALSE;
	
	if (!$result["scheme"]) {
		if ($_SERVER["HTTPS"] != "on") {
			$url = "http";
		} else {
			$url = "https";
		}
	} else {
		$url = $result["scheme"];
	}
	$url .= "://";
	
	if ($result["user"]) {
		$url .= $result["user"];
		$mark = TRUE;
	}
	
	if ($result["pass"]) {
		$url .= ":".$result["pass"];
		$mark = TRUE;
	}
	
	if ($mark) {
		$url .= "@";
	}
	
	if ($result["host"]) {
		$url .= $result["host"];
	} else {
		$url .= $_SERVER['HTTP_HOST'];
	}
	
	if ($result["path"][0] != '/') {			
		$url .= dirname($_SERVER['REQUEST_URI'])."/";
	}
	
	$url .= $result["path"];
	
	if ($result["query"]) {
		$url .= "?".$result["query"];
	}
	
	if ($result["fragment"]) {
		$url .= "#".$result["fragment"];
	}
	
	return $url;
}
	
function read_ini_file($file, $section=FALSE) {
	$fp = fopen($file, "r");
	if (!$fp) {
		return FALSE;
	}
	
	$ini = array();
	while (($buf = fgets($fp, 1024))) {
		$buf = trim($buf);
		if (strlen($buf) == 0) {
			continue;
		}
		if ($buf{0} != ';') {	// Skip the comment
			if ($buf{0} == '['){
				if ($section) {
					$pos = strpos($buf, ']');
					if (!$pos) {
						return FALSE;
					}
					$section_name = substr($buf, 1, $pos - 1);
					$ini[$section_name] = array();
				}
			} else if (strpos($buf, "=") !== FALSE) {
				list($key, $value) = split("=", $buf, 2);
				$value = preg_replace("/^(['|\"])?(.*?)(?(1)['|\"])$/", "\\2", trim($value));

				if ((strlen($key) != 0) && (strlen($value) != 0)) {					
					if (isset($section_name)) {
						$ini[$section_name][$key] = $value;
					} else {
						$ini[$key] = $value;
					}
				}
			}
		}	
	}
	fclose($fp);
	
	return $ini;
}
	
function make_search_pattern($query) {
	$words = split(" ", $query);
	
	$search_pat = "";
	for ($i = 0;$i < sizeof($words);$i++) {
		$search_pat .= "|(".preg_quote(trim($words[$i])).")";
	}
	$search_pat = "/".substr($search_pat, 1)."/i";
	
	return $search_pat;
}

// Code found on http://rumkin.com/tools/mailto_encoder/index.php
function HideEmail($user, $host, $name='', $subject='') {
    $MailLink = '<a href="mailto:' . $user . '@' . $host;
    if ($subject != '')
      $MailLink .= '?subject=' . urlencode($subject);
    if ($name!="")	$MailLink .= '">'.$name.'</a>';
    else			$MailLink .= '">' . $user . '@' . $host . '</a>';
    
    $MailLetters = '';
    for ($i = 0; $i < strlen($MailLink); $i ++) {
		$l = substr($MailLink, $i, 1);
		if (strpos($MailLetters, $l) === false)	{
		    $p = rand(0, strlen($MailLetters));
		    $MailLetters = substr($MailLetters, 0, $p) .
		      $l . substr($MailLetters, $p, strlen($MailLetters));
		}
    }
    
    $MailLettersEnc = str_replace("\\", "\\\\", $MailLetters);
    $MailLettersEnc = str_replace("\"", "\\\"", $MailLettersEnc);

    $MailIndexes = '';
    for ($i = 0; $i < strlen($MailLink); $i ++) {
	    $index = strpos($MailLetters, substr($MailLink, $i, 1));
	    $index += 48;
	    $MailIndexes .= chr($index);
    }

    $MailIndexes = str_replace("\\", "\\\\", $MailIndexes);
    $MailIndexes = str_replace("\"", "\\\"", $MailIndexes);
    
?><script language="javascript" type="text/javascript"><!--
ML="<?= $MailLettersEnc ?>";
MI="<?= $MailIndexes ?>";
OT="";
for(j=0;j<MI.length;j++){
OT+=ML.charAt(MI.charCodeAt(j)-48);
}document.write(OT);
// --></script><noscript><?=$messages_ini["text"]["need_Javascript"] ?></noscript><?PHP
}

function HideEmailWithName($name, $user, $host) {
    print $name . " &lt;";
    HideEmail($user, $host);
    print "&gt;";
}

// End of code


?>
