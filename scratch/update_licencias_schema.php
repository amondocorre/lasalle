<?php
$mysqli = new mysqli("localhost", "root", "", "colegiolasalle");
if ($mysqli->connect_error) die("Connection failed: " . $mysqli->connect_error);

// 1. Add column if it doesn't exist
$res = $mysqli->query("SHOW COLUMNS FROM licencias LIKE 'fecha_fin'");
if ($res->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE licencias ADD COLUMN fecha_fin DATE AFTER dias")) {
        echo "Column fecha_fin added successfully.\n";
    } else {
        echo "Error adding column: " . $mysqli->error . "\n";
    }
} else {
    echo "Column fecha_fin already exists.\n";
}

// 2. Function to calculate business days end date
function calculateEndDate($startDate, $businessDays) {
    $date = new DateTime($startDate);
    $addedDays = 0;
    
    // The start date itself counts as the first business day
    // Check if start date is weekend
    $dow = (int)$date->format('w'); // 0 (Sun) to 6 (Sat)
    if ($dow == 0 || $dow == 6) {
        // If it starts on weekend, move to next Monday and start counting
        while ($dow == 0 || $dow == 6) {
            $date->modify('+1 day');
            $dow = (int)$date->format('w');
        }
    }
    
    $addedDays = 1; // Count today/first Monday
    while ($addedDays < $businessDays) {
        $date->modify('+1 day');
        $dow = (int)$date->format('w');
        if ($dow != 0 && $dow != 6) {
            $addedDays++;
        }
    }
    
    return $date->format('Y-m-d');
}

// 3. Update existing records
$res = $mysqli->query("SELECT id, fecha_inicio, dias FROM licencias");
while ($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $fin = calculateEndDate($row['fecha_inicio'], $row['dias']);
    $mysqli->query("UPDATE licencias SET fecha_fin = '$fin' WHERE id = $id");
    echo "Updated license $id: End date set to $fin\n";
}

$mysqli->close();
?>
