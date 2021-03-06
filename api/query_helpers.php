<?php

function showHelp($error)
{
	$title = 'CourseOverflow API Specification';
	
	echo '<!DOCTYPE HTML><html><head><title>'.$title.'</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo '<style type="text/css">table{width:100%;} th{width:25%;text-align:left;} td,th{padding-left:6px; padding-right:6px; vertical-align:top} #pagetitle{text-align:center} .error{color:red; font-weight:bold} #desc {padding-left:6px}</style>';
	echo '</head><body>';
	
	if(!empty($error)){
		echo '<p><span class="error">Error:</span> '.$error.'</p><br />';
	}

	echo '<div id="pagetitle"><h3>'.$title.'</h3></div>';
	
	echo '<p id="desc">Plug your newsgroup reader into our API to add support for voting, reputations, and remembering user subscriptions.</p>';
	
	echo '<table>';
	echo '<tr><th>action</th><th>return</th><th>param1 (GET)</th><th>param2 (GET)</th></tr>';
	echo '<tr><td>authenticate</td><td>bool</td><td>str - netid (GET/POST)</td><td>str - password (GET/POST)</td></tr>';
	echo '<tr><td>submit_vote</td><td>bool</td><td>str - message_id</td><td>bool - direction</td></tr>';
	echo '<tr><td>get_votes</td><td>array - list of votes on a post</td><td>str - message_id</td><td>&nbsp;</td></tr>';
	echo '<tr><td>get_reputation</td><td>int - reputation of a user</td><td>str - their_netid</td><td>&nbsp;</td></tr>';
	echo '<tr><td>subscribe_to_class</td><td>bool</td><td>str - fully_qualified_class_name</td><td>&nbsp;</td></tr>';
	echo '<tr><td>unsubscribe_from_class</td><td>bool</td><td>str - fully_qualified_class_name</td><td>&nbsp;</td></tr>';
	echo '<tr><td>get_subscriptions</td><td>array - list of groups that a user is subscribed to</td><td>str - their_netid</td><td>&nbsp;</td></tr>';
	echo '</table>';
	
	echo '<br /><br /><form method="post" action="?action=authenticate">';
	echo '<h3>Quick Login</h3>';
	if(isset($_SESSION["netid"])){
		echo '<p>(currently logged in as '.$_SESSION['netid'].')</p>';
	}
	echo '<p><input type="text" name="netid" id="netid" placeholder="NetID" /></p>';
	echo '<p><input type="password" name="password" id="password" placeholder="Password" /></p>';
	echo '<p><input type="submit" value="Authenticate" /></p>';
	echo '</form>';


	echo '</body></html>';
}

function outputResults($action,$obj)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = true;
	$resp["response"] = $obj;
	$resp["msg"] = null;
	echo json_encode($resp);
	exit;
}

function outputBoolean($action,$bool,$msg = null)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = $bool;
	$resp["response"] = null;
	$resp["msg"] = $msg;
	echo json_encode($resp);
	exit;
}

function outputSuccess($action)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = true;
	$resp["response"] = null;
	$resp["msg"] = null;
	echo json_encode($resp);
	exit;
}

function outputFailure($action)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = false;
	$resp["response"] = null;
	$resp["msg"] = null;
	echo json_encode($resp);
	exit;
}

function cleanInput($in)
{
	return addslashes(trim($in));
}

?>
