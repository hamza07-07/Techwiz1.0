<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int) $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $sql = "INSERT INTO feedback (owner_id, rating, comment) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $owner_id, $rating, $comment);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: owner_dashboard.php#feedback");
        exit();
    } else {
        echo "❌ Error: " . mysqli_error($conn);
    }
}
?>
