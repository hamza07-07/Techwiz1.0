<?php
session_start();
include("config/db.php");

$message = "";

if (isset($_POST['admin_login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Fetch only admin users
    $sql = "SELECT * FROM users WHERE email='$email' AND role='admin' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);

        if (password_verify($password, $admin['password'])) {
            // Save session
            $_SESSION['id'] = $admin['id'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['name'] = $admin['name'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Access denied! Not an admin account.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #ff4d6d 0%, #ffcc00 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
      position: relative;
    }

    @keyframes popUp {
      0% { opacity: 0; transform: scale(0.8) translateY(50px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    .admin-box {
      background: rgba(0,0,0,0.85);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      width: 100%;
      max-width: 450px;
      color: #fff;
      animation: popUp 0.8s ease-out forwards;
      position: relative;
      z-index: 2;
    }

    .admin-box h2 {
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
      color: #fff;
    }

    .btn-admin {
      background: linear-gradient(90deg,#0d6efd,#6a11cb);
      border: none;
      color: #fff;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-admin:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    .form-control {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 15px;
    }
    .form-control::placeholder {
      color: rgba(255,255,255,0.7);
    }

    .admin-box a {
      color: #ffcc00;
      text-decoration: none;
    }
    .admin-box a:hover {
      text-decoration: underline;
    }

    /* Floating shapes */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      animation: float 6s ease-in-out infinite;
      z-index: 1;
    }
    @keyframes float {
      0%,100% { transform: translateY(0) rotate(0deg);}
      50% { transform: translateY(-20px) rotate(45deg);}
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

  <div class="admin-box">
    <h2><i class="fa-solid fa-user-shield"></i> Admin Login</h2>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Admin Email</label>
        <input type="email" name="email" class="form-control" placeholder="admin@furshield.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter admin password" required>
      </div>
      <button type="submit" name="admin_login" class="btn btn-admin w-100">Login as Admin</button>
    </form>
    <p class="text-center mt-3"><a href="login.php">← Back to User Login</a></p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
