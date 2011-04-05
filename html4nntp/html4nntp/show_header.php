<?php
/******************************************************************************		
 * $Id: show_header.php,v 1.14 2004/10/27 19:38:31 svanpo Exp $
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

	$sort_by_list = array("subject", "from", "date");
	if (is_requested("sign")) {
		$sign = get_request("sign");
	}
	if (is_requested("sort")) {
		$sort = get_request("sort");
	}
	if (is_requested("search")) {
		unset($_SESSION["search_txt"]);
		$do_search = TRUE;
	}
	if ($renew || $change_mpp) {
		$page = 1;
	} else if (is_requested("page")) {
		$page = intval(get_request("page"));
		$renew = 1;
	} else if (isset($_SESSION["last_page"])) {
		$page = $_SESSION["last_page"];
	} else {
		$page = 1;
	}

	if (is_requested("option")) {
		$_SESSION["more_option"] = !$_SESSION["more_option"];
	}

	if (isset($_COOKIE["wn_pref_mpp"])) {
		$message_per_page = $_COOKIE["wn_pref_mpp"];
	}

	$_SESSION["last_page"] = $page;

	if (!$nntp->connect()) {
		$_SESSION["result"] = null;
		$content_page = "html4nntp/show_error.php";
		$nntp_usermsg = $messages_ini["error"]["nntp_fail"];
		$nntp_error = $nntp->get_error_message();
		include ($template);
		exit;
	} else {
		$group_info = $nntp->join_group($_SESSION["newsgroup"]);

		if ($group_info == NULL) {
			$_SESSION["result"] = null;
			$content_page = "html4nntp/show_error.php";
			$nntp_usermsg = $messages_ini["error"]["group_fail"].$_SESSION["newsgroup"];
			$nntp_error = $nntp->get_error_message();
			include ($template);
			exit;
		} else {			
			if ($renew || $do_search || ($_SESSION["result"] == null)) {
				$renew = 1;
				$_SESSION["result"] = null;
				if ($group_info["count"] > 0) {
					$_SESSION["article_list"] = $nntp->get_article_list($_SESSION["newsgroup"]);
					if ($_SESSION["article_list"] === FALSE) {
						unset($_SESSION["article_list"]);
						$content_page = "html4nntp/show_error.php";
						$nntp_usermsg = $messages_ini["error"]["group_fail"].$_SESSION["newsgroup"];
						$nntp_error = $nntp->get_error_message();
						include ($template);
						exit;
					}				
					
					if ($do_search) {
						$search_txt = get_request("search_txt");
						if (get_magic_quotes_gpc()) {
							$search_txt = stripslashes($search_txt);
						} 
						$search_pat = make_search_pattern($search_txt);
						$flat_tree = TRUE;
						$_SESSION["search_txt"] = $search_txt;
					} else {
						$search_pat = "//";
						$flat_tree = FALSE;
						unset($_SESSION["search_txt"]);
					}					
					
					if ((strcmp($message_per_page, "all") == 0) || $do_search) {
						// Search through all messages
						$start_id = 0;
						$end_id = sizeof($_SESSION["article_list"]) - 1;
					} else {
						$end_id = sizeof($_SESSION["article_list"]) - $message_per_page*($page - 1) - 1;
						$start_id = $end_id - $message_per_page + 1;
					}
					if ($start_id < 0) {
						$start_id = 0;
					}

					$result = $nntp->get_message_summary($_SESSION["article_list"][$start_id], $_SESSION["article_list"][$end_id], $search_pat, $flat_tree);
					if ($result) {
						$result[0]->compact_tree();						
						$need_sort = TRUE;
						krsort($result[1], SORT_NUMERIC);
						reset($result[1]);
					}
		
					// Set the tree sorting setting as previous group and force sorting
					if (!isset($sort) && isset($_SESSION["sort_by"]) && $need_sort) {
						$sort = $_SESSION["sort_by"];
						$_SESSION["sort_by"] = -1;
					}
				
					$_SESSION["result"] = $result;
				} else {
					$_SESSION["article_list"] = array();
					$_SESSION["result"] = array(new MessageTreeNode(NULL), array());
				}
			}
		}
		
		$nntp->quit();
	}

	if ($_SESSION["result"]) {
		$root_node =& $_SESSION["result"][0];
		$ref_list =& $_SESSION["result"][1];
		
		if (!isset($_SESSION["sort_by"])) {
			$_SESSION["sort_by"] = 2;
			$last_sort = -1;
			$_SESSION["sort_asc"] = 0;
			$last_sort_dir = 0;
		} else {
			$last_sort = $_SESSION["sort_by"];
			$last_sort_dir = $_SESSION["sort_asc"];
			if (isset($sort)) {				
				$_SESSION["sort_by"] = intval($sort);
				if ($_SESSION["sort_by"] == $last_sort) $_SESSION["sort_asc"] = ($_SESSION["sort_asc"] == 1)?0:1;
			} else $_SESSION["sort_by"] = $last_sort;
		}
			
		if (($_SESSION["sort_by"]!=$last_sort) || ($_SESSION["sort_asc"]!=$last_sort_dir)) $root_node->deep_sort_message($sort_by_list[$_SESSION["sort_by"]], $_SESSION["sort_asc"]);
		
		if (isset($sign) && isset($mid)) {
			$message_id = $ref_list[$mid][0];
			$references = $ref_list[$mid][1];
			$node =& $root_node;
			
			// Search the reference list only when the expand node is not a child of the root
			if (!$node->get_child($message_id)) {	
				if (sizeof($references) != 0) {
					foreach ($references as $ref) {
						$child =& $node->get_child($ref);
						if ($child != NULL) $node =& $child;
					}
				}
			}

			$node =& $node->get_child($message_id);

			if ($node) {
				if (strcasecmp($sign, "minus") == 0) $node->set_show_children(FALSE);
				else if (strcasecmp($sign, "plus") == 0) $node->set_show_all_children(TRUE);
			}
		}
		
		if (isset($_SESSION["search_txt"])) {
			if (sizeof($root_node->get_children()) == 0) $info_msg["msg"] = "No match for query - ".$_SESSION["search_txt"];
			else $info_msg["msg"] = $messages_ini["text"]["sch_found1"]." ".sizeof($root_node->get_children())." ".$messages_ini["text"]["sch_found2"]." ".htmlentities($_SESSION["search_txt"]).".";
		}
?>
<?php if ((!isset($_REQUEST["article_id"])) && ($_REQUEST["compose"]!="post")) { ?>
<div class="logo">
	<div class="picture"><a href="http://html4nntp.sourceforge.net" target="_blank"><img src="<?=$image_base."html4nntp.gif"?>" alt="html4nntp" /></a></div>
	<div class="version"><?=$messages_ini["text"]["title"]?></div>
</div>
<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>" name="html4nntp_main_form">
	<table class="form">
		<tr>
			<td class="label"><?=$messages_ini["text"]["search"]?></td>
			<td colspan="3">
				<input type="text" size="40" name="search_txt" value="<? echo isset($_SESSION["search_txt"])?htmlescape($_SESSION["search_txt"]):""; ?>" />
				<input type="submit" name="search" value="<?=$messages_ini["control"]["search"]?>" />
			</td>
		</tr>
		<tr>
			<td class="label"><?=$messages_ini["text"]["newsgroup"]?></td>
			<td  colspan="3">
				<select name="group" onchange="javascript:html4nntp_main_form.submit()"><?php
						while (list($key, $value) = each($newsgroups_list)) {
							echo "<option value=\"$value\"";
							if (strcmp($value, $_SESSION["newsgroup"]) == 0) echo " selected=\"selected\"";
							echo ">$value</option>\r\n";
						}
						reset($newsgroups_list);
				?></select>
				<input type="submit" value="<?=$messages_ini["control"]["go"]?>" />
			</td>
		</tr>

<?php 	if ($_SESSION["more_option"]) { ?>
		<tr>
			<td class="label"><?=$messages_ini["text"]["language"]?></td>
			<td>
				<select name="language"><?php
				foreach ($locale_list as $key=>$value) {
					echo "<option value=\"$key\"";
					if (strcmp($_COOKIE["wn_pref_lang"], $key) == 0) echo " selected=\"selected\"";
					echo ">";
					echo $value."</option>\n";
				} ?>
				</select>
			</td>
			<td class="label"><?=$messages_ini["text"]["messages_per_page"]?></td>
			<td>
				<select name="msg_per_page"><?php
					foreach ($message_per_page_choice as $i) {
						echo "<option value=\"$i\"";
						if (strcmp($message_per_page, $i) == 0) echo " selected=\"selected\"";
						if (strcmp($i, "all") == 0) echo ">".$messages_ini["text"]["all"]."</option>";
						else echo ">$i</option>";
					} ?>
				</select>
				<input type="submit" name="set" value="<?=$messages_ini["control"]["set"] ?>" />
			</td>
		</tr>
<?php 	} ?>
	</table>
	<ul class="menu">
<?php			if ($display_toc) { ?>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?home=1" title="<?=$messages_ini["help"]["home"] ?>"><?=$messages_ini["control"]["home"] ?></a></li>
			<li>|</li>
<?php			} ?>
<?php			if (isset($_SESSION["search_txt"])) { ?>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?renew=1" title="<?=$messages_ini["help"]["return"] ?>"><?=$messages_ini["control"]["return"] ?></a></li>
<?php			} else { ?>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?renew=1" title="<?=$messages_ini["help"]["new_news"] ?>"><?=$messages_ini["control"]["new_news"] ?></a></li>
<?php			} ?>
			<li>|</li>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?compose=1" title="<?=$messages_ini["help"]["compose"] ?>"><?=$messages_ini["control"]["compose"] ?></a></li>
			<li>|</li>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?expand=1" title="<?=$messages_ini["help"]["expand"] ?>"><?=$messages_ini["control"]["expand"] ?></a></li>
			<li>|</li>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?collapse=1" title="<?=$messages_ini["help"]["collapse"] ?>"><?=$messages_ini["control"]["collapse"] ?></a></li>
			<li>|</li>
			<li><a href="rss.php?nb=<?=$message_per_page ?>&amp;group=<?=urlencode($_SESSION["newsgroup"]) ?>" target="_blank" title="<?=$messages_ini["help"]["rss_feed"] ?>"><?=$messages_ini["control"]["rss_feed"] ?></a></li>
			<li>|</li>
			<li><a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?option=1" <?php
			if ($_SESSION["more_option"]) {
				echo "title=\"".$messages_ini["help"]["less_option"]."\">".$messages_ini["control"]["less_option"];
			} else {
				echo "title=\"".$messages_ini["help"]["more_option"]."\">".$messages_ini["control"]["more_option"];
			} ?></a></li>
	</ul>
<?php 	if (($auth_level > 1) && $_SESSION["auth"]) { ?>
	<div class="logged">
		<div class="user"><?=$messages_ini["text"]["login"].$user ?>.</div>
		<div class="logout"><input type="submit" name="logout" value="<?=$messages_ini["control"]["logout"] ?>" /></div>
	</div>
<?php 	} ?>
<?php
	/*if (strlen($info_msg["msg"]) != 0) {
		echo "<tr><td colspan=\"5\" align=\"center\" colspan=\"5\">";
		echo "<b>";
		echo htmlescape($info_msg["msg"]);
		echo "</b></td></tr>";
	}*/
