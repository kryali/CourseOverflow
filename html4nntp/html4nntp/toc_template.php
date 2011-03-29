<?php
/******************************************************************************		
 * $Id: toc_template.php,v 1.2 2004/10/06 20:31:33 svanpo Exp $
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
?>
<div class="logo">
	<div class="picture"><a href="http://html4nntp.sourceforge.net" target="_blank"><img src="<?=$image_base."html4nntp.gif"?>" alt="html4nntp" /></a></div>
	<div class="version"><?=$messages_ini["text"]["title"]?></div>
</div>
<div class="toc">
	<h1><?= $messages_ini["text"]["toc"] ?></h1>
<?php
	if (!$group_newsgroups) { ?>
	<table class="toc">
<?php	foreach($newsgroups_list as $group) {
			echo("<tr>");
			if ($grp_desc[$group][1]!="") {
				echo "<td width=\"20%\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($group)."\">".
					htmlentities($group)."</a></td><td align=\"right\" width=\"10%\">".$grp_desc[$group][0]."</td><td width=\"70%\">".
					htmlentities($grp_desc[$group][1])."</td>";
			} else {
				echo "<td width=\"20%\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($group)."\">".
					htmlentities($group)."</a></td><td align=\"right\" width=\"10%\">".$grp_desc[$group][0]."</td><td width=\"70%\">&nbsp;</td>";
			}
			echo("</tr>\n");
		} ?>
	</table>
<?php } else {
		sort($groups_toc);
		$group_label_displayed = "";
		if (count($groups_toc)>0) $group_label=$groups_toc[0];
		$array_groups = array_unique($newsgroups_list);
		
		// Display groups of newsgroups
		foreach($groups_toc as $group_filter) {
			
			// Display one group
			foreach($array_groups as $index => $value) {
				//print("$group_filter * $value<br/>");
				if (ereg("^$group_filter", $value)) {
					if ($group_label_displayed!=$group_filter) {
						if ($group_label_displayed!="") echo "</table>\n";
						echo "<h2>$group_filter</h2>\n";
						echo "<table class=\"toc\">";
						$group_label_displayed = $group_filter;
					}
					echo("<tr>");
					if ($grp_desc[$group][1]!="") {
						echo "<td class=\"name\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($value)."\">".
							htmlentities($value)."</a></td><td class=\"msg\">".$grp_desc[$value][0]."</td><td class=\"desc\">".
							htmlentities($grp_desc[$value][1])."</td>";
					} else {
						echo "<td class=\"name\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($value)."\">".
							htmlentities($value)."</a></td><td class=\"msg\">".$grp_desc[$value][0]."</td><td class=\"desc\">&nbsp;</td>";
					}
					echo("</tr>\n");
					unset($array_groups[$index]);
				}
			}
		}
		if ($group_label_displayed!="") echo "</table>\n";
		
		// Display misc group
		if (count($array_groups)>0) {
			echo "<h2>".$messages_ini["text"]["misc"]."</h2>\n";
			foreach($array_groups as $index => $value) {
				echo "<table class=\"toc\">";
				$group_label_displayed = $value;
				echo("<tr>");
				if ($grp_desc[$group][1]!="") {
					echo "<td class=\"name\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($value)."\">".
						htmlentities($value)."</a></td><td class=\"msg\">".$grp_desc[$value][0]."</td><td class=\"desc\">".
						htmlentities($grp_desc[$value][1])."</td>";
				} else {
					echo "<td class=\"name\"><a href=\"".basename($_SERVER["SCRIPT_NAME"])."?group=".urlencode($value)."\">".
						htmlentities($value)."</a></td><td class=\"msg\">".$grp_desc[$value][0]."</td><td class=\"desc\">&nbsp;</td>";
				}
				echo("</tr>\n");
			}
			if ($group_label_displayed!="") echo "</table>\n";
		}
	}
?>
</div>