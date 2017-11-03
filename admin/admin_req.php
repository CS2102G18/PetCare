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
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

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
    <div class="panel new-task-panel">
        <div class="container ">
            <h2>Summary on Requests</h2>
        </div>
        <div class="table-vertical first-table">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Pet Category</th>
                    <th>Time Period</th>
                    <th>Number of Successful Requests</th>
                    <th>Average bids</th>
                    <th>User Post Most</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query1 = " SELECT k.species, k.timeslot, k.RequestNum, k.average, r1.owner_id 
                        FROM (SELECT c.species AS species, r.slot AS timeslot, COUNT(r.request_id) AS RequestNum, AVG(r.bids) AS average
                              FROM petcategory c, pet p, request r 
                              WHERE r.pets_id = p.pets_id AND c.pcat_id = p.pcat_id AND r.status = 'successful'
                              GROUP BY r.slot, c.species) AS k, request r1, petcategory c1, pet p1
                        WHERE r1.pets_id = p1.pets_id AND c1.pcat_id = p1.pcat_id AND r1.status = 'successful' AND c1.species = k.species AND r1.slot = k.timeslot
                        GROUP BY r1.owner_id, k.species, k.timeslot, k.RequestNum, k.average
                        HAVING COUNT(*) >= ALL(
                                           SELECT COUNT(*)
                                           FROM request r2, petcategory c2, pet p2
                                           WHERE r2.pets_id = p2.pets_id AND c2.pcat_id = p2.pcat_id AND r2.status = 'successful' AND c2.species = k.species AND r2.slot = k.timeslot
                                           GROUP BY r2.owner_id)
                        ORDER BY k.RequestNum DESC;";

                $result1 = pg_query($query1) or die('Query failed: ' . pg_last_error());


                while ($row1 = pg_fetch_row($result1)) {
                    $owner_name = pg_fetch_row(pg_query("SELECT name FROM pet_user WHERE user_id = " . $row1[4] . ";"))[0];
                    $average = $row1[3] < 0 ? '' : round(floatval($row1[3]), 2);
                    echo "
                    <tr>
                    <td>$row1[0]</td>
                    <td>$row1[1]</td>
                    <td>$row1[2]</td>
                    <td>$average</td>
                    <td>$owner_name</td>
                    </tr>";
                }

                pg_free_result($result1);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>