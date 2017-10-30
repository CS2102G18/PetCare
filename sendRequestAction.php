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

<!DOCTYPE html>
<html>
<head>
    <title>PetCare</title>
    <link rel="stylesheet" type="text/css" href="./vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./vendor/css/style.css">
    <link rel="stylesheet" type="text/css" href="./vendor/css/new-task-styling.css">
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="./vendor/js/bootstrap-datetimepicker.min.js"></script>
    <script src="./vendor/js/find-task.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
        });
    </script>
</head>
<body>
<?php
if (isset($_GET["send_req"])) {
    $taker_id = $_GET["taker_id"];
    $user_id = $_GET["user_id"];
    $start_time = $_GET["start_time"];
    $end_time = $_GET["end_time"];
    $pet_id = $_GET["pet_id"];
    $bids = $_GET["bids"];

    $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
                     VALUES ($user_id, $taker_id, '$start_time', '$end_time', '$remarks','$bids',$pet_id);";
    $result = pg_query($insert_query) or die('Query failed: ' . pg_last_error());
    print $insert_query;
    pg_free_result($result);

    echo "<script>window.location = 'request.php';</script>";


}

?>
</body>>
