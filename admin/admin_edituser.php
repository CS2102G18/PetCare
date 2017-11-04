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

if (isset($_GET["u_id"])) {
    $user_id = $_GET["u_id"];
    $query = "SELECT * FROM pet_user WHERE user_id = $user_id;";
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $name = $row[1];
    $password = $row[2];
    $email = $row[3];
    $address = $row[4];
    $role = $row[5];
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
    <div class="panel new-task-panel">
        <div class="page-heading">
            <ol class="breadcrumb">
                <li><a href="../admin.php">Admin</a></li>
                <li><a href="admin_user.php">User</a></li>
                <li>Update Users</li>
            </ol>
        </div>
        <div class="container">
            <h2>User profile</h2>
            <form action="admin_edituser.php">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Name</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="name" type="text" class="form-control"
                                   value="<?php echo $name ?>">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Password</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="password" type="text" class="form-control"
                                   value="<?php echo $password ?>">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>email</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="email" type="text" class="form-control"
                                   value="<?php echo $email ?>">
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Address</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="address" type="text" class="form-control"
                                   value="<?php echo $address ?>">
                            <input name="id" type="hidden" class="form-control"
                                   value="<?php echo $user_id ?>">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>Role</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="role" class="form-control">
                                <option value="<?php echo $role ?>"><?php echo $role ?></option>
                                <?php
                                $query = "SELECT DISTINCT role FROM pet_user WHERE role <> '" . $role . "';";
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
                        <button type="submit" name="update" class="btn btn-default">Update</button>
                    </div>

            </form>
        </div>
    </div>
</div>

<?php
if (isset($_GET['update'])) {
    $u_id = $_GET['id'];
    $u_name = $_GET["name"];
    $u_password = $_GET["password"];
    $u_email = $_GET["email"];
    $u_address = $_GET["address"];
    $u_role = $_GET['role'];
    $update_query = "UPDATE pet_user
                     SET name = '$u_name', password = '$u_password', email = '$u_email', address = '$u_address', role = '$u_role'
                     WHERE user_id = $u_id;";
    $result = pg_query($update_query) or die('Query failed: ' . pg_last_error());
    if ($result) {
        pg_free_result($result);
        header("Location: admin_user.php");
        echo "<script>window.location = 'admin_user.php';</script>";

    }
    exit();
}
?>
</body>
</html>
