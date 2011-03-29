<?php
/******************************************************************************		
 * $Id: article_template.php,v 1.12 2004/10/27 19:38:31 svanpo Exp $
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

	$header = $MIME_Message->get_main_header();
	$parts = $MIME_Message->get_all_parts();
	
	if (is_requested("art_group")) $group = get_request("art_group");
	else $group = $_SESSION["newsgroup"]; 
	
	if (isset($_REQUEST["mid"])) 		$_REQUEST["article_id"]=$_REQUEST["mid"];
	if (isset($_REQUEST["reply_id"])) 	$_REQUEST["article_id"]=$_REQUEST["reply_id"];
	$renew = true;
?>

<div class="show-article">
	<form name="show_headers_state" action=""><input type="hidden" name="state" value="false" /></form>
	<script language="JavaScript" type="text/javascript">
		function html4nntpShowHeaders(show) {
			if (show==true) {
				document.getElementById("show-headers").style.display = "none";
				document.getElementById("hide-headers").style.display = "block";
			} else {
				document.getElementById("show-headers").style.display = "block";
				document.getElementById("hide-headers").style.display = "none";
			}
			document.forms["show_headers_state"].state.value = show;
		}
	</script>
	<ul>
		<li><span class="label"><?=$messages_ini["text"]["subject"] ?>:</span> <span class="data"><?=htmlentities($header["subject"]) ?></span></li>
		<li><span class="label"><?=$messages_ini["text"]["from"] ?>:</span> <span class="data"><?php
			echo htmlescape($header["from"]["name"])." ";
			list($user,$host) = split("@", $header["from"]["email"]);
			if ((is_requested("post") || $_SESSION["auth"])) echo "&lt;";
			if ((is_requested("post") || $_SESSION["auth"])) HideEmail(htmlentities($user), $host);
			if ((is_requested("post") || $_SESSION["auth"])) echo "&gt;"; ?>
		</span></li>
		<li><span class="label"><?=$messages_ini["text"]["date"] ?>:</span> <span class="data"><?=htmlentities($header["date"]) ?></span></li>
		<li><span class="label"><?=$messages_ini["text"]["newsgroups"] ?>:</span> <span class="data"><?=htmlentities($header["newsgroups"]) ?></span></li>

	<?php if (sizeof($parts) > 1) {	// We've got attachment ?>
		<li><span class="label"><?=$messages_ini["text"]["attachments"] ?>:</span> <span class="data">
			<?php $attach_file = "";
			for ($i = 1;$i < sizeof($parts);$i++) {
				if (($i != 1) && (($i - 1) % 5 == 0)) {
					$attach_file .= "<br />\n";
				}
				if (strcmp($parts[$i]["filename"], "") != 0) {
					$attach_file .= "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?art_group=".urlencode($group)."&message_id=".$article_id."&attachment_id=".$i."\" target=\"_blank\">".$parts[$i]["filename"]."</a>,&nbsp;";
				} else {
					$attach_file .= "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?art_group=".urlencode($group)."&message_id=".$article_id."&attachment_id=".$i."\" target=\"_blank\">".$messages_ini["text"]["no_name"]." $i</a>,&nbsp;";
				}
			}
			if (strlen($attach_file) > 0) $attach_file = substr($attach_file, 0, strlen($attach_file) - 7);
			echo $attach_file;
			?></span>
		</li>
	<?php } ?>
		<li style="padding:5px 0px 2px 0px" id="show-headers">
			<input type="button" value="<?=$messages_ini["control"]["show_headers"] ?>" onclick="javascript:html4nntpShowHeaders(true);" />
		</li>
		<li style="padding:5px 0px 2px 0px" id="hide-headers">
			<input type="button" value="<?=$messages_ini["control"]["hide_headers"] ?>" onclick="javascript:html4nntpShowHeaders(false);" />
			<?php if ($_SESSION["auth"]) { ?>
			<div class="original-headers"><?php
				// Display headers
				$header_lines = split("\r\n", trim($MIME_Message->get_original_header()));
				for ($i=0; $i<count($header_lines); $i++) {
					list($key,$value) = split(":", $header_lines[$i], 2);
					if (isset($value)) echo "<span class=\"label\">".htmlentities($key)."</span>:".htmlentities($value)."<br />";
					else echo htmlentities($key)."<br />";
				}
				// nl2br(str_replace(" ", "&nbsp;", htmlentities()))
				?>
			</div>
			<?php } else { ?>
				<p><?=$messages_ini["text"]["must_auth"] ?></p>
			<?php } ?>
		</li>
	</ul>
	<script language="JavaScript" type="text/javascript">
		// Preset the show/hide headers
		html4nntpShowHeaders(document.forms["show_headers_state"].state.value);
	</script>
	<?php 
		$count = 0;
		foreach ($parts as $part) {
			if (stristr($part["header"]["content-type"], "text/html")) {	// HTML
				$body = filter_html(decode_message_content($part));

				// Replace the image link for internal resources
				$content_map = $MIME_Message->get_content_map();
				$search_array = array();
				$replace_array = array();
				foreach ($content_map as $cid => $aid) {
					$cid = substr($cid, 1, strlen($cid) - 2);
					$search_array[] = "cid:".$cid;
					$replace_array[] = basename($_SERVER["SCRIPT_NAME"])."?art_group=".urlencode($group)."&amp;message_id=".$article_id."&amp;attachment_id=".$aid;
				}
		
				$body = str_replace($search_array, $replace_array, $body); 
				echo "<div>$body</div>\n";
			} elseif (stristr($part["header"]["content-type"], "text")) {	// Treat all other form of text as plain text
				?>
	<div><?php	$body = decode_message_content($part);
				$body = htmlescape($body);
				$body = preg_replace(array("/\r\n/", "/(^&gt;.*)/m", "/\t/", "/  /"),
											array("<br>\r\n", "<i>$1</i>", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "&nbsp;&nbsp;"),
											add_html_links($body)); ?><?=$body ?></div>
	<?php	} elseif (preg_match("/^image\/(gif|jpeg|png|pjpeg)/i", $part["header"]["content-type"])) { ?>
	<div align="center">
		<hr width="100%">
		<img src="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?art_group=<?=urlencode($group) ?>&amp;message_id=<?=$article_id ?>&amp;attachment_id=<?=$count ?>" border="0" />
	</div>
	<?php	}
			$count++;
		}
	?>
</div>

<?php if (isset($_REQUEST["article_id"])) { ?>
<div class="show-thread">
	<?php include("html4nntp/show_header.php"); ?>
</div
<?php } ?>

<?php if ($show_validate) { ?>
<ul class="w3c-validate">
	<li class="xhtml"><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0!" height="31" width="88" /></a></li>
	<li class="css"><a href="http://jigsaw.w3.org/css-validator/"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a></li>
</ul>
<?php } ?>
