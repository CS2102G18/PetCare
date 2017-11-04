<?php include "config/db-connection.php"; ?>
<?php
// Start the session
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $role = $_SESSION["role"];
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
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="./vendor/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
        });
    </script>
</head>
<body>
<?php

$mode = $_GET["mode"];
$request_id = $_GET["request_id"];

if ($mode==2) {
    $reject_id = $request_id;
    $reject_query = "UPDATE request SET status = 'failed' WHERE request_id =$reject_id;";
    $result = pg_query($reject_query) or die('Query failed: ' . pg_last_error());
    pg_free_result($result);
    echo "<script>window.location = 'taker.php';</script>";
}

if ($mode==1) {
    $accept_id = $request_id;
    $check_query = "SELECT COUNT(*) FROM request r1,request r2 WHERE r1.request_id = $accept_id AND r2.taker_id = $user_id AND r2.status = 'successful' AND r1.care_begin < r2.care_end AND r1.care_end > r2.care_begin;";
    $check_result = pg_query($check_query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($check_result);

    if ($row[0] != 0){
        echo "
    <form action='requestAction.php'>
    <div id='successmodal' class='modal fade'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                  <button type='button' class='close' data-dismiss='modal'>&times;</button>
                  <h4 class='modal-title'>Warning: You have clashes</h4>
                </div>
                <div class='modal-body'>
                  <h4>You have already accepted ".$row[0]." requests during the same slot.</h4>
                </div>
                
                <div class='modal-footer'>
                  <form method='get'><button type='submit' class='btn btn-default' name='accept'>Accept Anyway</button><input type='hidden' name='request_id' value=$accept_id></form>>
                  <button type='button' class='btn btn-default'><a href='taker.php'>Cancel</a></button>
                </div>
            </div>
        </div>
    </div>
    </form>";
    }
    else {
        $accept_query = "UPDATE request SET status = 'successful' WHERE request_id =$accept_id;";
        $accept_result = pg_query($accept_query) or die('Query failed: ' . pg_last_error());
        pg_free_result($accept_result);
        $info_query = "SELECT owner_id, pets_id, care_begin, care_end FROM request WHERE request_id = $accept_id;";
        $info_result = pg_query($info_query) or die('Query failed: ' . pg_last_error());
        $row = pg_fetch_row($info_result);
        $owner_id = $row[0];
        $pets_id = $row[1];
        $start = $row[2];
        $end = $row[3];
        $cancel_query = "UPDATE request SET status = 'cancelled' WHERE request_id <> $accept_id AND pets_id = $pets_id AND '$start' < care_end AND '$end' > care_begin;";
        $cancel_result = pg_query($cancel_query) or die('Query failed: ' . pg_last_error());
        pg_free_result($info_result);
        pg_free_result($cancel_result);
        echo "<script>window.location = 'taker.php';</script>";
    }

}
if ((isset($_GET['accept']))) {
    $accept_id = $request_id;
    $accept_query = "UPDATE request SET status = 'successful' WHERE request_id =$accept_id;";
    $accept_result = pg_query($accept_query) or die('Query failed: ' . pg_last_error());
    pg_free_result($accept_result);
    $info_query = "SELECT owner_id, pets_id, care_begin, care_end FROM request WHERE request_id = $accept_id;";
    $info_result = pg_query($info_query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($info_result);
    $owner_id = $row[0];
    $pets_id = $row[1];
    $start = $row[2];
    $end = $row[3];
    $cancel_query = "UPDATE request SET status = 'cancelled' WHERE request_id <> $accept_id AND pets_id = $pets_id AND '$start' < care_end AND '$end' > care_begin;";
    $cancel_result = pg_query($cancel_query) or die('Query failed: ' . pg_last_error());
    pg_free_result($info_result);
    pg_free_result($cancel_result);
    echo "<script>window.location = 'taker.php';</script>";
}
?>
</body>
</html>