?>
</form>
<?php } ?>

<?php
		// Display section
		if ($_SESSION["sort_asc"]) 	$arrow_img = $image_base."sort_arrow_up.gif";
		else 						$arrow_img = $image_base."sort_arrow_down.gif";
?>

<table class="headers">
	<tr>
		<th width="65%" class="list">
<?php if (!isset($_REQUEST[article_id])) { ?>
			<a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?renew=0&amp;sort=0"><?=$messages_ini["text"]["subject"] ?></a>
			<?php if ($_SESSION["sort_by"] == 0) echo "&nbsp;<img src=\"$arrow_img\" alt=\"*\" />"; ?>
<?php } else { ?>
			<?=$messages_ini["text"]["subject"] ?>
<?php } ?>
		</th>
		<th width="23%" class="list">
<?php if (!isset($_REQUEST[article_id])) { ?>
			<a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?renew=0&amp;sort=1"><?=$messages_ini["text"]["sender"] ?></a>
			<?php if ($_SESSION["sort_by"] == 1) echo "&nbsp;<img src=\"$arrow_img\" alt=\"*\" />"; ?>
<?php } else { ?>
			<?=$messages_ini["text"]["sender"] ?>
<?php } ?>
		</th>
		<th width="12%" class="list">
<?php if (!isset($_REQUEST[article_id])) { ?>
			<a href="<?=basename($_SERVER["SCRIPT_NAME"]) ?>?renew=0&amp;sort=2\"><?=$messages_ini["text"]["date"] ?></a>
			<?php if ($_SESSION["sort_by"] == 2) echo "&nbsp;<img src=\"$arrow_img\" alt=\"*\" />"; ?>
<?php } else { ?>
			<?=$messages_ini["text"]["date"] ?>
<?php } ?>
		</th>
	</tr>

