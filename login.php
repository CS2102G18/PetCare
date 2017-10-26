<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>PetCare</title>
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
                    <h5 class="login-panel-subtitle">Please login here.</h5>
                </div>

                <!-- body -->
                <div class="panel-body">

                    <!-- login form -->
                    <form class="login-form">

                        <!-- email -->
                        <div class="row email-input-row">
                            <div class="col-lg-5 col-sm-5 email-label">
                                <h5>Email</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 email-input">
                                <input class="form-control" name="email" type="email"></input>
                            </div>
                        </div>

                        <!-- password -->
                        <div class="row password-input-row">
                            <div class="col-lg-5 col-sm-5 password-label">
                                <h5>Password</h5>
                            </div>
                            <div class="col-lg-7 col-sm-7 password-input">
                                <input class="form-control" name="password" type="password"></input>
                            </div>
                        </div>

                        <!-- login button -->
                        <div class="row login-button-wrapper">
                            <div class="col-lg-6 col-md-6 col-xs-12">
                                <button type="submit" name="login" class="btn btn-large btn-success login-button">Sign
                                    in
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xs-12">
                                <button type="button" class="btn btn-large btn-primary login-button"
                                        onclick="window.location='signup.php'">Sign Up
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (isset($_GET['login'])) {
                        $email = $_GET['email'];
                        $query = "SELECT u.user_id
                                      FROM pet_user u
                                      WHERE u.email = '" . $email . "'
                                        AND u.password = '" . $_GET['password'] . "';";
                        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

                        $row = pg_fetch_row($result);
                        if ($row) {
                            $user_id = $row[0];
                            $_SESSION["user_email"] = $email;
                            $_SESSION["user_id"] = $user_id;
                            header("Location: owner.php");
                            exit;
                        } else {
                            echo "Incorrect email or password";
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