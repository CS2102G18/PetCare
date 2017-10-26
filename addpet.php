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
    <script type="text/javascript">
        $(document).ready(function () {
            $("#successmodal").modal('show');
        });
    </script>
</head>
<body>
<?php include "config/db-connection.php"; ?>
<nav class="navbar navbar-inverse navigation-bar navbar-fixed-top">
    <div class="container navbar-container">
        <div class="navbar-header pull-left"><a class="navbar-brand" href="userprofile.php"> PetCare</a></div>
        <div class="nav navbar-nav navbar-form">
            <div class="input-icon">
                <i class="glyphicon glyphicon-search search"></i>
                <input type="text" placeholder="Type to search..." class="form-control search-form" tabindex="1">
            </div>
        </div>
        <div class="collapse navbar-collapse pull-right">
            <ul class="nav navbar-nav">
                <li><a href="Alpha/request.php"> Send Request </a></li>
                <li><a href="history.php"> View History </a></li>
                <li><a href="logout.php"> Log Out </a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="content-container container">
    <div class="container">
        <h2>Add your pet</h2>
        <form action="addpet.php">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-2">
                        <h5>New Pet's Name</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="pet_name" type="text" class="form-control" placeholder="Pet Name" required="true">
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
                            $query = "SELECT * FROM util_species";
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
                            $query = "SELECT * FROM util_age";
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
                            $query = "SELECT * FROM util_size";
                            $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                            while ($row = pg_fetch_row($result)) {
                                echo "<option value='" . $row[0] . "'>" . $row[0] . "</option><br>";
                            }
                            pg_free_result($result);
                            ?>
                        </select>
                    </div>
                </div>

            <div class="row">
                    <div class="col-sm-2">
                        <h5>New Pet's Name</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="pet_name" class="form-control" required="true">
                            <option value="">Input Name</option>
                            
                        </input>
                    </div>
                </div>
             </div>
            <button type="submit" name="create" class="btn btn-default">Submit</button>
        </form>
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
    if (!$result) {
        echo "
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          <h4 class='modal-title'>Create Pet</h4>
                        </div>
                        <div class='modal-body'>
                          <h4>Creation failed!</h4>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                        </div>
                    </div>
                </div>
            </div>";
        die('Query failed: ' . pg_last_error());
    } else {
        echo " 
            <div id='successmodal' class='modal fade'>
                <div class='modal-dialog'><div class='modal-content'>
                    <div class='modal-header'>
                      <button type='button' class='close' data-dismiss='modal'>&times;</button>
                      <h4 class='modal-title'>Create Pet</h4>
                    </div>
                    <div class='modal-body'>
                      <p>Creation successful!</p>
                    </div>
                    <div class='modal-footer'>
                      <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                    </div>
                </div>
            </div>";
        pg_free_result($result);
        header("Location: userprofile.php");
    }
    exit();
}
?>
</body>