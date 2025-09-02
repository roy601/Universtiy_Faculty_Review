<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../DBconnect.php";

echo "✅ Connected to database<br>";

$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("❌ Query failed: " . mysqli_error($conn));
}

echo "✅ Tables in DB:<br>";
while ($row = mysqli_fetch_array($result)) {
    echo "- " . htmlspecialchars($row[0]) . "<br>";
}
