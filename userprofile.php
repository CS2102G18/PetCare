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
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>
<?php $query = "SELECT *
          FROM pet_user u
          WHERE u.user_id = $user_id";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

$row = pg_fetch_row($result);
$user_name = $row[1];
?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href=""> PetCare</a></div>
        <div class="nav navbar-nav navbar-form">
            <div class="input-icon">
                <i class="glyphicon glyphicon-search search"></i>
                <input type="text" placeholder="Type to search..." class="form-control search-form" tabindex="1">
            </div>
        </div>
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
    <!-- page content -->
    <div class="container-fluid">

        <!-- panel -->
        <div class="panel new-task-panel">
            <div class="row">
                <div class="col-md-12">
                    <div class="container">
                        <div>
                            <h2>User's name</h2>
                        </div>
                        <table>
                            <tr>
                                <th>Email:</th>
                                <td>email@email.com</td>
                            </tr>
                            <tr>
                                <th>Home address:</th>
                                <td>address, address road, address city</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="container">
                        <h3>Your pets</h3>
                        <div class="col-lg-6 col-md-6 col-xs-12">
                            <button type="button" class="btn btn-large btn-primary"
                                    onclick="window.location='addpet.php'">Add
                            </button>
                        </div>
                    </div>
                    <table>
                        <tr>
                            <!-- Add sorting features -->
                            <th>
                                No.
                            </th>
                            <th>
                                pet size
                            </th>
                            <th>
                                pet age
                            </th>
                            <th>
                                pet name
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>