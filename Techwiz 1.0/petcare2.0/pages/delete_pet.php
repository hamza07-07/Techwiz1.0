<?php
session_start();

include("../config/db.php");

// Ensure user is logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $pet_id = (int) $_GET['id'];

    if ($_SESSION['role'] === "owner") {
        // ✅ Owner can delete only their own pets
        $owner_id = $_SESSION['id'];
        $sql = "DELETE FROM pets WHERE pet_id=? AND owner_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $pet_id, $owner_id);

    } elseif ($_SESSION['role'] === "shelter" || $_SESSION['role'] === "admin") {
        // ✅ Shelter & Admin can delete ANY pet
        $sql = "DELETE FROM pets WHERE pet_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $pet_id);

    } else {
        // ❌ Unauthorized role
        header("Location: ../login.php");
        exit();
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ✅ Redirect based on role
if ($_SESSION['role'] === "owner") {
    header("Location: owner_dashboard.php#pets");
} elseif ($_SESSION['role'] === "shelter") {
    header("Location: shelter_dashboard.php#pets");
} elseif ($_SESSION['role'] === "admin") {
    header("Location: ../manage_shelters.php"); // Or admin pet management page
} else {
    header("Location: ../login.php");
}
exit();
?>
