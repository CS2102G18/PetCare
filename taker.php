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

$query = "SELECT name, email, address FROM pet_user WHERE user_id = $user_id";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

$row = pg_fetch_row($result);
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
                <li><a href="Alpha/request.php"> Send Request </a></li>
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
                        <h4>Pending Requests</h4>
                    </div>


                    <table class="table table-striped">

                        <tr>
                            <th>Pet Owner</th>
                            <th>Pet Name</th>
                            <th>Pet Species</th>
                            <th>Pet Age</th>
                            <th>Pet Size</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Bid Offered</th>
                            <th>Action</th>
                        </tr>

                        <?php
                        $query = "SELECT r.request_id, r.owner_id, r.care_begin, r.care_end, r.remarks, r.bids, p.pet_name, c.age, c.size, c.species FROM request r, pet p, petcategory c WHERE r.taker_id = $user_id AND r.status = 'pending' AND r.care_begin > CURRENT_TIMESTAMP AND p.pets_id = r.pets_id AND p.pcat_id = c.pcat_id ORDER BY r.bids DESC;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $request_id = $row[0];
                            $owner_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = " . $row[1] . ";"))[0];
                            $care_begin = $row[2];
                            $care_end = $row[3];
                            $remarks = $row[4];
                            $bids = $row[5];
                            $request_pet_name = $row[6];
                            $request_pet_age = $row[7];
                            $request_pet_size = $row[8];
                            $request_pet_species = $row[9];


                            echo "<tr>";
                            echo "<td >$owner_name</td >";
                            echo "<td >$request_pet_name</td >";
                            echo "<td >$request_pet_species</td >";
                            echo "<td >$request_pet_age</td >";
                            echo "<td >$request_pet_size</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$bids</td >";
                            echo "<td >
                                    <form class='form-inline' action='requestAction.php' method='get'><div class='form-group' style='float: left;'><input type='submit' class='form-control' value='Accept'></div><input type='hidden' name='accept_id' value=$request_id></form>
                                    <form class='form-inline' action='requestAction.php' method='get'><div class='form-group' style='float: left;'><input type='submit' class='form-control' value='Reject'></div><input type='hidden' name='reject_id' value=$request_id></form>
                                  </td>";

                            echo "</tr>";
                        }
                        ?>

                    </table>

                    <div class="container">
                        <h4>Ongoing Requests</h4>
                    </div>


                    <table class="table table-striped">

                        <tr>
                            <th>Pet Owner</th>
                            <th>Pet Name</th>
                            <th>Pet Species</th>
                            <th>Pet Age</th>
                            <th>Pet Size</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Bid Offered</th>
                        </tr>

                        <?php
                        $query = "SELECT r.request_id, r.owner_id, r.care_begin, r.care_end, r.remarks, r.bids, p.pet_name, c.age, c.size, c.species FROM request r, pet p, petcategory c WHERE r.taker_id = $user_id AND r.status = 'successful' AND r.care_begin > CURRENT_TIMESTAMP AND p.pets_id = r.pets_id AND p.pcat_id = c.pcat_id ORDER BY r.bids DESC;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $request_id = $row[0];
                            $owner_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = " . $row[1] . ";"))[0];
                            $care_begin = $row[2];
                            $care_end = $row[3];
                            $remarks = $row[4];
                            $bids = $row[5];
                            $request_pet_name = $row[6];
                            $request_pet_age = $row[7];
                            $request_pet_size = $row[8];
                            $request_pet_species = $row[9];


                            echo "<tr>";
                            echo "<td >$owner_name</td >";
                            echo "<td >$request_pet_name</td >";
                            echo "<td >$request_pet_species</td >";
                            echo "<td >$request_pet_age</td >";
                            echo "<td >$request_pet_size</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$bids</td >";
                            echo "</tr>";
                        }
                        ?>

                    </table>


                    <div class="container">
                        <h3>Your available slots</h3>
                    </div>

                    <table class="table table-striped">

                        <div class="container">
                            <h4>Active Slots</h4>
                        </div>

                        <tr>
                            <th>Duration From</th>
                            <th>Duration To</th>
                            <th>Pet Species</th>
                            <th>Pet Size</th>
                            <th>Pet Age</th>
                            <th>Action</th>
                        </tr>

                        <?php
                        $query = "SELECT a.avail_id, a.start_time, a.end_time, p.species, p.size, p.age FROM availability a, petcategory p WHERE a.pcat_id = p.pcat_id AND a.taker_id =$user_id AND a.is_deleted = FALSE AND a.start_time > CURRENT_TIMESTAMP ORDER BY a.start_time;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $avail_id = $row[0];
                            $start = $row[1];
                            $end = $row[2];
                            $pet_species = $row[3];
                            $pet_size = $row[4];
                            $pet_age = $row[5];


                            echo "<tr>
                                  <td >$start</td >
                                  <td >$end</td >
                                  <td >$pet_species</td >
                                  <td >$pet_size</td >
                                  <td >$pet_age</td >
                                  <td>
                                    <form class='form-inline' action='availAction.php' method='get'><div class='form-group' style='float: left;'><input type='submit' class='form-control' value='Delete Slot'></div><input type='hidden' name='delete_avail_id' value=$avail_id></form>
                                  </td>
                                  </tr>";

                        }
                        ?>
                    </table>

                    <table class="table table-striped">

                        <div class="container">
                            <h4>Deleted Slots</h4>
                        </div>

                        <tr>
                            <th>Duration From</th>
                            <th>Duration To</th>
                            <th>Pet Species</th>
                            <th>Pet Size</th>
                            <th>Pet Age</th>
                            <th>Action</th>
                        </tr>

                        <?php
                        $query = "SELECT a.avail_id, a.start_time, a.end_time, p.species, p.size, p.age FROM availability a, petcategory p WHERE a.pcat_id = p.pcat_id AND a.taker_id =$user_id AND a.is_deleted = TRUE AND a.start_time > CURRENT_TIMESTAMP ORDER BY a.start_time;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $avail_id = $row[0];
                            $start = $row[1];
                            $end = $row[2];
                            $pet_species = $row[3];
                            $pet_size = $row[4];
                            $pet_age = $row[5];


                            echo "<tr>
                                  <td >$start</td >
                                  <td >$end</td >
                                  <td >$pet_species</td >
                                  <td >$pet_size</td >
                                  <td >$pet_age</td >
                                  <td>
                                    <form class='form-inline' action='availAction.php' method='get'><div class='form-group' style='float: left;'><input type='submit' class='form-control' value='Restore Slot'></div><input type='hidden' name='restore_avail_id' value=$avail_id></form>
                                  </td>
                                  </tr>";

                        }
                        ?>
                    </table>

                    <div class="container">
                        <a class="btn btn-info" role="button" href="addavail.php">Add New Slots +</a>
                    </div>


                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
</body>                                                                                                                                                              

