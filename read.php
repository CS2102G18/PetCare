<?php include "config/db-connection.php"; ?>
<?php
// Start the session
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
} else {
    header("Location: login.php");
    exit;
}
?>
<?php

$request_id = $_GET["request_id"];

$read_query = "UPDATE request SET status = 'cancelled' WHERE request_id =$request_id;";
$result = pg_query($read_query) or die('Query failed: ' . pg_last_error());

pg_free_result($result);
header("Location: owner.php");
?>
