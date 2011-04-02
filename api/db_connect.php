<?php
$host     = 'localhost';
$user     = 'courseov_dbuser';
$password = 'courseoverflow';

$dbconn = mysql_connect($host, $user, $password) or die('Could not connect to the database: '.mysql_error());

mysql_select_db('courseov_api');

?>
