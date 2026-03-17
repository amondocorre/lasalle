<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'colegiolasalle';
$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("SELECT * FROM licencias WHERE rude = '2024001001'");
$lic = $res->fetch_assoc();
echo "KEYS IN DB RESULT:\n";
print_r(array_keys($lic));
$conn->close();
