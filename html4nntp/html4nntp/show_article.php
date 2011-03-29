<?php
/******************************************************************************		
 * $Id: show_article.php,v 1.4 2004/10/14 20:53:37 svanpo Exp $
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
<table cellspacing="2" cellpadding="2" border="0" width="100%">
	<tr>
		<td>
			<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>">
				<input type="hidden" name="compose" value="reply" />
				<input type="hidden" name="mid" value="<?=$article_id ?>" />
				<input type="submit" value="<?=$messages_ini["control"]["reply"] ?>" />
			</form>
		</td>
		<td width="100%">
			<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>">
				<input type="hidden" name="mid" value="<?=$article_id ?>" />
				<input type="hidden" name="group" value="<?=$_REQUEST["art_group"] ?>" />
				<input type="hidden" name="renew" value="0" />
<?php 		if (isset($_SESSION["search_txt"])) { ?>
				<input type="submit" value="<?=$messages_ini["control"]["return_search"] ?>" /></form></td>
<?php		} else { ?>
				<input type="submit" value="<?=$messages_ini["control"]["return"] ?>" /></form></td>
<?php		} ?>
	</tr>
</table>

<?php
//	$nntp = new NNTP($nntp_server, $user, $pass);
	if (!$nntp->connect()) {
		echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["nntp_fail"]."</div>";
		echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
	} else {
		if (is_requested("art_group")) {
			$group_info = $nntp->join_group(get_request("art_group"));
		} else {
			$group_info = $nntp->join_group($_SESSION["newsgroup"]);
		}

		if ($group_info == NULL) {
			echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["group_fail"].$_SESSION["newsgroup"]." </div>";
			echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
		} else {
			$MIME_Message = $nntp->get_article($article_id);

			if ($MIME_Message == NULL) {
				echo "<div class=\"nntp-usererror\">".$messages_ini["error"]["article_fail"]."$article_id </div>";
				echo "<div class=\"nntp-error\">".$nntp->get_error_message()."</div>";
			} else {
				include("html4nntp/article_template.php");
			}
		}	
		$nntp->quit();
	}
?>

