<?php

function showHelp()
{
	echo '<div align="center"><h3>Newsgroup Extender API Specification</h3></div>';
	echo '<table width="600px">';
	echo '<tr><th align="left">action</th><th align="left">return</th><th align="left">param1</th><th align="left">param2</th></tr>';
	echo '<tr><td>authenticate</td><td>boolean</td><td>email</td><td>password</td></tr>';
	echo '<tr><td>submit_vote</td><td>boolean</td><td>message_id</td><td></td></tr>';
	echo '<tr><td>get_votes</td><td>list of votes on a post</td><td>message_id</td><td></td></tr>';
	echo '<tr><td>get_reputation</td><td>vote count</td><td>email</td><td></td></tr>';
	echo '<tr><td>subscribe_to_class</td><td>boolean</td><td>class_name</td><td></td></tr>';
	echo '<tr><td>unsubscribe_from_class</td><td>boolean</td><td>class_name</td><td></td></tr>';
	echo '<tr><td>get_subscriptions</td><td>list of newsgroup groups</td><td>email</td><td></td><td></td></tr>';
	echo '</table>';
}

function showError($error)
{
	echo '<p><strong style="color:red">Error:</strong> '.$error.'</p><br />';
}

function outputResults($action,$obj)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = true;
	$resp["response"] = $obj;
	echo json_encode($resp);
	exit;
}

function outputBoolean($action,$bool)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = $bool;
	$resp["response"] = null;
	echo json_encode($resp);
	exit;
}

function outputSuccess($action)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = true;
	$resp["response"] = null;
	echo json_encode($resp);
	exit;
}

function outputFailure($action)
{
	$resp = array();
	$resp["action"] = $action;
	$resp["return"] = false;
	$resp["response"] = null;
	echo json_encode($resp);
	exit;
}

function cleanInput($in)
{
	return addslashes(trim($in));
}

?>
