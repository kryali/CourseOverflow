<?php
require("html4nntp/nntp.php");
require("config/html4nntp.cfg.php");

$username = $_POST['username'];
$password = $_POST['password'];

?>

<form method="get" action="http://courseoverflow.web.cs.illinois.edu/CourseOverflow/api/">

<input type="hidden" name="action" value="authenticate" />

<p>Email: <input type="email" name="username" id="username" /></p>
<p>Password: <input type="password" name="password" id="password" /></p>

</form>
