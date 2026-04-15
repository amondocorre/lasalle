<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("SHOW CREATE TABLE novedades");
$row = $res->fetch_assoc();
echo $row['Create Table'];
$conn->close();
?>
