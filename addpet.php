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
    <style>
        .navbar-owner {
            color: #FFFFFF;
            background-color: #8a3541;
        }

        body {
            background: url('./media/background_owner.png');
        }
    </style>
</head>
<body>
<?php include "config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top navbar-owner">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="owner.php"> PetCare</a></div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="history.php"> View History </a></li>
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
    <div class="panel new-task-panel">
        <div class="container">
            <h2>Add your pet</h2>
            <form action="addpet.php">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Name</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="pet_name" type="text" class="form-control" placeholder="Pet Name"
                                   required="true">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Species</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_species" class="form-control" required="true">
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
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Age</h5>
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
                            <h5>New Pet's Size</h5>
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
                    <br>
                    
                    <div class="container">
                        <button type="submit" name="create" class="btn btn-default">Submit</button>
                        <a class="btn btn-danger" role="button" href="owner.php">Cancel</a>
                    </div>
                    
            </form>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $pet_age = $_GET["pet_age"];
    $pet_size = $_GET["pet_size"];
    $pet_species = $_GET["pet_species"];
    $pcat_query = "SELECT pcat_id FROM petcategory WHERE age = '$pet_age'
                      AND size = '$pet_size'
                      AND species = '$pet_species';";
    $pcat_result = pg_query($pcat_query) or die('Query failed: ' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];
    $pet_name = $_GET["pet_name"];
    $insert_query = "INSERT INTO pet(pcat_id, owner_id, pet_name) VALUES ($pcat_id,$user_id,'$pet_name');";
    $result = pg_query($insert_query);
    if (!$result){
        die('Query failed: ' . pg_last_error());
    } else {
        pg_free_result($result);
        header("Location: owner.php");
        echo "<script>window.location = './owner.php';</script>";
    }
    exit();
}
?>
</body>
</html>

