<!DOCTYPE html>
<head>
    <title>Request Info</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="./vendor/materialize/css/materialize.min.css">
    <script src="./vendor/materialize/js/materialize.min.js"></script>
    <style>

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            width: 100%;
            height: 500px;
            position: relative;
        }

        svg {
            width: 100%;
            height: 100%;
        }

        path.slice {
            stroke-width: 2px;
        }

        polyline {
            opacity: .3;
            stroke: black;
            stroke-width: 2px;
            fill: none;
        }

    </style>
</head>
<body>
<nav>
    <div class="nav-wrapper" style="background-color: #1976d2">
        <a href="index.php" class="brand-logo">PET OWNER</a>
        <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
            <li><a class="active" href="index.php">Home</a></li>
            <li><a href="pet_owner.php">Pet Owner</a></li>
            <li><a href="care_taker.php">Care Taker</a></li>
            <li><a href="pet.php">Pet</a></li>
            <li><a href="request.php">Request</a></li>
            <li><a href="index.php">Logout</a></li>
        </ul>
        <ul class="side-nav" id="mobile-demo">
            <li><a class="active" href="index.php">Home</a></li>
            <li><a href="pet_owner.php">Pet Owner</a></li>
            <li><a href="care_taker.php">Care Taker</a></li>
            <li><a href="pet.php">Pet</a></li>
            <li><a href="request.php">Request</a></li>
            <li><a href="index.php">Logout</a></li>
        </ul>
    </div>
</nav>
<h2>Supply your user id and enter</h2>
<ul>
    <form name="display" action="pet_owner.php" method="POST">
        <li>User ID:</li>
        <li><input type="number" name="userid"/></li>
        <li><input type="submit" name="submit"></li>
        <li><input type="submit" name="signup" value='Signup'/></li>
    </form>
</ul>
<?php
// Connect to the database. Please change the password in the following line accordingly
$db = pg_connect("host=localhost port=5432 dbname=PetCare user=postgres password=admin");
if (isset($_POST['signup'])) {
    echo "<ul><form name='signup' action='pet_owner.php' method='POST'>  
        <li>User Password:</li>  
        <li><input type='text' name='new_userpassword' /></li>   
        <li>User Name</li>  
        <li><input type='text' name='new_user_name' /></li>  
        <li>User Email:</li>  
        <li><input type='text' name='new_user_email' /></li>  
        <li>User Address</li>  
        <li><input type='text' name='new_user_address' /></li> 
        <li><input type='submit' name='create' value='Create User'/></li>  

        </form>  
        </ul>";
}
if (isset($_POST['create'])) {
    $result = pg_query($db, "INSERT INTO pet_user (name,password,email,address) VALUES ('$_POST[new_user_name]','$_POST[new_userpassword]','$_POST[new_user_email]','$_POST[new_user_address]')");


    if ($result) {
        echo "Sign up successfully!";
        sleep(10);
        echo "<script>window.location = 'pet_owner.php';</script>";
        exit;
    } else {
        die('Query failed: ' . pg_last_error());
        echo "Incorrect information";
    }

}


if (isset($_POST['submit'])) {
    $result = pg_query($db, "SELECT * FROM pet_user WHERE user_id = '$_POST[userid]'");   // Query template
    $row = pg_fetch_assoc($result);    // To store the result row
    echo "<ul><form name='update' action='pet_owner.php' method='POST' >  
        <li>User ID:</li>  
        <li><input type='number' name='userid_updated' value='$row[user_id]' /></li>  
        <li>User Name</li>  
        <li><input type='text' name='user_name_updated' value='$row[name]' /></li>  
        <li>User Email:</li>  
        <li><input type='text' name='user_email_updated' value='$row[email]' /></li>  
        <li>User Address</li>  
        <li><input type='text' name='user_address_updated' value='$row[address]' /></li> 
        <li><input type='submit' name='new' value='Update'/></li>
        <li><input type='submit' name='deleteuser' value='Delete'/></li>  
        </form>  
        </ul>";


    $query = "SELECT p.pets_id, c.name, c.size, c.age FROM pet_user u, pet p, petcategory c WHERE u.user_id = '$_POST[userid]' AND u.user_id = p.owner_id AND p.pcat_id = c.pcat_id";
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());


    while ($row = pg_fetch_row($result)) {
        echo "<ul><form name='update' action='pet_owner.php' method='POST' >
        <li>Your Pet ID:</li> 
        <li><input type='text' name='a_updated' value='$row[0]' /></li>
        <li>Your Pet Name:</li> 
        <li><input type='text' name='a_updated' value='$row[1]' /></li>
        <li>Your Pet Size:</li> 
        <li><input type='text' name='a_updated' value='$row[2]' /></li>
        <li>Your Pet Age:</li> 
        <li><input type='text' name='a_updated' value='$row[3]' /></li>
        <li><input type='submit' name='newpet' value='>< Creat one more pet! ><'/></li> 
        </form>  
        </ul>";
    }
}


if (isset($_POST['new'])) {    // Submit the update SQL command
    $address = pg_escape_string($_POST[user_address_updated]);
    $email = pg_escape_string($_POST[user_email_updated]);
    $result = pg_query($db, "UPDATE pet_user SET (name, email, address) = ('$_POST[user_name_updated]', '" . $email . "', '" . $address . "') WHERE user_id='$_POST[userid_updated]'");

    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Update failed!!";
    } else {
        echo "Update successful!";
    }
}

if (isset($_POST['newpet'])) {
    echo "<ul><form name='newpet' action='pet_owner.php' method='POST'>
        <li>Your User ID:</li>  
        <li><input type='text' name='userid' /></li>  
        <li>Your new pet size</li>  
        <li><input type='text' name='new_petsize' /></li>  
        <li>Your new pet age:</li>  
        <li><input type='text' name='new_petage' /></li>
        <li><input type='submit' name='createnewpet' value='Create Pet'/></li>  
        </form>  
        </ul>";
}


if (isset($_POST['createnewpet'])) {
    $result = pg_query($db, "INSERT INTO pet (owner_id, pcat_id) VALUES ('$_POST[userid]', (SELECT pcat_id FROM petcategory p WHERE p.size = '$_POST[new_petsize]' AND p.age = '$_POST[new_petage]'))");


    if ($result) {
        echo "Created successfully!";
        sleep(10);
        echo "<script>window.location = 'pet_owner.php';</script>";
        exit;
    } else {
        die('Query failed: ' . pg_last_error());
        echo "Incorrect information";
    }

}

if (isset($_POST['deleteuser'])) {    // Submit the delete pet SQL command
    $result = pg_query($db, "DELETE FROM pet_user  WHERE user_id ='$_POST[userid_updated]'");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Delete failed!!";
    } else {
        echo "Delete successful!";
    }
}

?>
</body>
</html>