<?php
		if (is_requested("expand")) {
			$_SESSION["expand_all"] = TRUE;
			$need_expand = TRUE;
		} elseif (is_requested("collapse")) {
			$_SESSION["expand_all"] = FALSE;
			$need_expand = TRUE;
		} elseif ($renew) {
			$need_expand = TRUE;
			if (!isset($_SESSION["expand_all"])) $_SESSION["expand_all"] = $default_expanded;
		}

		if ($need_expand) {
			$root_node->set_show_all_children($_SESSION["expand_all"]);
			$root_node->set_show_children(TRUE);
		}

		$display_counter = 0;
		if (isset($_SESSION["search_txt"]) && (strcasecmp($message_per_page, "all") != 0)) {
			$nodes = array_slice($root_node->get_children(), ($page - 1)*$message_per_page, $message_per_page);
			display_tree($nodes, 0);
		} else {
			if ((isset($_REQUEST["article_id"])) && (isset($header))) {
				$references = preg_split("/\s+/", trim($header["references"]), -1, PREG_SPLIT_NO_EMPTY);
				$children = $root_node->get_children();
				if (count($references)>0) {
					// Test if the message has been deleted 
					if (isset($children[$references[0]])) {
						$child = array($references[0] => $children[$references[0]]);
					} else {
						$child = array($header["message-id"] => $children[$header["message-id"]]);
					}
				} else {
					$child = array($header["message-id"] => $children[$header["message-id"]]);
				}
				display_tree($child, 0);
			} else {
				display_tree($root_node->get_children(), 0);
			}
		}
