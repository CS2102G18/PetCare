<?php include "../config/db-connection.php"; ?>
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

if (isset($_GET["r_id"])) {
    $req_id = (int)$_GET["r_id"];
    $query = "SELECT r.care_begin, r.care_end, u1.name, u1.user_id, u1.role,
                     r.remarks, r.bids, p.pet_name, p.pets_id, u2.name, u2.role
              FROM request r INNER JOIN pet p ON r.pets_id = p.pets_id
                             INNER JOIN pet_user u1 ON r.taker_id = u1.user_id
                             INNER JOIN pet_user u2 ON p.owner_id = u2.user_id
              WHERE r.request_id = $req_id;";
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($result);

    $r_start = $row[0];
    $r_end = $row[1];
    $r_care_id = $row[3];
    $r_care_info = $row[2] . " (id: " . $row[3] . ($row[4] == 'admin' ? " ADMIN" : "") . ")";

    $r_remark = $row[5];
    $r_bids = $row[6];

    $r_pid = $row[8];
    $r_pinfo = $row[7] . " (id: " . $row[8] . ", owned by " . $row[9] . ($row[10] == 'admin' ? " ADMIN" : "") . ")";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PetCare</title>
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/css/style.css">
    <link rel="stylesheet" href="../vendor/css/bootstrap-datetimepicker.min.css">

    <script src="../vendor/js/jquery-3.2.0.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="../vendor/js/bootstrap-datetimepicker.min.js"></script>
    <style>
        .navbar-admin {
            color: #FFFFFF;
            background-color: #793585;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
            $('#start-datetimepicker').datetimepicker();
            $('#end-datetimepicker').datetimepicker();
        });

    </script>
</head>
<body>
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
                <li>Update Request</li>
            </ol>
        </div>
        <div class="container">
            <h2>Update existing request</h2>
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
                                        <input type="text" class="form-control" name="start_time"
                                               placeholder="<?php echo $r_start ?>"
                                               value="<?php echo $r_start ?>">
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
                                        <input type="text" class="form-control" name="end_time"
                                               placeholder="<?php echo $r_end ?>"
                                               value="<?php echo $r_end ?>">
                                        <input name="r_id" value="<?php echo $req_id ?>" type='hidden'/>
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
                            <select name="care_taker" class="form-control">
                                <option value="<?php echo $r_care_id?>"><?php echo $r_care_info ?></option>
                                <?php
                                $query = "SELECT user_id, name, role FROM pet_user WHERE user_id <> ".$r_care_id. " ORDER BY user_id";
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
                            <select name="pet_concerned" class="form-control">
                                <option value="<?php echo $r_pid ?>"><?php echo $r_pinfo ?></option>
                                <?php
                                $query = "SELECT p.pets_id, p.pet_name, 
                                                 u.user_id, u.role, u.name
                                          FROM pet p INNER JOIN pet_user u ON p.owner_id = u.user_id 
                                          WHERE p.pets_id <> ".$r_pid."
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
                            <textarea name="remarks" class="form-control autosize" placeholder="<?php echo $r_remark ?>"
                                      value = "<?php echo $r_remark ?>"
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
                            <input name="bids" type="text" class="form-control" placeholder="<?php echo $r_bids ?>"
                                   value="<?php echo $r_bids ?>">
                        </div>
                    </div>
                </div>
                <div class="container">
                    <button type="submit" name="update" class="btn btn-default">Submit</button>
                    <a class="btn btn-danger" role="button" href="admin_addreq.php">Cancel</a>
                </div>
            </form>
            <br>
        </div>
    </div>
</div>
<?php
if (isset($_GET['update'])) {
    $req_id = $_GET['r_id'];
    $proposed_start = $_GET['start_time'];
    $proposed_end = $_GET['end_time'];
    $care_taker = $_GET['care_taker'];
    $pet_concerned = $_GET['pet_concerned'];
    $remarks = $_GET['remarks'];
    $bids = $_GET['bids'];

    $check_duplicate_query = "SELECT request_id 
                              FROM request
                              WHERE request_id <> ".$req_id.
                            " AND care_begin <= '" . $proposed_end . "' ";
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
                          <h4 class='modal-title'>Update Request</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Overlapping request exists!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_req.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed c: ' . pg_last_error());
    } else if (pg_numrows($check_available_result) == 0) {
        echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Update Request</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>No available slots!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_req.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed d: ' . pg_last_error());

    } else {
        $owner_query = "SELECT owner_id FROM pet WHERE pets_id = " . $pet_concerned . ";";
        $owner_res = pg_query($owner_query) or die('Query failedc: ' . pg_last_error());
        $owner_id = pg_fetch_row($owner_res)[0];
        //die('ownerownerowner'. $owner_id);

        $insert_query = "UPDATE request
                         SET owner_id = $owner_id, 
                             taker_id = " . $care_taker . ", 
                             care_begin = '" . $proposed_start . "',
                             care_end = '" . $proposed_end . "',
                             remarks = '" . $remarks . "',
                             bids = " . $bids . ",
                             pets_id = " . $pet_concerned . "
                         WHERE request_id = ".$req_id.";";
        $insert_result = pg_query($insert_query) or die('Query failedd: ' . pg_last_error());
        if ($insert_result) {
            echo " 
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'><div class='modal-content'>
                    <div class='modal-header'>
                      <button type='button' class='close' data-dismiss='modal'>&times;</button>
                      <h4 class='modal-title'>Update Availability</h4>
                    </div>
                    <div class='modal-body'>
                      <p>Update successful!</p>
                    </div>
                    <div class='modal-footer'>
                      <button type='button' class='btn btn-default'><a href='admin_req.php'>Close</a></button>
                    </div>
                </div>
            </div>";
            pg_free_result($result);
            header("Location: admin_req.php");
            echo "<script>window.location = 'admin_req.php';</script>";
        } else {
            echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Update Availability</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Update failed!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_req.php'>Close</a></button>
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
