<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("./config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $target = "assets/products/" . basename($image);

    $sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sds", $name, $price, $image);

    if ($stmt->execute()) {
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        header("Location: manage_owners.php#products");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #0d6efd, #6a11cb);
      min-height: 100vh;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
    }
    .card {
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 500px;
    }
    .card h3 {
      font-weight: 700;
      margin-bottom: 20px;
      text-align: center;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    label {
      font-weight: 500;
      color: #fff;
    }
    .form-control {
      background: rgba(255,255,255,0.9) !important;
      color: #000 !important;
      border: none !important;
    }
    .btn-primary {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      border: none;
      font-weight: 500;
      color: #fff;
    }
    .btn-secondary {
      background: #6c757d;
      border: none;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="card">
    <h3><i class="fa-solid fa-box"></i> Add Product</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Price ($)</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control" required>
      </div>
      <div class="d-flex justify-content-between">
        <a href="manage_owners.php#products" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Product</button>
      </div>
    </form>
  </div>
</body>
</html>