?>
</table>
<?php }

	// Pagination number generation
	if (strcasecmp($message_per_page, "all") != 0) {
		if (isset($_SESSION["search_txt"])) $page_count=ceil((float)sizeof($root_node->get_children())/(float)$message_per_page); // Count from the number of search results
		else $page_count = ceil((float)sizeof($_SESSION["article_list"])/(float)$message_per_page);
		$start_page = (ceil($page/$pages_per_page) - 1)*$pages_per_page + 1;
		$end_page = $start_page + $pages_per_page - 1;
		if ($end_page > $page_count) $end_page = $page_count;
	} else $page_count = 0;	// Show All
	
	if  (($_SESSION["result"]) && (($page_count!=0) && (($start_page!=1) || ($start_page!=$end_page)))) {
?>
	<div class="pages"><?=$messages_ini["text"]["page"] ?>:
	<?php
		$search = ($search_txt=="")?"":"&amp;search_txt=$search_txt&amp;search=1";		
		if ($page != 1) echo "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?page=".($page - 1)."$search\"><img src=\"".$image_base."previous_arrow.gif\" alt=\"&lt;\" /></a>";
		echo "&nbsp;";
		for ($i = $start_page;$i <= $end_page;$i++) {
			if ($page == $i) {
				echo $i;
			} else {
				echo "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?page=$i$search\">$i</a>";
			}
			echo "&nbsp;";
		}
		
		if ($page != $page_count) echo "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?page=".($page + 1)."$search\"><img src=\"".$image_base."next_arrow.gif\" alt=\"&gt;\" /></a>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;";
		if ($start_page != 1) echo "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?page=".($start_page - 1)."$search\">".$messages_ini["text"]["previous"]."$pages_per_page".$messages_ini["text"]["page_quality"]."</a>&nbsp;&nbsp;";
		if ($end_page != $page_count) echo "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?page=".($end_page + 1)."$search\">".$messages_ini["text"]["next"]."$pages_per_page".$messages_ini["text"]["page_quality"]."</a>\r\n";
	?>
	</div>
<?php } ?>

