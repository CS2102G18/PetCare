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
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
            $('#start-datetimepicker').datetimepicker();
            $('#end-datetimepicker').datetimepicker();
            $('#sb-datetimepicker').datetimepicker();
            $('#se-datetimepicker').datetimepicker();
        });

    </script>

</head>
<body>
<!-- include php -->
<?php include "../config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-admin">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="../admin.php"> PetCare</a></div>
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
            <li>Requests</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-10">
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
                                    <label for="pet_info">Pet</label>
                                    <select name="pet_info" class="form-control">
                                        <option value="">Select Pet</option>
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
                        <div class="col-md-12">
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Request Status</label>
                                <div class="col-sm-6">
                                    <select name="status" class="form-control">
                                        <option value="">Select Status</option>
                                        <?php
                                        $query = "SELECT DISTINCT status FROM request";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[0];
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">

                                <label class="col-sm-3 control-label">Request Time Slot</label>
                                <div class="col-sm-6">
                                    <select name="req_slot" class="form-control">
                                        <option value="">Select Time Slot</option>
                                        <?php
                                        $query = "SELECT DISTINCT slot FROM request";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[0];
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
                                <label class="col-sm-3 control-label">Bid Lower Bound</label>
                                <div class="col-sm-6">
                                    <input id="bid_low" name="bid_low" type="text" class="form-control"
                                           placeholder="Keywords">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label">Bid Upper Bound</label>
                                <div class="col-sm-6">
                                    <input id="bid_upp" name="bid_upp" type="text" class="form-control"
                                           placeholder="Keywords">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <br>
                            <div class="col-sm-6">
                                <div class="container">
                                    <input type="submit" class="btn-primary btn" id="findBtn" name="search"
                                           value="Search">
                                    <a href="admin_req.php" class="btn-default btn">Cancel</a>
                                    <a href="admin_addreq.php" class="btn-success btn">Add New Request</a>
                                    <a href="admin_reqstats.php" class="btn-warning btn">Show statistics</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="overflow: auto;">
                        <br>
                        <table class="table table-striped" id="pet_info">
                            <tr>
                                <th>Request ID</th>
                                <th>Pet Owner</th>
                                <th>Care Giver</th>
                                <th>Pet Name</th>
                                <th>Pet Category</th>
                                <th>Post at</th>
                                <th>Begin at</th>
                                <th>End at</th>
                                <th>Bids</th>
                                <th>Slot</th>
                                <th>Remarks</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $pet_owner = $_GET['pet_owner'];
                                $care_giver = $_GET['care_giver'];
                                $pet_info = $_GET['pet_info'];
                                $post_start = $_GET['post_start'];
                                $post_end = $_GET['post_end'];
                                $slot_start = $_GET['slot_start'];
                                $slot_end = $_GET['slot_end'];
                                $req_status = $_GET['status'];
                                $req_slot = $_GET['req_slot'];
                                $bid_low = $_GET['bid_low'];
                                $bid_upp = $_GET['bid_upp'];

                                $query = "SELECT r.request_id, u1.user_id, u1.name, u1.role, u2.user_id, u2.name, u2.role,
                                                 r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.slot, r.status,
                                                 p.pets_id, p.pet_name, pc.age, pc.size, pc.species
                                          FROM request r INNER JOIN pet_user u1 ON r.owner_id = u1.user_id
                                                         INNER JOIN pet_user u2 ON r.taker_id = u2.user_id
                                                         INNER JOIN pet p ON r.pets_id = p.pets_id
                                                         INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                          WHERE 1=1";

                                if (trim($pet_owner)) {
                                    $query .= " AND u1.user_id = $pet_owner";
                                }

                                if (trim($care_giver)) {
                                    $query .= " AND u2.user_id = $care_giver";
                                }

                                if (trim($pet_info)) {
                                    $query .= " AND p.pets_id = $pet_info";
                                }

                                if (trim($post_start)) {
                                    $query .= " AND r.post_time >= '" . $post_start . "'";
                                }

                                if (trim($post_end)) {
                                    $query .= " AND r.post_time <= '" . $post_end . "'";
                                }

                                if (trim($slot_start)) {
                                    $query .= " AND r.care_begin >= '" . $slot_start . "'";
                                }

                                if (trim($slot_end)) {
                                    $query .= " AND r.care_end <= '" . $slot_end . "'";
                                }

                                if (trim($req_status)) {
                                    $query .= " AND r.status = '" . $req_status . "'";
                                }

                                if (trim($req_slot)) {
                                    $query .= " AND r.slot = '" . $req_slot . "'";
                                }

                                if (trim($bid_low)) {
                                    $query .= " AND r.bids >= $bid_low";
                                }

                                if (trim($bid_upp)) {
                                    $query .= " AND r.bids <= $bid_upp";
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
                                $owner_info = $row[2] . "(id: " . $row[1] . ")" . ($row[3] == 'admin' ? '***ADMIN***' : '');
                                $taker_info = $row[5] . "(id: " . $row[4] . ")" . ($row[6] == 'admin' ? '***ADMIN***' : '');
                                $pet_info = $row[15] . "(id: " . $row[14] . ")";
                                $pet_cate = $row[16] . " " . $row[17] . " " . $row[18];
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
                                echo "<td >$status</td >";
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
</body>