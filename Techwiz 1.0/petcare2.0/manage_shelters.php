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
  <title>Manage Shelters - Admin Dashboard</title>
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
    .navbar { background: rgba(0,0,0,0.85); }
    .navbar-brand {
      font-weight: 700;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .wrapper { flex: 1; display: flex; flex-direction: row; }
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
    .content { flex: 1; padding: 20px; overflow: auto; }
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
      <a class="navbar-brand"><i class="fa-solid fa-warehouse"></i> Manage Shelters</a>
      <span class="ms-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
    </div>
    <div><a href="logout.php" class="btn btn-warning">Logout</a></div>
  </div>
</nav>

<div class="wrapper">
  <!-- Sidebar -->
  <div class="sidebar">
    <a href="admin_dashboard.php">Overview</a>
    <a href="manage_owners.php"><i class="fa-solid fa-users"></i> Owners</a>
    <a href="manage_vets.php"><i class="fa-solid fa-user-doctor"></i> Vets</a>
    <a href="manage_shelters.php" class="active"><i class="fa-solid fa-warehouse"></i> Shelters</a>
    <a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> All Users</a>
  </div>

  <!-- Main Content -->
  <div class="content">

    <!-- Manage Shelter Accounts -->
    <div class="section">
      <h4><i class="fa-solid fa-warehouse"></i> Manage Shelter Accounts</h4>
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded">
          <thead class="table-dark">
            <tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php
            $res = $conn->query("SELECT * FROM users WHERE role='shelter' ORDER BY created_at DESC");
            $i=1;
            while ($row = $res->fetch_assoc()) {
              echo "<tr>
                <td>{$i}</td>
                <td>".htmlspecialchars($row['name'])."</td>
                <td>".htmlspecialchars($row['email'])."</td>
                <td>{$row['created_at']}</td>
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

    <!-- Manage Shelter Pets -->
    <div class="section">
      <h4><i class="fa-solid fa-paw"></i> Manage Shelter Pets</h4>
      <?php
      $res_pets = $conn->query("SELECT p.pet_id, p.name, p.species, p.breed, p.age, p.created_at, u.name AS shelter_name
                                FROM pets p 
                                LEFT JOIN users u ON p.owner_id = u.id 
                                WHERE u.role='shelter'
                                ORDER BY p.created_at DESC");
      if ($res_pets && $res_pets->num_rows > 0) {
          echo "<table class='table table-hover bg-light rounded'>
                  <thead class='table-dark'>
                    <tr><th>Pet</th><th>Species</th><th>Breed</th><th>Age</th><th>Shelter</th><th>Actions</th></tr>
                  </thead><tbody>";
          while ($pet = $res_pets->fetch_assoc()) {
              echo "<tr>
                      <td>{$pet['name']}</td>
                      <td>{$pet['species']}</td>
                      <td>{$pet['breed']}</td>
                      <td>{$pet['age']}</td>
                      <td>{$pet['shelter_name']}</td>
                      <td>
                        <a href='pages/edit_pet.php?id={$pet['pet_id']}' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='pages/delete_pet.php?id={$pet['pet_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this pet?\")'>Delete</a>
                      </td>
                    </tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p>No pets found for shelters.</p>";
      }
      ?>
    </div>

    <!-- Manage Shelter Health Records -->
    <!-- Manage Shelter Health Records -->
<div class="section">
  <h4><i class="fa-solid fa-notes-medical"></i> Manage Shelter Health Records</h4>
  <?php
  $sql = "SELECT hr.record_id, hr.visit_date, hr.diagnosis, hr.treatment, 
                 p.name AS pet_name, u.name AS shelter_name
          FROM health_records hr
          JOIN pets p ON hr.pet_id = p.pet_id
          JOIN users u ON p.owner_id = u.id
          WHERE u.role = 'shelter'
          ORDER BY hr.visit_date DESC";  // ❌ removed LIMIT 20 so ALL records show
  $res_records = $conn->query($sql);

  if ($res_records && $res_records->num_rows > 0) {
      echo "<table class='table table-hover bg-light rounded'>
              <thead class='table-dark'>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Pet</th>
                  <th>Shelter</th>
                  <th>Diagnosis</th>
                  <th>Treatment</th>
                  <th>Actions</th>
                </tr>
              </thead><tbody>";
      $i=1;
      while ($r = $res_records->fetch_assoc()) {
          echo "<tr>
                  <td>{$i}</td>
                  <td>{$r['visit_date']}</td>
                  <td>{$r['pet_name']}</td>
                  <td>{$r['shelter_name']}</td>
                  <td>{$r['diagnosis']}</td>
                  <td>{$r['treatment']}</td>
                  <td>
                    <a href='edit_health.php?id={$r['record_id']}' class='btn btn-warning btn-sm'>Edit</a>
                    <a href='delete_health.php?id={$r['record_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this record?\")'>Delete</a>
                  </td>
                </tr>";
          $i++;
      }
      echo "</tbody></table>";
  } else {
      echo "<p>No health records found for shelters.</p>";
  }
  ?>
</div>


  </div>
</div>
</body>
</html>
