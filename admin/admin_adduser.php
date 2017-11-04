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
                <li><a href="admin_user.php">User</a></li>
                <li>Add new user</li>
            </ol>
        </div>
        <div class="container">
            <h2>Add new user into the system</h2>
            <form action="admin_adduser.php">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Name</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="name" type="text" class="form-control"
                                   value="">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Password</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="password" type="text" class="form-control"
                                   value="">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>email</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="email" type="text" class="form-control"
                                   value="">
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Address</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="address" type="text" class="form-control"
                                   value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="col-sm-2">
                            </div>
                            <div class="checkbox">
                                <label><input name="admin" type="checkbox" value="admin">Admin User</label>
                            </div>
                        </div>
                    </div>
                    <br>

                    <div class="container">
                        <button type="submit" name="create" class="btn btn-default">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
if (isset($_GET['create'])) {
    $user_name = $_GET['name'];
    $user_add = $_GET["address"];
    $user_email = $_GET["email"];
    $user_password = $_GET["password"];

    if (!$_GET['admin']=='admin') {
        $insert_query = "INSERT INTO pet_user(name, password, email, address) VALUES ('$user_name', '$user_password', '$user_email','$user_add');";
    } else {
        $insert_query = "INSERT INTO pet_user(name, password, email, address, role) VALUES ('$user_name', '$user_password', '$user_email','$user_add', 'admin');";
    }
    $result = pg_query($insert_query);
    if (!$result) {
        die('Query failed: ' . pg_last_error());
    } else {
        pg_free_result($result);
        header("Location: admin_user.php");
        echo "<script>window.location = 'admin_user.php';</script>";
    }
    exit();
}
?>
</body>
