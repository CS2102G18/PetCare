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

$p_id = $_GET['pet_id'];
$query = "DELETE FROM pet p WHERE p.pets_id=" . $p_id . ";";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
pg_free_result($result);
header("Location: owner.php");
echo "<script>window.location = './owner.php';</script>";
exit();
?>