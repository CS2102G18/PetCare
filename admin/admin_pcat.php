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

    <script src="../vendor/sortTable.js"></script>

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
            <li>Pet Categories</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
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
                            <a href="admin_addpcat.php" class="btn-success btn">Add New Category</a>

                        </div>
                    </div>
                    <div class="col-md-12">
                        <br>
                        <table class="table table-striped" id="pet_info">
                            <tr>
                                <th>Category ID</th>
                                <th>Species</th>
                                <th>Size</th>
                                <th>Age</th>
                                <th>Number of pets in this category</th>
                                <th>Number of available slots for this category</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $pet_species = $_GET['pet_species'];
                                $pet_age = $_GET['pet_age'];
                                $pet_size = $_GET['pet_size'];

                                $query = "SELECT pc.pcat_id, pc.species, pc.size, pc.age, COUNT(DISTINCT p.pets_id), COUNT(DISTINCT a.avail_id)
                                          FROM petcategory pc INNER JOIN pet p ON p.pcat_id= pc.pcat_id
                                                              INNER JOIN availability a ON a.pcat_id = pc.pcat_id
                                          WHERE 1=1";

                                if (trim($pet_age)) {
                                    $query .= " AND pc.age = '" . $pet_age . "'";
                                }

                                if (trim($pet_species)) {
                                    $query .= " AND pc.species = '" . $pet_species . "'";
                                }

                                if (trim($pet_size)) {
                                    $query .= " AND pc.size = '" . $pet_size . "'";
                                }
                                $query .= " GROUP BY pc.pcat_id ORDER BY pc.pcat_id;";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT pc.pcat_id, pc.age, pc.size, pc.species, COUNT(DISTINCT p.pets_id), COUNT(DISTINCT a.avail_id)
                                          FROM petcategory pc INNER JOIN pet p ON p.pcat_id= pc.pcat_id
                                                              INNER JOIN availability a ON a.pcat_id = pc.pcat_id
                                          GROUP BY pc.pcat_id
                                          ORDER BY pc.pcat_id";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $pcat_id = $row[0];
                                $pcat_age = $row[1];
                                $pcat_size = $row[2];
                                $pcat_species = $row[3];
                                $pcat_pcount = $row[4];
                                $pcat_acount = $row[5];
                                echo "<tr>";
                                echo "<td >$pcat_id</td >";
                                echo "<td >$pcat_species</td >";
                                echo "<td >$pcat_size</td>";
                                echo "<td >$pcat_age</td >";
                                echo "<td> $pcat_pcount</td>";
                                echo "<td> $pcat_acount</td>";
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