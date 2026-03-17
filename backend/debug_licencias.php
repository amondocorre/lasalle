<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'colegiolasalle';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

$res = $conn->query("SELECT rude, nombres, apellidos FROM estudiantes WHERE apellidos LIKE '%Mamani%'");
echo "ESTUDIANTES:\n";
while($row = $res->fetch_assoc()) {
    echo "RUDE: {$row['rude']} | Nombre: {$row['apellidos']}, {$row['nombres']}\n";
    
    $rude = $row['rude'];
    $res2 = $conn->query("SELECT * FROM licencias WHERE rude = '$rude'");
    echo "  - LICENCIAS: " . $res2->num_rows . "\n";
    while($lic = $res2->fetch_assoc()) {
        echo "    * ID: {$lic['id']} | Motivo: {$lic['motivo']} | Fecha: {$lic['fecha_inicio']}\n";
    }
}
$conn->close();
