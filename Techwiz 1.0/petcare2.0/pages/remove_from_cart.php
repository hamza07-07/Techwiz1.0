<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];
if (isset($_GET['id'])) {
    $cart_id = (int) $_GET['id'];
    $sql = "DELETE FROM cart WHERE cart_id=? AND owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $owner_id);
    mysqli_stmt_execute($stmt);
}

header("Location: owner_dashboard.php#cart");
exit();
