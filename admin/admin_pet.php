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
        <div class="navbar-header pull-left"><a class="navbar-brand" href="../admin.php"> Admin</a></div>
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

                                $query = "SELECT p.pets_id, p.pet_name, pc.species, pc.size, pc.age
                                          FROM pet p INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id
                                          WHERE 1 = 1 ";

                                if (trim($pet_kw)) {
                                    $query .= " AND UPPER(p.pet_name) LIKE UPPER('%" . $pet_kw . "%')";
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
                                $query .= ";";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT p.pets_id, p.pet_name, pc.species, pc.size, pc.age
                                          FROM pet p INNER JOIN petcategory pc ON p.pcat_id = pc.pcat_id;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $pet_id = $row[0];
                                echo "<tr>";
                                echo "<td >$row[0]</td >";
                                echo "<td >$row[1]</td >";
                                echo "<td >$row[2]</td >";
                                echo "<td >$row[3]</td>";
                                echo "<td >$row[4]</td >";
                                echo "<td >
                                <a class=\"btn btn-default\" role=\"button\" href=\"../editpet.php?p_id=$pet_id\">Edit</a>
                                <a class=\"btn btn-danger\" role=\"button\" href=\"../deletepet.php?pet_id=$pet_id\">Delete</a>
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

</body>