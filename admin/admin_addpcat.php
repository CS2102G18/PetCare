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
    <div class="panel new-task-panel">
        <div class="page-heading">
            <ol class="breadcrumb">
                <li><a href="../admin.php">Admin</a></li>
                <li><a href="admin_pcat.php">Pet Category</a></li>
                <li>Add new pet category</li>
            </ol>
        </div>
        <div class="container">
            <h2>Add new pet category into the system</h2>
            <form action="admin_addpcat.php">
                <div class="form-group">
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Category's Species</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="pcat_species" type="text" class="form-control" placeholder="Species"
                                   required="true">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Category's Age</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="pcat_age" type="text" class="form-control" placeholder="Age"
                                   required="true">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Category's Size</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="pcat_size" type="text" class="form-control" placeholder="Size"
                                   required="true">
                        </div>
                    </div>
                    <br><br>
                    <button type="submit" name="create" class="btn btn-primary">Submit</button>
                    <a href="admin_addpcat.php" class="btn-default btn">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $pcat_size = $_GET['pcat_size'];
    $pcat_species = $_GET['pcat_species'];
    $pcat_age = $_GET['pcat_age'];
    $insert_query = "INSERT INTO petcategory(age, size, species) VALUES ('$pcat_age','$pcat_size','$pcat_species');";
    $result = pg_query($insert_query);
    if (!$result){
        die('Query failed: ' . pg_last_error());
    } else {
        pg_free_result($result);
        header("Location: admin_pcat.php");
        echo "<script>window.location = 'admin_pcat.php';</script>";
    }
    exit();
}
?>
</body>
