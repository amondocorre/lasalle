<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'colegiolasalle';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

echo "TABLA LICENCIAS:\n";
$res = $conn->query("DESCRIBE licencias");
while($row = $res->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}

echo "\nTABLA ESTUDIANTES:\n";
$res = $conn->query("DESCRIBE estudiantes");
while($row = $res->fetch_assoc()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']}\n";
}
$conn->close();
