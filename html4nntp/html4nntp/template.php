<?php
/******************************************************************************		
 * $Id: template.php,v 1.4 2004/09/26 19:40:35 svanpo Exp $
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

ob_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?=$messages_ini["text"]["title"]?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link href="css/html4nntp.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		div.maintitle {
			margin: 0;
			font-weight: bold;
			font-size: 18px;
			text-align: center;
			background-color: #C1DFFA;
			margin-left: 30px;
			margin-right: 30px;
		}
		div.subtitle {
			margin: 0;
			font-weight: bold;
			font-size: 13px;
			text-align: center;
			background-color: #C1DFFA;
			margin-left: 30px;
			margin-right: 30px;
		}
	</style>
</head>
<body <?php if (isset($on_load_script)) {echo "onLoad=\"$on_load_script\"";} ?>>
	<div class="maintitle"><?=$messages_ini["text"]["header1"]; ?></div>
	<div class="subtitle"><?=$messages_ini["text"]["header2"]; ?></div>
	<div id="html4nntp">
	<?php include($content_page); ?>
	</div>
</body>
</html>

<?php ob_end_flush(); ?>
