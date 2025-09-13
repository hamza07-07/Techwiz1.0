<?php


// ✅ Restrict access to vets only
session_start();

// ✅ Restrict access to vets only
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["vet", "admin"])) {
    header("Location: login.php");
    exit();
}


// ✅ Correct path for DB connection (since this file is in root)
include("config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = (int) $_POST['appointment_id'];
    $status = $_POST['status'];

    // ✅ Allow only valid statuses
    $allowed = ["Pending", "Approved", "Completed", "Cancelled"];
    if (!in_array($status, $allowed)) {
        die("❌ Invalid status value.");
    }

    // ✅ Update appointment
    $sql = "UPDATE appointments SET status=? WHERE appointment_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $appointment_id);

    if ($stmt->execute()) {
        // Redirect back with success flag
        header("Location: pages/vet_dashboard.php#appointments");
        exit();
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    // If accessed directly
    header("Location: pages/vet_dashboard.php#appointments");
    exit();
}
