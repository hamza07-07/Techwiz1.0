<?php
session_start();
include("../config/db.php");

// ✅ Restrict access to owners & vets only
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["owner", "vet"])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['id'];

if (isset($_GET['id'])) {
    $record_id = (int) $_GET['id'];

    if ($role === "owner") {
        // ✅ Ensure this record belongs to the owner via pets
        $sql = "DELETE h FROM health_records h
                JOIN pets p ON h.pet_id = p.pet_id
                WHERE h.record_id=? AND p.owner_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $record_id, $user_id);
    } else {
        // ✅ Vets can delete any record
        $sql = "DELETE FROM health_records WHERE record_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $record_id);
    }

    mysqli_stmt_execute($stmt);
}

// ✅ Redirect back to correct dashboard
if ($role === "owner") {
    header("Location: owner_dashboard.php#health");
} else {
    header("Location: vet_dashboard.php#medical");
}
exit();
?>
