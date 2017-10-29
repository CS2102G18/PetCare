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
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>
<?php

$query1 = "SELECT name, email, address FROM pet_user WHERE user_id = $user_id";
$result1 = pg_query($query1) or die('Query failed: ' . pg_last_error());

$row = pg_fetch_row($result1);
$user_name = $row[0];
$user_email = $row[1];
$user_address = $row[2];

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
                            <h2>Hello, <?php echo $user_name; ?> </h2>
                        </div>
                        <table>

                            <tr>
                                <th>Email:</th>
                                <td><?php echo $user_email; ?></td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td><?php echo $user_address; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <br>
            <br>

            <div class="container">
                <a class="btn btn-info" role="button" href="owner.php">As a pet owner</a>
                <a class="btn btn-info" role="button" href="taker.php">As a care taker</a>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="container">
                        <h3>Your Requests</h3>
                    </div>

                    <div class="container">
                        <h4>Ongoing Requests</h4>
                    </div>


                    <table class="table table-striped">

                        <tr>
                            <th>Taker Name</th>
                            <th>Taker's Email</th>
                            <th>Pet Name</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Your bid</th>
                        </tr>

                        <?php
                        pg_free_result($result1);
                        $query3 = "SELECT u.name, u.email, r.care_begin, r.care_end, r.bids, p.pet_name FROM request r, pet_user u, pet p 
                                   WHERE r.owner_id = $user_id 
                                         AND r.status = 'successful' 
                                         AND r.care_end > current_timestamp 
                                         AND r.taker_id = u.user_id
                                         AND r.pets_id = p.pets_id
                                   ORDER BY care_begin;";
                        $result3 = pg_query($query3) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result3)) {

                            $taker_name = $row[0];
                            $taker_email = $row[1];
                            $care_begin = $row[2];
                            $care_end = $row[3];
                            $bids = $row[4];
                            $request_pet_name = $row[5];

                            echo "<tr>";
                            echo "<td >$taker_name</td >";
                            echo "<td >$taker_email</td >";
                            echo "<td >$request_pet_name</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$bids</td >";
                            echo "</tr>";
                        }
                        pg_free_result($result3);
                        ?>

                    </table>



                    <div class="container">
                        <h4>Unsuccessful Requests</h4>
                    </div>

                    <table class="table table-striped">

                        <tr>
                            <th>Taker Name</th>
                            <th>Pet Name</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Your bid</th>
                            <th>Status</th>
                        </tr>


                        <?php
                        $query4 = "SELECT u.name, r.care_begin, r.care_end, r.bids, p.pet_name FROM request r, pet_user u, pet p 
                                   WHERE r.owner_id = $user_id 
                                         AND r.status = 'failed' 
                                         AND r.taker_id = u.user_id
                                         AND r.pets_id = p.pets_id
                                   ORDER BY care_begin;;";
                        $result4 = pg_query($query4) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result4)) {

                            $taker_name = $row[0];
                            $care_begin = $row[1];
                            $care_end = $row[2];
                            $bids = $row[3];
                            $request_pet_name = $row[4];


                            echo "<tr>";
                            echo "<td >$taker_name</td >";
                            echo "<td >$request_pet_name</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$bids</td >";
                            echo "<td>
                                        <form class='form-inline' action='request.php' method='get'><div class='form-group' style='float: right;'><input type='submit' class='form-control' value='Request Another Taker'></div><input type='hidden' name='request_id' value=$request_id></form>
                                        <form class='form-inline' action='read.php' method='get'><div class='form-group' style='float: right;'><input type='submit' class='form-control' value='Read'></div><input type='hidden' name='request_id' value=$request_id></form>                                                                                                                                                                                                            
                                  </td>";
                            echo "</tr>";
                            };
                        pg_free_result($result4);

                        ?>

                    </table>
                    <br/>
                    <br/>
                    <div class="container">
                        <h3>Your pets</h3>
                    </div>

                    <table class="table table-striped">

                        <tr>
                            <th>Pet ID</th>
                            <th>Pet Name</th>
                            <th>Pet Species</th>
                            <th>Pet Size</th>
                            <th>Pet Age</th>
                            <th>Actions</th>
                        </tr>

                        <?php

                        $query2 = "SELECT * FROM pet p WHERE p.owner_id =$user_id;";
                        $result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result2)) {

                            $pet_id = $row[0];
                            $pet_name = $row[3];
                            $pet_species = pg_fetch_row(pg_query("SELECT species FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_size = pg_fetch_row(pg_query("SELECT size FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_age = pg_fetch_row(pg_query("SELECT age FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];

                            echo "<tr>";
                            echo "<td >$pet_id</td >";
                            echo "<td >$pet_name</td >";
                            echo "<td >$pet_species</td >";
                            echo "<td >$pet_size</td>";
                            echo "<td >$pet_age</td >";
                            echo "</tr>";
                        }
                        pg_free_result($result2);
                        ?>
                    </table>
                    <div class="container">
                        <a class="btn btn-info" role="button" href="addpet.php">Add New Pet +</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body> 
