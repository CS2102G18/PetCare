<?php include "config/db-connection.php"; ?>
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

if (isset($_GET["p_id"])) {
    $pet_id = (int)$_GET["p_id"];
    $query = "SELECT * FROM pet p WHERE p.pets_id = $pet_id;";
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    $row = pg_fetch_row($result);

    $pet_name = $row[3];
    $pet_species = pg_fetch_row(pg_query("SELECT species FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
    $pet_size = pg_fetch_row(pg_query("SELECT size FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
    $pet_age = pg_fetch_row(pg_query("SELECT age FROM petcategory WHERE pcat_id = " . $row[2] . ";"))[0];
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
            <h2>Edit your pet</h2>
            <form action="editpet.php">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Name</h5>
                        </div>
                        <div class="col-sm-8">
                            <input name="p_id" value="<?php echo $pet_id ?>" type='hidden'/>
                            <input name="pet_name" type="text" class="form-control"
                                   placeholder="<?php echo $pet_name ?>"
                                   value="<?php echo $pet_name ?>">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-2">
                            <h5>New Pet's Species</h5>
                        </div>
                        <div class="col-sm-8">
                            <select name="pet_species" class="form-control">
                                <option value="<?php echo $pet_species ?>"><?php echo $pet_species ?></option>
                                <?php
                                $query = "SELECT DISTINCT species FROM petcategory WHERE species <> '" . $pet_species . "';";
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
                            <select name="pet_age" class="form-control">
                                <option value="<?php echo $pet_age ?>"> <?php echo $pet_age ?> </option>
                                <?php
                                $query = "SELECT DISTINCT age FROM petcategory WHERE age <> '" . $pet_age . "';";
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
                            <select name="pet_size" class="form-control">
                                <option value="<?php echo $pet_size ?>"> <?php echo $pet_size ?> </option>
                                <?php
                                $query = "SELECT DISTINCT size FROM petcategory WHERE size <> '" . $pet_size . "';";
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
                        <button type="submit" name="update" class="btn btn-default">Submit</button>
                        <a class="btn btn-danger" role="button" href="owner.php">Cancel</a>
                    </div>
                    
            </form>
        </div>
    </div>
</div>
<?php
if (isset($_GET['update'])) {
    $pet_id = $_GET["p_id"];
    $pet_age = $_GET["pet_age"];
    $pet_size = $_GET["pet_size"];
    $pet_species = $_GET["pet_species"];
    $pcat_query = "SELECT pcat_id FROM petcategory WHERE age = '$pet_age'
                      AND size = '$pet_size'
                      AND species = '$pet_species';";
    $pcat_result = pg_query($pcat_query) or die('Query failed: a' . pg_last_error());
    $pcat_id = pg_fetch_row($pcat_result)[0];
    $pet_name = $_GET["pet_name"];
    $update_query = "UPDATE pet
                     SET pcat_id = $pcat_id, pet_name = '$pet_name'
                     WHERE pets_id = $pet_id;";
    $result = pg_query($update_query) or die('Query failed: b' . pg_last_error());
    if ($result) {
        pg_free_result($result);
        header("Location: owner.php");
        echo "<script>window.location = './owner.php';</script>";
    }
    exit();
}
?>
</body>
</html>
