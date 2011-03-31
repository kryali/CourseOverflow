<?php

include("db_connect.php");

function authenticate($email, $password){
    //TODO Authenticate with newsgroup

    session_start();
    $_SESSION["email"] = $email;    
    
    return true;
}

function submit_vote($message_id, $positive){

    if(!isset($_SESSION["email"]))
        return false;

    //Check for duplicate votes
    $query  = "SELECT * FROM Votes WHERE ";
    $query .= "email = '" . mysql_real_escape_string($_SESSION["email"]) . "' AND ";
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
        $query .= "email = '" . mysql_real_escape_string($_SESSION["email"]) . "' AND ";
        $query .= "message_id = '" . mysql_real_escape_string($message_id) . "'";
        $query .= ";";
        
        $result = mysql_query($query);

        if(!$result)
            return false;
    }

    //Insert the new vote
    $query  = "INSERT INTO Votes Values( ";
    $query .= "'" . mysql_real_escape_string($_SESSION["email"]) . "', ";
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
    
    $query  = "SELECT * FROM Votes WHERE ";
    $query .= "message_id = '" . mysql_real_escape_string($message_id) . "'";
    $query .= ";";

    $result = mysql_query($query);

    return $result;
}

function get_reputation($email_address){
    return 0;
}

function subscribe_to_class($class_name){
    return false;
}

function unsubscribe_from_class($class_name){
    return false;
}

function get_subscriptions($email_address){
    $ret = array();
    return $ret;
}

?>
