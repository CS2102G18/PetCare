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

    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="./vendor/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
            $('#start-datetimepicker').datetimepicker();
            $('#end-datetimepicker').datetimepicker();
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

        #avgbidselect {
            all: inherit;

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
$taker_name = $_GET['taker_name'];
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
        <form name="frm" onchange="checkComplete()">

            <script>
                var complete = false;
                function checkComplete() {
                    if(document.frm.start_time.value == "" || document.frm.end_time.value == "" ||
                        document.frm.pet_name.value == "" || document.frm.remarks.value == "" ||
                        document.frm.bids.value == "") {
                        complete = false;
                        var btn = document.getElementById("send_btn");
                        btn.style.color = 'darkred';
                        btn.style.backgroundColor = 'lightgray';
                        btn.type = "button";
                    }else{
                        complete = true;
                        var btn = document.getElementById("send_btn");
                        btn.style.color = 'blue';
                        btn.style.backgroundColor = 'white';
                        btn.type = "submit";
                    }
                }


            </script>





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
                                    <input type="text" class="form-control" name="start_time"  value = '<?php echo $start_time;?>' >
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
                                    <input type="text" class="form-control" name="end_time" value = '<?php echo $end_time;?>' >
                                    <div class="input-group-addon">
                                        <i class="glyphicon glyphicon-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2">
                        <h5>Pet to be taken care of</h5>
                    </div>
                    <div class="col-sm-8">
                        <select name="pet_name" class="form-control">
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
                        <h5>Preferred Taker Name</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="taker_name" class="form-control" value = '<?php echo $taker_name;?>' >
                        </input>
                    </div>
                </div>
                <br>

                <div class="row">
                    <div class="col-sm-2">
                        <h5>Remarks </h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="remarks" class="form-control" value = '<?php echo $remarks;?>' >
                        </input>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-sm-2">
                        <h5>Bids</h5>
                    </div>
                    <div class="col-sm-8">
                        <input type="number" name="bids" min = "1" class="form-control"  value = '<?php echo $bids;?>' >
                        </input>
                    </div>
                </div>
                <br>




                <div class="row"  style="display:block; text-align:center; padding-left: 0px " >

                    <button type="submit" name="find" class="btn btn-default">Find Takers</button>

                    <button type="button" id="send_btn" name="find" class="btn btn-default" style="color:darkred; margin-left:50px; background-color: lightgray ">Send Request</button>
                </div>
            </div>
            <br>
        </form>
    </div>




