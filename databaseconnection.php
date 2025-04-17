<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbase = "library_inventory";

$con = new mysqli($servername, $username, $password, $dbase);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
