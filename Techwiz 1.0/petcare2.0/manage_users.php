<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("./config/db.php");

$sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Users - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #0d6efd, #6a11cb);
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      color: #fff;
    }
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.15);
      animation: float 6s ease-in-out infinite;
      z-index: 0;
    }
    @keyframes float {
      0%,100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(45deg); }
    }
    .card {
      background: rgba(0,0,0,0.85);
      padding: 25px;
      border-radius: 15px;
      width: 100%;
      max-width: 1000px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 2;
      animation: popup 0.8s ease;
    }
    @keyframes popup {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    h3 {
      font-weight: 600;
      margin-bottom: 20px;
      text-align: center;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .table thead th {
      background: #222;
      color: #fff;
    }
    .table tbody td {
      color: #000;
    }
    .btn-custom {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      border: none;
      color: #fff;
      font-weight: 500;
      transition: 0.3s;
    }
    .btn-custom:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }
  </style>
</head>
<body>

  <!-- Floating Shapes -->
  <div class="floating-shape" style="top:5%; left:10%; width:70px; height:70px;"></div>
  <div class="floating-shape" style="top:20%; right:15%; width:100px; height:100px;"></div>
  <div class="floating-shape" style="bottom:15%; left:20%; width:120px; height:120px;"></div>
  <div class="floating-shape" style="bottom:5%; right:25%; width:80px; height:80px;"></div>

  <!-- Main Card -->
  <div class="card">
    <h3><i class="fa-solid fa-users"></i> Manage All Users</h3>
    <div class="table-responsive">
      <table class="table table-hover bg-light rounded">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i=1;
          while ($row = $res->fetch_assoc()) {
            echo "<tr>
              <td>{$i}</td>
              <td>".htmlspecialchars($row['name'])."</td>
              <td>".htmlspecialchars($row['email'])."</td>
              <td>".htmlspecialchars($row['role'])."</td>
              <td>".htmlspecialchars($row['created_at'])."</td>
              <td>
                <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this user?\")'>Delete</a>
              </td>
            </tr>";
            $i++;
          }
          ?>
        </tbody>
      </table>
    </div>
    <a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-3">⬅ Back to Dashboard</a>
  </div>

</body>
</html>
