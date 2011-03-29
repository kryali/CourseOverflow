<?php
/******************************************************************************		
 * $Id: show_error.php,v 1.1 2004/10/09 11:51:31 svanpo Exp $
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

 unset($_SESSION["newsgroups_list"]);
?>
<form action="<?=basename($_SERVER["SCRIPT_NAME"]) ?>">
	<input type="hidden" name="home" value="1" />
	<input type="submit" value="<? echo $messages_ini["control"]["home"]; ?>" />
</form>
<div class="nntp-usererror"><?= $nntp_usermsg ?></div>
<div class="nntp-error"><?= $nntp_error ?></div>
