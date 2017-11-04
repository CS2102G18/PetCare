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
    <style>
        .navbar-taker {
            color: #FFFFFF;
            background-color: #035f72;
        }

        body {
            background: url('./media/background_taker.png');
        }
    </style>
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



<nav class="navbar navigation-bar navbar-inverse navbar-taker navbar-fixed-top">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href=""> PetCare</a></div>
        <div class="collapse pull-right navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="history.php"> View History </a></li>
                <li><a href="profile.php"> Your Profile </a></li>
                <?php
                    $admin_query = "SELECT role FROM pet_user WHERE user_id=" . $user_id . ";";
                    $admin_result = pg_query($admin_query) or die('Query failed: ' . pg_last_error());
                    $admin_row = pg_fetch_row($admin_result);
                    if(strcmp($admin_row[0],"admin") == 0){
                        echo '<li><a href="admin.php"> Admin </a></li>';
                    }
                    pg_free_result($admin_result);
                ?>
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
                            <tr><th>Email:</th><td><?php echo $user_email; ?></td></tr>
                            <tr><th>Address:</th><td><?php echo $user_address; ?></td></tr>
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
                            <th>Pet Owner Info</th>
                            <th>Pet Info</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Remarks</th>
                            <th>Bid Offered</th>
                            <th>Action</th>
                        </tr>

                        <?php
                        $query = "SELECT r.request_id, u.name, u.email, r.care_begin, r.care_end, r.remarks, r.bids, p.pet_name, c.age, c.size, c.species 
                                  FROM request r, pet p, petcategory c, pet_user u
                                  WHERE r.taker_id = $user_id
                                        AND r.status = 'pending' 
                                        AND r.care_begin > CURRENT_TIMESTAMP 
                                        AND p.pets_id = r.pets_id 
                                        AND p.pcat_id = c.pcat_id
                                        AND u.user_id = r.owner_id
                                        AND p.is_deleted = false
                                  ORDER BY r.bids DESC;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $request_id = $row[0];
                            $owner_name = $row[1];
                            $owner_email = $row[2];
                            $care_begin = $row[3];
                            $care_end = $row[4];
                            $remarks = $row[5];
                            $bids = $row[6];
                            $request_pet_name = $row[7];
                            $request_pet_age = $row[8];
                            $request_pet_size = $row[9];
                            $request_pet_species = $row[10];


                            echo "<tr>";
                            echo "<td >$owner_name<br>$owner_email</td >";
                            echo "<td >$request_pet_name<br>$request_pet_species<br>$request_pet_age<br>$request_pet_size</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$remarks</td >";
                            echo "<td >$bids</td >";
                            echo "<td >
                                      <a class=\"btn btn-default\" role=\"button\" href=\"requestAction.php?mode=1&request_id=$request_id\">Accept</a>
                                      <a class=\"btn btn-danger\" role=\"button\" href=\"requestAction.php?mode=2&request_id=$request_id\">Reject</a>
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
                            <th>Pet Owner Info</th>
                            <th>Pet Info</th>
                            <th>Begin</th>
                            <th>End</th>
                            <th>Remarks</th>
                            <th>Bid Offered</th>
                        </tr>

                        <?php
                        $query = "SELECT r.request_id, u.name, u.email, r.care_begin, r.care_end, r.remarks, r.bids, p.pet_name, c.age, c.size, c.species 
                                  FROM request r, pet p, petcategory c, pet_user u
                                  WHERE r.taker_id = $user_id
                                        AND r.status = 'successful' 
                                        AND r.care_begin > CURRENT_TIMESTAMP 
                                        AND p.pets_id = r.pets_id 
                                        AND p.pcat_id = c.pcat_id
                                        AND u.user_id = r.owner_id
                                        AND p.is_deleted = false
                                  ORDER BY r.bids DESC;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $request_id = $row[0];
                            $owner_name = $row[1];
                            $owner_email = $row[2];
                            $care_begin = $row[3];
                            $care_end = $row[4];
                            $remarks = $row[5];
                            $bids = $row[6];
                            $request_pet_name = $row[7];
                            $request_pet_age = $row[8];
                            $request_pet_size = $row[9];
                            $request_pet_species = $row[10];


                            echo "<tr>";
                            echo "<td >$owner_name<br>$owner_email</td >";
                            echo "<td >$request_pet_name<br>$request_pet_species<br>$request_pet_age<br>$request_pet_size</td >";
                            echo "<td >$care_begin</td >";
                            echo "<td >$care_end</td >";
                            echo "<td >$remarks</td >";
                            echo "<td >$bids</td >";
                            echo "</tr>";

                            echo "
                            
                            
                            ";
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
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>

                        <?php
                        $query = "SELECT a.avail_id, a.start_time, a.end_time, a.remarks, p.species, p.size, p.age 
                                  FROM availability a, petcategory p 
                                  WHERE a.pcat_id = p.pcat_id 
                                        AND a.taker_id =$user_id 
                                        AND a.is_deleted = FALSE 
                                        AND a.start_time > CURRENT_TIMESTAMP 
                                  ORDER BY a.start_time;";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());


                        while ($row = pg_fetch_row($result)) {

                            $avail_id = $row[0];
                            $start = $row[1];
                            $end = $row[2];
                            $remarks = $row[3];
                            $pet_species = $row[4];
                            $pet_size = $row[5];
                            $pet_age = $row[6];


                            echo "<tr>
                                  <td >$start</td >
                                  <td >$end</td >
                                  <td >$pet_species</td >
                                  <td >$pet_size</td >
                                  <td >$pet_age</td >
                                  <td >$remarks</td >
                                  <td>
                                    <a class=\"btn btn-danger\" role=\"button\" href=\"deleteavail.php?avail_id=$avail_id\">Delete</a>
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
</html>   
