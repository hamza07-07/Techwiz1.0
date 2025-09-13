<?php
session_start();

// Restrict access to vets only
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["vet", "admin"])) {
    header("Location: login.php");
    exit();
}

include("../config/db.php");

$doctor_id = $_SESSION['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg, #6a11cb, #0d6efd); min-height: 100vh; color: #fff; margin: 0; overflow-x: hidden; position: relative; }
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float 6s ease-in-out infinite; z-index: 0; }
    @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(45deg); } }
    .navbar { background: rgba(0,0,0,0.85); padding: 15px; position: relative; z-index: 2; }
    .navbar-brand { font-weight: bold; font-size: 1.5rem; background: linear-gradient(90deg, #ffcc00, #ff4d6d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-logout { background: linear-gradient(90deg,#ff4d6d,#ffcc00); border: none; color: #fff; font-weight: 500; }
    .wrapper { display: flex; height: calc(100vh - 70px); position: relative; z-index: 2; }
    .sidebar { width: 260px; background: rgba(0,0,0,0.6); padding: 20px; overflow-y: auto; }
    .sidebar a { display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; color: #fff; text-decoration: none; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(90deg,#ff4d6d,#ffcc00); }
    .content { flex: 1; padding: 20px; overflow-y: auto; }
    .section { background: rgba(0,0,0,0.4); padding: 25px; border-radius: 16px; margin-bottom: 25px; }
    .section h3 { margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
    .highlight { color: #ffcc00; font-weight: 600; }
    /* ===== Responsive Fixes ===== */

/* Tablets */
@media (max-width: 991px) {
  .wrapper {
    flex-direction: column;
    height: auto;
  }
  .sidebar {
    width: 100%;
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 10px;
  }
  .sidebar a {
    flex: 1;
    text-align: center;
    font-size: 14px;
    padding: 10px 6px;
  }
  .content {
    padding: 15px;
  }
}

/* Mobile Phones */
@media (max-width: 576px) {
  .navbar-brand {
    font-size: 1.2rem;
  }
  .btn-logout {
    font-size: 0.85rem;
    padding: 6px 12px;
  }
  .sidebar {
    flex-wrap: wrap;
    gap: 5px;
  }
  .sidebar a {
    font-size: 13px;
    padding: 8px 5px;
  }
  .section {
    padding: 15px;
  }
  .section h3 {
    font-size: 1rem;
    gap: 6px;
  }
  .table {
    font-size: 12px;
  }
}

  </style>
</head>
<body>

  <!-- Floating Shapes -->
  <div class="floating-shape" style="top:5%; left:10%; width:60px; height:60px;"></div>
  <div class="floating-shape" style="top:15%; right:15%; width:80px; height:80px;"></div>
  <div class="floating-shape" style="top:40%; left:20%; width:100px; height:100px;"></div>
  <div class="floating-shape" style="bottom:10%; right:10%; width:120px; height:120px;"></div>
  <div class="floating-shape" style="bottom:25%; left:30%; width:70px; height:70px;"></div>
  <div class="floating-shape" style="top:60%; right:40%; width:50px; height:50px;"></div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="container-fluid">
      <a class="navbar-brand"><i class="fa-solid fa-stethoscope"></i> Doctor Dashboard</a>
      <a href="../logout.php" class="btn btn-logout">Logout</a>
    </div>
  </nav>

  <!-- Wrapper -->
  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
      <a href="#welcome" class="active"><i class="fa-solid fa-house"></i> Welcome</a>
      <a href="#profile"><i class="fa-solid fa-user-doctor"></i> My Profile</a>
      <a href="#appointments"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
      <a href="#history"><i class="fa-solid fa-file-medical"></i> Pet Medical History</a>
      <a href="#treatments"><i class="fa-solid fa-prescription"></i> Treatments</a>
    </div>

    <!-- Main Content -->
    <div class="content">

      <!-- Welcome -->
      <div id="welcome" class="section">
        <h3>Welcome Dr. <span class="highlight"><?php echo $_SESSION['name']; ?></span> 👨‍⚕️</h3>
        <p>Manage appointments, view pet histories, and record treatments.</p>
      </div>

      <!-- Profile -->
      <div id="profile" class="section">
        <h3><i class="fa-solid fa-user-doctor"></i> My Profile</h3>
        <?php
        $sql = "SELECT u.name, u.email, u.created_at, v.specialization, v.experience, v.availability
                FROM users u
                LEFT JOIN vet_profiles v ON u.id = v.vet_id
                WHERE u.id=? AND u.role='vet'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo "<p><strong>Name:</strong> {$row['name']}</p>";
            echo "<p><strong>Email:</strong> {$row['email']}</p>";
            echo "<p><strong>Joined:</strong> {$row['created_at']}</p>";
            echo "<p><strong>Specialization:</strong> " . ($row['specialization'] ?? 'Not set') . "</p>";
            echo "<p><strong>Experience:</strong> " . ($row['experience'] ? $row['experience'].' years' : 'Not set') . "</p>";
            echo "<p><strong>Availability:</strong> " . ($row['availability'] ?? 'Not set') . "</p>";

            echo "<a href='edit_doctor.php?id={$doctor_id}' class='btn btn-warning mt-3'>
                    <i class='fa-solid fa-pen'></i> Update
                  </a>";
        } else {
            echo "<p>No profile found.</p>";
        }
        ?>
      </div>

      <!-- Appointments -->
      <div id="appointments" class="section">
        <h3><i class="fa-solid fa-calendar-check"></i> My Appointments</h3>
        <?php
        $sql = "SELECT a.appointment_id, a.appointment_date, a.status, a.reason,
                       p.name AS pet_name, u.name AS owner_name
                FROM appointments a
                JOIN pets p ON a.pet_id = p.pet_id
                JOIN users u ON a.owner_id = u.id
                WHERE a.vet_id = ?
                ORDER BY a.appointment_date ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table class='table table-dark table-striped'>";
            echo "<thead><tr>
                    <th>Pet</th>
                    <th>Owner</th>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                $status_color = "secondary";
                if ($row['status'] == "Pending") $status_color = "warning";
                if ($row['status'] == "Approved") $status_color = "info";
                if ($row['status'] == "Completed") $status_color = "success";
                if ($row['status'] == "Cancelled") $status_color = "danger";

                echo "<tr>
                        <td>{$row['pet_name']}</td>
                        <td>{$row['owner_name']}</td>
                        <td>{$row['appointment_date']}</td>
                        <td>{$row['reason']}</td>
                        <td><span class='badge bg-$status_color'>{$row['status']}</span></td>
                        <td>";

                if ($row['status'] != "Cancelled" && $row['status'] != "Completed") {
                    echo "<form method='POST' action='../update_appointment.php' class='d-inline'>
                            <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
                            <select name='status' class='form-select form-select-sm d-inline w-auto'>
                              <option value='Pending' " . ($row['status']=='Pending'?'selected':'') . ">Pending</option>
                              <option value='Approved' " . ($row['status']=='Approved'?'selected':'') . ">Approved</option>
                              <option value='Completed' " . ($row['status']=='Completed'?'selected':'') . ">Completed</option>
                              <option value='Cancelled' " . ($row['status']=='Cancelled'?'selected':'') . ">Cancelled</option>
                            </select>
                            <button type='submit' class='btn btn-sm btn-warning'>Update</button>
                          </form>";
                } else {
                    echo "<em class='text-muted'>No actions</em>";
                }

                echo "</td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No upcoming appointments.</p>";
        }
        ?>
      </div>

      <!-- Pet Medical History -->
<div id="history" class="section">
  <h3><i class="fa-solid fa-file-medical"></i> Pet Medical History</h3>
  <?php
  $sql = "SELECT h.record_id, h.visit_date, h.diagnosis, h.treatment, 
                 p.name AS pet_name,
                 u.name AS vet_name
          FROM health_records h
          JOIN pets p ON h.pet_id = p.pet_id
          LEFT JOIN users u ON h.vet_id = u.id
          ORDER BY h.visit_date DESC";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      echo "<table class='table table-dark table-striped table-bordered'>";
      echo "<thead>
              <tr>
                <th>Date</th>
                <th>Pet</th>
                <th>Diagnosis</th>
                <th>Treatment</th>
                <th>Added By</th>
                <th>Action</th>
              </tr>
            </thead><tbody>";
      while ($row = $result->fetch_assoc()) {
          $added_by = $row['vet_name'] ? "Dr. {$row['vet_name']}" : "Owner";

          echo "<tr>
                  <td>{$row['visit_date']}</td>
                  <td>{$row['pet_name']}</td>
                  <td>{$row['diagnosis']}</td>
                  <td>{$row['treatment']}</td>
                  <td>$added_by</td>
                  <td>
                    <a href='delete_health.php?id={$row['record_id']}' 
                       class='btn btn-sm btn-danger'
                       onclick=\"return confirm('Are you sure you want to delete this record?');\">
                       Delete
                    </a>
                  </td>
                </tr>";
      }
      echo "</tbody></table>";
  } else {
      echo "<p>No medical history yet.</p>";
  }
  ?>
</div>


      <!-- Treatments -->
      <div id="treatments" class="section">
        <h3><i class="fa-solid fa-prescription"></i> Treatments</h3>
        <a href="treatment_doctor.php" class="btn btn-success mb-3">+ Add Treatment</a>
        <?php
        $sql = "SELECT t.treatment_id, t.treatment_date, t.notes, t.medication, t.follow_up_date, 
                       p.name AS pet_name
                FROM treatments t
                JOIN pets p ON t.pet_id = p.pet_id
                WHERE t.vet_id = ?
                ORDER BY t.treatment_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table class='table table-dark table-striped table-bordered'>";
            echo "<thead>
                    <tr>
                      <th>Date</th>
                      <th>Pet</th>
                      <th>Medication</th>
                      <th>Notes</th>
                      <th>Follow-up</th>
                      <th>Action</th>
                    </tr>
                  </thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['treatment_date']}</td>
                        <td>{$row['pet_name']}</td>
                        <td>{$row['medication']}</td>
                        <td>{$row['notes']}</td>
                        <td>{$row['follow_up_date']}</td>
                        <td>
                          <a href='edit_treatment.php?id={$row['treatment_id']}' class='btn btn-sm btn-warning'>Edit</a>
                          <a href='delete_treatment.php?id={$row['treatment_id']}' 
                             class='btn btn-sm btn-danger' 
                             onclick=\"return confirm('Are you sure you want to delete this treatment?');\">Delete</a>
                        </td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No treatments recorded yet.</p>";
        }
        ?>
      </div>

    </div>
  </div>

</body>
</html>
