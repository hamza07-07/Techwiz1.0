<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'shelter') {
    header("Location: login.php");
    exit();
}
include("../config/db.php");

$shelter_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Handle chat submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $chat_with = (int)$_POST['receiver_id']; // this is the owner ID

    // Insert reply from shelter → owner
    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $shelter_id, $chat_with, $msg);
    mysqli_stmt_execute($stmt);

    header("Location: shelter_dashboard.php?chat_with=$chat_with");
    exit();
}


// Quick stats
$pets_count = $conn->query("SELECT COUNT(*) AS c FROM pets")->fetch_assoc()['c'] ?? 0;
$records_count = $conn->query("SELECT COUNT(*) AS c FROM health_records")->fetch_assoc()['c'] ?? 0;

// Pets list
$stmt = $conn->prepare("
  SELECT p.pet_id, p.name, p.species, p.breed, p.age, p.created_at,
         u.name AS owner_name
  FROM pets p
  LEFT JOIN users u ON p.owner_id = u.id
  ORDER BY p.created_at DESC
");

$stmt->execute();
$pets = $stmt->get_result();

// Owners who chatted with shelter
// Owners who have chatted with ANY shelter
$owners = $conn->query("
    SELECT u.id, u.name
    FROM users u
    JOIN messages m 
      ON ( (m.sender_id = u.id AND m.receiver_id = {$shelter_id})
        OR (m.receiver_id = u.id AND m.sender_id = {$shelter_id}) )
    WHERE u.role = 'owner'
    GROUP BY u.id, u.name
    ORDER BY MAX(m.created_at) DESC
");



// Active chat
$chat_with = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : 0;
$chat_msgs = [];
if ($chat_with) {
  $q = $conn->prepare("SELECT m.*, u.name AS sender_name, u.role AS sender_role
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id IN (SELECT id FROM users WHERE role='shelter')) 
       OR (m.receiver_id = ? AND m.sender_id = ?)
       OR (m.sender_id IN (SELECT id FROM users WHERE role='shelter') AND m.receiver_id = ?)
    ORDER BY m.created_at ASC");

$q->bind_param("iiii", $chat_with, $chat_with, $chat_with, $chat_with);



    $q->execute();
    $chat_msgs = $q->get_result();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Shelter Dashboard - FurShield</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0; 
      min-height:100vh;
      background: linear-gradient(120deg,#0d6efd,#6a11cb);
      color: #fff;
      display:flex; 
      flex-direction:column;
    }
    .navbar { background: rgba(0,0,0,0.85); }
    .navbar-brand { font-weight:700; background: linear-gradient(90deg,#ff4d6d,#ffcc00); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
    .page { flex:1; display:flex; gap:20px; padding:20px; }
    .sidebar { width:260px; background: rgba(0,0,0,0.6); padding:18px; border-radius:12px; }
    .sidebar .links a { display:block; color:#fff; padding:10px; margin-bottom:8px; text-decoration:none; border-radius:8px; }
    .sidebar .links a:hover, .sidebar .links a.active { background: linear-gradient(90deg,#ff4d6d,#ffcc00); color:#fff; }
    .main { flex:1; min-width:0; }
    .card-glass { background: rgba(0,0,0,0.45); padding:16px; border-radius:12px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); margin-bottom:18px; }
    .pet-card { background: rgba(255,255,255,0.06); color:#fff; border-radius:12px; padding:12px; display:flex; gap:12px; align-items:center; }
    .pet-image { width:92px; height:92px; border-radius:8px; background:linear-gradient(180deg,#ccc,#aaa); display:flex; align-items:center; justify-content:center; color:#333; font-weight:700; }
    .table thead th { color:#000; }
    .table tbody td { color:#000; vertical-align:middle; }
    .small-muted { color: rgba(255,255,255,0.75); }

    /* Chat styles */
    .chat-box { background:#1e1e2f; border-radius:12px; padding:12px; display:flex; flex-direction:column; height:400px; }
    .chat-messages { flex:1; overflow-y:auto; margin-bottom:10px; display:flex; flex-direction:column; gap:6px; }
    .chat-msg { padding:8px 12px; border-radius:15px; max-width:60%; font-size:14px; line-height:1.3; }
    .msg-sender { align-self:flex-end; background:#0d6efd; color:#fff; border-bottom-right-radius:2px; }
    .msg-receiver { align-self:flex-start; background:#6a11cb; color:#fff; border-bottom-left-radius:2px; }
    .chat-msg small { display:block; font-size:11px; opacity:0.7; margin-top:3px; }
    .chat-form { display:flex; gap:5px; }
    .chat-form input { flex:1; }

    @media(max-width:992px){
      .page { flex-direction:column; padding:14px; }
      .sidebar { width:100%; display:flex; gap:12px; overflow:auto; }
      .sidebar .links { display:flex; gap:8px; }
      .sidebar .links a { white-space:nowrap; }
    }

    /* Owner chat links in sidebar */
.sidebar .owner-chat {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  margin-bottom: 8px;
  border-radius: 10px;
  background: rgba(255,255,255,0.05);
  color: #fff;
  text-decoration: none;
  transition: all 0.25s ease;
  font-weight: 500;
}

.sidebar .owner-chat:hover {
  background: linear-gradient(90deg,#ff4d6d,#ffcc00);
  color: #fff;
  transform: translateX(4px);
}

.sidebar .owner-chat.active {
  background: linear-gradient(90deg,#ff4d6d,#ffcc00);
  color: #fff;
}

.owner-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0d6efd, #6a11cb);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
  color: #fff;
  flex-shrink: 0;
  box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

.owner-name {
  font-size: 15px;
  font-weight: 600;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sender-info {
  margin-bottom: 3px;
  font-size: 11px;
  opacity: 0.8;
}


  </style>
</head>
<body>
  <nav class="navbar navbar-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <div>
        <a class="navbar-brand"><i class="fa-solid fa-house-chimney-crack"></i> Shelter Dashboard</a>
        <span class="ms-3">Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
      </div>
      <div>
        <a href="../logout.php" class="btn btn-warning">Logout</a>
      </div>
    </div>
  </nav>

  <div class="page container-fluid">
    <aside class="sidebar">
      <div class="links mb-3">
        <a href="shelter_dashboard.php" class="<?php echo !$chat_with ? 'active':''; ?>"><i class="fa-solid fa-house"></i> Overview</a>
        <a href="shelter_dashboard.php#pets"><i class="fa-solid fa-paw"></i> Manage Pets</a>
        <a href="shelter_dashboard.php#health"><i class="fa-solid fa-notes-medical"></i> Health Records</a>
        <a href="pages/add_pet.php" class="btn btn-sm btn-primary mt-2 w-100">+ Add Pet</a>
      </div>

      

      <div class="card-glass text-center mt-3">
        <h6 class="mb-1">Adoptable Pets</h6>
        <div style="font-size:20px;font-weight:700;"><?php echo intval($pets_count); ?></div>
      </div>

      <strong class="d-block mb-2">💬 Chats</strong>
      <?php while ($o = $owners->fetch_assoc()): ?>
        <a href="shelter_dashboard.php?chat_with=<?php echo $o['id']; ?>" 
   class="owner-chat <?php echo ($chat_with==$o['id'])?'active':''; ?>">
  <div class="owner-avatar"><?php echo strtoupper(substr($o['name'],0,1)); ?></div>
  <div class="owner-name"><?php echo htmlspecialchars($o['name']); ?></div>
</a>

      <?php endwhile; ?>
    </aside>

    <main class="main">
      <?php if ($chat_with): ?>
        <!-- Chat only -->
        <section class="card-glass">
          <h5>Chat</h5>
          <div class="chat-box">
            <div class="chat-messages">
  <?php while ($m = $chat_msgs->fetch_assoc()): 
    $isShelter = $m['sender_id'] == $shelter_id;
    $cls = $isShelter ? "msg-sender" : "msg-receiver"; ?>
    <div class="chat-msg <?php echo $cls; ?>">
      <?php if (!$isShelter): ?>
        <div class="sender-info">
          <small><strong><?php echo htmlspecialchars($m['sender_name']); ?> (<?php echo ucfirst($m['sender_role']); ?>)</strong></small>
        </div>
      <?php endif; ?>
      <?php echo htmlspecialchars($m['message']); ?>
      <small><?php echo date("H:i", strtotime($m['created_at'])); ?></small>
    </div>
  <?php endwhile; ?>
</div>

            <form method="POST" class="chat-form">
              <input type="hidden" name="receiver_id" value="<?php echo $chat_with; ?>">
              <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
              <button type="submit" class="btn btn-primary">Send</button>
            </form>
          </div>
        </section>
      <?php else: ?>
        <!-- Normal dashboard -->
        <section class="card-glass">
          <h4>Overview</h4>
          <p class="small-muted">Quick shelter stats</p>
          <div class="row mt-3">
            <div class="col-6 col-md-3 mb-2">
              <div class="card-glass text-center"><strong><?php echo $pets_count; ?></strong><div class="small-muted">Pets</div></div>
            </div>
            <div class="col-6 col-md-3 mb-2">
              <div class="card-glass text-center"><strong><?php echo $records_count; ?></strong><div class="small-muted">Health Logs</div></div>
            </div>
          </div>
        </section>

        <!-- Pets list -->
        <section id="pets" class="card-glass">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">All Pets</h5>
            <a href="pages/add_pet.php" class="btn btn-sm btn-primary">+ Add Pet</a>
          </div>
          <div class="row">
            <?php while ($p = $pets->fetch_assoc()): ?>
              <div class="col-md-6 col-lg-4 mb-3">
                <div class="pet-card">
                  <div class="pet-image"><?php echo strtoupper(substr($p['name'],0,1)); ?></div>
                  <div style="flex:1">
  <div style="font-weight:600;"><?php echo htmlspecialchars($p['name']); ?></div>
  <div class="small-muted">
    <?php echo htmlspecialchars($p['species'] ?: 'Unknown'); ?> • 
    <?php echo htmlspecialchars($p['breed'] ?: 'Mixed'); ?>
  </div>
  <div class="small-muted">Age: <?php echo htmlspecialchars($p['age'] ?? 'N/A'); ?></div>
  <div class="small-muted">
    Owner: <?php echo htmlspecialchars($p['owner_name'] ?? '—'); ?>
  </div>
  <div class="mt-2">
    <a href="pages/edit_pet.php?id=<?php echo intval($p['pet_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="pages/delete_pet.php?id=<?php echo intval($p['pet_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this pet?')">Delete</a>
    <a href="view_health.php?pet_id=<?php echo intval($p['pet_id']); ?>" class="btn btn-sm btn-secondary">Health</a>
  </div>
</div>

                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </section>

        <!-- Health quick view -->
        <section id="health" class="card-glass">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Recent Health Records</h5>
            <a href="add_health.php" class="btn btn-sm btn-success">+ Add Health Log</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover bg-light rounded">
              <thead class="table-dark">
                <tr><th>#</th><th>Pet</th><th>Date</th><th>Diagnosis</th><th>Treatment</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php
                $q = $conn->query("SELECT hr.record_id, hr.visit_date, hr.diagnosis, hr.treatment, p.name, p.pet_id
                                    FROM health_records hr
                                    JOIN pets p ON hr.pet_id = p.pet_id
                                    ORDER BY hr.visit_date DESC LIMIT 10");
                $k=1;
                while ($r = $q->fetch_assoc()):
                ?>
                <tr>
                  <td><?php echo $k++; ?></td>
                  <td><?php echo htmlspecialchars($r['name']); ?></td>
                  <td><?php echo htmlspecialchars($r['visit_date']); ?></td>
                  <td><?php echo htmlspecialchars($r['diagnosis']); ?></td>
                  <td><?php echo htmlspecialchars($r['treatment'] ?: '-'); ?></td>
                  <td><a href="view_health.php?pet_id=<?php echo intval($r['pet_id']); ?>" class="btn btn-sm btn-secondary">View</a></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
