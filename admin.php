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
    <script src="./vendor/d3/d3.min.js"></script>
    <script src="./vendor/calendar_graph.js"></script>

    <style>
        .navbar-admin {
            color: #FFFFFF;
            background-color: #793585;
        }

        .col-centered {
            display: block;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
    </style>
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-admin">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
          	<li><a href="owner.php"> As a Pet Owner </a></li>
            <li><a href="taker.php"> As a Care Taker </a></li>
            <li><a href="history.php"> View History </a></li>
            <li><a href="profile.php"> Your Profile </a></li>
            <li><a href="logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content-container container">
    <div class="panel new-task-panel">
        <div class="container">
            <h2>Managing Entry Data</h2>
        </div>
        <div class="row">
            <div class="col-sm-2 col-centered">
                <a href="./admin/admin_pet.php"><h4>Pets</h4></a>
            </div>
            <div class="col-sm-3 col-centered">
                <a href="./admin/admin_avail.php"><h4>Availability</h4></a>
            </div>
            <div class="col-sm-2 col-centered">
                <a href="./admin/admin_user.php"><h4>Users</h4></a>
            </div>
            <div class="col-sm-3 col-centered">
                <a href="./admin/admin_req.php"><h4>Requests</h4></a>
            </div>
            <div class="col-sm-2 col-centered">
                <a href="./admin/admin_pcat.php"><h4>Pet Categories</h4></a>
            </div>
        </div>

    </div>
</body>                                                                                                                                                              
