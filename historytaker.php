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
            background-color: #035f72;
        }

        body {
            background: url('./media/background_taker.png');
        }
        .box {
            /* Add shadows to create the "card" effect */
            width: 50rem; margin-top: 2rem; margin-left:2rem; margin-bottom: 2rem; position:relative;display:-ms-flexbox;display:flex;-ms-flex-direction:column;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid rgba(0,1,0,.125);border-radius:.55rem
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
<?php include "config/db-connection.php"; ?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-owner">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="owner.php"> As a Pet Owner </a></li>
                <li><a href="taker.php"> As a Care Taker </a></li>
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
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="taker.php">Home</a></li>
            <li><a href="history.php">History (Owner)</a></li>
            <li>View Request History (Taker)</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <?php
            $query = "SELECT u.name, COUNT(*), (SUM(r.bids)/SUM(r.totaltime))*60 AS avg FROM request r, pet_user u
                  WHERE r.taker_id = $user_id AND r.status = 'successful' AND u.user_id = r.owner_id
                  GROUP BY r.owner_id, u.name
                  HAVING COUNT(*) >= ALL(SELECT COUNT(*) FROM
                                         request r1
                                         WHERE r1.taker_id = $user_id AND r1.status = 'successful'
                                         GROUP BY r1.owner_id)
                  ORDER BY (SUM(r.bids)/SUM(r.totaltime)) DESC;";
            $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
            $row = pg_fetch_row($result);
            $favoritename = $row[0];
            $favoritetime = $row[1];
            $favoriteavg = $row[2];
            ?>

            <div class="box">
                <div class="container">
                    <h4><strong>Favorite Pet Owner:</strong> <?php echo"$favoritename"; ?></h4>
                    <h4><strong>Number of successful Requests:</strong> <?php echo"$favoritetime"; ?></h4>
                    <h4><strong>Average Bids/Hour the Owner provided:</strong> <?php echo"$favoriteavg"; ?></h4>
                </div>
            </div>
            <form action="" id="findForm">
                <div class="row">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-sm-10">
                                <div class="col-sm-3">
                                    <label for="pet_id">Pet's Name</label>
                                    <select name="pet_id" class="form-control">
                                        <option value="">Select Pet</option>
                                        <?php
                                        $query = "SELECT p.pets_id, p.pet_name FROM request r, pet p
                                              WHERE r.pets_id = p.pets_id AND r.taker_id = $user_id;";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[1];
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label for="owner_id">Pet Owner</label>
                                    <select name="owner_id" class="form-control">
                                        <option value="">Select Pet Owner</option>
                                        <?php
                                        $query = "SELECT DISTINCT o.user_id, o.name FROM pet_user o, request r
                                          WHERE o.user_id = r.owner_id AND r.taker_id = $user_id";
                                        $result = pg_query($query) or die('Query failed: ' . $query . pg_last_error());
                                        while ($row = pg_fetch_row($result)) {
                                            $option = "<option value='" . $row[0] . "'>" . $row[1];
                                            $option .= "</option><br>";
                                            echo $option;
                                        }
                                        pg_free_result($result);
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label for="status">Request Status</label>
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
                                <div class="col-sm-3">
                                    <label for="req_slot">Request Time Slot</label>
                                    <select name="req_slot" class="form-control">
                                        <option value="">Select Slot</option>
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
                            <div class="col-sm-6">
                                <br>
                                <input type="submit" class="btn-primary btn" id="findBtn" name="search" value="Search">
                                <a href="history.php" class="btn-default btn">Cancel</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <br>
                        <table class="table table-striped" id="pet_info">
                            <tr>
                                <th >Pet </th>
                                <th >Owner </th>
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
                                $owner_id = $_GET['owner_id'];
                                $status = $_GET['status'];

                                $req_slot = $_GET['req_slot'];
                                $post_start = $_GET['post_start'];
                                $post_end = $_GET['post_end'];
                                $slot_start = $_GET['slot_start'];
                                $slot_end = $_GET['slot_end'];
                                $bid_low = $_GET['bid_low'];
                                $bid_upp = $_GET['bid_upp'];

                                $query = "SELECT p.pet_name, o.name, r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.status FROM pet_user o, request r, pet p, pet_user t
                                          WHERE r.owner_id = o.user_id AND r.pets_id = p.pets_id AND t.user_id = r.taker_id AND t.user_id = $user_id" ;

                                if (trim($pet_id)) {
                                    $query .= " AND p.pets_id = " . $pet_id;
                                }

                                if (trim($owner_id)) {
                                    $query .= " AND r.owner_id = '" . $owner_id . "'";
                                }

                                if (trim($status)) {
                                    $query .= " AND r.status = '" . $status . "'";
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

                                if (trim($req_slot)) {
                                    $query .= " AND r.slot = '" . $req_slot . "'";
                                }

                                if (trim($bid_low)) {
                                    $query .= " AND r.bids >= $bid_low";
                                }

                                if (trim($bid_upp)) {
                                    $query .= " AND r.bids <= $bid_upp";
                                }
                                $query .= " ORDER BY r.post_time;";
                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT p.pet_name, o.name, r.post_time, r.care_begin, r.care_end, r.bids, r.remarks, r.status FROM pet_user o, request r, pet p, pet_user t
                                          WHERE r.owner_id = o.user_id AND r.pets_id = p.pets_id AND t.user_id = r.taker_id AND t.user_id = $user_id" ;
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
