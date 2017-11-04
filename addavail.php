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
        .navbar-taker {
            color: #FFFFFF;
            background-color: #035f72;
        }

        body {
            background: url('./media/background_taker.png');
        }
    </style>
</head>
<body>
<?php include "config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-taker">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="taker.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="history.php"> View History </a></li>
                <li><a href="profile.php"> Your Profile </a></li>
                <?php
                $admin_query = "SELECT role FROM pet_user WHERE user_id=" . $user_id . ";";
                $admin_result = pg_query($admin_query) or die('Query failed: ' . pg_last_error());
                $admin_row = pg_fetch_row($admin_result);
                if (strcmp($admin_row[0], "admin") == 0) {
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
            <h2>Add your available slot</h2>
            <form>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Declare your available time</h4>
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
                            <h4>Declare the available pet categories</h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Species</h5>
                        </div>
                        <div class="col-sm-8">
                            <div class="checkbox-group required">
                                <?php
                                $query = "SELECT DISTINCT species FROM petcategory";
                                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    echo "<div class=\"form-check\">";
                                    echo "<label class=\"form-check-label\">";
                                    echo "<input class='form-check-input' type='checkbox' name='pet_species[]' value='" . $row[0] . "'/> " . $row[0];
                                    echo "</label></div>";
                                }
                                pg_free_result($result);
                                ?>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Age</h5>
                        </div>
                        <div class="col-sm-8">
                            <div class="checkbox-group required">
                                <?php
                                $query = "SELECT DISTINCT age FROM petcategory";
                                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    echo "<div class=\"form-check\">";
                                    echo "<label class=\"form-check-label\">";
                                    echo "<input class='form-check-input' type='checkbox' name='pet_age[]' style='font-size:14px' value='" . $row[0] . "'/> " . $row[0];
                                    echo "</label></div>";
                                }
                                pg_free_result($result);
                                ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Size</h5>
                        </div>
                        <div class="col-sm-8">
                            <div class="checkbox-group required">
                                <?php
                                $query = "SELECT DISTINCT size FROM petcategory";
                                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    echo "<div class=\"form-check\">";
                                    echo "<label class=\"form-check-label\">";
                                    echo "<input class='form-check-input' type='checkbox' name='pet_size[]' value='" . $row[0] . "'/> " . $row[0];
                                    echo "</label></div>";
                                }
                                pg_free_result($result);
                                ?>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Remarks</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="remarks" type="text" class="form-control" required="true">
                        </div>

            </form>


        </div>
        <br>
        <div class="container">
            <button type="submit" name="create" class="btn btn-default">Submit</button>
            <a class="btn btn-danger" role="button" href="taker.php">Cancel</a>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $start_time = $_GET['start_time'];
    $end_time = $_GET['end_time'];
    $pet_age = $_GET['pet_age'];
    $pet_species = $_GET['pet_species'];
    $pet_size = $_GET['pet_size'];
    $remarks = $_GET['remarks'];

    $pcat_id_list = array();

    for ($i = 0; $i < count($pet_age); $i++) {
        for ($j = 0; $j < count($pet_species); $j++) {
            for ($k = 0; $k < count($pet_size); $k++) {
                $age = $pet_age[$i];
                $species = $pet_species[$j];
                $size = $pet_size[$k];
                $pcat_query = "SELECT pcat_id FROM petcategory
                   WHERE age = '$age'
                   AND size = '$size'
                   AND species = '$species';";
                $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
                $pcat_id = pg_fetch_row($pcat_result)[0];
                $check_query = "SELECT start_time, end_time FROM availability WHERE pcat_id=" . $pcat_id . " AND taker_id=" . $user_id . " AND is_deleted=false;";
                $check_result = pg_query($check_query);
                if (!$check_result) {
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
                                      <a class='btn btn-default' role='button' href='addavail.php'>Close</a>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    die('Query failed: ' . pg_last_error());
                }
                array_push($pcat_id_list, $pcat_id);
                //check overlap

                $overlap_exist = false;
                while ($check_row = pg_fetch_row($check_result)) {
                    if (!(($check_row[0] < $check_row[1] and $check_row[1] < $start_time and $start_time < $end_time)
                        or ($start_time < $end_time and $end_time < $check_row[0] and $check_row[0] < $check_row[1]))) {
                        $overlap_exist = true;
                    }
                }
                if ($overlap_exist) {
                    echo "
                        <div id='successmodal' class='modal fade'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                      <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                      <h4 class='modal-title'>Create Availability</h4>
                                    </div>
                                    <div class='modal-body'>
                                      <h4>Time slot overlap. Creation failed!</h4>
                                      <h4>Two consecutive slots will still be considered as overlap</h4>
                                    </div>
                                    <div class='modal-footer'>
                                      <a class='btn btn-default' role='button' href='addavail.php'>Close</a>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    die('Query failed: ' . pg_last_error());
                }
                pg_free_result($check_result);
                //complete check overlap
                $insert_query = "INSERT INTO availability(start_time, end_time, pcat_id, taker_id, remarks)
                     VALUES ('$start_time', '$end_time', $pcat_id, $user_id, '$remarks');";
                $result = pg_query($insert_query);
                //print $insert_query;
                if (!$result) {
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
                                      <a class='btn btn-default' role='button' href='addavail.php'>Close</a>
                                    </div>
                                </div>
                            </div>
                        </div>";
                    die('Query failed: ' . pg_last_error());
                } else {
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
                                  <a class='btn btn-default' role='button' href='taker.php'>OK</a>
                                </div>
                            </div>
                        </div>";
                    pg_free_result($result);
                }
            }
        }
    }

    exit();
}
?>
</body>
</html>
