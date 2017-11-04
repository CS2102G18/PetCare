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
$upperbound = $_GET['upperbound'];
$lowerbound = $_GET['lowerbound'];
?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-owner">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
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

    <div class="panel new-task-panel">

        <div class="container">
            <form name="frm" onchange="checkComplete()">

                <script>
                    var complete = false;
                    function checkComplete() {
                        var btn = document.getElementById("send_btn");
                        if(document.frm.start_time.value == "" || document.frm.end_time.value == "" ||
                            document.frm.pet_name.value == "" || document.frm.remarks.value == "" ||
                            document.frm.bids.value == "") {
                            complete = false;
                            btn.style.color = 'darkred';
                            btn.style.backgroundColor = 'lightgray';
                            btn.type = "button";
                        }else{
                            complete = true;
                            btn.style.color = 'blue';
                            btn.style.backgroundColor = 'white';
                            btn.type = "submit";
                        }
                    }
                </script>


                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Choose time slot</h4>
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
                            <h5>Pet to be care for</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_name" class="form-control">
                                <option value="">Select Pet</option>
                                <?php
                                $query = "SELECT pet_name FROM pet WHERE owner_id = $user_id AND is_deleted = false";
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
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Remarks </h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="remarks" class="form-control" value = '<?php echo $remarks;?>' >
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Consider New Takers? </h5>
                        </div>
                        <div class="col-sm-8">
                            <label class="radio-inline"><input type="radio" name="newtakers" value=0>Yes</label>
                            <label class="radio-inline"><input type="radio" name="newtakers" value=1>No</label>
                        </div>
                    </div>
                    <br>


                    <div class="row">
                        <div class="col-lg-2">
                            <h5>Average Bids/Hour</h5>
                        </div>

                        <div class="col-lg-3">
                            <div class="input-group">
                                <span class="input-group-addon">LOWER THAN</span>
                                <span class="input-group-addon">$</span>
                                <input type="number" name="upperbound" class="form-control"  value = '<?php echo $upperbound;?>' >
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <span class="input-group-addon">HIGHER THAN</span>
                                <span class="input-group-addon">$</span>
                                <input type="number" name="lowerbound" class="form-control"  value = '<?php echo $lowerbound;?>' >
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Bids</h5>
                        </div>
                        <div class="col-sm-8">
                            <input type="number" name="bids" min = "1" class="form-control"  value = '<?php echo $bids;?>' >
                        </div>
                    </div>
                    <br>

                    <div class="row"  style="display:block; text-align:center; padding-left: 0 " >

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
            $newtaker = $_GET['newtakers'];
            $upperbound = $_GET['upperbound'];
            $lowerbound = $_GET['lowerbound'];

            $complete = true;
            $pid_query = "SELECT pets_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name' AND is_deleted = false";
            $pid_result = pg_query($pid_query) or die('Query failed: ' . pg_last_error());
            $pet_id = pg_fetch_row($pid_result)[0];
            $pcat_query = "SELECT pcat_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name' AND is_deleted = false";
            $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
            $pcat_id = pg_fetch_row($pcat_result)[0];
            $avail_query = "SELECT a.avail_id, a.start_time, a.end_time, a.taker_id, p.name,
                            (CASE WHEN t.avgbids is NULL THEN 0 ELSE t.avgbids END) AS avgbids, a.remarks
                            FROM (availability a INNER JOIN pet_user p ON p.user_id = a.taker_id AND a.is_deleted = FALSE AND p.is_deleted = FALSE) 
                                  LEFT JOIN requesttime AS t ON a.taker_id = t.taker_id 
                            WHERE a.taker_id <> '$user_id'";
            if(trim($pet_name)) {
                $pid_query = "SELECT pets_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name' AND is_deleted = false";
                $pid_result = pg_query($pid_query) or die('Query failed: ' . pg_last_error());
                $pet_id = pg_fetch_row($pid_result)[0];
                $pcat_query = "SELECT pcat_id FROM pet WHERE owner_id = $user_id AND pet_name = '$pet_name' AND is_deleted = false";
                $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
                $pcat_id = pg_fetch_row($pcat_result)[0];
                $avail_query .= " AND a.pcat_id = $pcat_id ";

            }else
                $complete = false;
            if(trim($start_time)) {
                $avail_query .= " AND a.start_time <= '$start_time' ";
            }else
                $complete = false;
            if(trim($end_time)) {
                $avail_query .= " AND a.end_time >= '$end_time' ";
            }else
                $complete = false;
            if($complete){
                $avail_query .= " AND a.taker_id NOT IN (SELECT r.taker_id FROM request r WHERE r.care_end > '$start_time' AND r.care_begin < '$end_time' AND r.pets_id = $pet_id AND r.status='pending') ";
            }
            if(!trim($bids))
                $complete = false;
            if(trim($taker_name)) {
                $avail_query .= " AND UPPER(p.name) LIKE UPPER('%$taker_name%') ";
            }
            if($upperbound){
                $avail_query .= " AND (t.avgbids <= $upperbound OR t.avgbids is NULL) ";
            }
            if($lowerbound){
                $avail_query .= " AND (t.avgbids >= $lowerbound OR t.avgbids is NULL) ";
            }
            if($newtaker){
                $avail_query .= " AND t.avgbids is NOT NULL ";
            }
            $avail_query .= "ORDER BY avgbids ASC";
            $avail_result = pg_query($avail_query) or die('Query failed: ' . pg_last_error());
            //print $avail_query;
            echo "<div class=\"container\">
                <h4>Available care takers</h4>
                </div>";
            echo "
    
    
    <table class=\"table table - striped\" >
                <tr>
                <th>Taker Name</th>
                <th>Availability Start Time</th>
                <th>Availability End Time</th>
                <th>
                
                
                <select id = \"avgbidselect\" name=\"AvgBid\" onchange=\"bid_function()\" class=\"table table-striped\" style=''>
                            <option value=\"hour\">Average Bids/Hour</option>
                            <option value=\"day\">Average Bids/Day</option>
                            <option value=\"min\">Average Bids/Min</option>
                </select>
                
                                                              
                </th>
                <th>Remarks</th>
                <th>Your Bids</th>
                <th>Send Request</th>
                
                
                <script>
                    function bid_function() {
                        
                        var select = document.getElementById(\"avgbidselect\");
                        var result = document.getElementsByName(\"avgbidhourresult\");
                        
                        for (var i = 0, max=result.length; i < max; i++) {
                                
                                var bid_hour = result[i].value;
                                
                                if (bid_hour == 'N/A, no request yet')
                                    continue;
                                
                                var change_id = 'avgbidresult' + i;
                                
                                var tochange = document.getElementById(change_id);
                                
                                if(select.value == \"day\") 
                                    tochange.innerHTML = (parseFloat(bid_hour) * 24).toFixed(2).toString();
                                
                                if(select.value == \"min\") 
                                    tochange.innerHTML = (parseFloat(bid_hour) / 60).toFixed(2).toString();
                                
                                if(select.value == \"hour\") 
                                    tochange.innerHTML = bid_hour;
                                
                         }       
                                
                    }    
                        
                </script>
    
    ";
            $count = 0;
            while ($row = pg_fetch_row($avail_result)) {
                $avail_id = $row[0];
                $start_avail_time = $row[1];
                $end_avail_time = $row[2];
                $taker_id = $row[3];
                $taker_name = $row[4];
                $avg = $row[5];
                $avg_bids = number_format((float)$avg/1, 2, '.', '');
                if($avg_bids == 0.00)
                    $avg_bids = 'N/A, no request yet';
                echo "
                </tr>";
                echo "<tr>";
                echo "<td >$taker_name</td >";
                echo "<td >$start_avail_time</td >";
                echo "<td >$end_avail_time</td >";
                echo" <input type = 'hidden' name = 'avgbidhourresult' value = '$avg_bids' > ";
                $td_name = 'avgbidresult' . $count;
                $bid_id = 'bid' . $count;
                echo "<td id=$td_name>$avg_bids</td >";
                echo "<td > $remarks </td>";
                echo "
            <form method = 'get' class='form-inline'  >
              <td>
              
                
                <input type='number' id = $bid_id name='bids_updated' min = '1' onchange = 'sendBtn($count, this.value)' value = $bids >
                
                <script>
                
                    function sendBtn(count, val) {
                        var id = 'sendbtn' + count;
                        var send_btn = document.getElementById(id);
                                                
                        if('$start_time' == \"\" || '$end_time' == \"\" 
                        || '$pet_name' == \"\" || '$remarks' == \"\" 
                        || val == '') {
                            send_btn.style.color = 'darkred';
                            send_btn.style.backgroundColor = 'gainsboro';
                            send_btn.type = \"button\";
                        }else{
                            send_btn.style.color = 'blue';
                            send_btn.style.backgroundColor = 'white';
                            send_btn.type = \"submit\";
                        }
                    }
                
                
                </script>
                
                                                                            
              </td>
                                                  
              </div>                       
              <td >                
                <div class='form-group' style='float: left;'>
                <input type='hidden' name='taker_id' value=$taker_id>
                <input type='hidden' name='user_id' value=$user_id>
                <input type='hidden' name='start_time' value='$start_time'>
                <input type='hidden' name='end_time' value='$end_time'>
                <input type='hidden' name='pet_id' value=$pet_id>
                <input type='hidden' name='remarks' value='$remarks'>
                <input type='hidden' name='pet_name' value='$pet_name'>
                <input type='hidden' name='pcat_id' value=$pcat_id>  
                
                ";
                $td_name = 'sendbtn' . $count;
                if($complete) {
                    echo "  
                        <input type='submit' id = $td_name class='form-control' name = 'send_req' value='Send'>
                ";
                }else {
                    echo "
                          <input type='button' id = $td_name class='form-control' name = 'send_req' value='Send' style = 'color: darkred ; background-color: gainsboro'>                                               
                          </td >
                          </form>
                          ";
                }
                $count = $count + 1;
            }
            echo "</tr> ";
            echo "</table>";
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
            $bids = $_GET["bids_updated"];
            $remarks = $_GET["remarks"];
            $pet_name = $_GET["pet_name"];
            $check_query = "SELECT * FROM request r WHERE r.care_begin <'$end_time' AND r.care_end > '$start_time' AND r.pets_id = $pet_id AND r.status = 'successful';";
            $check_result = pg_query($insert_query);
            if (pg_fetch_row($check_result)){
                echo"<div class=\"container\"  style=\"text-align:center\">
                       <p style=\"color:red;\" >Your pet has already been taken care of during the period!</p>
                     </div>";
            }
            else {
                $insert_query = "INSERT INTO request(owner_id, taker_id, care_begin, care_end, remarks, bids, pets_id)
                     VALUES ($user_id, $taker_id, '$start_time', '$end_time', '$remarks', $bids, $pet_id);";
                print $insert_query;
                print 'Bid value is: ' . $bids;
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
                    <input type='hidden' name='remarks' value='$remarks'>
                    <input type='hidden' name='pet_name' value='$pet_name'>
                    </div>
                    
               
                </form>
            </div>
            
            ";
            }
        }
        ?>


    </div>
</div>
</body>
</html>
