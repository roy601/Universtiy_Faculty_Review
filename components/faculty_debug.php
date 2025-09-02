<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../DBconnect.php";
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Faculty Debug Minimal</title>
</head>
<body>
<h2>Faculty Debug Minimal</h2>

<?php
$sql = "SELECT id, name, designation FROM faculty";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("❌ Query failed: " . mysqli_error($conn));
}

$rows = mysqli_num_rows($result);
echo "<p>✅ Faculty rows found: <strong>$rows</strong></p>";

if ($rows > 0) {
    echo "<table border='1' cellpadding='6'><tr><th>ID</th><th>Name</th><th>Designation</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['designation']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No faculty rows found.</p>";
}
?>

</body>
</html>

