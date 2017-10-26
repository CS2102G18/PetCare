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
        <a href="index.php" class="brand-logo">PET INFO</a>
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


<h4>Supply your pet id and enter</h4>


<form name="display" action="pet.php" method="POST">
    <li>Search from Pet ID:</li>
    <li><input type="number" name="petid"/></li>
    <li><input type="submit" name="submit"></li>
    <li><input type="submit" name="create" value='Create New Pet'/></li>

</form>



<?php include "config/db-connection.php"; ?>
<?php

if (isset($_POST['submit'])) {
    $result = pg_query($db, "SELECT * FROM pet WHERE pets_id = '$_POST[petid]'");   // Query template
    $row = pg_fetch_assoc($result);    // To store the result row
    echo "<ul><form name='update' action='pet.php' method='POST' >  
        <li>Pet ID:</li>  
        <li><input type='number' name='pets_id_updated' value='$row[pets_id]' /></li>  
        <li>Owner ID</li>  
        <li><input type='number' name='owner_id_updated' value='$row[owner_id]' /></li>  
        <li>Pet Category:</li>  
        <li><input type='number' name='pcat_id_updated' value='$row[pcat_id]' /></li>  
        <li><input type='submit' name='updatepet' value='Update Pet'/></li>
        <li><input type='submit' name='deletepet' value='Delete Pet'/></li>


        </form>
        </ul>";

}

if (isset($_POST['updatepet'])) {    // Submit the update pet SQL command
    $result = pg_query($db, "UPDATE pet SET (owner_id, pcat_id) = ('$_POST[owner_id_updated]', '$_POST[pcat_id_updated]') WHERE pets_id ='$_POST[pets_id_updated]'");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Update failed!!";
    } else {
        echo "Update successful!";
    }
}

if (isset($_POST['deletepet'])) {    // Submit the delete pet SQL command
    $result = pg_query($db, "DELETE FROM pet  WHERE pets_id ='$_POST[pets_id_updated]'");
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