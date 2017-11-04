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
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="../admin.php">Admin</a></li>
            <li><a href="admin_avail.php">Availability</a></li>
            <li>Add new Availability</li>
        </ol>
    </div>
    <div class="panel new-task-panel">
        <div class="container">
            <h2>Add new available slot into the system</h2>
            <form>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Declare available time</h4>
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
                            <h4>Declare the care giver concerned and the pet categories</h4>
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
                                $query = "SELECT user_id, name, role FROM pet_user";
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
                        <div class="col-sm-2">
                            <h5>Available Pet's Species</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_species" class="form-control" required="true">
                                <option value="">Select Species</option>
                                <?php
                                $query = "SELECT DISTINCT species FROM petcategory";
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
                            <h5>Available Pet's Age</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_age" class="form-control" required="true">
                                <option value="">Select Age</option>
                                <?php
                                $query = "SELECT DISTINCT age FROM petcategory";
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
                            <h5>Available Pet's Size</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_size" class="form-control" required="true">
                                <option value="">Select Size</option>
                                <?php
                                $query = "SELECT DISTINCT size FROM petcategory";
                                $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                                while ($row = pg_fetch_row($result)) {
                                    echo "<option value='" . $row[0] . "'>" . $row[0] . "</option><br>";
                                }
                                pg_free_result($result);
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <button type="submit" name="create" class="btn btn-default">Submit</button>
                    <a class="btn btn-danger" role="button" href="admin_addavail.php">Cancel</a>
                </div>
            </form>
            <br>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $care_taker = $_GET['care_taker'];
    $start_time = $_GET['start_time'];
    $end_time = $_GET['end_time'];
    $pet_age = $_GET['pet_age'];
    $pet_species = $_GET['pet_species'];
    $pet_size = $_GET['pet_size'];
    $pcat_query = "SELECT pcat_id FROM petcategory
                   WHERE age = '$pet_age'
                   AND size = '$pet_size'
                   AND species = '$pet_species';";
    $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];

    $insert_query = "INSERT INTO availability(start_time, end_time, pcat_id, taker_id) 
                     VALUES ('$start_time', '$end_time', $pcat_id, $care_taker);";
    $result = pg_query($insert_query);
    print $insert_query;
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
                          <button type='button' class='btn btn-default'><a href='admin_avail.php'>Close</a></button>
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
                      <button type='button' class='btn btn-default'><a href='admin_avail.php'>Close</a></button>
                    </div>
                </div>
            </div>";
        pg_free_result($result);
        header("Location: admin_avail.php");
    }
    exit();
}
?>
</body>
</html>
