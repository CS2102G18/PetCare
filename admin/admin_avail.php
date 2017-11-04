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
    <link rel="stylesheet" href="../vendor/css/bootstrap-datetimepicker.min.css">

    <script src="../vendor/js/jquery-3.2.0.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="../vendor/js/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
            $('#start-datetimepicker').datetimepicker();
            $('#end-datetimepicker').datetimepicker();
            $('#sb-datetimepicker').datetimepicker();
            $('#se-datetimepicker').datetimepicker();
        });

    </script>
    <style>
        .navbar-admin {
            color: #FFFFFF;
            background-color: #793585;
        }

        .col-centered {
            display: block;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
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
            <li> Availability</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-sm-3">
                                    <label for="care_giver">Care Giver</label>
                                    <select name="care_giver" class="form-control">
                                        <option value="">Select Owner</option>
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
                                <div class="col-sm-2">
                                    <label for="pet_species">Pet's Species</label>
                                    <select id="pet_species" name="pet_species" class="form-control">
                                        <option value="">Select Category</option>
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
                                <div class="col-sm-2">
                                    <label for="pet_age">Pet's Age</label>
                                    <select id="pet_age" name="pet_age" class="form-control">
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
                                <div class="col-sm-2">
                                    <label for="pet_size">Pet's Size</label>
                                    <select name="pet_size" id="pet_size" class="form-control">
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
                                    <br>
                                    <input type="submit" class="btn-primary btn" id="findBtn" name="search"
                                           value="Search">
                                    <a href="admin_avail.php" class="btn-default btn">Cancel</a>
                                    <a href="admin_addavail.php" class="btn-success btn">Add Availability Slots</a>
                                    <?php echo (!isset($_GET['show_deleted']))
                                        ? "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"show_deleted\"
                                   value=\"Show Deleted\">"
                                        : "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"back\"
                                   value=\"Back\">" ?>

                                </div>
                            </div>
                        </div>
                        <br><br>
                    </div>
                    <br>
                    <div class="col-md-12">
                        <table class="table table-striped" id='avail_info'>
                            <tr>
                                <th>Availability ID</th>
                                <th>User</th>
                                <th>Post time</th>
                                <th>Begin time</th>
                                <th>End time</th>
                                <th>Pet Category Available</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $pet_species = $_GET['pet_species'];
                                $pet_age = $_GET['pet_age'];
                                $pet_size = $_GET['pet_size'];
                                $care_giver = $_GET['care_giver'];

                                $slot_start = $_GET['slot_start'];
                                $slot_end = $_GET['slot_end'];
                                $post_start = $_GET['post_start'];
                                $post_end = $_GET['post_end'];


                                $query = "SELECT a.avail_id, a.post_time, a.start_time, a.end_time,
                                                 u.user_id, u.name, a.is_deleted, pc.age, pc.size, pc.species
                                          FROM availability a INNER JOIN pet_user u ON a.taker_id = u.user_id
                                                              INNER JOIN petcategory pc ON a.pcat_id = pc.pcat_id
                                          WHERE a.is_deleted " . (isset($_GET['show_deleted']) ? "='t'" : "='f'");

                                if (trim($pet_age)) {
                                    $query .= " AND pc.age = '" . $pet_age . "'";
                                }

                                if (trim($pet_species)) {
                                    $query .= " AND pc.species = '" . $pet_species . "'";
                                }

                                if (trim($care_giver)) {
                                    $query .= " AND a.taker_id = '" . $care_giver . "'";
                                }

                                if (trim($pet_size)) {
                                    $query .= " AND pc.size = '" . $pet_size . "'";
                                }

                                if (trim($post_start)) {
                                    $query .= " AND a.post_time >= '" . $post_start . "'";
                                }

                                if (trim($post_end)) {
                                    $query .= " AND a.post_time <= '" . $post_end . "'";
                                }

                                if (trim($slot_start)) {
                                    $query .= " AND a.start_time >= '" . $slot_start . "'";
                                }

                                if (trim($slot_end)) {
                                    $query .= " AND a.end_time <= '" . $slot_end . "'";
                                }

                                $query .= " ORDER BY a.avail_id;";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT a.avail_id, a.post_time, a.start_time, a.end_time,
                                                 u.user_id, u.name, a.is_deleted, pc.age, pc.size, pc.species
                                          FROM availability a INNER JOIN pet_user u ON a.taker_id = u.user_id
                                                              INNER JOIN petcategory pc ON a.pcat_id = pc.pcat_id
                                          WHERE a.is_deleted " . (isset($_GET['show_deleted']) ? "='t'" : "='f'") .
                                    " ORDER BY a.avail_id;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $a_id = $row[0];
                                $a_post = $row[1];
                                $a_start = $row[2];
                                $a_end = $row[3];
                                $a_user = $row[5] . " (id: " . $row[4] . ")";
                                $a_pcat = $row[8] . " " . $row[7] . " " . $row[9];
                                $a_status = ($row[6] == 't' ? 'Deleted' : 'Active');
                                echo "<tr>";
                                echo "<td >$a_id</td >";
                                echo "<td >$a_user</td >";
                                echo "<td >$a_post</td>";
                                echo "<td >$a_start</td >";
                                echo "<td >$a_end</td>";
                                echo "<td >$a_pcat</td >";
                                echo "<td >$a_status</td >";
                                echo "<td >" .
                                    (!isset($_GET['show_deleted'])
                                        ? "<a class=\"btn btn-default\" role=\"button\" href=\"admin_editavail.php?a_id=$a_id\">Edit</a>
                                               <a class=\"btn btn-danger\" role=\"button\" href=\"admin_delete.php?a_id=$a_id&usage=avail\">Delete</a>"
                                        : "<a class=\"btn btn-default\" role=\"button\" href=\"admin_restore.php?a_id=$a_id&usage=avail\">Restore</a>") .

                                    "</td>";
                                echo "</tr>";
                            }
                            pg_free_result($result);
                            ?>
                        </table>
                    </div>
            </form>
        </div>
    </div>
</div>
</body>