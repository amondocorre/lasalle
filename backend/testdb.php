<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli('localhost', 'root', '', 'colegiolasalle');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "SELECT * FROM perfil_menu WHERE perfil_id = 2";
$result = $mysqli->query($sql);
if (!$result) die("Query failed: " . $mysqli->error);
while($row = $result->fetch_assoc()) {
    print_r($row);
}
