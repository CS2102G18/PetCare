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
    <script src="../vendor/js/find-task.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
            $('#start-datetimepicker').datetimepicker();
            $('#end-datetimepicker').datetimepicker();
        });

    </script>
    <style>
        .navbar-admin {
            color: #FFFFFF;
            background-color: #793585;
        }

    </style>
</head>
<body>
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
    <div class="panel new-task-panel">
        <div class="page-heading">
            <ol class="breadcrumb">
                <li><a href="../admin.php">Admin</a></li>
                <li><a href="admin_req.php">Request</a></li>
                <li>Add New Request</li>
            </ol>
        </div>
        <div class="container">
            <h2>Add new request into the system</h2>
            <form>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Declare requested time</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label">
                                    <h5>Start</h5>
                                </label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="start-datetimepicker">
                                        <input type="text" class="form-control" name="start_time" required="true">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label">
                                    <h5>End</h5>
                                </label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="end-datetimepicker">
                                        <input type="text" class="form-control" name="end_time" required="true">
                                        <div class="input-group-addon">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Declare the user and bids information</h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Care giver</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="care_taker" class="form-control" required="true">
                                <option value="">Select Care Taker</option>
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
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Declare the care giver concerned and the pet categories</h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Pet Concerned</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_concerned" class="form-control" required="true">
                                <option value="">Select Pet</option>
                                <?php
                                $query = "SELECT p.pets_id, p.pet_name, 
                                                 u.user_id, u.role, u.name
                                          FROM pet p INNER JOIN pet_user u ON p.owner_id = u.user_id 
                                          ORDER BY p.pets_id";
                                $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    $option = "<option value='" . $row[0] . "'>" . $row[1] . " (id: " . $row[0] . "), ";
                                    $option .= "owned by " . $row[4] . " (id: " . $row[2] . ($row[3] == "admin" ? ' ADMIN ' : "") . ")";
                                    $option .= "</option><br>";
                                    echo $option;
                                }
                                pg_free_result($result);
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Remarks</h5>
                        </div>
                        <div class="col-sm-8">
                            <textarea name="remarks" class="form-control autosize"
                                      style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 56px;"
                            ></textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Bids</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="bids" type="text" class="form-control"
                                   value="">
                        </div>
                    </div>
                </div>
                <div class="container">
                    <button type="submit" name="create" class="btn btn-default">Submit</button>
                    <a class="btn btn-danger" role="button" href="admin_addreq.php">Cancel</a>
                </div>
            </form>
            <br>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $proposed_start = $_GET['start_time'];
    $proposed_end = $_GET['end_time'];
    $care_taker = $_GET['care_taker'];
    $pet_concerned = $_GET['pet_concerned'];
    $remarks = $_GET['remarks'];
    $bids = $_GET['bids'];

    $check_duplicate_query = "SELECT * 
                              FROM request
                              WHERE care_begin <= '" . $proposed_end . "' ";
    $check_duplicate_query .= " AND care_end >= '" . $proposed_start . "'";
    $check_duplicate_query .= " AND taker_id = " . $care_taker . " AND pets_id = " . $pet_concerned . ";";
    //die($check_duplicate_query);
    $check_duplicate_result = pg_query($check_duplicate_query) or die('Query faileda: ' . pg_last_error());

    $check_available_query = "SELECT a.avail_id 
                          FROM request r INNER JOIN pet p ON r.pets_id = p.pets_id
                                         INNER JOIN availability a ON p.pcat_id = a.pcat_id
                          WHERE a.start_time <= '" . $proposed_start . "'";
    $check_available_query .= " AND a.end_time >= '" . $proposed_end . "'";
    $check_available_query .= " AND a.taker_id = " . $care_taker;
    $check_available_query .= " AND p.pets_id = " . $pet_concerned;
    //die($check_available_query);
    $check_available_result = pg_query($check_available_query) or die('Query failedb: ' . pg_last_error());

    if (pg_numrows($check_duplicate_result) > 0) {
        echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Create Request</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Overlapping request exists!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_addreq.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed: ' . pg_last_error());
    } else if (pg_numrows($check_available_result) == 0) {
        echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Create Request</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>No available slots!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_addreq.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed: ' . pg_last_error());

    } else {
        $owner_query = "SELECT owner_id FROM pet WHERE pets_id = " . $pet_concerned . ";";
        $owner_res = pg_query($owner_query) or die('Query failedc: ' . pg_last_error());
        $owner_id = pg_fetch_row($owner_res)[0];
        //die('ownerownerowner'. $owner_id);

        $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
                         VALUES ($owner_id," . $care_taker . ",'" . $proposed_start . "','" . $proposed_end . "','" . $remarks . "'," . $bids . "," . $pet_concerned . ");";
        $insert_result = pg_query($insert_query) or die('Query failedd: ' . pg_last_error());
        if ($insert_result) {
            echo " 
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'><div class='modal-content'>
                    <div class='modal-header'>
                      <button type='button' class='close' data-dismiss='modal'>&times;</button>
                      <h4 class='modal-title'>Create Availability</h4>
                    </div>
                    <div class='modal-body'>
                      <p>Creation successful!</p>
                    </div>
                    <div class='modal-footer'>
                      <button type='button' class='btn btn-default'><a href='admin_req.php'>Close</a></button>
                    </div>
                </div>
            </div>";
            pg_free_result($result);
            header("Location: admin_avail.php");
        } else {
            echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Create Availability</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Creation failed!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_addreq.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        }
    }
    exit();
}
?>
</body>
</html>
