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

$avail_id = $_GET['avail_id'];
$query = "UPDATE availability SET is_deleted=true WHERE avail_id=" . $avail_id . ";";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
pg_free_result($result);
$query = "UPDATE request SET status='failed' WHERE status='pending' AND request_id NOT IN(
         SELECT r.request_id FROM request r,availability a,pet p
         WHERE a.pcat_id=p.pcat_id AND r.pets_id=p.pets_id AND a.is_deleted=false AND r.taker_id=" . $user_id . " AND a.taker_id=" . $user_id . " AND a.start_time<=r.care_begin AND r.care_end<=a.end_time);";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
pg_free_result($result);
header("Location: taker.php");
echo "<script>window.location = './taker.php';</script>";
exit();
?>