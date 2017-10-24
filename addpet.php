<?php
// Start the session
session_start();
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
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
    <link rel="stylesheet" type="text/css" href="./vendor/css/new-task-styling.css">
    <link rel="stylesheet" href="./vendor/css/bootstrap-datetimepicker.min.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="./vendor/js/jquery.ns-autogrow.min.js"></script>
    <script src="./vendor/js/bootstrap-datetimepicker.min.js"></script>
    <script src="./vendor/js/find-task.js"></script>
</head>
<body>
<?php include "config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href=""> PetCare</a></div>
        <div class="nav navbar-nav navbar-form">
            <div class="input-icon">
                <i class="glyphicon glyphicon-search search"></i>
                <input type="text" placeholder="Type to search..." class="form-control search-form" tabindex="1">
            </div>
        </div>
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
    <div class="container">
        <h2>Add your pet</h2>
        <form action="userprofile.php">
            <div class="form-group">
                <label class="col-sm-3 control-label">Pet Category</label>
                <div class="col-sm-6">
                    <select name="category" class="form-control" required="true">
                        <option value="">Select Category</option>
                        <?php
                        $query = "SELECT * FROM petcategory pc ORDER BY pc.pcat_id";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

                        while ($row = pg_fetch_row($result)) {
                            echo "<option value=\"" . $row[0] . "\">" . $row[1] . " " . $row[2] . " " . $row[3] . "</option><br>";
                        }
                        pg_free_result($result);
                        ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</div>
<?php
if (isset($_GET["create"])) {
    $new_pcat = $_GET["category"];
    $result = pg_query($db, "INSERT INTO pet(owner_id, pcat_id) VALUES ('$user_id','$new_pcat');");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Creation failed!!";
    } else {
        echo "Creation successful!";
    }
    exit();
}
?>
</body>
