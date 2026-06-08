<?php
// deleteuser.php
include '../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        $sql = "DELETE FROM usernamepass WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo "User deleted successfully!";
        } else {
            echo "Error deleting user: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "User ID is required.";
    }
}

$conn->close();
?>
