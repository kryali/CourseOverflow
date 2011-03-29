<?php
/******************************************************************************		
 * $Id: rss.php,v 1.7 2004/10/19 20:56:50 svanpo Exp $
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

	@include_once("config/html4nntp.cfg.php");
	@include_once("html4nntp/feedcreator.php");
	@include_once("html4nntp/nntp.php");

	// Limit nb of messages	
	$_REQUEST["nb"] = intval($_REQUEST["nb"]);
	if ($_REQUEST["nb"]<1) $_REQUEST["nb"]=$message_per_page;
	if ($_REQUEST["nb"]>100) $_REQUEST["nb"]=100;

	if (isset($_COOKIE["wn_pref_lang"])) $text_ini = "config/messages_".$_COOKIE["wn_pref_lang"].".ini";
	$messages_ini = read_ini_file($text_ini, true);
	
	$nntp = new NNTP($nntp_server, $user, $pass, $proxy_server, $proxy_port, $proxy_user, $proxy_pass);
	if (!$nntp->connect()) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["nntp_fail"]."</div>";
		echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
		exit;
	} else {
		$group_info = $nntp->join_group($_REQUEST["group"]);

		if ($group_info == NULL) {
			echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["group_fail"].$_REQUEST["group"]." </div>";
			echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
			$nntp->quit();
			exit;
		} else {
			$rss_feed_count = (int)get_request("nb");
			if ($rss_feed_count <= 0) $rss_feed_count = $message_per_page;

			$article_list = $nntp->get_article_list($_REQUEST["group"]);
			$end_id = sizeof($article_list) - 1;
			$start_id = $end_id - $rss_feed_count + 1;
			if ($start_id < 0) $start_id = 0;
			//$message_summary = $nntp->get_summary($article_list[$start_id], $article_list[$end_id]);
			// Sort message by date
			//uasort($message_summary, cmp_by_date);
			
			// Prepare cache
			$cache_file = "$feed_cache_dir/feed_".$_REQUEST["group"]."_".$_REQUEST["nb"]."_".$article_list[$start_id]."_".$article_list[$end_id].".xml";
			if (file_exists(dirname($_SERVER["SCRIPT_FILENAME"])."/$cache_file")) {
				$rss = new UniversalFeedCreator();
				$rss->_redirect(dirname($_SERVER["SCRIPT_FILENAME"])."/$cache_file");
				exit;
			} else {
				// Delete old caches
				$handle = opendir("./$feed_cache_dir");
				while ($file = readdir($handle)) {
					if (ereg("^feed_".$_REQUEST["group"]."_".$_REQUEST["nb"]."_", $file)) {
						unlink("./$feed_cache_dir/$file");
					}
				}
				closedir($handle);
			}
			
			// Create description of the feed
			$desc_label = $messages_ini["text"]["newsgroup"]." ".$_REQUEST["group"];
			$desc = $nntp->get_groups_description($_REQUEST["group"]);
			if (sizeof($desc) > 0) $desc_label .= " - ".$desc[$_REQUEST["group"]];
			
			// Simplify path
			$path = dirname($_SERVER["SCRIPT_NAME"]);
			if ($path=="/") $path="";
			
			// Create the feed
			$rss = new UniversalFeedCreator();
			$rss->title = $_REQUEST["group"];
			$rss->description = trim($desc_label);
			$rss->link = "http://".$_SERVER["HTTP_HOST"]."$path/$main_page?group=".urlencode($_REQUEST["group"]);
			//$rss->syndicationURL = link = "http://".$_SERVER["HTTP_HOST"]."/".dirname($_SERVER["SCRIPT_NAME"]);
			
			$image = new FeedImage();
			$image->title = "Logo";
			$image->url = $feed_logo;
			$image->link = "http://".$_SERVER["HTTP_HOST"].$path;
			$image->description = "Click to visit.";
			$rss->image = $image;
			
			if (($article_list!==false) && (count($article_list)>0)) { 
				for ($i=$end_id; $i>=$start_id; $i--) {
					$MIME_Message = $nntp->get_article($article_list[$i]);
					$header = $MIME_Message->get_main_header();
					$parts = $MIME_Message->get_all_parts();
					$link = "http://".$_SERVER["HTTP_HOST"]."$path/$main_page?art_group=".urlencode($_REQUEST["group"])."&amp;article_id=".$article_list[$i];
					
					//print_r($MIME_Message); exit;
					
					// Create description
					$count = 0;
					$desc = "";
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
								$replace_array[] = "http://".$_SERVER["HTTP_HOST"]."$path/$main_page?art_group=".urlencode($_REQUEST["group"])."&amp;message_id=".$article_id."&amp;attachment_id=".$aid;
							}
					
							$body = str_replace($search_array, $replace_array, $body); 
							$desc .= "<div>$body</div>\n";
						} elseif (stristr($part["header"]["content-type"], "text")) {	// Treat all other form of text as plain text
							$body = decode_message_content($part);
							$body = htmlescape($body);
							$body = preg_replace(array("/\r\n/", "/(^&gt;.*)/m", "/\t/", "/  /"), array("<br>\r\n", "<i>$1</i>", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", "&nbsp;&nbsp;"), add_html_links($body));
							$desc .= "<div>$body</div>\n";
						} elseif (preg_match("/^image\/(gif|png|jpeg|pjpeg)/i", $part["header"]["content-type"])) {
							$desc .= "<div align=\"center\">";
							$desc .= "<hr width=\"100%\">";
							$desc .= "<img src=\"http://".$_SERVER["HTTP_HOST"]."$path/$main_page?art_group=".urlencode($_REQUEST["group"])."&amp;message_id=".$article_list[$i]."&amp;attachment_id=$count\" border=\"0\" />";
							$desc .= "</div>";
						}
						$count++;
					}
					
					$item = new FeedItem();
				    $item->title = $header["subject"];
				    $item->link = $link;
				    $item->description = $desc;
				    $item->date = $header["date"];
				    //$item->source = "http://www.dailyphp.net";
				    $item->author = $header["from"]["name"];
				    
				    $rss->addItem($item);
				}
			} 

			$nntp->quit();
			
			$rss->saveFeed("RSS1.0", $cache_file);
		}
	}
?>
