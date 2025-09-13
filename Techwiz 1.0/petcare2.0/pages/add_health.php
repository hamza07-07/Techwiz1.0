<?php
session_start();

// ✅ Allow both owners & vets
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["admin", "shelter", "owner"])) {
    header("Location: ../login.php");
    exit();
}
include("../config/db.php");

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];
$message = "";

// ✅ Fetch pets
if ($role === "owner") {
    $sql = "SELECT * FROM pets WHERE owner_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $pets_result = mysqli_stmt_get_result($stmt);
} else {
    // Vet sees all pets (you can filter by assigned pets if needed)
    $pets_result = mysqli_query($conn, 
        "SELECT p.pet_id, p.name, u.name AS owner_name 
         FROM pets p 
         JOIN users u ON p.owner_id = u.id 
         ORDER BY p.name ASC");
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = (int) $_POST['pet_id'];
    $visit_date = $_POST['visit_date'];
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($conn, $_POST['treatment']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // Vet_id logic
    $vet_id = ($role === "vet") ? $user_id : NULL;

    $sql = "INSERT INTO health_records (pet_id, vet_id, visit_date, diagnosis, treatment, notes) 
            VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iissss",
        $pet_id,
        $vet_id,
        $visit_date,
        $diagnosis,
        $treatment,
        $notes
    );

    if (mysqli_stmt_execute($stmt)) {
        if ($role === "vet") {
            header("Location: vet_dashboard.php#history");
        } else {
            header("Location: owner_dashboard.php#health");
        }
        exit();
    } else {
        $message = "<div class='alert alert-danger'>❌ Error adding record: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Health Record - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg, #0d6efd, #6a11cb); 
           min-height: 100vh; display: flex; align-items: center; justify-content: center; color: #fff; margin: 0; position: relative; overflow: hidden; }
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float 6s ease-in-out infinite; z-index: 0; }
    @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(45deg); } }
    .card { background: rgba(0,0,0,0.85); padding: 25px; border-radius: 15px; width: 100%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 2; animation: popup 0.8s ease; }
    @keyframes popup { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .card h3 { font-weight: 600; text-align: center; margin-bottom: 15px; background: linear-gradient(90deg,#ff4d6d,#ffcc00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .form-label { color: rgba(255,255,255,0.75); font-weight: 500; }
    .form-control, .form-select, textarea { background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: 10px; padding: 10px; }
    .form-control::placeholder, textarea::placeholder { color: rgba(255,255,255,0.6); }
    .form-select option { color: #000; }
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
    <h3>➕ Add Health Record</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Select Pet</label>
        <select name="pet_id" class="form-select" required>
          <option value="">-- Choose Pet --</option>
          <?php 
          if ($pets_result && mysqli_num_rows($pets_result) > 0) {
              while ($pet = mysqli_fetch_assoc($pets_result)) {
                  $display = htmlspecialchars($pet['name'] . " (Owner: " . $pet['owner_name'] . ")");
                  echo "<option value='{$pet['pet_id']}'>$display</option>";
              }
          } else {
              echo "<option disabled>No pets found</option>";
          }
          ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Visit Date</label>
        <input type="date" name="visit_date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Diagnosis</label>
        <input type="text" name="diagnosis" class="form-control" placeholder="e.g. Skin Allergy" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Treatment</label>
        <input type="text" name="treatment" class="form-control" placeholder="e.g. Ointment" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Extra details..."></textarea>
      </div>
      <button type="submit" class="btn btn-custom w-100">Save Record</button>
      <a href="vet_dashboard.php#history" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>

</body>
</html>
