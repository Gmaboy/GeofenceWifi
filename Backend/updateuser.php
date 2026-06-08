<?php
// updateuser.php
include '../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'], $_POST['email'], $_POST['password'])) {
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE usernamepass SET Email=?, Password=? WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $hashedPassword, $user_id);

        if ($stmt->execute()) {
            echo "User updated successfully!";
        } else {
            echo "Error updating user: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Missing required fields.";
    }
}

$conn->close();
?>
