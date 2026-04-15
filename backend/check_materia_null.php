<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE novedades");
while($row = $res->fetch_assoc()) {
    if ($row['Field'] == 'materia_id') {
        echo "Materia ID: " . $row['Null'] . "\n";
    }
}
$conn->close();
?>
