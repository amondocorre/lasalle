<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'colegiolasalle';
$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("SELECT ci FROM estudiantes WHERE rude = '2024001001'");
print_r($res->fetch_assoc());
$conn->close();
