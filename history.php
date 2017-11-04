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
        .navbar-owner {
            color: #FFFFFF;
            background-color: #8a3541;
        }
        body {
            background: url('./media/background_owner.png');
        }
    </style>
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-owner">
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
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="owner.php">Home</a></li>
            <li>View Request History</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-sm-3">
                            <label for="pet_id">Pet's Name</label>
                            <select id="pet_id" name="pet_id" class="form-control">
                                <option value="">Select Pet</option>
                                <?php
                                $query = "SELECT p.pets_id, p.pet_name FROM pet_user o, pet p
                                          WHERE o.user_id = p.owner_id AND o.user_id = $user_id;";
                                $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    $option = "<option value='" . $row[0] . "'>" . $row[1] ;
                                    $option .= "</option><br>";
                                    echo $option;
                                }
                                pg_free_result($result);
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label for="taker_id">Taker's Name</label>
                            <select id="taker_id" name="taker_id" class="form-control">
                                <option value="">Select Owner</option>
                                <?php
                                $query = "SELECT DISTINCT t.user_id, t.name FROM pet_user o, pet_user t, request r
                                          WHERE r.taker_id = t.user_id AND o.user_id = r.owner_id AND o.user_id = $user_id";
                                $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    $option = "<option value='" . $row[0] . "'>" . $row[1] ;
                                    $option .= "</option><br>";
                                    echo $option;
                                }
                                pg_free_result($result);
                                ?>
                            </select>
                        </div>


                        <div class="col-sm-3">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">Select Status</option>
                                <?php
                                $query = "SELECT DISTINCT r.status FROM pet_user o, request r
                                          WHERE o.user_id = r.owner_id AND o.user_id = $user_id";
                                $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    $option = "<option value='" . $row[0] . "'>" . $row[0] ;
                                    $option .= "</option><br>";
                                    echo $option;
                                }
                                pg_free_result($result);
                                ?>
                            </select>
                        </div>


                        <br>
                        <div class="col-sm-6">
                            <br>
                            <input type="submit" class="btn-primary btn" id="findBtn" name="search" value="Search">
                            <a href="history.php" class="btn-default btn">Cancel</a>


                        </div>
                    </div>
                    <div class="col-md-12">
                        <br>
                        <table class="table table-striped" id="pet_info">
                            <tr>
                                <th >Pet </th>
                                <th >Taker </th>
                                <th >Posted</th>
                                <th >Begin</th>
                                <th >End</th>
                                <th >Bids</th>
                                <th>Remark</th>
                                <th>Status</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $pet_id = $_GET['pet_id'];
                                $taker_id = $_GET['taker_id'];
                                $status = $_GET['status'];


                                $query = "SELECT p.pet_name, t.name, r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.status FROM pet_user o, request r, pet p, pet_user t
                                          WHERE r.owner_id = o.user_id AND r.pets_id = p.pets_id AND t.user_id = r.taker_id AND o.user_id = $user_id" ;

                                if (trim($pet_id)) {
                                    $query .= " AND p.pets_id = " . $pet_id;
                                }

                                if (trim($taker_id)) {
                                    $query .= " AND r.taker_id = '" . $taker_id . "'";
                                }

                                if (trim($status)) {
                                    $query .= " AND r.status = '" . $status . "'";
                                }

                                $query .= " ORDER BY r.post_time;";
                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT p.pet_name, t.name, r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.status FROM pet_user o, request r, pet p, pet_user t
                                          WHERE r.owner_id = o.user_id AND r.pets_id = p.pets_id AND t.user_id = r.taker_id AND o.user_id = $user_id" ;
                                $query .= " ORDER BY r.post_time;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {

                                $post_time = substr($row[2], 0, -7);

                                echo "<tr>";
                                echo "<td >$row[0]</td >";
                                echo "<td >$row[1]</td >";
                                echo "<td >$post_time</td>";
                                echo "<td >$row[3]</td >";
                                echo "<td >$row[4]</td>";
                                echo "<td >$row[5]</td >";
                                echo "<td >$row[6]</td >";
                                echo "<td >$row[7]</td >";

                                echo "</tr>";
                            }
                            pg_free_result($result);
                            ?>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>