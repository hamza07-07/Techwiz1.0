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
  <title>Admin Dashboard</title>
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
      min-height: calc(100vh - 70px);
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
    .sidebar .submenu a {
      font-size: 14px;
      margin-left: 15px;
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
    .table thead th {
      color: #000;
    }
    .table tbody td {
      color: #000;
    }
    @media(max-width: 992px) {
      .wrapper {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        height: auto;
        margin-bottom: 20px;
      }
    }
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
  <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Overview</a>

  <a data-bs-toggle="collapse" href="#usersMenu" role="button" aria-expanded="false" aria-controls="usersMenu">
    <i class="fa-solid fa-users"></i> Users
  </a>
  <div class="collapse submenu" id="usersMenu">
    <a href="manage_users.php"><i class="fa-solid fa-user"></i> All Users</a>
    <a href="manage_owners.php"><i class="fa-solid fa-user-tie"></i> Owners</a>
    <a href="manage_vets.php"><i class="fa-solid fa-user-md"></i> Vets</a>
    <a href="manage_shelters.php"><i class="fa-solid fa-house"></i> Shelters</a>
  </div>
</div>


  <!-- Main Content -->
  <div class="content">

    <!-- Overview Section -->
    <div id="overview" class="section">
      <h4>System Overview</h4>
      <p class="small">Quick stats</p>
      <?php
      $q = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='owner'"); $owners = $q->fetch_assoc()['c'] ?? 0;
      $q = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='vet'"); $vets = $q->fetch_assoc()['c'] ?? 0;
      $q = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='shelter'"); $shelters = $q->fetch_assoc()['c'] ?? 0;
      $q = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'"); $admins = $q->fetch_assoc()['c'] ?? 0;
      $q = $conn->query("SELECT COUNT(*) AS c FROM pets"); $pets = $q->fetch_assoc()['c'] ?? 0;
      $q = $conn->query("SELECT COUNT(*) AS c FROM health_records"); $records = $q->fetch_assoc()['c'] ?? 0;
      ?>
      <div class="row text-center">
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $owners; ?></h5><div>Owners</div></div></div>
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $vets; ?></h5><div>Vets</div></div></div>
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $shelters; ?></h5><div>Shelters</div></div></div>
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $admins; ?></h5><div>Admins</div></div></div>
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $pets; ?></h5><div>Pets</div></div></div>
        <div class="col-6 col-md-4 col-lg-2 mb-3"><div class="section"><h5><?php echo $records; ?></h5><div>Health Records</div></div></div>
      </div>
    </div>

    <!-- All Users Section -->
    <div id="all-users" class="section">
      <h4>All Users</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th></tr></thead>
          <tbody>
            <?php
            $i=1;
            $res = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
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
    </div>

    <!-- Owners Section -->
    <div id="owners" class="section">
      <h4>Owners</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr></thead>
          <tbody>
            <?php
            $i=1;
            $res = $conn->query("SELECT id, name, email, created_at FROM users WHERE role='owner' ORDER BY created_at DESC");
            while ($row = $res->fetch_assoc()) {
              echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>".htmlspecialchars($row['created_at'])."</td>
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

    <!-- Vets Section -->
    <div id="vets" class="section">
      <h4>Veterinarians</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr></thead>
          <tbody>
            <?php
            $i=1;
            $res = $conn->query("SELECT id, name, email, created_at FROM users WHERE role='vet' ORDER BY created_at DESC");
            while ($row = $res->fetch_assoc()) {
              echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>".htmlspecialchars($row['created_at'])."</td>
                <td>
                  <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                  <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this vet?\")'>Delete</a>
                </td>
              </tr>";
              $i++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Shelters Section -->
    <div id="shelters" class="section">
      <h4>Shelters</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark"><tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr></thead>
          <tbody>
            <?php
            $i=1;
            $res = $conn->query("SELECT id, name, email, created_at FROM users WHERE role='shelter' ORDER BY created_at DESC");
            while ($row = $res->fetch_assoc()) {
              echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>".htmlspecialchars($row['created_at'])."</td>
                <td>
                  <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                  <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this shelter?\")'>Delete</a>
                </td>
              </tr>";
              $i++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
