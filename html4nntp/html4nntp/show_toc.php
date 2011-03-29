<?php
/******************************************************************************		
 * $Id: show_toc.php,v 1.4 2004/10/09 11:51:31 svanpo Exp $
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

	if (!$nntp->connect()) {
		$content_page = "html4nntp/show_error.php";
		$nntp_usermsg = $messages_ini["error"]["nntp_fail"];
		$nntp_error = $nntp->get_error_message();
		include ($template);
		exit;
	} else {
		// Find descriptions^
		$grp_desc = array();
		foreach ($newsgroups_list as $group) {
			$grp = $nntp->get_group_list($group);
			$desc = $nntp->get_groups_description($group);
			$first = intval($grp[0][2]);
			$last = intval($grp[0][1]);
			$nb_articles = $last-$first+1;
			if ($nb_articles<0) $nb_articles=0;
			$grp_desc[$group] = array($nb_articles, $desc[$group]);
		}
		
		$nntp->quit();
	}

	include("html4nntp/toc_template.php");
?>

<?php if ($show_validate) { ?>
<ul class="w3c-validate">
	<li class="xhtml"><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0!" height="31" width="88" /></a></li>
	<li class="css"><a href="http://jigsaw.w3.org/css-validator/"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a></li>
</ul>
<?php } ?>