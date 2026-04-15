<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "colegiolasalle";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE novedades MODIFY COLUMN materia_id INT(11) NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column materia_id changed to NULLable successfully.\n";
} else {
    echo "Error updating column: " . $conn->error . "\n";
}
$conn->close();
?>
