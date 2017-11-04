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
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/css/style.css">
    <link rel="stylesheet" type="text/css" href="../vendor/css/new-task-styling.css">
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="../vendor/js/jquery-3.2.0.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="../vendor/js/bootstrap-datetimepicker.min.js"></script>

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
<?php include "../config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-admin">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="../owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="../owner.php"> As a Pet Owner </a></li>
                <li><a href="../taker.php"> As a Care Taker </a></li>
                <li><a href="../history.php"> View History </a></li>
                <li><a href="../profile.php"> Your Profile </a></li>
                <li><a href="../logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content-container container">
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="../admin.php">Admin</a></li>
            <li>View Requests</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-6">
                                <div class="col-sm-3">
                                    <label for="pet_owner">Pet's Owner</label>
                                    <select name="pet_owner" class="form-control">
                                        <option value="">Select Owner</option>
                                        <?php
                                        $query = "SELECT user_id, name, role FROM pet_user ORDER BY user_id";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[1] . " (id: " . $row[0] . ")";
                                            if ($row[2] == "admin") {
                                                $option .= " ***ADMIN***";
                                            }
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label for="care_giver">Care Giver</label>
                                    <select name="care_giver" class="form-control">
                                        <option value="">Select Care Giver</option>
                                        <?php
                                        $query = "SELECT user_id, name, role FROM pet_user ORDER BY user_id";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[1] . " (id: " . $row[0] . ")";
                                            if ($row[2] == "admin") {
                                                $option .= " ***ADMIN***";
                                            }
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label for="care_giver">Pet</label>
                                    <select name="care_giver" class="form-control">
                                        <option value="">Select Care Giver</option>
                                        <?php
                                        $query = "SELECT pets_id, pet_name FROM pet ORDER BY pets_id";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[1] . " (id: " . $row[0] . ")";
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Post Start</label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="start-datetimepicker">
                                        <input type="text" class="form-control" name="post_start">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Post End</label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="end-datetimepicker">
                                        <input type="text" class="form-control" name="post_end">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Slot Start</label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="sb-datetimepicker">
                                        <input type="text" class="form-control" name="slot_start">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Slot End</label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="se-datetimepicker">
                                        <input type="text" class="form-control" name="slot_end">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="col-sm-3">
                                <label for="lower_bound">Lower bound</label>
                                <input id="user_kw" name="user_kw" type="text" class="form-control" placeholder="Keywords">
                            </div>
                            <div class="col-sm-3">
                                <label for="add_kw">Upper bound</label>
                                <input id="add_kw" name="add_kw" type="text" class="form-control" placeholder="Keywords">
                            </div>
                        </div>
                    </div>



                    <div class="col-md-12" style="overflow: auto;">
                        <br>
                        <table class="table table-striped" id="pet_info">
                            <tr>
                                <th >Request ID</th>
                                <th >Pet Owner</th>
                                <th >Care Giver</th>
                                <th >Pet Name</th>
                                <th >Pet Category</th>
                                <th >Post at</th>
                                <th >Begin at</th>
                                <th >End at</th>
                                <th >Bids</th>
                                <th >Slot</th>
                                <th >Remarks</th>
                                <th >Status</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $pet_kw = $_GET['pet_kw'];
                                $pet_species = $_GET['pet_species'];
                                $pet_age = $_GET['pet_age'];
                                $pet_size = $_GET['pet_size'];
                                $pet_owner = $_GET['pet_owner'];

                                $query = "SELECT p.pets_id, p.pet_name, pc.species, pc.size, pc.age, u.name, u.user_id, u.role
                                          FROM pet p INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                                     INNER JOIN pet_user u ON p.owner_id = u.user_id
                                          WHERE p.is_deleted = " . (isset($_GET['show_deleted']) ? "true" : "false");

                                if (trim($pet_kw)) {
                                    $query .= " AND UPPER(p.pet_name) LIKE UPPER('%" . $pet_kw . "%')";
                                }

                                if (trim($pet_owner)) {
                                    $query .= " AND u.user_id = '" . $pet_owner . "'";
                                }

                                if (trim($pet_age)) {
                                    $query .= " AND pc.age = '" . $pet_age . "'";
                                }

                                if (trim($pet_species)) {
                                    $query .= " AND pc.species = '" . $pet_species . "'";
                                }

                                if (trim($pet_size)) {
                                    $query .= " AND pc.size = '" . $pet_size . "'";
                                }
                                $query .= " ORDER BY p.pets_id;";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT r.request_id, u1.user_id, u1.name, u1.role, u2.user_id, u2.name, u2.role,
                                                 r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.slot, r.status,
                                                 p.pets_id, p.pet_name, pc.age, pc.size, pc.species
                                          FROM request r INNER JOIN pet_user u1 ON r.owner_id = u1.user_id
                                                         INNER JOIN pet_user u2 ON r.taker_id = u2.user_id
                                                         INNER JOIN pet p ON r.pets_id = p.pets_id
                                                         INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                          WHERE r.status " . (isset($_GET['show_deleted']) ? "='failed'" : "IN ('pending', 'successful', 'cancelled')") .
                                        " ORDER BY r.request_id;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $req_id = $row[0];
                                $owner_info = $row[2] . "(id: ". $row[1] . ")" . ($row[3] == 'admin' ? '***ADMIN***' : '');
                                $taker_info = $row[5] . "(id: ". $row[4] . ")" . ($row[6] == 'admin' ? '***ADMIN***' : '');
                                $pet_info = $row[15] . "(id: ". $row[14] . ")";
                                $pet_cate = $row[16]." ".$row[17]." ".$row[18];
                                $post_time = $row[7];
                                $begin_time = $row[8];
                                $end_time = $row[9];
                                $bids = $row[10];
                                $remarks = $row[11];
                                $slots = $row[12];
                                $status = $row[13];
                                echo "<tr>";
                                echo "<td >$req_id</td >";
                                echo "<td >$owner_info</td >";
                                echo "<td >$taker_info</td>";
                                echo "<td >$pet_info</td >";
                                echo "<td >$pet_cate</td>";
                                echo "<td >$post_time</td>";
                                echo "<td >$begin_time</td>";
                                echo "<td >$end_time</td>";
                                echo "<td >$bids</td>";
                                echo "<td >$slots</td>";
                                echo "<td >$remarks</td>";
                                echo "<td >" . ($status=="failed" ? "Deleted" : "Active") . "</td >";
                                echo "<td >" .
                                    (!isset($_GET['show_deleted'])
                                        ? "<a class=\"btn btn-default\" role=\"button\" href=\"admin_editreq.php?r_id=$req_id\">Edit</a>
                                               <a class=\"btn btn-danger\" role=\"button\" href=\"admin_delete.php?r_id=$req_id&usage=req\">Delete</a>"
                                        : "<a class=\"btn btn-default\" role=\"button\" href=\"admin_restore.php?r_id=$req_id&usage=req\">Restore</a>") .

                                    "</td>";
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
<div class="content-container container">
    <div class="panel new-task-panel">
        <div class="container ">
            <h2>Summary on Requests</h2>
        </div>
        <div class="table-vertical first-table">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Pet Category</th>
                    <th>Time Period</th>
                    <th>Number of Successful Requests</th>
                    <th>Average bids</th>
                    <th>User Post Most</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query1 = " SELECT k.species, k.timeslot, k.RequestNum, k.average, r1.owner_id 
                        FROM (SELECT c.species AS species, r.slot AS timeslot, COUNT(r.request_id) AS RequestNum, AVG(r.bids) AS average
                              FROM petcategory c, pet p, request r 
                              WHERE r.pets_id = p.pets_id AND c.pcat_id = p.pcat_id AND r.status = 'successful'
                              GROUP BY r.slot, c.species) AS k, request r1, petcategory c1, pet p1
                        WHERE r1.pets_id = p1.pets_id AND c1.pcat_id = p1.pcat_id AND r1.status = 'successful' AND c1.species = k.species AND r1.slot = k.timeslot
                        GROUP BY r1.owner_id, k.species, k.timeslot, k.RequestNum, k.average
                        HAVING COUNT(*) >= ALL(
                                           SELECT COUNT(*)
                                           FROM request r2, petcategory c2, pet p2
                                           WHERE r2.pets_id = p2.pets_id AND c2.pcat_id = p2.pcat_id AND r2.status = 'successful' AND c2.species = k.species AND r2.slot = k.timeslot
                                           GROUP BY r2.owner_id)
                        ORDER BY k.RequestNum DESC;";

                $result1 = pg_query($query1) or die('Query failed: ' . pg_last_error());


                while ($row1 = pg_fetch_row($result1)) {
                    $owner_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = " . $row1[4] . ";"))[0];
                    $average = $row1[3] < 0 ? '' : round(floatval($row1[3]), 2);
                    echo "
                    <tr>
                    <td>$row1[0]</td>
                    <td>$row1[1]</td>
                    <td>$row1[2]</td>
                    <td>$average</td>
                    <td>$owner_name</td>
                    </tr>";
                }

                pg_free_result($result1);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>