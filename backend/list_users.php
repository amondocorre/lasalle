<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'colegiolasalle';

$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("SELECT id, username, password, activo FROM usuarios");
echo "LISTA DE USUARIOS EN DB:\n";
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | User: {$row['username']} | Activo: {$row['activo']} | Hash: " . substr($row['password'], 0, 15) . "...\n";
}
$conn->close();
?>
