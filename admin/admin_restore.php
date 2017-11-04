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
if ($_GET['usage'] == 'user') {
    $u_id = $_GET['u_id'];
    $query = "UPDATE pet_user SET is_deleted=false WHERE user_id=" . $u_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);

    header("Location: admin_user.php");
    echo "<script>window.location = 'admin_user.php';</script>";
    exit();
}
if ($_GET['usage'] == 'avail') {
    $u_id = $_GET['a_id'];
    $query = "UPDATE availability SET is_deleted=false WHERE avail_id=" . $u_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);

    header("Location: admin_avail.php");
    echo "<script>window.location = 'admin_avail.php';</script>";
    exit();
}

if ($_GET['usage'] == 'req') {
    $r_id = $_GET['r_id'];
    $query = "UPDATE request SET status='pending' WHERE request_id=" . $r_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);

    header("Location: admin_req.php");
    echo "<script>window.location = 'admin_req.php';</script>";
    exit();
}
?>