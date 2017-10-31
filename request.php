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
    <script src="./vendor/js/find-task.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
        });
    </script>
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
<?php include "config/db-connection.php";

$start_time = '';
$end_time = '';
$pet_name = '';
$remarks = '';
$bids = 1;

$start_time = $_GET['start_time'];
$end_time = $_GET['end_time'];
$pet_name = $_GET['pet_name'];
$remarks = $_GET['remarks'];
$bids = $_GET['bids'];

?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-owner">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
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

    <div class="panel new-task-panel">

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
                                    <input type="text" class="form-control" name="start_time"  value = '<?php echo $start_time;?>' required="true">
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
                                    <input type="text" class="form-control" name="end_time" value = '<?php echo $end_time;?>' required="true">
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
                        <h5>Your remarks for the care taker</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="remarks" class="form-control" value = '<?php echo $remarks;?>' required="true">
                        </input>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-2">
                        <h5>Your bids</h5>
                    </div>
                    <div class="col-sm-8">
                        <input type="number" name="bids" min = "1" class="form-control"  value = '<?php echo $bids;?>' required="true">
                        </input>
                    </div>
                </div>
                <br>
            </div>
            <br>
            <div class="container">
                <button type="submit" name="find" class="btn btn-default">Find takers</button>
            </div>
            <br>
        </form>
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

    echo "<div class=\"container\">
                <h4>Available care takers</h4>
                </div>";

    while ($row = pg_fetch_row($avail_result)) {


        $avail_id = $row[0];
        $start_avail_time = $row[2];
        $end_avail_time = $row[3];
        $taker_id = $row[5];
        $taker_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = $taker_id;"))[0];
        $request_pet_name = pg_fetch_row(pg_query("SELECT pet_name FROM pet WHERE pets_id = " . $row[8] . ";"))[0];
        $status = $row[9];

        $bids_query = "SELECT AVG(bids) FROM request WHERE taker_id = '$taker_id'";
        $bids_result = pg_query($bids_query) or die('Query failed: ' . pg_last_error());
        $avg_bids = pg_fetch_row($bids_result)[0];
        $avg_bids = number_format((float)$avg_bids, 2, '.', '');

        echo "
                <table class=\"table table-striped\">
                <tr>
                <th>Taker Name</th>
                <th>Availability Start Time</th>
                <th>Availability End Time</th>
                <th>Average Bids</th>
                <th>Your Bids</th>
                <th>Send Request</th>
               
                
                </tr>";
        echo "<tr>";
        echo "<td >$taker_name</td >";
        echo "<td >$start_avail_time</td >";
        echo "<td >$end_avail_time</td >";
        echo "<td >$avg_bids</td >";
        echo "<td>
                <input type='number' name='bids' min = '1' value=$bids>                                                            
              </td>                                    
              </div>
              </form>
              ";

        echo "<form method = 'get' class='form-inline' >
                    
              <td >
                
                <div class='form-group' style='float: left;'>
                <input type='submit' class='form-control' name = 'send_req' value='Send'>                    
                <input type='hidden' name='taker_id' value=$taker_id>
                <input type='hidden' name='user_id' value=$user_id>
                <input type='hidden' name='start_time' value='$start_time'>
                <input type='hidden' name='end_time' value='$end_time'>
                <input type='hidden' name='pet_id' value=$pet_id>
                <input type='hidden' name='bids' value=$bids>
                <input type='hidden' name='remarks' value='$remarks'>
                <input type='hidden' name='pet_name' value='$pet_name'>
                <input type='hidden' name='pcat_id' value=$pcat_id>
                    
              </td >";


        echo "</tr>";
        echo "</table>";

    }

    exit();
}
?>
        <?php

        if (isset($_GET["send_req"])) {
            $taker_id = $_GET["taker_id"];
            $user_id = $_GET["user_id"];
            $start_time = $_GET["start_time"];
            $end_time = $_GET["end_time"];
            $pet_id = $_GET["pet_id"];
            $bids = $_GET["bids"];
            $remarks = $_GET["remarks"];
            $pet_name = $_GET["pet_name"];

            $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
                     VALUES ($user_id, $taker_id, '$start_time', '$end_time', '$remarks', $bids, $pet_id);";
            //print $insert_query;
            $result = pg_query($insert_query) or die('Query failed: ' . pg_last_error());
            pg_free_result($result);

            echo "
            <br>
            <br>
            <div class=\"container\">
            <form method = 'get' class='form-inline' >
                    <div class='form-group' style='float: top;'>
                    <input type='submit' class='form-control' name = 'find' value='Send to another taker'>                    
                    <input type='hidden' name='taker_id' value=$taker_id>
                    <input type='hidden' name='user_id' value=$user_id>
                    <input type='hidden' name='start_time' value='$start_time'>
                    <input type='hidden' name='end_time' value='$end_time'>
                    <input type='hidden' name='pet_id' value=$pet_id>
                    <input type='hidden' min = '1' name='bids' value=$bids>
                    <input type='hidden' name='pet_id' value=$pet_id>
                    <input type='hidden' name='remarks' value='$remarks'>
                    <input type='hidden' name='pet_name' value='$pet_name'>
                    </div>
                    
               
                </form>
            </div>
            
            ";


        }

        ?>


    </div>
</div>
</body>
</html>

