<?php
session_start();

// ✅ Only vets can edit treatments
if (!isset($_SESSION['role']) || $_SESSION['role'] != "vet") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$doctor_id = $_SESSION['id'];
$message = "";

// ✅ Get treatment ID from URL
$treatment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($treatment_id <= 0) {
    die("❌ Invalid treatment ID.");
}

// ✅ Fetch treatment details
$sql = "SELECT * FROM treatments WHERE treatment_id=? AND vet_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $treatment_id, $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$treatment = mysqli_fetch_assoc($result);

if (!$treatment) {
    die("❌ Treatment not found or you are not allowed to edit it.");
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $treatment_date = $_POST['treatment_date'];
    $medication = trim($_POST['medication']);
    $notes = trim($_POST['notes']);
    $follow_up_date = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : NULL;

    if (!empty($treatment_date) && !empty($medication)) {
        $sql = "UPDATE treatments 
                SET treatment_date=?, medication=?, notes=?, follow_up_date=? 
                WHERE treatment_id=? AND vet_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssii", $treatment_date, $medication, $notes, $follow_up_date, $treatment_id, $doctor_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: vet_dashboard.php#treatments");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>❌ Error updating treatment: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Treatment date and medication are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Treatment - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #6a11cb, #0d6efd);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      color: #fff;
    }
    .card {
      background: rgba(0,0,0,0.85);
      padding: 30px;
      border-radius: 15px;
      width: 100%;
      max-width: 600px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .card h3 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 600;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .form-label { color: rgba(255,255,255,0.8); }
    .form-control, .form-select {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 12px;
    }
    .form-control::placeholder { color: rgba(255,255,255,0.6); }
    .btn-custom {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      border: none;
      color: #fff;
      font-weight: 500;
    }
    .btn-custom:hover { transform: scale(1.05); }
  </style>
</head>
<body>

<div class="card">
  <h3>✏️ Edit Treatment</h3>
  <?php echo $message; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Treatment Date</label>
      <input type="date" name="treatment_date" class="form-control" value="<?php echo htmlspecialchars($treatment['treatment_date']); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Medication</label>
      <input type="text" name="medication" class="form-control" value="<?php echo htmlspecialchars($treatment['medication']); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($treatment['notes']); ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Follow-up Date</label>
      <input type="date" name="follow_up_date" class="form-control" value="<?php echo htmlspecialchars($treatment['follow_up_date']); ?>">
    </div>
    <button type="submit" class="btn btn-custom w-100">Update Treatment</button>
    <a href="vet_dashboard.php#treatments" class="btn btn-secondary w-100 mt-2">Cancel</a>
  </form>
</div>

</body>
</html>
