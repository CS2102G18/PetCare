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
            <li>Users</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-sm-3">
                            <label for="user_kw">User's Name</label>
                            <input id="user_kw" name="user_kw" type="text" class="form-control" placeholder="Keywords">
                        </div>
                        <div class="col-sm-3">
                            <label for="add_kw">User's Address</label>
                            <input id="add_kw" name="add_kw" type="text" class="form-control" placeholder="Keywords">
                        </div>

                        <div class="col-sm-3">
                            <label for="em_kw">User's Email</label>
                            <input id="em_kw" name="em_kw" type="text" class="form-control" placeholder="Keywords">
                        </div>
                        <div class="col-sm-3">
                            <label for="user_role">Role</label>
                            <select name="user_role" id="pet_size" class="form-control">
                                <option value="">Select Role</option>
                                <option value="normal">Normal</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-sm-12">
                            <br>
                            <input type="submit" class="btn-primary btn" id="findBtn" name="search" value="Search">
                            <a href="admin_user.php" class="btn-default btn">Cancel</a>
                            <a href="admin_adduser.php" class="btn-success btn">Add new user</a>
                            <?php echo (!isset($_GET['show_deleted']))
                                ? "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"show_deleted\"
                                   value=\"Show Deleted\">"
                                : "<input type=\"submit\" class=\"btn-info btn\" id=\"findBtn\" name=\"back\"
                                   value=\"Back\">" ?>
                            <a href="admin_userstats.php" class="btn-warning btn">Show statistics</a>
                        </div>

                    </div>
                </div>
                <br><br>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped" id="user_info">
                            <tr>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>User Password</th>
                                <th>User email</th>
                                <th>User address</th>
                                <th>User role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            <?php
                            if (isset($_GET['search'])) {
                                $user_kw = $_GET['user_kw'];
                                $add_kw = $_GET['add_kw'];
                                $em_kw = $_GET['em_kw'];
                                $user_role = $_GET['user_role'];

                                $query = "SELECT u.user_id, u.name, u.password, u.email, u.address, u.role, u.is_deleted
                                          FROM pet_user u
                                          WHERE u.is_deleted = " . (isset($_GET['show_deleted']) ? "true" : "false");

                                if (trim($user_kw)) {
                                    $query .= " AND UPPER(u.name) LIKE UPPER('%" . $user_kw . "%')";
                                }

                                if (trim($add_kw)) {
                                    $query .= " AND UPPER(u.address) LIKE UPPER('%" . $add_kw . "%')";
                                }

                                if (trim($em_kw)) {
                                    $query .= " AND UPPER(u.email) LIKE UPPER('%" . $em_kw . "%')";
                                }

                                if (trim($user_role)) {
                                    $query .= " AND u.role ='" . $user_role . "')";
                                }

                                $query .= " ORDER BY u.user_id;";

                                $result = pg_query($query) or die('Query failed1: ' . pg_last_error());
                            } else {
                                $query = "SELECT u.user_id, u.name, u.password, u.email, u.address, u.role, u.is_deleted
                                          FROM pet_user u
                                          WHERE u.is_deleted = " . (isset($_GET['show_deleted']) ? "true" : "false") .
                                    " ORDER BY u.user_id;";
                                $result = pg_query($query) or die('Query failed2: ' . pg_last_error());
                            }

                            while ($row = pg_fetch_row($result)) {
                                $user_id = $row[0];
                                echo "<tr>";
                                echo "<td >$row[0]</td >";
                                echo "<td >$row[1]</td >";
                                echo "<td >$row[2]</td>";
                                echo "<td >$row[3]</td >";
                                echo "<td >$row[4]</td>";
                                echo "<td >$row[5]</td >";
                                echo "<td >" . (!$row[6] ? "Deleted" : "Active") . "</td >";
                                echo "<td >" .
                                    (!isset($_GET['show_deleted'])
                                        ? "<a class=\"btn btn-default\" role=\"button\" href=\"admin_edituser.php?u_id=$user_id\">Edit</a>
                                               <a class=\"btn btn-danger\" role=\"button\" href=\"admin_delete.php?u_id=$user_id&usage=user\">Delete</a>"
                                        : "<a class=\"btn btn-default\" role=\"button\" href=\"admin_restore.php?u_id=$user_id&usage=user\">Restore</a>") .

                                    "</td>";
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
