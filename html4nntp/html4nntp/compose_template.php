<?php
/******************************************************************************		
 * $Id: compose_template.php,v 1.6 2004/11/22 07:31:37 svanpo Exp $
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

	if (is_requested("group")) {
		$group = get_request("group");
	} else {
		unset($group);
	}

	if (is_requested("add_file")) {
		$_SESSION["attach_count"]++;
	} ?>
<script language="JavaScript" type="text/JavaScript">
function emailValid(email) {
	// Extensions de domaines connues
	var ext = "(ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|az|ba|bb|bd|be|bf|"
		+"bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cf|cg|ch|ci|ck|"
		+"cl|cm|cn|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|"
		+"es|et|fi|fj|fk|fm|fo|fr|fx|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gp|gq|"
		+"gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|io|iq|ir|is|it|"
		+"jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|"
		+"lt|lu|lv|ly|ma|mc|md|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|"
		+"mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|"
		+"ph|pk|pl|pm|pn|pr|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|"
		+"si|sj|sk|sl|sm|sn|so|sr|st|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|"
		+"to|tp|tr|tt|tv|tw|tz|ua|ug|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|"
		+"ws|ye|yt|yu|za|zm|zr|zw|aero|arpa|biz|com|coop|edu|info|int|"
		+"museum|net|org)";
	// IP
	var ip = "\\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\\]";
	// domaine litéral
	var domaineLiteral = "([-a-zA-Z0-9]+.)+"+ext;
	// atome
	var atome = "[^\\s@\\(\\)<>,;:\\\\\"\\[\\]]+";
			
	// email patterns
	var emailPatternLiteral = atome+"@"+domaineLiteral;
	var emailPatternIP = atome+"@"+ip;
			
	// Email avec domaine litéral
	var re = new RegExp("^"+emailPatternLiteral+"$");
	var result = email.match(re);
	if (result!=null) return(true);
			
	// Email avec IP
	re = new RegExp("^"+emailPatternIP+"$");
	var result = email.match(re);
	if (result==null) return(false);
	if (result[1]>255) return(false);
	if (result[2]>255) return(false);
	if (result[3]>255) return(false);
	if (result[4]>255) return(false);
			
	return(true);
}
function submitForm(form) {
	if (form.button.value=="post") {
		if (form.name.value=="") {
			alert("<?=$messages_ini["error"]["no_name"] ?>");
			return(false);
		}
		if (!emailValid(form.email.value)) {
			alert("<?=$messages_ini["error"]["no_email"] ?>");
			return(false);
		}
	}
	return(true);
}
</script>
	<?php if (isset($_SESSION["attach_count"])) { ?>
<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>" method="post" enctype="multipart/form-data" name="composeForm" onsubmit="javascript:return(submitForm(this));">
<?php } else {?>
<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>" method="post" name="composeForm" onsubmit="javascript:return(submitForm(this));">
<?php }?>
	<input type="hidden" name="compose" value="post" />
	<input type="hidden" name="button" value="" />
	<table class="form">
<?php if (isset($error_messages)) {
		echo "<tr>";
		echo "<td colspan=\"2\"><div class=\"error\">";
		foreach ($error_messages as $msg) echo "$msg<br>";
		echo "</div></td></tr>";
	} ?>
		<tr>
			<td class="label"><?=$messages_ini["text"]["subject"] ?>:</td>
			<td><input type="text" name="subject" size="60" value="<? echo $subject; ?>" /></td>
		</tr>
		<tr>
			<td class="label"><?=$messages_ini["text"]["name"] ?>:</td>
			<td><input type="text" name="name" size="60" value="<? echo $name; ?>" /></td>
		</tr>
		<tr>
			<td class="label"><?=$messages_ini["text"]["email"] ?>:</td>
			<td><input type="text" name="email" size="60" value="<? echo $email; ?>" /></td>
		</tr>
<?php if ($can_post_file) { ?>
		<tr>
<?php	echo "<td class=\"label\" valign=\"top\" rowspan=\"";
		if (isset($_SESSION["attach_count"])) echo ($_SESSION["attach_count"] + 1);
		else echo '1';
		echo "\">"; 
		echo $messages_ini["text"]["attachments"].":</td>";
		if (isset($_SESSION["attach_count"])) {
			for ($i = 1;$i <= $_SESSION["attach_count"];$i++) {
				if ($i!=1) echo "<tr>"; ?>
			<td><input type="file" name="file<?=$i?>" size="32" /></td>
<?php			if ($i!=1) echo "</tr>";
			}
			echo "<tr>";
		} ?>
			<td>
				<input type="submit" name="add_file" value="<?=$messages_ini["control"]["add_file"] ?>" />
			</td>
		</tr>
<?php } ?>
		<tr>
			<td class="label"><?=$messages_ini["text"]["newsgroups"] ?>:</td>
			<td>
				<?
					if ($allow_cross_post) {
						$count = 1;
						while (list($key, $value) = each($newsgroups_list)) {
							echo "<input name=\"groups[]\" type=\"checkbox\" value=\"$value\"";
							if (isset($groups)) {
								if (in_array($value, $groups)) {
									echo "checked=\"checked\"";
								}
							} elseif (strcmp($value,$_SESSION["newsgroup"]) == 0) {
								echo " checked=\"checked\"";
							}
							echo " />$value&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							if (($count++ % 2) == 0) {
								echo "<br />";
							}
						}
						reset($newsgroups_list);
					} else {
						echo "<input name=\"groups[]\" type=\"radio\" value=\"".$_SESSION["newsgroup"]."\" checked=\"checked\" />";
						echo $_SESSION["newsgroup"];
					}
				?>
			</td>
		</tr>
		<tr>
			<td class="label" colspan="2"><?=$messages_ini["text"]["message"] ?>:</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="message" rows="10" cols="79"><?=$message ?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?
					if (isset($reply_id)) {
						echo "<input type=\"hidden\" name=\"reply_id\" value=\"$reply_id\" />";
					}

					if (isset($_SESSION["attach_count"])) {
						echo "<input type=\"hidden\" name=\"attachment\" value=\"1\" />";
					}
				?>
				<input type="submit" name="post" value="<?=$messages_ini["control"]["post"] ?>" onclick="javascript:this.form.button.value='post';" />
				<input type="submit" name="cancel" value="<?=$messages_ini["control"]["cancel"] ?>" onclick="javascript:this.form.button.value='cancel';" />
				&nbsp;
				&nbsp;
				<input type="checkbox" name="save_name_mail" value="1" <?php if ($save_name_mail) echo "checked=\"checked\"" ?>/> <?=$messages_ini["text"]["save_name_mail"] ?>
			</td>
		</tr>
	</table>
</form>

<?php if ($show_validate) { ?>
<ul class="w3c-validate">
	<li class="xhtml"><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0!" height="31" width="88" /></a></li>
	<li class="css"><a href="http://jigsaw.w3.org/css-validator/"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a></li>
</ul>
<?php } ?>
