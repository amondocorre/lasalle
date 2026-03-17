<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'colegiolasalle';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

$res = $conn->query("SELECT rude, nombres, apellidos FROM estudiantes");
while($row = $res->fetch_assoc()) {
    $rude = $row['rude'];
    $res2 = $conn->query("SELECT COUNT(*) as total FROM licencias WHERE rude = '$rude'");
    $total = $res2->fetch_assoc()['total'];
    if($total > 0) {
        echo "RUDE: $rude | Name: {$row['apellidos']}, {$row['nombres']} | Licencias: $total\n";
    }
}
$conn->close();
