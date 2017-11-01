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
if (isset($_GET["reject_id"])) {
    $reject_id = $_GET["reject_id"];
    $reject_query = "UPDATE request SET status = 'failed' WHERE request_id =$reject_id;";
    $result = pg_query($reject_query) or die('Query failed: ' . pg_last_error());
    pg_free_result($result);
    echo "<script>window.location = 'taker.php';</script>";
}

if (isset($_GET["accept_id"])) {
    $accept_id = $_GET["accept_id"];
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
                  <form method='get'><button type='submit' class='btn btn-default' name='accept'>Accept Anyway</button><input type='hidden' name='accept_id' value=$accept_id></form>>
                  <button type='button' class='btn btn-default'><a href='taker.php'>Cancel</a></button>
                </div>
            </div>
        </div>
    </div>
    </form>";
    }
    else {
        $accept_query = "UPDATE request SET status = 'successful' WHERE request_id =$accept_id;";
        $result = pg_query($accept_query) or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
        echo "<script>window.location = 'taker.php';</script>";
    }

}
if ((isset($_GET['accept']))) {
    $accept_id = $_GET["accept_id"];
    $accept_query = "UPDATE request SET status = 'successful' WHERE request_id =$accept_id;";
    $result = pg_query($accept_query) or die('Query failed: ' . pg_last_error());
    pg_free_result($result);
    echo "<script>window.location = 'taker.php';</script>";
}
?>
</body>
</html>
