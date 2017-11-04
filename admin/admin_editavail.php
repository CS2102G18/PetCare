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

if (isset($_GET["a_id"])) {
    $avail_id = (int)$_GET["a_id"];
    $query = "SELECT a.start_time, a.end_time, pc.species, pc.age, pc.size, a.taker_id, u.name, u.role
              FROM availability a INNER JOIN petcategory pc ON a.pcat_id = pc.pcat_id
                                  INNER JOIN pet_user u ON a.taker_id = u.user_id
              WHERE a.avail_id = $avail_id;";
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($result);

    $a_start = $row[0];
    $a_end = $row[1];
    $a_species = $row[2];
    $a_age = $row[3];
    $a_size = $row[4];
    $a_uid = $row[5];
    $a_uname = $row[6];
    $a_role = $row[7];

    $owner_info = $a_uname . "(id: " . $a_uid . ")" . ($a_role == "admin" ? " ***ADMIN***" : "");
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
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="../admin.php">Admin</a></li>
            <li><a href="admin_avail.php">Availability</a></li>
            <li>Edit Availability</li>
        </ol>
    </div>
    <div class="panel new-task-panel">
        <div class="container">
            <h2>Edit existing available slot</h2>
            <form action="admin_editavail.php">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>Edit available time</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label">
                                    <h5>Start</h5>
                                </label>
                                <div class="col-sm-6">
                                    <div class="input-group date" id="start-datetimepicker">
                                        <input type="text" class="form-control" name="start_time" required="true"
                                               placeholder="<?php echo $a_start ?>"
                                               value="<?php echo $a_start ?>">
                                        <input name="a_id" value="<?php echo $avail_id ?>" type='hidden'/>
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
                                        <input type="text" class="form-control" name="end_time" required="true"
                                               placeholder="<?php echo $a_end ?>"
                                               value="<?php echo $a_end ?>">
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
                            <h4>Edit the care giver concerned and the pet categories</h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Care giver</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="care_taker" class="form-control" required="true">
                                <option value="<?php echo $a_uid ?>"><?php echo $owner_info ?></option>
                                <?php
                                $query = "SELECT user_id, name, role FROM pet_user WHERE user_id <>" . $a_uid;
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
                                <option value="<?php echo $a_species ?>"><?php echo $a_species ?></option>
                                <?php
                                $query = "SELECT DISTINCT species FROM petcategory WHERE species <> '" . $a_species. "'";
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
                                <option value="<?php echo $a_age ?>"><?php echo $a_age ?></option>
                                <?php
                                $query = "SELECT DISTINCT age FROM petcategory WHERE age <> '" . $a_age . "'";
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
                                <option value="<?php echo $a_size ?>"><?php echo $a_size ?></option>
                                <?php
                                $query = "SELECT DISTINCT size FROM petcategory WHERE size <> '" . $a_size . "'";
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
    $a_uid = $_GET['care_taker'];
    $a_start = $_GET['start_time'];
    $a_end = $_GET['end_time'];
    $a_age = $_GET['pet_age'];
    $a_species = $_GET['pet_species'];
    $a_size = $_GET['pet_size'];
    $pcat_query = "SELECT pcat_id FROM petcategory
                   WHERE age = '$a_age'
                   AND size = '$a_size'
                   AND species = '$a_species';";
    $pcat_result = pg_query($pcat_query) or die('Query failed a: ' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];

    $check_overlap_query = "SELECT avail_id FROM availbility a
                            WHERE a.start_time <= '" . $a_end . "' 
                            AND a.end_time >= '" . $a_start . "'
                            AND a.pcat_id = " . $pcat_id . "
                            AND a.taker_id = " . $a_uid . ";";
    $check_overlap_result = pg_query($check_overlap_query);
    if (pg_numrows($check_overlap_result) > 0) {
        echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Edit Availability</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Overlapping available slot exists!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default'><a href='admin_editavail.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed b : ' . pg_last_error());
    }

    $update_query = "UPDATE availability
                     SET start_time = '".$a_start."',
                         end_time = '".$a_end."',
                         pcat_id = $pcat_id, 
                         taker_id = $a_uid
                     WHERE avail_id = $avail_id;";
    $result = pg_query($update_query);
    if (!$result) {
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
                          <button type='button' class='btn btn-default'><a href='admin_avail.php'>Close</a></button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed c: ' . pg_last_error());
    } else {
        pg_free_result($result);
        header("Location: admin_avail.php");
        echo "<script>window.location = 'admin_avail.php';</script>";
    }
    exit();
}
?>
</body>
</html>
