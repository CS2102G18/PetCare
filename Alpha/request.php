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
        <a href="index.php" class="brand-logo">REQUEST</a>
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

<h4>Supply Request ID and enter</h4>
<ul>
    <form name="display" action="request.php" method="POST">
        <li>Request ID:</li>
        <li><input type="text" name="request_id"/></li>
        <li><input type="submit" name="submit"/></li>
        <li><input type="submit" name="create" value='Create New Request'/></li>

    </form>
</ul>
<?php
// Connect to the database. Please change the password in the following line accordingly
$db = pg_connect("host=localhost port=5432 dbname=PetCare user=postgres password=admin");

if (isset($_POST['submit'])) {
    $result = pg_query($db, "SELECT * FROM request WHERE request_id = '$_POST[request_id]'");        // Query template
    $row = pg_fetch_assoc($result);        // To store the result row
    echo "<ul><form name='update' action='request.php' method='POST' >

      <li>Request ID:</li>  
      <li><input type='text' name='requestid_updated' value='$row[request_id]' /></li>  


        <li>Owner ID:</li>  
        <li><input type='text' name='onwerid_updated' value='$row[owner_id]' /></li>  
      <li>Care Begins:</li>  
      <li><input type='text' name='care_begin_updated' value='$row[care_begin]' /></li>  
      <li>Care Ends:</li>  
      <li><input type='text' name='care_end_updated' value='$row[care_end]' /></li>
      <li>Kinds of work:</li>  
      <li><input type='text' name='kow_updated' value='$row[kind_of_work]' /></li>
      <li>Bids:</li>  
      <li><input type='text' name='bids_updated' value='$row[bids]' /></li>
      <li>Pet ID:</li>  
      <li><input type='text' name='petid_updated' value='$row[pets_id]' /></li>
      <li>Status:</li>  
      <li><input type='text' name='status_updated' value='$row[status]' /></li>
      <li><input type='submit' name='updatereq' value='Update Request'/></li>
      <li><input type='submit' name='deletereq' value='Delete Request'/></li>
        </form>  
        </ul>";
}

if (isset($_POST['create'])) {    // Submit the delete pet SQL command
    echo "<ul><form name='signup' action='request.php' method='POST'>  
        
      <li>Owner ID:</li>  
      <li><input type='text' name='onwerid_new'  /></li>  
      <li>Care Begins:</li>  
      <li><input type='text' name='care_begin_new'  /></li>  
      <li>Care Ends:</li>  
      <li><input type='text' name='care_end_new'/></li>
      <li>Kinds of work:</li>  
      <li><input type='text' name='kow_new'  /></li>
      <li>Bids:</li>  
      <li><input type='text' name='bids_new'  /></li>
      <li>Pet ID:</li>  
      <li><input type='text' name='petid_new' /></li>
      <li>Status:</li>  
      <li><input type='text' name='status_new' /></li>


        <li><input type='submit' name='createreq' value='Create Request'/></li>  

        </form>  
        </ul>";

}


if (isset($_POST['createreq'])) {
    $result = pg_query($db, "
      INSERT INTO request (owner_id, care_begin, care_end, kind_of_work, bids, pets_id, status) VALUES ( '$_POST[onwerid_new]',, '$_POST[care_begin_new]', '$_POST[care_end_new]', '$_POST[kow_new]', '$_POST[bids_new]', '$_POST[petid_new]', '$_POST[status_new]');
      ");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Creation failed!!";
    } else {
        echo "Creation successful!";
    }

}


if (isset($_POST['updatereq'])) {    // Submit the update request SQL command
    $result = pg_query($db, "UPDATE request SET (owner_id, care_begin, care_end, kind_of_work, bids, pets_id, status) = ('$_POST[onwerid_updated]', '$_POST[care_begin_updated]', '$_POST[care_end_updated]','$_POST[kow_updated]','$_POST[bids_updated]','$_POST[petid_updated]','$_POST[status_updated]') WHERE request_id =  '$_POST[requestid_updated]'");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Update failed!!";
    } else {
        echo "Update successful!";
    }
}

if (isset($_POST['deletereq'])) {    // Submit the delete pet SQL command
    $result = pg_query($db, "DELETE FROM request  WHERE request_id ='$_POST[requestid_updated]'");
    if (!$result) {
        die('Query failed: ' . pg_last_error());
        echo "Delete failed!!";
    } else {
        echo "Delete successful!";
    }
}


?>
</body>
