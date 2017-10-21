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
    <title>Tasource - Making task sourcing simple</title>
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

    <!-- navigation bar -->
    <nav class="navbar navbar-inverse navigation-bar navbar-fixed-top">
        <div class="container navbar-container">
            <div class="navbar-header pull-left"><a class="navbar-brand" href="">PetPet</a></div>
            <div class="nav navbar-nav navbar-form">
                <div class="input-icon">
                    <i class="glyphicon glyphicon-search search"></i>
                    <input type="text" placeholder="Type to search..." class="form-control search-form" tabindex="1">
                </div>
            </div>
            <div class="collapse navbar-collapse pull-right">
                <ul class="nav navbar-nav">
                    <li><a href="userprofile.php">View profile</a></li>
                    <li><a href="request.php">Send Requests</a></li>

                </ul>
            </div>
        </div>
    </nav>


    <div class="container-fluid">

        <!-- panel -->
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <!-- panel heading -->
                <div class="panel-heading">
                    <h2 class="new-task-form-title">Your Pets and its takers</h2>
                </div>

                <!-- panel body -->
                <div class="panel-body">


                    <table class="table table-striped">

                        <tr>
                            <th>Pet ID</th>
                            <th>Pet Name</th>
                            <th>Pet Size</th>
                            <th>Pet Age</th>
                        </tr>


                        <?php
                        $query = "SELECT * FROM pet p WHERE p.owner_id =$user_id;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $pet_id = $row[0];
                            $pet_name = pg_fetch_row(pg_query("SELECT name FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_size = pg_fetch_row(pg_query("SELECT size FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_age = pg_fetch_row(pg_query("SELECT age FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];

                            echo "<tr>";
                            echo "<td >$pet_id</td >";
                            echo "<td >$pet_name</td >";
                            echo "<td >$pet_size</td >";
                            echo "<td >$pet_age</td >";
                            echo "</tr>";
                        }
                        ?>


                    </table>

                </div>

            </form>
        </div>
    </div>



</body>>

