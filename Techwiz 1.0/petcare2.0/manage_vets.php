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
  <title>Manage Veterinarians - Admin Dashboard</title>
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
    <a href="manage_owners.php"><i class="fa-solid fa-users"></i> Owners</a>
    <a href="manage_vets.php" class="active"><i class="fa-solid fa-user-doctor"></i> Vets</a>
    <a href="manage_shelters.php"><i class="fa-solid fa-warehouse"></i> Shelters</a>
  </div>

  <!-- Main Content -->
  <div class="content">

    <!-- Manage Veterinarians -->
    <div id="vets" class="section">
      <h4>Manage Veterinarians</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Registered</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $res_vets = $conn->query("SELECT * FROM users WHERE role='vet' ORDER BY created_at DESC");
            $i=1;
            if ($res_vets && $res_vets->num_rows > 0) {
              while ($row = $res_vets->fetch_assoc()) {
                echo "<tr>
                  <td>{$i}</td>
                  <td>".htmlspecialchars($row['name'])."</td>
                  <td>".htmlspecialchars($row['email'])."</td>
                  <td>{$row['created_at']}</td>
                  <td>
                    <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='delete_user.php?id={$row['id']}' 
                       class='btn btn-sm btn-danger' 
                       onclick='return confirm(\"Delete this vet?\")'>Delete</a>
                  </td>
                </tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5' class='text-center'>No veterinarians found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manage Vet Profiles -->
    <div id="vet-profiles" class="section">
      <h4>Manage Vet Profiles</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark">
            <tr>
              <th>Vet Name</th>
              <th>Specialization</th>
              <th>Experience</th>
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT u.id, u.name, v.specialization, v.experience, v.availability
                    FROM users u
                    LEFT JOIN vet_profiles v ON u.id = v.vet_id
                    WHERE u.role='vet'
                    ORDER BY u.name ASC";
            $res_profiles = $conn->query($sql);

            if ($res_profiles && $res_profiles->num_rows > 0) {
              while ($row = $res_profiles->fetch_assoc()) {
                echo "<tr>
                  <td>".htmlspecialchars($row['name'])."</td>
                  <td>".htmlspecialchars($row['specialization'] ?? 'Not set')."</td>
                  <td>".htmlspecialchars($row['experience'] ? $row['experience'].' years' : 'Not set')."</td>
                  <td>".htmlspecialchars($row['availability'] ?? 'Not set')."</td>
                  <td>
                    <a href='edit_vet_profile.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                  </td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='5' class='text-center'>No vet profiles found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</body>
</html>
