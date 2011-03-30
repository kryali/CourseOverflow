<?php

function authenticate($email, $password){
    return false;
}

function submit_vote($message_id){
    return false;
}

function get_votes($message_id){
    $ret = array();
    return $ret;
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
