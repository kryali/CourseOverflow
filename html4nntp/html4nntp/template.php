<?php
/******************************************************************************		
 * $Id: template.php,v 1.4 2004/09/26 19:40:35 svanpo Exp $
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
	<script type="text/javascript">
	function vote(message_id){
		loadXMLDoc("http://courseoverflow.web.cs.illinois.edu/CourseOverflow/api/?action=submit_vote&message_id="+message_id);
		document.getElementById("count_"+message_id).innerHTML = parseInt(document.getElementById("count_"+message_id).innerHTML) + 1;
		document.getElementById("image_"+message_id).innerHTML = "<img src=\"images/thumbsup_disabled.jpeg\" height=\"15px\" width=\"15px\" title=\"You voted up!\" />";
	};

      	function loadXMLDoc(url){
		var xmlhttp;
		if (window.XMLHttpRequest)
	 	 {// code for IE7+, Firefox, Chrome, Opera, Safari
	 	 	xmlhttp=new XMLHttpRequest();
	 	 }
		else
	 	 {// code for IE6, IE5
	 	 	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	 	 }
	 
        	xmlhttp.onreadystatechange=function()
	  	{
	   	 	if (xmlhttp.readyState==4 && xmlhttp.status==200)
	   	 	{
	    		document.getElementById("myDiv").innerHTML=xmlhttp.responseText;
	    		}
	 	}
		xmlhttp.open("GET",url,true);
		xmlhttp.send();
	}
	

	</script>
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
