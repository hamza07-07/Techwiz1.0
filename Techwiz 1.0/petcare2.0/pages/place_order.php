<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];

// Get cart items
$sql = "SELECT c.product_id, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.owner_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: owner_dashboard.php#cart");
    exit();
}

mysqli_begin_transaction($conn);

try {
    // Create order
    $sql = "INSERT INTO orders (owner_id, status, order_date) VALUES (?, 'Pending', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

   // Insert items
$sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    while ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_bind_param($stmt, "iiid", $order_id, $row['product_id'], $row['quantity'], $row['price']);
        mysqli_stmt_execute($stmt);
    }
} else {
    throw new Exception("Failed to prepare order_items insert: " . mysqli_error($conn));
}


    // Clear cart
    $sql = "DELETE FROM cart WHERE owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);

    header("Location: owner_dashboard.php#orders");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "❌ Error placing order: " . $e->getMessage();
}
?>
