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
            <li>View Users</li>
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
                                <th >User ID</th>
                                <th >User Name</th>
                                <th >User Password</th>
                                <th >User email</th>
                                <th >User address</th>
                                <th >User role</th>
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
                    <th >Pet Species</th>
                    <th >Taker Name</th>
                    <th >Taker Email</th>
                    <th >Average Bids Provided</th>
                    <th >Number of Successful Assignments Done</th>
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
                    <th >Taker Name</th>
                    <th >Average Bids Provided</th>
                    <th >Number of Successful Assignments Done</th>
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
