<?php
session_start();

// Only owners can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];
$message = "";

// Fetch pets for dropdown
$sql = "SELECT * FROM pets WHERE owner_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$pets_result = $stmt->get_result();

// Fetch vets for dropdown
$vets_result = $conn->query("SELECT id, name FROM users WHERE role='vet'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = (int) $_POST['pet_id'];
    $appointment_date = $_POST['appointment_date'];
    $reason = trim($_POST['reason']);
    $vet_id = !empty($_POST['vet_id']) ? (int) $_POST['vet_id'] : NULL;
    $status = "Pending"; // default

    // ✅ Prevent past dates
    if (strtotime($appointment_date) < time()) {
        $message = "<div class='alert alert-danger'>❌ Appointment date must be in the future.</div>";
    } else {
        // ✅ Prevent duplicate booking for same pet/date
        $check = $conn->prepare("SELECT appointment_id FROM appointments WHERE pet_id=? AND appointment_date=?");
        $check->bind_param("is", $pet_id, $appointment_date);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<div class='alert alert-danger'>❌ This pet already has an appointment at that time.</div>";
        } else {
            // ✅ Insert appointment
            $sql = "INSERT INTO appointments (pet_id, owner_id, vet_id, appointment_date, reason, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiisss", $pet_id, $owner_id, $vet_id, $appointment_date, $reason, $status);

            if ($stmt->execute()) {
                header("Location: owner_dashboard.php#appointments");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>❌ Error booking appointment: " . $conn->error . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Appointment - FurShield</title>
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
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float 6s ease-in-out infinite; z-index: 0; }
    @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(45deg); } }
    .card { background: rgba(0,0,0,0.85); padding: 30px; border-radius: 15px; width: 100%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 2; animation: popup 0.8s ease; }
    @keyframes popup { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .card h3 { font-weight: 600; text-align: center; margin-bottom: 20px; background: linear-gradient(90deg,#ff4d6d,#ffcc00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .form-label { color: rgba(255,255,255,0.75); font-weight: 500; }
    .form-control, .form-select { background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: 10px; padding: 12px; }
    .form-control::placeholder { color: rgba(255,255,255,0.6); }
    .form-select option { color: #000; }
    .btn-success { background: linear-gradient(90deg,#28a745,#85e085); border: none; font-weight: 500; transition: 0.3s; }
    .btn-success:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
    .btn-light { background: rgba(255,255,255,0.2); border: none; color: #fff; transition: 0.3s; }
    .btn-light:hover { background: rgba(255,255,255,0.35); }
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

  <!-- Appointment Form Card -->
  <div class="card">
    <h3>📅 Book Appointment</h3>
    <?php echo $message; ?>
    <form method="POST">
      <!-- Select Pet -->
      <div class="mb-3">
        <label class="form-label">Select Pet</label>
        <select name="pet_id" class="form-select" required>
          <option value="">-- Choose Pet --</option>
          <?php 
          if ($pets_result && $pets_result->num_rows > 0) {
              while ($pet = $pets_result->fetch_assoc()) { ?>
                <option value="<?php echo $pet['pet_id']; ?>">
                  <?php echo htmlspecialchars($pet['name']); ?>
                </option>
          <?php } } else {
              echo "<option disabled>No pets found. Please add a pet first.</option>";
          } ?>
        </select>
      </div>
      <!-- Select Vet -->
      <div class="mb-3">
        <label class="form-label">Select Vet (Optional)</label>
        <select name="vet_id" class="form-select">
          <option value="">-- No Vet Assigned Yet --</option>
          <?php 
          if ($vets_result && $vets_result->num_rows > 0) {
              while ($vet = $vets_result->fetch_assoc()) { ?>
                <option value="<?php echo $vet['id']; ?>">
                  <?php echo htmlspecialchars($vet['name']); ?>
                </option>
          <?php } } else {
              echo "<option disabled>No vets available.</option>";
          } ?>
        </select>
      </div>
      <!-- Date -->
      <div class="mb-3">
        <label class="form-label">Appointment Date</label>
        <input type="datetime-local" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
      </div>
      <!-- Reason -->
      <div class="mb-3">
        <label class="form-label">Reason</label>
        <input type="text" name="reason" class="form-control" placeholder="e.g. Vaccination" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Save Appointment</button>
      <a href="owner_dashboard.php#appointments" class="btn btn-light w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
