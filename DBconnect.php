<?php
$servername="localhost";
$username="root";
$password="";
$dbname="fr";

$conn = mysqli_connect($servername, $username, $password, $dbname);
// $conn = new mysqli($servername, $username, $password);
if($conn->connect_error)
{
	die("Connection Failed!: " . $conn->connect_error);
}

?>