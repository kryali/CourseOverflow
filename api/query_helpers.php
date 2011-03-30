<?php

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
