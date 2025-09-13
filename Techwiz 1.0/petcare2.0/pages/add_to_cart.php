<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($quantity < 1) $quantity = 1;

// Check if product already in cart
$sql = "SELECT cart_id, quantity FROM cart WHERE owner_id=? AND product_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $owner_id, $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Update quantity
    $new_quantity = $row['quantity'] + $quantity;
    $sql = "UPDATE cart SET quantity=? WHERE cart_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $row['cart_id']);
    mysqli_stmt_execute($stmt);
} else {
    // Insert new
    $sql = "INSERT INTO cart (owner_id, product_id, quantity) VALUES (?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $owner_id, $product_id, $quantity);
    mysqli_stmt_execute($stmt);
}

header("Location: owner_dashboard.php#cart");
exit();
?>
