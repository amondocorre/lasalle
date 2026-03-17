<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'colegiolasalle';
$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("SELECT rude, LENGTH(rude) as len FROM estudiantes WHERE rude LIKE '%2024001001%'");
while($row = $res->fetch_assoc()) {
    echo "RUDE: '{$row['rude']}' | Len: {$row['len']}\n";
}
$conn->close();
