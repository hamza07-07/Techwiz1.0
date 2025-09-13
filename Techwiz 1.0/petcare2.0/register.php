<?php
session_start();
include("config/db.php");

$message = "";

if (isset($_POST['register'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Insert into DB
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$password','$role')";
   if (mysqli_query($conn, $sql)) {
    header("Location: login.php?registered=1");
    exit();
} else {
    $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - FurShield</title>
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

    /* Pop-up animation for register box */
    @keyframes popUp {
      0% { opacity: 0; transform: scale(0.8) translateY(50px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    .register-box {
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

    .register-box h2 {
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
      color: #fff;
    }

    .btn-register {
      background: linear-gradient(90deg,#ff4d6d,#ffcc00);
      border: none;
      color: #fff;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-register:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0,0,0,0.4);
    }

    /* Inputs and dropdowns */
    .form-control, .form-select {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 15px;
      transition: all 0.3s ease;
    }
    .form-control::placeholder, .form-select option {
      color: rgba(255,255,255,0.7);
    }
    .form-control:focus, .form-select:focus {
      outline: none;
      background: rgba(255,255,255,0.1);
      color: #fff;
      box-shadow: 0 0 10px #ff4d6d;
      backdrop-filter: blur(10px);
    }

    /* Dropdown menu remains dark */
    .form-select {
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }
    .form-select option {
      background: #000000cc;
      color: #fff;
    }

    /* Floating Shapes */
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

    /* Links */
    .register-box a {
      color: #ffcc00;
      text-decoration: none;
    }
    .register-box a:hover {
      text-decoration: underline;
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

  <div class="register-box">
    <h2>Create Account</h2>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="text" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
          <option value="">-- Select Role --</option>
          <option value="owner">Pet Owner</option>
          <option value="vet">Veterinarian</option>
          <option value="shelter">Shelter</option>
        </select>
      </div>
      <button type="submit" name="register" class="btn btn-register w-100">Register</button>
    </form>
    <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
