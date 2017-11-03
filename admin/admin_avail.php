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
        <div class="navbar-header pull-left"><a class="navbar-brand" href="../owner.php"> PetCare</a></div>
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
            <li>View Pets</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-sm-3">
                            <label for="pet_kw">Pet's Name</label>
                            <input id="pet_kw" name="pet_kw" type="text" class="form-control" placeholder="Keywords">
                        </div>
                        <div class="col-sm-3">
                            <label for="pet_species">Pet's Owner</label>
                            <select name="pet_owner" class="form-control">
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
                        <div class="col-sm-6">
                            <br>
                            <input type="submit" class="btn-primary btn" id="findBtn" name="search" value="Search">
                            <a href="admin_pet.php" class="btn-default btn">Cancel</a>
                            <a href="admin_addpet.php" class="btn-success btn">Add New Pet</a>
                            <?php echo (!isset($_GET['show_deleted']))
                                ? "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"show_deleted\"
                                   value=\"Show Deleted\">"
                                : "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"back\"
                                   value=\"Back\">" ?>

                        </div>
                    </div>
                    <div class="col-md-12">
                        <br>
                        <table class="table table-striped" id="pet_info">
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
                                $pet_kw = $_GET['pet_kw'];
                                $pet_species = $_GET['pet_species'];
                                $pet_age = $_GET['pet_age'];
                                $pet_size = $_GET['pet_size'];
                                $pet_owner = $_GET['pet_owner'];

                                $query = "SELECT p.pets_id, p.pet_name, pc.species, pc.size, pc.age, u.name, u.user_id, u.role
                                          FROM pet p INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                                     INNER JOIN pet_user u ON p.owner_id = u.user_id
                                          WHERE p.is_deleted = " . (isset($_GET['show_deleted']) ? "true" : "false");

                                if (trim($pet_kw)) {
                                    $query .= " AND UPPER(p.pet_name) LIKE UPPER('%" . $pet_kw . "%')";
                                }

                                if (trim($pet_owner)) {
                                    $query .= " AND u.user_id = '" . $pet_owner . "'";
                                }

                                if (trim($pet_age)) {
                                    $query .= " AND pc.age = '" . $pet_age . "'";
                                }

                                if (trim($pet_species)) {
                                    $query .= " AND pc.species = '" . $pet_species . "'";
                                }

                                if (trim($pet_size)) {
                                    $query .= " AND pc.size = '" . $pet_size . "'";
                                }
                                $query .= " ORDER BY p.pets_id;";

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
                </div>
            </form>
        </div>
    </div>
</div>
</body>