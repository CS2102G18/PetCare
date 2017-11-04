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
            <li><a href="admin_user.php">User</a></li>
            <li>User Statistics</li>
        </ol>
    </div>
    <div class="container-fluid">
        <div class="panel new-task-panel">
            <form action="" id="findForm">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-6">
                            <div class="container">
                                <h2>Summary on Takers</h2>
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                        <div class="col-sm-2">
                            <br><br>
                            <a href="admin_user.php" class="btn-default btn">Back to User Page</a>
                        </div>
                    </div>
                    <br>
                    <br>

                    <div class="col-sm-12">
                        <div class="col-sm-6">
                            <div class="container">
                                <h4>Takers with highest average bids offered</h4>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-12">
                        <div class="table-vertical first-table">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Pet Species</th>
                                    <th>Taker Name</th>
                                    <th>Taker Email</th>
                                    <th >Average Bids／Hour</th>
                                    <th >Total Number of Hours Completed</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query2 = "
                        SELECT u.name, u.email, k.average, k.num
                        FROM (SELECT r.taker_id AS id, (SUM(r.bids)/SUM(r.totaltime)*60) AS average, (SUM(r.totaltime)/60) AS num
                             FROM request r WHERE r.status = 'successful'
                             GROUP BY r.taker_id) AS k, pet_user u
                        WHERE u.user_id = k.id AND NOT EXISTS(SELECT *
                                                              FROM (SELECT (SUM(r1.bids)/SUM(r1.totaltime)*60) AS avg FROM request r1 GROUP BY r1.taker_id) AS k1 
                                                              WHERE k.average < k1.avg);";

                                $result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
                                $row2 = pg_fetch_row($result2);

                                $average2 = $row2[2] < 0 ? '' : round(floatval($row2[2]), 2);
                                $totaltime2 = $row2[3] < 0 ? '' : round(floatval($row2[3]), 2);
                                echo "<tr>
                  <td>All</td>
                  <td>$row2[0]</td>
                  <td>$row2[1]</td>
                  <td>$average2</td>
                  <td>$totaltime2</td>
                  
                  </tr>";
                                $query4 = "SELECT k.species, u.name, u.email, k.average, k.num
                       FROM (SELECT r.taker_id AS id, (SUM(r.bids)/SUM(r.totaltime)*60) AS average, (SUM(r.totaltime)/60) AS num, c.species AS species
                             FROM request r, pet p, petcategory c
                             WHERE r.pets_id = p.pets_id AND p.pcat_id = c.pcat_id AND r.status = 'successful'
                             GROUP BY c.species, r.taker_id) AS k, pet_user u
                       WHERE u.user_id = k.id AND NOT EXISTS(SELECT *
                                                             FROM (SELECT (SUM(r1.bids)/SUM(r1.totaltime)*60) AS avg 
                                                                   FROM request r1, pet p1, petcategory c1 
                                                                   WHERE r1.pets_id = p1.pets_id AND p1.pcat_id = c1.pcat_id AND c1.species = k.species AND r1.status = 'successful'
                                                                   GROUP BY r1.taker_id) AS k1 
                                                             WHERE k.average < k1.avg);";

                                $result4 = pg_query($query4) or die('Query failed: ' . pg_last_error());

                                while ($row4 = pg_fetch_row($result4)) {
                                    $average4 = $row4[3] < 0 ? '' : round(floatval($row4[3]), 2);
                                    $totaltime4 = $row4[4] < 0 ? '' : round(floatval($row4[4]), 2);
                                    echo "
                    <tr>
                    <td>$row4[0]</td>
                    <td>$row4[1]</td>
                    <td>$row4[2]</td>
                    <td>$average4</td>
                    <td>$totaltime4</td>
                    </tr>";
                                };


                                pg_free_result($result2);
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="col-sm-6">
                            <div>
                                <h4>Takers who have taken care of all species of pets</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="table-vertical first-table">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th >Taker Name</th>
                                    <th >Average Bids / Hour</th>
                                    <th >Total Number of Hours Completed</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query3 = "SELECT u.name, (SUM(r1.bids)/SUM(r1.totaltime)*60) AS average, SUM(r1.totaltime)
                       FROM request r1, pet_user u
                       WHERE r1.taker_id = u.user_id AND r1.status = 'successful' AND NOT EXISTS (SELECT c1.species
                                                                                                  FROM petcategory c1
                                                                                                  WHERE NOT EXISTS (SELECT *
                                                                                                                    FROM request r2, pet p, petcategory c2
                                                                                                                    WHERE r2.taker_id = r1.taker_id
                                                                                                                    AND r2.pets_id = p.pets_id
                                                                                                                    AND p.pcat_id = c2.pcat_id
                                                                                                                    AND c2.species = c1.species
                                                                                                                    AND r2.status = 'successful'))
                       GROUP BY r1.taker_id, u.name
                       ORDER BY average DESC";

                                $result3 = pg_query($query3) or die('Query failed: ' . pg_last_error());
                                $flag = 0;


                                while ($row3 = pg_fetch_row($result3)) {
                                    $flag = 1;
                                    $average = $row3[1] < 0 ? '' : round(floatval($row3[1]), 2);
                                    $totaltime = $row3[2] < 0 ? '' : round(floatval($row3[1]), 2);
                                    echo "
                     <tr>
                    <td>$row3[0]</td>
                    <td>$average</td>
                    <td>$totaltime</td>
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
            </form>
        </div>
    </div>
</div>

</body>
