<?php
// get_students.php
header('Content-Type: application/json');
include '../Database/db.php';

$sql = "SELECT ID as id, UserName as username, Email as email, course, year, section
        FROM usernamepass
        WHERE role = 'student'
        ORDER BY course, year, section, UserName";

$result = $conn->query($sql);
$rows = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

echo json_encode($rows);
$conn->close();
?>
