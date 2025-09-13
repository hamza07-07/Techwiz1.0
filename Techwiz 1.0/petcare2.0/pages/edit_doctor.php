<?php
session_start();

// Restrict access to vets only
if (!isset($_SESSION['role']) || $_SESSION['role'] != "vet") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$doctor_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = "";

// Fetch doctor data (users + vet_profiles)
$sql = "SELECT u.name, u.email, v.specialization, v.experience, v.availability
        FROM users u
        LEFT JOIN vet_profiles v ON u.id = v.vet_id
        WHERE u.id=? AND u.role='vet'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    die("❌ Doctor not found or invalid ID.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $availability = $_POST['availability'];

    // Update users table
    $sql = "UPDATE users SET name=?, email=? WHERE id=? AND role='vet'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $email, $doctor_id);
    $stmt->execute();

    // Insert or update vet_profiles table
    $sql = "INSERT INTO vet_profiles (vet_id, specialization, experience, availability)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE specialization=VALUES(specialization), 
                                    experience=VALUES(experience), 
                                    availability=VALUES(availability)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $doctor_id, $specialization, $experience, $availability);
    $stmt->execute();

    header("Location: vet_dashboard.php#profile");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Doctor - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {font-family: 'Poppins', sans-serif;background: linear-gradient(120deg, #0d6efd, #6a11cb);min-height: 100vh;display: flex;align-items: center;justify-content: center;color: #fff;margin: 0;position: relative;overflow: hidden;}
    .floating-shape {position: absolute;border-radius: 50%;background: rgba(255,255,255,0.15);animation: float 6s ease-in-out infinite;z-index: 0;}
    @keyframes float {0%,100% { transform: translateY(0) rotate(0deg);}50% { transform: translateY(-20px) rotate(45deg);} }
    .card {background: rgba(0,0,0,0.85);padding: 30px;border-radius: 15px;width: 100%;max-width: 500px;box-shadow: 0 10px 30px rgba(0,0,0,0.5);z-index: 2;animation: popup 0.8s ease;}
    @keyframes popup {from { opacity: 0; transform: scale(0.9);}to { opacity: 1; transform: scale(1);} }
    .card h3 {font-weight: 600;text-align: center;margin-bottom: 20px;background: linear-gradient(90deg,#ff4d6d,#ffcc00);-webkit-background-clip: text;-webkit-text-fill-color: transparent;}
    .form-label {color: rgba(255,255,255,0.75);font-weight: 500;}
    .form-control {background: rgba(255,255,255,0.1);border: none;color: #fff;border-radius: 10px;padding: 12px;}
    .form-control::placeholder {color: rgba(255,255,255,0.6);}
    .btn-custom {background: linear-gradient(90deg,#ff4d6d,#ffcc00);border: none;color: #fff;font-weight: 500;transition: 0.3s;}
    .btn-custom:hover {transform: scale(1.05);box-shadow: 0 5px 15px rgba(0,0,0,0.4);}
    .btn-secondary {background: rgba(255,255,255,0.2);border: none;color: #fff;transition: 0.3s;}
    .btn-secondary:hover {background: rgba(255,255,255,0.35);}
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
    <h3>✏️ Edit Profile</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Specialization</label>
        <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($doctor['specialization']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Experience (years)</label>
        <input type="number" name="experience" class="form-control" value="<?php echo htmlspecialchars($doctor['experience']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Availability</label>
        <input type="text" name="availability" class="form-control" value="<?php echo htmlspecialchars($doctor['availability']); ?>">
      </div>
      <button type="submit" class="btn btn-custom w-100">Update Profile</button>
      <a href="vet_dashboard.php#profile" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
