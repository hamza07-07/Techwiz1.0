<?php
session_start();
include("config/db.php");

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Redirect based on role
            if ($user['role'] == "owner") {
                header("Location: pages/owner_dashboard.php");
                exit();
            } elseif ($user['role'] == "vet") {
                header("Location: pages/vet_dashboard.php");
                exit();
            } elseif ($user['role'] == "shelter") {
                header("Location: pages/shelter_dashboard.php");
                exit();
            } elseif ($user['role'] == "admin") {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Invalid role detected.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>No user found with that email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #0d6efd 0%, #6a11cb 100%);
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

    .login-box {
      background: rgba(0,0,0,0.85);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      width: 100%;
      max-width: 450px;
      position: relative;
      z-index: 2;
      color: #fff;
      animation: popUp 0.8s ease-out forwards;
    }

    .login-box h2 {
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
      color: #fff;
    }

    .btn-login {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      border: none;
      color: #fff;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-login:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    .form-control {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 15px;
      transition: all 0.3s ease;
    }
    .form-control::placeholder {
      color: rgba(255,255,255,0.7);
    }
    .form-control:focus {
      outline: none;
      background: rgba(255,255,255,0.1);
      color: #fff;
      box-shadow: 0 0 10px #ff4d6d;
      backdrop-filter: blur(10px);
    }

    .login-box a {
      color: #ffcc00;
      text-decoration: none;
    }
    .login-box a:hover {
      text-decoration: underline;
    }

    /* Floating shapes */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,77,109,0.2);
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

  <!-- Admin Login Button (top-right) -->
  <div style="position: absolute; top: 20px; right: 20px; z-index: 10;">
    <a href="admin_login.php" class="btn btn-warning">
      <i class="fa-solid fa-user-shield"></i> Admin Login
    </a>
  </div>

  <!-- Floating Shapes -->
  <div class="floating-shape" style="top:5%; left:10%; width:60px; height:60px;"></div>
  <div class="floating-shape" style="top:15%; right:15%; width:80px; height:80px;"></div>
  <div class="floating-shape" style="top:40%; left:20%; width:100px; height:100px;"></div>
  <div class="floating-shape" style="bottom:10%; right:10%; width:120px; height:120px;"></div>
  <div class="floating-shape" style="bottom:25%; left:30%; width:70px; height:70px;"></div>
  <div class="floating-shape" style="top:60%; right:40%; width:50px; height:50px;"></div>

  <div class="login-box">
    <h2>Login</h2>
    <?php echo $message; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" name="login" class="btn btn-login w-100">Login</button>
    </form>
    <p class="text-center mt-3">Don’t have an account? <a href="register.php">Register</a></p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
