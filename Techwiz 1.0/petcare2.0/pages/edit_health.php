<?php
session_start();

// ✅ Allow both owner & vet
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["owner", "vet"])) {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];
$message = "";

$record_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ✅ Fetch the health record
if ($role === "owner") {
    $sql = "SELECT h.*, p.name AS pet_name 
            FROM health_records h
            JOIN pets p ON h.pet_id = p.pet_id
            WHERE h.record_id=? AND p.owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $record_id, $user_id);
} else {
    $sql = "SELECT h.*, p.name AS pet_name, u.name AS owner_name
            FROM health_records h
            JOIN pets p ON h.pet_id = p.pet_id
            JOIN users u ON p.owner_id = u.id
            WHERE h.record_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $record_id);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($result);

if (!$record) {
    die("❌ Record not found or you don’t have permission to edit it.");
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visit_date = $_POST['visit_date'];
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($conn, $_POST['treatment']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $sql = "UPDATE health_records SET visit_date=?, diagnosis=?, treatment=?, notes=? WHERE record_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $visit_date, $diagnosis, $treatment, $notes, $record_id);

    if (mysqli_stmt_execute($stmt)) {
        if ($role === "owner") {
            header("Location: owner_dashboard.php#health");
        } else {
            header("Location: vet_dashboard.php#medical");
        }
        exit();
    } else {
        $message = "<div class='alert alert-danger'>❌ Error updating record: " . mysqli_error($conn) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Health Record - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg, #0d6efd, #6a11cb); 
           min-height: 100vh; display: flex; align-items: center; justify-content: center; color: #fff; margin: 0; position: relative; overflow: hidden; }
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float 6s ease-in-out infinite; z-index: 0; }
    @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(45deg); } }
    .card { background: rgba(0,0,0,0.85); padding: 30px; border-radius: 15px; width: 100%; max-width: 550px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 2; animation: popup 0.8s ease; }
    @keyframes popup { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .card h3 { font-weight: 600; text-align: center; margin-bottom: 20px; background: linear-gradient(90deg,#ff4d6d,#ffcc00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .form-label { color: rgba(255,255,255,0.75); font-weight: 500; }
    .form-control, textarea { background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: 10px; padding: 12px; }
    .form-control::placeholder, textarea::placeholder { color: rgba(255,255,255,0.6); }
    .btn-custom { background: linear-gradient(90deg,#ff4d6d,#ffcc00); border: none; color: #fff; font-weight: 500; transition: 0.3s; }
    .btn-custom:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
    .btn-secondary { background: rgba(255,255,255,0.2); border: none; color: #fff; transition: 0.3s; }
    .btn-secondary:hover { background: rgba(255,255,255,0.35); }
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
    <h3>✏️ Edit Health Record</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Pet</label>
        <input type="text" class="form-control" value="<?php 
          echo htmlspecialchars($record['pet_name']); 
          if ($role === "vet" && isset($record['owner_name'])) echo " (Owner: " . htmlspecialchars($record['owner_name']) . ")";
        ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Visit Date</label>
        <input type="date" name="visit_date" class="form-control" value="<?php echo htmlspecialchars($record['visit_date']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Diagnosis</label>
        <input type="text" name="diagnosis" class="form-control" value="<?php echo htmlspecialchars($record['diagnosis']); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Treatment</label>
        <input type="text" name="treatment" class="form-control" value="<?php echo htmlspecialchars($record['treatment']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($record['notes']); ?></textarea>
      </div>
      <button type="submit" class="btn btn-custom w-100">Update Record</button>
      <a href="<?php echo $role === 'owner' ? 'owner_dashboard.php#health' : 'vet_dashboard.php#medical'; ?>" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
