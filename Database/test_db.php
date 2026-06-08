<?php
include 'db.php';
if ($conn->connect_error) {
    echo "FAILED: " . $conn->connect_error;
} else {
    echo "Connected OK — Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0];
}
?>