<?php
// Start the session
session_start();
unset($_SESSION["user_id"]);
header("location:login.php");
exit();
?>