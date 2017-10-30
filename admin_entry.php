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
    <link rel="stylesheet" type="text/css" href="./vendor/css/new-task-styling.css">
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="./vendor/js/bootstrap-datetimepicker.min.js"></script>

    <style>
        .navbar-admin {
            color: #FFFFFF;
            background-color: #793585;
        }
    </style>
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-admin">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="taker.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="request.php"> Send Request </a></li>
                <li><a href="history.php"> View History </a></li>
                <li><a href="logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content-container container">
    <div class="panel new-task-panel">
        <div class="container">
            <div class="col-sm-12">
                <h5>Choose the category of the entries</h5>
            </div>
            <div class="col-sm-8">
                <select name="entry_cate" class="form-control" required="true">
                    <option value="">Select Category</option>
                    <option value="user">Users</option>
                    <option value="pet">Pet</option>
                    <option value="availability">Availability</option>
                    <option value="requests">Requests</option>
                </select>
            </div>
            <div class="container">
                <button type="submit" name="go" class="btn btn-primary">Go</button>
            </div>
        </div>

        <?php
        die($_GET['go']);
        if (isset($_GET['go'])) {
            if ($_GET["entry_cate"] == "user") {
                include "./admin/admin_user.php";
            } else if ($_GET["entry_cate"] == "pet") {
                include "./admin/admin_pet.php";
            } else if ($_GET["entry_cate"] == "availability") {
                include "./admin/admin_avail.php";
            } else if ($_GET["entry_cate"] == "requests") {
                include "./admin/admin_req.php";
            }
        }
        ?>
    </div>
</div>