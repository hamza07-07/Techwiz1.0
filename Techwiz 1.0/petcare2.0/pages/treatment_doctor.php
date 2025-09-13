<?php
session_start();

// Restrict access to vets or admins only
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["vet", "admin"])) {
    header("Location: login.php");
    exit();
}

include("../config/db.php");

$doctor_id = $_SESSION['id'];
$message   = "";

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pet_id         = (int) $_POST['pet_id'];
    $treatment_date = $_POST['treatment_date'];
    $medication     = trim($_POST['treatment']);
    $notes          = trim($_POST['notes']);
    $follow_up_date = NULL;

    if (!empty($pet_id) && !empty($treatment_date) && !empty($medication)) {
        $sql = "INSERT INTO treatments (pet_id, vet_id, treatment_date, medication, notes, follow_up_date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iissss", $pet_id, $doctor_id, $treatment_date, $medication, $notes, $follow_up_date);

        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='alert alert-success'>✅ Treatment logged successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>⚠️ Please fill in all required fields.</div>";
    }
}

// ✅ Fetch pets list for dropdown
$pets = [];
$sql = "SELECT p.pet_id, p.name, u.name AS owner_name
FROM pets p
JOIN users u ON p.owner_id = u.id
ORDER BY p.pet_id DESC
";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $pets[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Treatment - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #6a11cb, #0d6efd);
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
      padding: 25px;
      border-radius: 15px;
      width: 100%;
      max-width: 550px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      z-index: 2;
      animation: popup 0.8s ease;
    }

    .card h3 {
      font-weight: 600;
      text-align: center;
      margin-bottom: 20px;
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .form-label { color: rgba(255,255,255,0.8); font-weight: 500; }

    .form-control, .form-select {
  background: rgba(255,255,255,0.1);
  border: none;
  color: #fff;
  border-radius: 10px;
  padding: 12px;
}

.form-control::placeholder,
textarea::placeholder {
  color: rgba(255,255,255,0.7);
}

.form-select option {
  color: #000; /* ensures dropdown list items are black text on white background */
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
    <h3>📝 Log Treatment</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Select Pet</label>
        <select name="pet_id" class="form-select text-dark" required>
          <option value="">-- Choose a Pet --</option>
          <?php foreach ($pets as $p) { ?>
            <option value="<?php echo $p['pet_id']; ?>">
              <?php echo htmlspecialchars($p['name'] . " (Owner: " . $p['owner_name'] . ")"); ?>
            </option>
          <?php } ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Treatment Date</label>
        <input type="date" name="treatment_date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Medication</label>
        <textarea name="treatment" class="form-control" rows="3" placeholder="Enter medication" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes (optional)"></textarea>
      </div>
      <button type="submit" class="btn btn-custom w-100">Save Treatment</button>
    </form>
  </div>

</body>
</html>
