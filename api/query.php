<?php
require_once("functions.php");
require_once("query_helpers.php");

// GET variables
$action = cleanInput($_GET['action']);

if(empty($action)){
	showHelp("No action provided.");
	exit;
}

if($action != "authenticated" && !isset($_SESSION['auth'])){
	showHelp("Not authenticated.");
	exit;
}

if($action == "authenticate"){
	
	$email = cleanInput($_GET['email']);
	$password = cleanInput($_GET['password']);
	$ret = authenticate($email,$password);
	
	outputBoolean("authenticate",$ret);
	
}else if($action == "submit_vote"){

	$message_id = cleanInput($_GET['message_id']);
	$direction = cleanInput($_GET['direction']);
	$ret = submit_vote($message_id,$direction);
	outputBoolean("submit_vote",$ret);

}else if($action == "get_votes"){
	
	$message_id = cleanInput($_GET['message_id']);
	$votes = get_votes($message_id);
	outputResults("get_votes",$votes);

}else if($action == "get_reputation"){

	$email = cleanInput($_GET['email']);
	$reputation = get_reputation($email);
	outputResults("get_reputation",$reputation);

}else if($action == "subscribe_to_class"){

	$class_name = cleanInput($_GET['class_name']);
	$ret = subscribe_to_class($class_name);
	outputBoolean("subscribe_to_class",$ret);

}else if($action == "unsubscribe_from_class"){
	
	$class_name = cleanInput($_GET['class_name']);
	$ret = unsubscribe_from_class($class_name);
	outputBoolean("unsubscribe_from_class",$ret);

}else if($action == "get_subscriptions"){
	
	$email = cleanInput($_GET['email']);
	$subscriptions = get_subscriptions($email);
	outputResults("get_subscriptions",$subscriptions);
}

?>
