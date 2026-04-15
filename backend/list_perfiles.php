<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("SELECT * FROM perfiles");
if ($res) {
    echo "Perfiles encontrados:\n";
    while($row = $res->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Nombre: " . $row['nombre'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
