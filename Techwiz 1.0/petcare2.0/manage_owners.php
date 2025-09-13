<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("./config/db.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Owners - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg,#0d6efd,#6a11cb);
      color: #fff;
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .navbar {
      background: rgba(0,0,0,0.85);
    }
    .navbar-brand {
      font-weight: 700;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .wrapper {
      flex: 1;
      display: flex;
      flex-direction: row;
    }
    .sidebar {
      width: 260px;
      background: rgba(0,0,0,0.6);
      padding: 20px;
    }
    .sidebar a {
      display: block;
      color: #fff;
      padding: 10px;
      margin-bottom: 8px;
      text-decoration: none;
      border-radius: 8px;
    }
    .sidebar a.active,
    .sidebar a:hover {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      color: #fff;
    }
    .content {
      flex: 1;
      padding: 20px;
      overflow: auto;
    }
    .section {
      background: rgba(0,0,0,0.35);
      padding: 18px;
      border-radius: 12px;
      margin-bottom: 18px;
    }
    .table thead th { color: #000; }
    .table tbody td { color: #000; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark px-3">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div>
      <a class="navbar-brand"><i class="fa-solid fa-shield-dog"></i> Admin Dashboard</a>
      <span class="ms-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
    </div>
    <div>
      <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
  </div>
</nav>

<div class="wrapper">
  <!-- Sidebar -->
  <div class="sidebar">
    <a href="admin_dashboard.php">Overview</a>
    <a href="#users" class="active"><i class="fa-solid fa-users"></i> Owners</a>
    <a href="manage_vets.php"><i class="fa-solid fa-user-doctor"></i> Vets</a>
    <a href="manage_shelters.php"><i class="fa-solid fa-warehouse"></i> Shelters</a>
  </div>

  <!-- Main Content -->
  <div class="content">

    <!-- Manage Owners -->
    <div id="users" class="section">
      <h4>Manage Owners</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark">
            <tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php
            $res_users = $conn->query("SELECT * FROM users WHERE role='owner' ORDER BY created_at DESC");
            $i=1;
            while ($row = $res_users->fetch_assoc()) {






              
              echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>{$row['created_at']}</td>
                <td>
                  <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                  <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this owner?\")'>Delete</a>
                </td>
              </tr>";
              $i++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manage Pets -->
    <div id="pets" class="section">
      <h4>Manage Pets</h4>
      <?php
      $res_pets = $conn->query("SELECT p.pet_id, p.name, p.species, p.breed, p.age, u.name AS owner_name
                                FROM pets p JOIN users u ON p.owner_id = u.id");
      if ($res_pets && $res_pets->num_rows > 0) {
          echo "<table class='table table-hover bg-light rounded'><thead class='table-dark'>
                  <tr><th>Pet</th><th>Species</th><th>Breed</th><th>Age</th><th>Owner</th><th>Actions</th></tr>
                </thead><tbody>";
          while ($pet = $res_pets->fetch_assoc()) {
              echo "<tr>
                      <td>{$pet['name']}</td>
                      <td>{$pet['species']}</td>
                      <td>{$pet['breed']}</td>
                      <td>{$pet['age']}</td>
                      <td>{$pet['owner_name']}</td>
                      <td><a href='./pages/delete_pet.php?id={$pet['pet_id']}' class='btn btn-danger btn-sm'>Delete</a></td>
                    </tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p>No pets found.</p>";
      }
      ?>
    </div>

    <!-- Manage Orders -->
    <div id="orders" class="section">
      <h4>Manage Orders</h4>
      <?php
      $sql = "SELECT o.order_id, o.order_date, o.status, 
               u.name AS owner_name, 
               p.name AS product_name, p.price, oi.quantity
        FROM orders o
        JOIN users u ON o.owner_id = u.id
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        ORDER BY o.order_date DESC";

      $res_orders = $conn->query($sql);
      if ($res_orders && $res_orders->num_rows > 0) {
          echo "<table class='table table-hover bg-light rounded'><thead class='table-dark'>
                  <tr><th>Order ID</th><th>Owner</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th></tr>
                </thead><tbody>";
          while ($row = $res_orders->fetch_assoc()) {
              $total = $row['price'] * $row['quantity'];
              echo "<tr>
                      <td>#{$row['order_id']}</td>
                      <td>{$row['owner_name']}</td>
                      <td>{$row['product_name']}</td>
                      <td>{$row['quantity']}</td>
                      <td>\${$total}</td>
                      <td>
                        <form method='POST' action='update_order.php' class='d-flex'>
                          <input type='hidden' name='order_id' value='{$row['order_id']}'>
                          <select name='status' class='form-select form-select-sm me-2'>
                            <option ".($row['status']=='Pending'?'selected':'').">Pending</option>
                            <option ".($row['status']=='Shipped'?'selected':'').">Shipped</option>
                            <option ".($row['status']=='Completed'?'selected':'').">Completed</option>
                            <option ".($row['status']=='Cancelled'?'selected':'').">Cancelled</option>
                          </select>
                          <button type='submit' class='btn btn-sm btn-primary'>Update</button>
                        </form>
                      </td>
                      <td>{$row['order_date']}</td>
                    </tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p>No orders found.</p>";
      }
      ?>
    </div>

   <!-- Manage Products -->
<div id="products" class="section">
  <h4>Manage Products</h4>
  <a href="add_product.php" class="btn btn-success mb-3">+ Add Product</a>
  <style>
    .product-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .product-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    .product-img {
      height: 200px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }
    .card-footer .btn {
      font-size: 0.85rem;
    }
  </style>
  <?php
  $res_products = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
  if ($res_products && $res_products->num_rows > 0) {
      echo "<div class='row'>";
      while ($p = $res_products->fetch_assoc()) {
          echo "
          <div class='col-md-4 col-lg-3 mb-4'>
            <div class='card product-card h-100 border-0 rounded-4 shadow-sm'>
              <img src='assets/products/{$p['image']}' class='product-img w-100' alt='{$p['name']}'>
              <div class='card-body text-center'>
                <h5 class='card-title fw-bold'>{$p['name']}</h5>
                <p class='card-text text-primary fs-5 mb-0'>\$ {$p['price']}</p>
              </div>
              <div class='card-footer bg-light d-flex justify-content-between'>
                <a href='edit_product.php?id={$p['product_id']}' 
                   class='btn btn-warning btn-sm flex-fill me-1'>
                   <i class='fa fa-edit'></i> Edit
                </a>
                <a href='delete_product.php?id={$p['product_id']}' 
                   class='btn btn-danger btn-sm flex-fill ms-1' 
                   onclick='return confirm(\"Delete this product?\")'>
                   <i class='fa fa-trash'></i> Delete
                </a>
              </div>
            </div>
          </div>";
      }
      echo "</div>";
  } else {
      echo "<p>No products available.</p>";
  }
  ?>
</div>


    <!-- Appointments -->
<div id="appointments" class="section">
  <h4>Manage Appointments</h4>
  <?php
  $sql = "SELECT a.appointment_id, a.appointment_date, a.reason, a.status,
                 p.name AS pet_name, u.name AS owner_name, v.name AS vet_name
          FROM appointments a
          JOIN pets p ON a.pet_id = p.pet_id
          JOIN users u ON a.owner_id = u.id
          LEFT JOIN users v ON a.vet_id = v.id
          ORDER BY a.appointment_date DESC";
  $res_apps = $conn->query($sql);

  if ($res_apps && $res_apps->num_rows > 0) {
      echo "<table class='table table-hover bg-light rounded'>
              <thead class='table-dark'>
                <tr>
                  <th>Date</th>
                  <th>Pet</th>
                  <th>Owner</th>
                  <th>Vet</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>";

      while ($app = $res_apps->fetch_assoc()) {
          $vet = $app['vet_name'] ? $app['vet_name'] : "Not Assigned";

          echo "<tr>
                  <td>{$app['appointment_date']}</td>
                  <td>{$app['pet_name']}</td>
                  <td>{$app['owner_name']}</td>
                  <td>{$vet}</td>
                  <td>{$app['reason']}</td>
                  <td>
                    <form method='POST' action='update_appointment.php' class='d-flex'>
                      <input type='hidden' name='appointment_id' value='{$app['appointment_id']}'>
                      <select name='status' class='form-select form-select-sm me-2'>
                        <option ".($app['status']=='Pending'?'selected':'').">Pending</option>
                        <option ".($app['status']=='Approved'?'selected':'').">Approved</option>
                        <option ".($app['status']=='Completed'?'selected':'').">Completed</option>
                        <option ".($app['status']=='Cancelled'?'selected':'').">Cancelled</option>
                      </select>
                      <button type='submit' class='btn btn-sm btn-primary'>Update</button>
                    </form>
                  </td>
                  <td>
                    <a href='delete_appointment.php?id={$app['appointment_id']}' 
                       class='btn btn-sm btn-danger' 
                       onclick='return confirm(\"Cancel this appointment?\")'>Delete</a>
                  </td>
                </tr>";
      }

      echo "</tbody></table>";
  } else {
      echo "<p>No appointments found.</p>";
  }
  ?>
</div>


    <!-- Feedback -->
    <div id="feedback" class="section">
      <h4>User Feedback</h4>
      <?php
      $res_feedback = $conn->query("SELECT f.rating, f.comment, f.created_at, u.name AS owner_name
                                    FROM feedback f
                                    JOIN users u ON f.owner_id = u.id
                                    ORDER BY f.created_at DESC");
      if ($res_feedback && $res_feedback->num_rows > 0) {
          echo "<ul class='list-group'>";
          while ($fb = $res_feedback->fetch_assoc()) {
              echo "<li class='list-group-item bg-light'>
                      <strong>{$fb['owner_name']}</strong> ⭐{$fb['rating']}<br>
                      {$fb['comment']}<br>
                      <small class='text-muted'>{$fb['created_at']}</small>
                    </li>";
          }
          echo "</ul>";
      } else {
          echo "<p>No feedback submitted yet.</p>";
      }
      ?>
    </div>

  </div>
</div>
</body>
</html>
