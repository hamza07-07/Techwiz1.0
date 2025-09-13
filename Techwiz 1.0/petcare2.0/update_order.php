<?php
session_start();

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("config/db.php");  // ✅ fixed path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    // Validate status
    $valid_status = ['Pending', 'Shipped', 'Completed', 'Cancelled'];
    if (!in_array($status, $valid_status)) {
        die("❌ Invalid status value.");
    }

    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Order #$order_id status updated to $status.";
        } else {
            $_SESSION['error'] = "❌ Failed to update order status.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ SQL Error: " . $conn->error;
    }

    // Redirect back to Manage Owners
    header("Location: manage_owners.php#orders");
    exit();
} else {
    header("Location: manage_owners.php#orders");
    exit();
}
