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
<!-- include php -->
<?php include "../config/db-connection.php"; ?>

<!--navigation bar-->
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-admin">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="owner.php"> As a Pet Owner </a></li>
                <li><a href="taker.php"> As a Care Taker </a></li>
                <li><a href="history.php"> View History </a></li>
                <li><a href="profile.php"> Your Profile </a></li>
                <li><a href="logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content-container container">
    <div class="page-heading">
        <ol class="breadcrumb">
            <li><a href="../admin.php">Admin</a></li>
            <li>View Users</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-sm-3">
                            <label for="pet_kw">User's Name</label>
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
                        <div class="col-sm-3">
                            <br>
                            <a href="admin_addpet.php" class="btn-success btn">Add</a>
                            <input type="submit" class="btn-primary btn" id="findBtn" name="search" value="Search">
                            <a href="admin_pet.php" class="btn-default btn">Cancel</a>
                        </div>
                    </div>
                </div>
                <br><br>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tr>
                                <th>Pet ID</th>
                                <th>Pet Name</th>
                                <th>Pet Owner</th>
                                <th>Pet Species</th>
                                <th>Pet Size</th>
                                <th>Pet Age</th>
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
                                          WHERE p.is_deleted = false ";

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
                                $query .= "ORDER BY p.pets_id;";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT p.pets_id, p.pet_name, pc.species, pc.size, pc.age, u.name, u.user_id, u.role
                                          FROM pet p INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                                     INNER JOIN pet_user u ON p.owner_id = u.user_id
                                          WHERE p.is_deleted = false
                                          ORDER BY p.pets_id;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $pet_id = $row[0];
                                echo "<tr>";
                                echo "<td >$row[0]</td >";
                                echo "<td >$row[1]</td >";
                                echo "<td >$row[5] (id: $row[6])" . (($row[7] == 'admin') ? " ***ADMIN***" : "");
                                echo "<td >$row[2]</td >";
                                echo "<td >$row[3]</td>";
                                echo "<td >$row[4]</td >";
                                echo "<td >
                                <a class=\"btn btn-default\" role=\"button\" href=\"admin_editpet.php?p_id=$pet_id\">Edit</a>
                                <a class=\"btn btn-danger\" role=\"button\" href=\"admin_deletepet.php?p_id=$pet_id\">Delete</a>
                                </td>";
                                echo "</tr>";
                            }
                            pg_free_result($result2);
                            ?>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="content-container container">
    <div class="panel new-task-panel">
        <div class="container">
            <h2>Summary on Takers</h2>
        </div>
        <br>
        <br>


        <div class="container">
            <h4>Takers with highest average bids offered</h4>
        </div>


        <div class="table-vertical first-table">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Pet Species</th>
                    <th>Taker Name</th>
                    <th>Taker Email</th>
                    <th>Average Bids Provided</th>
                    <th>Number of Successful Assignments Done</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query2 = "SELECT u.name, u.email, k.average, k.num
                       FROM (SELECT r.taker_id AS id, AVG(r.bids) AS average, COUNT(r.request_id) AS num
                             FROM request r
                             GROUP BY r.taker_id) AS k, pet_user u
                       WHERE u.user_id = k.id AND NOT EXISTS(SELECT *
                                                             FROM (SELECT AVG(r1.bids) AS avg FROM request r1 GROUP BY r1.taker_id) AS k1 
                                                             WHERE k.average < k1.avg);";

                $result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
                $row2 = pg_fetch_row($result2);

                $average2 = $row2[2] < 0 ? '' : round(floatval($row2[2]), 2);
                echo "<tr>
                  <td>All</td>
                  <td>$row2[0]</td>
                  <td>$row2[1]</td>
                  <td>$average2</td>
                  <td>$row2[3]</td>

                  </tr>";
                $query4 = "SELECT k.species, u.name, u.email, k.average, k.num
                       FROM (SELECT r.taker_id AS id, AVG(r.bids) AS average, COUNT(r.request_id) AS num, c.species AS species
                             FROM request r, pet p, petcategory c
                             WHERE r.pets_id = p.pets_id AND p.pcat_id = c.pcat_id
                             GROUP BY c.species, r.taker_id) AS k, pet_user u
                       WHERE u.user_id = k.id AND NOT EXISTS(SELECT *
                                                             FROM (SELECT AVG(r1.bids) AS avg 
                                                                   FROM request r1, pet p1, petcategory c1 
                                                                   WHERE r1.pets_id = p1.pets_id AND p1.pcat_id = c1.pcat_id AND c1.species = k.species
                                                                   GROUP BY r1.taker_id) AS k1 
                                                             WHERE k.average < k1.avg);";

                $result4 = pg_query($query4) or die('Query failed: ' . pg_last_error());

                while ($row4 = pg_fetch_row($result4)) {
                    $average4 = $row4[3] < 0 ? '' : round(floatval($row4[3]), 2);
                    echo "
                    <tr>
                    <td>$row4[0]</td>
                    <td>$row4[1]</td>
                    <td>$row4[2]</td>
                    <td>$average4</td>
                    <td>$row4[4]</td>
                    </tr>";
                };


                pg_free_result($result2);
                ?>
                </tbody>
            </table>
        </div>


        <div>
            <h4>Takers who have taken care of all species of pets</h4>
        </div>


        <div class="table-vertical first-table">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Taker Name</th>
                    <th>Average Bids Provided</th>
                    <th>Number of Successful Assignments Done</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query3 = "SELECT u.name, AVG(r1.bids) AS average, COUNT(r1.taker_id)
                       FROM request r1, pet_user u
                       WHERE r1.taker_id = u.user_id AND NOT EXISTS (SELECT c1.species
                                                                     FROM petcategory c1
                                                                     WHERE NOT EXISTS (SELECT *
                                                                                       FROM request r2, pet p, petcategory c2
                                                                                       WHERE r2.taker_id = r1.taker_id
                                                                                             AND r2.pets_id = p.pets_id
                                                                                             AND p.pcat_id = c2.pcat_id
                                                                                             AND c2.species = c1.species))
                       GROUP BY r1.taker_id, u.name
                       ORDER BY average DESC";

                $result3 = pg_query($query3) or die('Query failed: ' . pg_last_error());
                $flag = 0;


                while ($row3 = pg_fetch_row($result3)) {
                    $flag = 1;
                    $average = $row3[1] < 0 ? '' : round(floatval($row3[1]), 2);
                    echo "
                    <tr>
                    <td>$row3[0]</td>
                    <td>$average</td>
                    <td>$row3[2]</td>
                    </tr>";
                }

                if (!$flag) {
                    echo "<tr><td>No Such Takers Yet</td></tr>";
                }
                pg_free_result($result3);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