<?php
if (isset($_GET['find'])) {
    $start_time = $_GET['start_time'];
    $end_time = $_GET['end_time'];
    $pet_id = $_GET['pet_id'];
    $pet_name = $_GET['pet_name'];
    $remarks = $_GET['remarks'];
    $bids = $_GET['bids'];
    $pcat_id = $_GET['pcat_id'];
    $taker_name = $_GET['taker_name'];

    $complete = true;

    $pid_query = "SELECT pets_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
    $pid_result = pg_query($pid_query) or die('Query failed: ' . pg_last_error());
    $pet_id = pg_fetch_row($pid_result)[0];
    $pcat_query = "SELECT pcat_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
    $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];
    $avail_query = "SELECT *
                    FROM availability a, pet_user p
                    WHERE is_deleted = false
                    AND p.user_id = a.taker_id
                    AND taker_id <> '$user_id'";


    if(trim($pet_name)) {
        $pid_query = "SELECT pets_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
        $pid_result = pg_query($pid_query) or die('Query failed: ' . pg_last_error());
        $pet_id = pg_fetch_row($pid_result)[0];
        $pcat_query = "SELECT pcat_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name'";
        $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
        $pcat_id = pg_fetch_row($pcat_result)[0];

        $avail_query .= " AND pcat_id = $pcat_id ";
    }else
        $complete = false;

    if(trim($start_time)) {
        $avail_query .= " AND start_time <= '$start_time' ";
    }else
        $complete = false;

    if(trim($end_time)) {
        $avail_query .= " AND end_time >= '$end_time' ";
    }else
        $complete = false;

    if(trim($taker_name)) {
        $avail_query .= " AND UPPER(p.name) LIKE UPPER('%$taker_name%') ";
    }

    $avail_result = pg_query($avail_query) or die('Query failed: ' . pg_last_error());
    //print $avail_query;

    echo "<div class=\"container\">
                <h4>Available care takers</h4>
                </div>";

    while ($row = pg_fetch_row($avail_result)) {


        $avail_id = $row[0];
        $start_avail_time = $row[2];
        $end_avail_time = $row[3];
        $taker_id = $row[5];
        $taker_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = $taker_id;"))[0];

        $bids_query = "SELECT SUM(bids) FROM request WHERE taker_id = '$taker_id'";
        $bids_result = pg_query($bids_query) or die('Query failed: ' . pg_last_error());
        $avg_bids = pg_fetch_row($bids_result)[0];


        $hour_query = "SELECT SUM(mins)/60 AS totalhours FROM requesttime WHERE taker_id = '$taker_id' ";
        $hour_result = pg_query($hour_query) or die('Query failed: ' . pg_last_error());
        $hour = pg_fetch_row($hour_result)[0];

        $avg_bids = number_format((float)$avg_bids / $hour, 2, '.', '');
        if($avg_bids == 'nan')
            $avg_bids = 'N/A, no request yet';

        echo "
                <table class=\"table table-striped\" >
                <tr>
                <th>Taker Name</th>
                <th>Availability Start Time</th>
                <th>Availability End Time</th>
                <th>
                
                <script>
                    function avgbidfn() {
                        var select = document.getElementById(\"avgbidselect\");
                        var result = '$avg_bids';
                        if (result == 'N/A, no request yet')
                            return;
                        if(select.value == \"day\") {
                            document.getElementById(\"avgbidresult\").innerHTML = (parseFloat(result) * 24).toString();
                        }
                        if(select.value == \"min\") {
                            document.getElementById(\"avgbidresult\").innerHTML = (parseFloat(result) / 60).toFixed(2).toString();
                        }
                        if(select.value == \"hour\") {
                            document.getElementById(\"avgbidresult\").innerHTML = '$avg_bids';
                        }
                    }
                </script>
                
                
                <select id = \"avgbidselect\" name=\"AvgBid\" onchange=\"avgbidfn()\" class=\"table table-striped\" style=''>
                            <option value=\"hour\">Average Bids/Hour</option>
                            <option value=\"day\">Average Bids/Day</option>
                            <option value=\"min\">Average Bids/Min</option>
                </select>
                
                                                              
                </th>
                <th>Your Bids</th>
                <th>Send Request</th>
                
                </tr>";
        echo "<tr>";
        echo "<td >$taker_name</td >";
        echo "<td >$start_avail_time</td >";
        echo "<td >$end_avail_time</td >";
        echo "<td id='avgbidresult'>$avg_bids</td >";
        echo "
            <form method = 'get' class='form-inline' >
              <td>
                <input type='number' name='bids' min = '1' value=$bids>                                                            
              </td>
                                                  
              </div>                       
              <td >                
                <div class='form-group' style='float: left;'>";

        if($complete) {
            echo "  
                <input type='submit' class='form-control' name = 'send_req' value='Send'>
                <input type='hidden' name='taker_id' value=$taker_id>
                <input type='hidden' name='user_id' value=$user_id>
                <input type='hidden' name='start_time' value='$start_time'>
                <input type='hidden' name='end_time' value='$end_time'>
                <input type='hidden' name='pet_id' value=$pet_id>
                <input type='hidden' name='remarks' value='$remarks'>
                <input type='hidden' name='pet_name' value='$pet_name'>
                <input type='hidden' name='pcat_id' value=$pcat_id>    
                ";
        }else {
            echo "
                Incomplete Info, Unable to Send                                              
              </td >
              </form>
              ";
        }

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
            
            <div class=\"container\"  style=\"text-align:center\">
            <form method = 'get' class='form-inline' >
                    <div class='form-group' style='float: top;'>
                    <p style=\"color:green;\" >Sent successfully!</p>
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

