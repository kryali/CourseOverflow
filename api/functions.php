<?php

include("db_connect.php");
include("html4nntp/nntp.php");
include("./api_config.php");

function authenticate($netid, $password){
    
    if(!verify_login($netid, $password)){
        session_destroy();
	return false;
    }

    $query  = "INSERT INTO Users(netid) Values(";
    $query .= "'" . mysql_real_escape_string($netid) . "'";
    $query .= ");";

    mysql_query($query);

    $_SESSION["netid"] = $netid;
    $_SESSION["password"] = $password;
    $_SESSION["auth"]  = true; 
    
    return true;
}

function submit_vote($message_id, $positive){

    if(!isset($_SESSION["netid"]))
        return false;

    //Check for duplicate votes
    $query  = "SELECT * FROM Votes WHERE ";
    $query .= "netid = '" . mysql_real_escape_string($_SESSION["netid"]) . "' AND ";
    $query .= "message_id = '" . mysql_real_escape_string($message_id) . "'";
    $query .= ";";

    $result = mysql_query($query);

    if(!$result)
        return false;

    $res_array = mysql_fetch_assoc($result);

    //Delete existing votes
    if(mysql_num_rows($result) != 0){

        if($positive && $res_array["positive"] == 1)
            return false;
        else if(!$positive && $res_array["positive"] == 0)
            return false;

        $query  = "DELETE FROM Votes WHERE ";
        $query .= "netid = '" . mysql_real_escape_string($_SESSION["netid"]) . "' AND ";
        $query .= "message_id = '" . mysql_real_escape_string($message_id) . "'";
        $query .= ";";
        
        $result = mysql_query($query);

        if(!$result)
            return false;
    }
    // update reputation
    $author_email = get_author_email($message_id);
    $author_netid = substr($author_email,0,strpos($author_email,"@"));
    mysql_query("INSERT INTO Users(netid) VALUES ('$author_netid')");
    mysql_query("UPDATE Users SET reputation = reputation + $positive WHERE netid = '$author_netid' LIMIT 1");	

    //Insert the new vote
    $query  = "INSERT INTO Votes Values( ";
    $query .= "'" . mysql_real_escape_string($_SESSION["netid"]) . "', ";
    $query .= "'" . mysql_real_escape_string($message_id) . "', ";

    if($positive)
        $query .= "1";
    else
        $query .= "0";

    $query .= ");";

    $result = mysql_query($query);
    if(!$result)
        return false;

    return true;

}

function get_votes($message_id){
    
    $query  = "SELECT netid,positive as direction FROM Votes WHERE ";
    $query .= "message_id = '" . mysql_real_escape_string($message_id) . "'";
    $query .= ";";

    $result = mysql_query($query);
    if(!$result){
	return NULL;
    }

    $num_results = mysql_num_rows($result);
    if($num_results == 0){
	return NULL;
    }
    
    $arr = array();
    for($ind = 0; $ind < $num_results; $ind++){
	$row = mysql_fetch_array($result);
	$arr[] = $row;
    }


    return $arr;
}

function get_reputation($netid_address){
    
    $query  = "SELECT reputation FROM Users WHERE ";
    $query .= "netid = '" . mysql_real_escape_string($netid_address) . "'";
    $query .= ";";

    $result = mysql_query($query);

    if(!$result || mysql_num_rows($result) == 0)
        return 0;

    $value = mysql_fetch_assoc($result);

    return $value["reputation"];
}

function subscribe_to_class($class_name){

    if(!isset($_SESSION["netid"]))
        return false;

    $query  = "INSERT INTO Subscriptions Values(";
    $query .= "'" . mysql_real_escape_string($_SESSION['netid']) . "', ";
    $query .= "'" . mysql_real_escape_string($class_name) . "'";
    $query .= ");";

    $result = mysql_query($query);

    if(!$result)
        return false;
    return true;
}

function unsubscribe_from_class($class_name){

    if(!isset($_SESSION["netid"]))
        return false;

    $query  = "DELETE FROM Subscriptions WHERE ";
    $query .= "netid = '" . mysql_real_escape_string($_SESSION['netid']) . "' AND ";
    $query .= "subname = '" . mysql_real_escape_string($class_name) . "'";
    $query .= ";";

    $result = mysql_query($query);

    if(!$result || mysql_affected_rows() == 0)
        return false;
    return true;
}

function get_subscriptions($netid_address){
    $query  = "SELECT subname FROM Subscriptions WHERE ";
    $query .= "netid = '" . mysql_real_escape_string($netid_address) . "'";
    $query .= ";";

    $result = mysql_query($query);
    if(!$result){
	return null;
    }
 
    $num_results = mysql_num_rows($result);
    if($num_results == 0){
	return null;
    }

    $arr = array();
    for($ind = 0; $ind < $num_results; $ind++){
	$row = mysql_fetch_array($result);
	$arr[] = $row;
    }

    return $arr;
}

?>
