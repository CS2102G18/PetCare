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

    <div class="container">
        <form action="addpet.php">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-2">
                        <h5>Search Pet's Name</h5>
                    </div>
                    <div class="col-sm-8">
                        <input name="pet_name" type="text" class="form-control" placeholder="Pet Name"
                               required="true">
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
                            $query = "SELECT DISTINCT species FROM petcategory";
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
                            $query = "SELECT DISTINCT age FROM petcategory";
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
                            $query = "SELECT DISTINCT size FROM petcategory";
                            $result = pg_query($query) or die('Query failed: ' . pg_last_error());
                            while ($row = pg_fetch_row($result)) {
                                echo "<option value='" . $row[0] . "'>" . $row[0] . "</option><br>";
                            }
                            pg_free_result($result);
                            ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="create" class="btn btn-default">Submit</button>
        </form>
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
        die('Query failed: ' . pg_last_error());
    } else {
        pg_free_result($result);
        header("Location: owner.php");
        echo "<script>window.location = './owner.php';</script>";
    }
    exit();
}
?>