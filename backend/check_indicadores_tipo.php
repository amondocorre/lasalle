<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE novedad_indicadores");
while($row = $res->fetch_assoc()) {
    if ($row['Field'] == 'tipo') {
        echo "Tipo: " . $row['Type'] . "\n";
    }
}
$conn->close();
?>
