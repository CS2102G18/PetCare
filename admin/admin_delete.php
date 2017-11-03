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
    $query = "UPDATE pet SET is_deleted=true WHERE pets_id=" . $p_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);
    $query = "UPDATE request SET status='cancelled' WHERE status='pending' AND pets_id=$p_id;";
    $result = pg_query($query) or die('Query failedb: ' . pg_last_error());
    pg_free_result($result);
    header("Location: admin_pet.php");
    echo "<script>window.location = 'admin_pet.php';</script>";
    exit();
}
if ($_GET['usage'] == 'user') {
    $u_id = $_GET['u_id'];
    $query = "UPDATE pet_user SET is_deleted=true WHERE user_id=" . $u_id . ";";
    $result = pg_query($query) or die('Query faileda: ' . pg_last_error());
    pg_free_result($result);

    $query = "UPDATE request SET status='cancelled' WHERE status='pending' AND (owner_id=$u_id OR taker_id=$u_id);";
    $result = pg_query($query) or die('Query failedb: ' . pg_last_error());
    pg_free_result($result);

    $query = "UPDATE availability SET is_deleted=true WHERE taker_id=$u_id;";
    $result = pg_query($query) or die('Query failedb: ' . pg_last_error());
    pg_free_result($result);

    $query = "UPDATE pet SET is_deleted=true WHERE owner_id=$u_id;";
    $result = pg_query($query) or die('Query failedb: ' . pg_last_error());
    pg_free_result($result);

    $query = "UPDATE request SET status='cancelled' 
              WHERE status='pending' 
              AND pets_id IN (
                  SELECT pets_id FROM pet
                  WHERE owner_id = $u_id
              );";
    $result = pg_query($query) or die('Query failedb: ' . pg_last_error());
    pg_free_result($result);

    header("Location: admin_user.php");
    echo "<script>window.location = 'admin_pet.php';</script>";
    exit();
}
?>