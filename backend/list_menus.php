<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
$res = $mysqli->query("SELECT id, label, path FROM menus ORDER BY id ASC");
echo "<table border='1'><tr><th>ID</th><th>Label</th><th>Path (DB)</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['label']}</td><td>{$row['path']}</td></tr>";
}
echo "</table>";
