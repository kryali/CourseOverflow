<?php
require_once("functions.php");
require_once("query_helpers.php");

// GET variables
$action = cleanInput($_GET['action']);
session_start();

if(empty($action)){
	showHelp("No action provided.");
	exit;
}

if($action == "authenticate" || !isset($_SESSION['auth'])){
	
	$netid = cleanInput($_POST['netid']);
	$password = cleanInput($_POST['password']);
	if(empty($netid) && empty($password)){
		$netid = cleanInput($_GET['netid']);
		$password = cleanInput($_GET['password']);
	}   

    	$ret = authenticate($netid,$password);
	
	if(!$ret){
		outputBoolean($action,$ret,"Not authenticated");
		exit;
	}elseif($action == "authenticate"){
		outputBoolean($action,$ret);
	}
}

if($action == "submit_vote"){

	$message_id = cleanInput($_GET['message_id']);
	$direction = cleanInput($_GET['direction']);
	if(empty($direction) || $direction == "1" || strtolower($direction) == "true" || strtolower($direction) == "up"){
		$direction = true;
	}else{
		$direction = false;
	}

	$ret = submit_vote($message_id,$direction);
	outputBoolean("submit_vote",$ret);

}else if($action == "get_votes"){
	
	$message_id = cleanInput($_GET['message_id']);
	$votes = get_votes($message_id);
	outputResults("get_votes",$votes);

}else if($action == "get_reputation"){

	$netid = cleanInput($_GET['author_netid']);
	$reputation = get_reputation($netid);
	outputResults("get_reputation",$reputation);

}else if($action == "subscribe_to_class"){

	$class_name = cleanInput($_GET['fully_qualified_class_name']);
	$ret = subscribe_to_class($class_name);
	outputBoolean("subscribe_to_class",$ret);

}else if($action == "unsubscribe_from_class"){
	
	$class_name = cleanInput($_GET['fully_qualified_class_name']);
	$ret = unsubscribe_from_class($class_name);
	outputBoolean("unsubscribe_from_class",$ret);

}else if($action == "get_subscriptions"){
	
	$netid = cleanInput($_GET['netid']);
	$subscriptions = get_subscriptions($netid);
	outputResults("get_subscriptions",$subscriptions);
}else{
	outputBoolean($action,false,"Invalid action.");
	exit;
}

?>
