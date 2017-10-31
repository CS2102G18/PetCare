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
            <div class="row">
                <div class="col-md-12">
                    <div class="col-sm-3">
                        <label for="pet_name">Pet's Name</label>
                        <input id="pet_name" type="text" class="form-control" placeholder="Pet Name"
                               required="true">
                    </div>
                    <div class="col-sm-3">
                        <label for="pet_species">Pet's Species</label>
                        <select id="pet_species" class="form-control" required="true">
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
                        <select id="pet_age" class="form-control" required="true">
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
                    <div class="col-sm-2">
                        <label for="search"></label>
                        <a class="btn btn-primary" role="button" name="search">Search</a>
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
                        if (!isset($_GET['search'])) {
                            $query2 = "SELECT * FROM pet p ORDER BY pets_id;";
                        } else {
                            $pet_kw = $_GET['pet_name'];
                            $pet_species = $_GET[''];
                        }
                        $result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
                        while ($row = pg_fetch_row($result2)) {
                            $pet_id = $row[0];
                            $pet_name = $row[3];
                            $pet_species = pg_fetch_row(pg_query("SELECT species FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_size = pg_fetch_row(pg_query("SELECT size FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
                            $pet_age = pg_fetch_row(pg_query("SELECT age FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];

                            echo "<tr>";
                            echo "<td >$pet_id</td >";
                            echo "<td >$pet_name</td >";
                            echo "<td >$pet_species</td >";
                            echo "<td >$pet_size</td>";
                            echo "<td >$pet_age</td >";
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
        </div>
    </div>
</div>

</body>