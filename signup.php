<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tasource - Making task sourcing simple</title>
    <link rel="stylesheet" type="text/css" href="./vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./vendor/css/style.css">
    <link rel="stylesheet" type="text/css" href="./vendor/css/login-styling.css">

    <script src="./vendor/js/jquery-3.2.0.min.js"></script>
    <script src="./vendor/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<!-- include php -->
<?php include "config/db-connection.php"; ?>

<div class="main-container">
    <div class="container">
        <div class="wrapper">
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-8 panel panel-default ogin-panel">

                <!-- heading -->
                <div class="panel-title login-panelheading">
                    <h4>Welcome</h4>
                    <h5 class="login-panel-subtitle">Please sign up here.</h5>
                </div>

                <!-- body -->
                <div class="panel-body">

                    <!-- login form -->
                    <form class="login-form" action='signup.php' method='POST'>

                        <!--  name -->
                        <div class="row name-input-row">
                            <div class="col-lg-5 col-sm-5 name-label">
                                <h5>Name</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 name-input">
                                <input class="form-control" name="name" type="name" required></input>
                            </div>
                        </div>


                        <!-- email -->
                        <div class="row email-input-row">
                            <div class="col-lg-5 col-sm-5 email-label">
                                <h5>Email</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 email-input">
                                <input class="form-control" name="email" type="email" required></input>
                            </div>
                        </div>

                        <!-- password -->
                        <div class="row password-input-row">
                            <div class="col-lg-5 col-sm-5 password-label">
                                <h5>Password</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 password-input">
                                <input class="form-control" name="password" type="password" pattern="\w{6,16}" title="Must use 6-16 characters" required></input>
                            </div>
                        </div>

                        <!-- address -->
                        <div class="row address-input-row">
                            <div class="col-lg-5 col-sm-5 address-label">
                                <h5>address</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 address-input">
                                <input class="form-control" name="address" type="address" required></input>
                            </div>
                        </div>

                        <!-- login button -->
                        <div class="row login-button-wrapper">
                            <div class="col-lg-6 col-md-6 col-xs-12">
                                <button type="submit" name="signup" class="btn btn-large btn-success login-button">Sign up</button>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xs-12">
                                <button type="button" class="btn btn-large btn-primary login-button" onclick="window.location='login.php'">Login</button>
                            </div>
                        </div>
                    </form>

                    <?php if(isset($_POST['signup'])) {
                        $name = pg_escape_string($_POST['name']);
                        $email = pg_escape_string($_POST['email']);
                        $password = pg_escape_string($_POST['password']);
                        $address = pg_escape_string($_POST['address']);


                        $query = "INSERT INTO pet_user (name, email, password, address) VALUES ('" . $name . "', '" . $email . "', '" . $password . "','" . $address . "' );";

                        $result = pg_query($query) or die('Add user failed: ' . pg_last_error());

                        if ($result) {
                            $_SESSION["user_id"] = $row[0];
                            echo "Sign up successfully!";
                            sleep(1);
                            echo "<script>window.location = 'pet_owner.php';</script>";
                            exit;
                        } else {
                            echo "Incorrect information";
                        }

                        pg_free_result($result);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>