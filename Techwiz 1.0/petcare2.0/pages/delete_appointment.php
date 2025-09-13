<?php
session_start();

// Only owners can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];

if (isset($_GET['id'])) {
    $appointment_id = (int) $_GET['id'];

    // Ensure appointment belongs to this owner
    $sql = "SELECT * FROM appointments WHERE appointment_id=? AND owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        // Instead of deleting, mark as "Cancelled"
        $sql = "UPDATE appointments SET status='Cancelled' WHERE appointment_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $appointment_id);
        mysqli_stmt_execute($stmt);
    }
}

// Redirect back
header("Location: owner_dashboard.php#appointments");
exit();
?>
