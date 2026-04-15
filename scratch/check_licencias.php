<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
$res = $mysqli->query("DESCRIBE licencias");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
$mysqli->close();
?>
