<?php
// Start the session
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
} else {
    header("Location: ../login.php");
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
    <link rel="stylesheet" href="../vendor/css/bootstrap-datetimepicker.min.css">

    <script src="../vendor/js/jquery-3.2.0.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="../vendor/js/bootstrap-datetimepicker.min.js"></script>
    <script src="../vendor/js/find-task.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
        });
    </script>
</head>
<body>
<?php include "../config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="../userprofile.php"> PetCare</a></div>
        <div class="nav navbar-nav navbar-form">
            <div class="input-icon">
                <i class="glyphicon glyphicon-search search"></i>
                <input type="text" placeholder="Type to search..." class="form-control search-form" tabindex="1">
            </div>
        </div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="request.php"> Send Request </a></li>
                <li><a href="../history.php"> View History </a></li>
                <li><a href="../logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>


<div class="content-container container">
    <div class="container">
        <h2>Choose time slots for your requests</h2>
        <form>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-12">
                        <h4>Choose time slots</h4>
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
                        <h4>Choose your pet to be taken care of</h4>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-2">
                        <h5>Pet to be taken care of</h5>
                    </div>
                    <div class="col-sm-8">
                        <select name="pet_name" class="form-control" required="true">
                            <option value="">Select Pet</option>
                            <?php
                            $query = "SELECT pet_name FROM pet WHERE owner_id = $user_id";
                            $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                            while ($row = pg_fetch_row($result)) {
                                echo "<option value='" . $row[0] . "'>" . $row[0] . "</option><br>";
                            }
                            pg_free_result($result);
                            ?>
                        </select>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-sm-2">
                        <h5>End your remarks for the care taker</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="remarks" class="form-control" required="true">
                        </input>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-2">
                        <h5>End your bids</h5>
                    </div>
                    <div class="col-sm-8">
                        <input type="number" name="bids" min = "1" class="form-control" required="true">
                        </input>
                    </div>
                </div>
                <br>
            </div>
            <br>
            <div class="container">
                <button type="submit" name="find" class="btn btn-default">Send Request</button>
            </div>
        </form>
    </div>
</div>



<?php
if (isset($_GET['find'])) { // send requests to all care takers who are available
    $start_time = $_GET['start_time'];
    $end_time = $_GET['end_time'];
    $pet_name = $_GET['pet_name'];
    $remarks = $_GET['remarks'];
    $bids = $_GET['bids'];
    $pid_query = "SELECT pets_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
    $pid_result = pg_query($pid_query) or die('Query failed: ' . pg_last_error());
    $pet_id = pg_fetch_row($pid_result)[0];
    $pcat_query = "SELECT pcat_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
    $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];
    $avail_query = "SELECT * FROM availability
                    WHERE pcat_id = $pcat_id
                    AND start_time <= '$start_time'
                    AND end_time >= '$end_time'
                    AND is_deleted = false
                    AND taker_id <> '$user_id'";
    $avail_result = pg_query($avail_query) or die('Query failed: ' . pg_last_error());
    while ($row = pg_fetch_row($avail_result)) {
        $avail_id = $row[0];
        $start_avail_time = $row[2];
        $end_avail_time = $row[3];
        $taker_id = $row[5];
        $taker_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = $taker_id;"))[0];
        $request_pet_name = pg_fetch_row(pg_query("SELECT pet_name FROM pet WHERE pets_id = " . $row[8] . ";"))[0];
        $status = $row[9];
        $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
                     VALUES ($user_id, $taker_id, '$start_time', '$end_time', '$remarks','$bids',$pet_id);";
        $result = pg_query($insert_query);
        print $insert_query;
        echo "<div class=\"container\">
                <h4>Available care takers</h4>
                </div>
                
                <table class=\"table table-striped\">
                <tr>
                <th>Taker Name</th>
                <th>Availability Start Time</th>
                <th>Availability End Time</th>
                <th>Request Status</th>
                </tr>";
        echo "<tr>";
        echo "<td >$taker_name</td >";
        echo "<td >$start_avail_time</td >";
        echo "<td >$end_avail_time</td >";
        echo "<td >
                Sent, Pending
              </td >";
        echo "</tr>";
        echo "</table>";
    }
//    $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
//                     VALUES ($user_id, null, '$start_time', '$end_time', '$remarks','$bids',$pet_id);";
//    $result = pg_query($insert_query);
//    print $insert_query;
//    if (!$result) {
//        echo "
//            <div id='successmodal' class='modal fade'>
//                <div class='modal-dialog'>
//                    <div class='modal-content'>
//                        <div class='modal-header'>
//                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
//                          <h4 class='modal-title'>Sorry</h4>
//                        </div>
//                        <div class='modal-body'>
//                          <h4>No available care takers. Please try again.</h4>
//                        </div>
//                        <div class='modal-footer'>
//                          <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
//                        </div>
//                    </div>
//                </div>
//            </div>";
//        die('Query failed: ' . pg_last_error());
//    }
    exit();
}
?>


</body>
</html>

