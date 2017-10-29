<?php
$dbconn = pg_connect("
    host=127.0.0.1
    port=5432
    dbname=PetCare
    user=postgres
    password=12345
    ")
    or die('Could not connect: ' . pg_last_error());
?>
