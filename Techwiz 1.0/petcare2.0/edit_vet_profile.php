<?php
session_start();

// ✅ Only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("config/db.php");

// ✅ Get vet ID
if (!isset($_GET['id'])) {
    die("❌ Vet ID missing.");
}
$vet_id = (int) $_GET['id'];
$message = "";

// ✅ Fetch vet profile
$sql = "SELECT u.id, u.name, u.email, v.specialization, v.experience, v.availability
        FROM users u
        LEFT JOIN vet_profiles v ON u.id = v.vet_id
        WHERE u.id=? AND u.role='vet'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vet_id);
$stmt->execute();
$result = $stmt->get_result();
$vet = $result->fetch_assoc();

if (!$vet) {
    die("❌ Vet not found.");
}

// ✅ Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $specialization = trim($_POST['specialization']);
    $experience = (int) $_POST['experience'];
    $availability = trim($_POST['availability']);

    // If profile exists → update, otherwise insert
    $check = $conn->prepare("SELECT vet_id FROM vet_profiles WHERE vet_id=?");
    $check->bind_param("i", $vet_id);
    $check->execute();
    $check_res = $check->get_result();

    if ($check_res->num_rows > 0) {
        $sql = "UPDATE vet_profiles SET specialization=?, experience=?, availability=? WHERE vet_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $specialization, $experience, $availability, $vet_id);
    } else {
        $sql = "INSERT INTO vet_profiles (vet_id, specialization, experience, availability) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $vet_id, $specialization, $experience, $availability);
    }

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✅ Vet profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Vet Profile - FurShield</title>
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
    <h3>✏️ Edit Vet Profile</h3>

    <!-- ✅ White text for Name & Email -->
    <p style="color: #fff;"><strong>Name:</strong> <?php echo htmlspecialchars($vet['name']); ?></p>
    <p style="color: #fff;"><strong>Email:</strong> <?php echo htmlspecialchars($vet['email']); ?></p>

    <?php echo $message; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Specialization</label>
        <input type="text" name="specialization" class="form-control"
               value="<?php echo htmlspecialchars($vet['specialization'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Experience (years)</label>
        <input type="number" name="experience" class="form-control"
               value="<?php echo htmlspecialchars($vet['experience'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Availability</label>
        <input type="text" name="availability" class="form-control"
               value="<?php echo htmlspecialchars($vet['availability'] ?? ''); ?>">
      </div>
      <button type="submit" class="btn btn-custom w-100">Save Changes</button>
      <a href="manage_vets.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
