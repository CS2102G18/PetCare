<?php include "../config/db-connection.php"; ?>
<?php
// Start the session
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
} else {
    header("Location: login.php");
    exit;
}
if ($_GET['usage'] == 'pet') {
    $p_id = $_GET['p_id'];
    $query = "UPDATE pet SET is_deleted=false WHERE pets_id=" . $p_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);

    header("Location: admin_pet.php");
    echo "<script>window.location = 'admin_pet.php';</script>";
    exit();
}
?>