<?php if ((!isset($_REQUEST[article_id])) && ($show_validate)) { ?>
<ul class="w3c-validate">
	<li class="xhtml"><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0!" height="31" width="88" /></a></li>
	<li class="css"><a href="http://jigsaw.w3.org/css-validator/"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a></li>
</ul>
<?php } ?>

<?
	function display_tree($nodes, $level, $indent = "") {
		global $image_base;
		global $display_counter;
		global $subject_length_limit;
		global $sender_length_limit;

		global $user,$pass;


		$count = 0;
		$last_index = sizeof($nodes) - 1;
		$old_indent = $indent;
		foreach ($nodes as $node) {
			$message_info = $node->get_message_info();

			// By BRYAN MISHKIN: Prepare data on this post
			$message_id = $message_info->{"message_id"};
			$message_from = $message_info->{"from"};
			$author_email = $message_from["email"];
			$author_netid = substr($author_email,0,strpos($author_email,"@"));
	
			$json = getJSONFromAPI("?action=get_votes&netid=".$user."&password=".$pass."&message_id=".$message_id);
			$voteCount = count($json->{"response"});

			$json = getJSONFromAPI("?action=get_reputation&netid=".$user."&password=".$pass."&their_netid=".$author_netid);
			$authorRep = $json->{"response"};
			if($authorRep == null){
				$authorRep = 0;
			}

			$is_first = ($count == 0)?1:0;
			$is_last = ($count == $last_index)?1:0;
			
			if ($node->count_children() == 0) {
				if ($is_first && $is_last) {
					if ($level == 0) {
						$sign = "<img src=\"".$image_base."white.gif\" width=\"15\" height=\"19\" alt=\".\" />";
					} else {
						$sign = "<img src=\"".$image_base."bar_L.gif\" width=\"15\" height=\"19\" alt=\"\\\" />";
					}
				} elseif ($is_first) {
					if ($level == 0) {
						$sign = "<img src=\"".$image_base."bar_7.gif\" width=\"15\" height=\"19\" alt=\"*\" />";
					} else {
						$sign = "<img src=\"".$image_base."bar_F.gif\" width=\"15\" height=\"19\" alt=\"|\" />";
					}
				} elseif ($is_last) {
					$sign = "<img src=\"".$image_base."bar_L.gif\" width=\"15\" height=\"19\" alt=\"\\\" />";
				} else {
					$sign = "<img src=\"".$image_base."bar_F.gif\" width=\"15\" height=\"19\" alt=\"|\" />";
				}
			} else {
				if (($node->is_show_children()) || (isset($_REQUEST["article_id"]))) {
					$sign = "minus";
					$alt = "-";
				} else {
					$sign = "plus";
					$alt = "+";
				}

				if (!isset($_REQUEST["article_id"])) {
					$link = "<a href=\"".basename($_SERVER["SCRIPT_NAME"])."?renew=0&amp;mid=".$message_info->nntp_message_id."&amp;sign=".$sign."\">";
				} else $link = "";

				if ($is_first && $is_last && ($level == 0)) {
					$sign = $link."<img src=\"".$image_base."sign_".$sign."_single.gif\" width=\"15\" height=\"19\" alt=\"".$alt."\" />";
				} elseif (($is_first) && ($level == 0)) {
					$sign = $link."<img src=\"".$image_base."sign_".$sign."_first.gif\" width=\"15\" height=\"19\" alt=\"".$alt."\" />";
				} elseif ($is_last) {
					$sign = $link."<img src=\"".$image_base."sign_".$sign."_last.gif\" width=\"15\" height=\"19\" alt=\"".$alt."\" />";
				} else {
					$sign = $link."<img src=\"".$image_base."sign_".$sign.".gif\" width=\"15\" height=\"19\" alt=\"".$alt."\" />";
				}
				if ((!isset($_REQUEST["article_id"])) || ($_REQUEST["article_id"]!=$message_info->nntp_message_id)) {
					$sign .= "</a>";
				};
			}

			if (($display_counter % 2) == 0) {
				echo "<tr>\r\n";
			} else {
				echo "<tr>\r\n";
			}
			$display_counter++;
			echo "<td class=\"msg-title".($display_counter%2+1)."\">\r\n";
			echo "<span class=\"msg-tree\"><a name=\"".$message_info->nntp_message_id."\" ></a>";
			echo "$old_indent$sign<img src=\"".$image_base."message.gif\" width=\"13\" height=\"13\" alt=\"#\" /></span>";
			if ((isset($_REQUEST["article_id"])) && ($_REQUEST["article_id"]==$message_info->nntp_message_id)) {
				echo "<span class=\"msg-title\"><img src="" height=\"10px\" width=\"10px\" title=\"Thumbs Up!\" /> <strong>(+".$voteCount.")".htmlentities(chop_str($message_info->subject, $subject_length_limit - $level*3))."</strong></span>\r\n";
			} else {
				echo "<span class=\"msg-title\"><img src="" height=\"10px\" width=\"10px\" title=\"Thumbs Up!\" /> (+".$voteCount.") <a href=\"".basename($_SERVER["SCRIPT_NAME"])."?art_group=".urlencode($_SESSION["newsgroup"])."&amp;article_id=".$message_info->nntp_message_id."\">";
				echo htmlentities(chop_str($message_info->subject, $subject_length_limit - $level*3))."</a></span>\r\n";
			}
			echo "</td>\r\n";
					
			echo "<td class=\"msg-mail".($display_counter%2+1)."\">\r\n";
			if ($_SESSION["auth"]) {
				// Encode email
				list($this_user,$host) = split("@", $message_info->from["email"]);
				if ((is_requested("post") || $_SESSION["auth"])) HideEmail(htmlentities($this_user), $host, chop_str($message_info->from["name"], $sender_length_limit));
				else htmlentities(chop_str($message_info->from["name"], $sender_length_limit));
			} else {
				echo htmlentities(chop_str($message_info->from["name"], $sender_length_limit));
			}
			echo " (".$authorRep.")</td>\r\n";

			echo "<td class=\"msg-date".($display_counter%2+1)."\">".format_date($message_info->date)."</td>\r\n";

			echo "</tr>\r\n";

			if ($is_last) {
				$indent = $old_indent."<img src=\"".$image_base."white.gif\" width=\"15\" height=\"19\" alt=\".\" />";
			} else {
				$indent = $old_indent."<img src=\"".$image_base."bar_1.gif\" width=\"15\" height=\"19\" alt=\"|\" />";
			}

			if ((isset($_REQUEST["article_id"])) || ($node->is_show_children() && ($node->count_children() != 0))) {
				display_tree($node->get_children(), $level + 1, $indent);
			}
			$count++;
		}
	}
?>
