<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'colegiolasalle';
$conn = new mysqli($host, $user, $pass, $db);
$res = $conn->query("DESCRIBE licencias");
while($row = $res->fetch_assoc()) echo $row['Field']." ".$row['Type']."\n";
echo "---ESTUDIANTES---\n";
$res = $conn->query("DESCRIBE estudiantes");
while($row = $res->fetch_assoc()) echo $row['Field']." ".$row['Type']."\n";
$conn->close();
