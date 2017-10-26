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
if (isset($_GET["delete_avail_id"])) {
    $delete_avail_id = $_GET["delete_avail_id"];
    $read_query = "UPDATE availability SET is_deleted = TRUE WHERE avail_id =$delete_avail_id;";
    $result = pg_query($read_query) or die('Query failed: ' . pg_last_error());
}

if (isset($_GET["restore_avail_id"])) {
    $restore_avail_id = $_GET["restore_avail_id"];
    $read_query = "UPDATE availability SET is_deleted = FALSE WHERE avail_id =$restore_avail_id;";
    $result = pg_query($read_query) or die('Query failed: ' . pg_last_error());
}

pg_free_result($result);
header("Location: taker.php");
?>
