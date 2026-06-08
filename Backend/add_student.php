<?php
// add_student.php
header('Content-Type: application/json');
include '../Database/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$username = $conn->real_escape_string($data['username']);
$email    = $conn->real_escape_string($data['email']);
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$course   = $conn->real_escape_string($data['course'] ?? 'BSIT');
$year     = (int)($data['year'] ?? 1);
$section  = $conn->real_escape_string($data['section'] ?? '');
$role     = 'student';

// Check duplicate email
$check = $conn->query("SELECT ID FROM usernamepass WHERE Email='$email' LIMIT 1");
if ($check && $check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists.']);
    exit;
}

$sql = "INSERT INTO usernamepass (UserName, Email, Password, role, course, year, section)
        VALUES ('$username', '$email', '$password', '$role', '$course', $year, '$section')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
