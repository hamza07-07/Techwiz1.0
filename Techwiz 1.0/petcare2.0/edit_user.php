<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
include("config/db.php");

$user_id = intval($_GET['id'] ?? 0);
$message = "";

// Fetch user
$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("❌ User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role  = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $user_id);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Error updating user: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #0d6efd, #6a11cb);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      margin: 0;
      position: relative;
      overflow: hidden;
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
      padding: 30px;
      border-radius: 15px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 2;
      animation: popup 0.8s ease;
    }
    @keyframes popup {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    .card h3 {
      font-weight: 600;
      text-align: center;
      margin-bottom: 20px;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .form-label {
      color: rgba(255,255,255,0.75);
      font-weight: 500;
    }

    .form-control {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 12px;
    }
    .form-control::placeholder {
      color: rgba(255,255,255,0.6);
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

    .btn-secondary {
      background: rgba(255,255,255,0.2);
      border: none;
      color: #fff;
      transition: 0.3s;
    }
    .btn-secondary:hover {
      background: rgba(255,255,255,0.35);
    }
  </style>
</head>
<body>

  <!-- Floating Shapes -->
  <div class="floating-shape" style="top:5%; left:10%; width:60px; height:60px;"></div>
  <div class="floating-shape" style="top:20%; right:15%; width:80px; height:80px;"></div>
  <div class="floating-shape" style="top:45%; left:25%; width:100px; height:100px;"></div>
  <div class="floating-shape" style="bottom:10%; right:10%; width:120px; height:120px;"></div>
  <div class="floating-shape" style="bottom:25%; left:35%; width:70px; height:70px;"></div>
  <div class="floating-shape" style="top:65%; right:40%; width:50px; height:50px;"></div>

  <!-- Card -->
  <div class="card">
    <h3>✏️ Edit User</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" required>
          <option value="owner"   <?= $user['role']=='owner'?'selected':'' ?>>Owner</option>
          <option value="vet"     <?= $user['role']=='vet'?'selected':'' ?>>Veterinarian</option>
          <option value="shelter" <?= $user['role']=='shelter'?'selected':'' ?>>Shelter</option>
          <option value="admin"   <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-custom w-100">Save Changes</button>
      <a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
