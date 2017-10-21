<?php
session_start();
$db = pg_connect("host=localhost port=5432 dbname=PetCare user=postgres password=12345678");

$message="";
if(!empty($_POST["login"])) {
    $result = pg_query($db,"SELECT * FROM pet_user WHERE email='" . $_POST["user_name"] . "' and password = '". $_POST["password"]."'");
    $row = pg_fetch_assoc($result);

    if(is_array($row)) {
        $_SESSION["user_id"] = $row['user_id'];
    } else {
        $message = "Invalid Username or Password!";
    }
}
if(!empty($_POST["logout"])) {
    $_SESSION["user_id"] = "";
    session_destroy();
}
?>
<html>
<head>
    <title>User Login</title>
    <style>
        #frmLogin {
            padding: 20px 60px;
            background: #B6E0FF;
            color: #555;
            display: inline-block;
            border-radius: 4px;
        }
        .field-group {
            margin:15px 0px;
        }
        .input-field {
            padding: 8px;width: 200px;
            border: #A3C3E7 1px solid;
            border-radius: 4px;
        }
        .form-submit-button {
            background: #65C370;
            border: 0;
            padding: 8px 20px;
            border-radius: 4px;
            color: #FFF;
            text-transform: uppercase;
        }
        .member-dashboard {
            padding: 40px;
            background: #D2EDD5;
            color: #555;
            border-radius: 4px;
            display: inline-block;
            text-align:center;
        }
        .logout-button {
            color: #09F;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0px;
            cursor: pointer;
        }
        .error-message {
            text-align:center;
            color:#FF0000;
        }
        .demo-content label{
            width:auto;
        }
    </style>
</head>
<body>
<div>
    <div style="display:block;margin:0px auto;">
        <?php if(empty($_SESSION["user_id"])) { ?>
            <form action="" method="post" id="frmLogin">
                <div class="error-message"><?php if(isset($message)) { echo $message; } ?></div>
                <div class="field-group">
                    <div><label for="login">Username</label></div>
                    <div><input name="user_name" type="text" class="input-field"></div>
                </div>
                <div class="field-group">
                    <div><label for="password">Password</label></div>
                    <div><input name="password" type="password" class="input-field"> </div>
                </div>
                <div class="field-group">
                    <div><input type="submit" name="login" value="Login" class="form-submit-button"></span></div>
                </div>
            </form>
            <?php
        } else {
        $result = pg_query($db,"SELECT * FROM pet_user WHERE email='" . $_POST["user_name"] . "' and password = '". $_POST["password"]."'");
        $row = pg_fetch_assoc($result);
        ?>
        <form action="" method="post" id="frmLogout">
            <div class="member-dashboard">Welcome <?php echo ucwords($row['display_name']); ?>, You have successfully logged in!<br>
                Click to <input type="submit" name="logout" value="Logout" class="logout-button">.</div>
        </form>
    </div>
</div>
<?php } ?>
</body></html>