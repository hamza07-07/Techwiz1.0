<?php
session_start();

// Only shelters can access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["admin", "shelter", "owner"])) {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$shelter_id = intval($_SESSION['id']);
$pet_id = intval($_GET['pet_id'] ?? 0);

// Fetch pet (if exists) and ensure it belongs to shelter
$s = $conn->prepare("SELECT pet_id, name FROM pets WHERE pet_id=? AND owner_id=?");
$s->bind_param("ii", $pet_id, $shelter_id);
$s->execute();
$res = $s->get_result();
$pet = $res->fetch_assoc();
$s->close();

// Use a fallback name if pet not found
$pet_name = $pet['name'] ?? 'Unknown Pet';

// Fetch health records
$h = $conn->prepare("SELECT record_id, visit_date, diagnosis, treatment, notes, created_at FROM health_records WHERE pet_id=? ORDER BY visit_date DESC");
$h->bind_param("i", $pet_id);
$h->execute();
$hr = $h->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Health Records - <?php echo htmlspecialchars($pet_name); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Poppins, sans-serif;
      background: linear-gradient(120deg,#0d6efd,#6a11cb);
      color: #fff;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }
    .card { background: rgba(0,0,0,0.85); padding: 20px; border-radius: 12px; }
    .table thead th { color: #000; }
    .table tbody td { color: #000; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card mb-3">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0">Health Records — <?php echo htmlspecialchars($pet_name); ?></h4>
          <small class="text-muted">Records for this pet</small>
        </div>
        <div>
          <a href="add_health.php?pet_id=<?php echo intval($pet_id); ?>" class="btn btn-success">+ Add Record</a>
          <a href="shelter_dashboard.php" class="btn btn-secondary">Back</a>
        </div>
      </div>
    </div>

    <div class="card p-0">
      <div class="table-responsive">
        <table class="table table-hover bg-light rounded mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Diagnosis</th>
              <th>Treatment</th>
              <th>Notes</th>
              <th>Added</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1; 
            if ($hr->num_rows > 0):
                while ($row = $hr->fetch_assoc()):
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($row['visit_date']); ?></td>
              <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
              <td><?php echo htmlspecialchars($row['treatment'] ?: '-'); ?></td>
              <td><?php echo nl2br(htmlspecialchars($row['notes'] ?: '-')); ?></td>
              <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
            <?php 
                endwhile; 
            else: 
            ?>
            <tr><td colspan="6" class="text-center">No records yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
