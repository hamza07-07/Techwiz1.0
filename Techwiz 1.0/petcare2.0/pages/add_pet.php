<?php
session_start();

// Only owners or shelters can access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ["owner", "shelter"])) {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['id'];
$role = $_SESSION['role'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $species = mysqli_real_escape_string($conn, $_POST['species']);
    $breed   = mysqli_real_escape_string($conn, $_POST['breed']);
    $age     = (int) $_POST['age'];

    if ($role == "owner") {
    // ✅ Owner adds their own pets
    $sql = "INSERT INTO pets (owner_id, name, species, breed, age) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $user_id, $name, $species, $breed, $age);
    $redirect = "owner_dashboard.php#pets";

} elseif ($role == "shelter") {
    // ✅ Shelter adds pets linked to shelter_id
    $sql = "INSERT INTO pets (owner_id, name, species, breed, age) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $user_id, $name, $species, $breed, $age);
    $redirect = "shelter_dashboard.php#pets";

} elseif ($role == "admin") {
    // ✅ Admin can add pets (assigning to admin account as owner_id)
    $sql = "INSERT INTO pets (owner_id, name, species, breed, age) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $user_id, $name, $species, $breed, $age);
    $redirect = "manage_shelters.php";  // or manage_pets.php if you have that

} elseif ($role == "vet") {
    // ✅ Vet can add pets (linked to vet account)
    $sql = "INSERT INTO pets (owner_id, name, species, breed, age) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $user_id, $name, $species, $breed, $age);
    $redirect = "vet_dashboard.php#pets";

} else {
    // ❌ Unauthorized role
    header("Location: ../login.php");
    exit();
}



    if (mysqli_stmt_execute($stmt)) {
        header("Location: " . $redirect);
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Error adding pet: " . mysqli_error($conn) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Pet - FurShield</title>
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
      overflow: hidden;
    }
    .floating-shape { position:absolute; border-radius:50%; background:rgba(255,255,255,0.15); animation:float 6s ease-in-out infinite; }
    @keyframes float { 0%,100% { transform:translateY(0);} 50%{ transform:translateY(-20px);} }
    .card { background:rgba(0,0,0,0.85); padding:30px; border-radius:15px; width:100%; max-width:500px; box-shadow:0 10px 30px rgba(0,0,0,0.5); z-index:2; animation:popup 0.8s ease; }
    @keyframes popup { from{opacity:0; transform:scale(0.9);} to{opacity:1; transform:scale(1);} }
    .card h3 { font-weight:600; text-align:center; margin-bottom:20px; background:linear-gradient(90deg,#ff4d6d,#ffcc00); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
    .form-label { color:rgba(255,255,255,0.75); font-weight:500; }
    .form-control { background:rgba(255,255,255,0.1); border:none; color:#fff; border-radius:10px; padding:12px; }
    .form-control::placeholder { color:rgba(255,255,255,0.6); }
    .btn-custom { background:linear-gradient(90deg,#ff4d6d,#ffcc00); border:none; color:#fff; font-weight:500; transition:0.3s; }
    .btn-custom:hover { transform:scale(1.05); box-shadow:0 5px 15px rgba(0,0,0,0.4); }
    .btn-secondary { background:rgba(255,255,255,0.2); border:none; color:#fff; transition:0.3s; }
    .btn-secondary:hover { background:rgba(255,255,255,0.35); }
  </style>
</head>
<body>
  <div class="floating-shape" style="top:5%; left:10%; width:60px; height:60px;"></div>
  <div class="floating-shape" style="top:20%; right:15%; width:80px; height:80px;"></div>
  <div class="floating-shape" style="bottom:10%; right:10%; width:120px; height:120px;"></div>

  <div class="card">
    <h3>➕ Add New Pet</h3>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Pet Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Species</label>
        <input type="text" name="species" class="form-control" placeholder="Dog, Cat, etc." required>
      </div>
      <div class="mb-3">
        <label class="form-label">Breed</label>
        <input type="text" name="breed" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Age</label>
        <input type="number" name="age" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-custom w-100">Save Pet</button>
      <a href="<?php echo ($role=='owner'?'owner_dashboard.php#pets':'shelter_dashboard.php#pets'); ?>" class="btn btn-secondary w-100 mt-2">Cancel</a>
    </form>
  </div>
</body>
</html>
