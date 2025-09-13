<?php
session_start();

// Restrict access to owner only
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

// ✅ include DB correctly (because file is inside /pages/)
include("../config/db.php");




// Restrict access to owner only
if (!isset($_SESSION['role']) || $_SESSION['role'] != "owner") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$owner_id = $_SESSION['id'];

// ✅ Add this block

// Handle chat submission (send only to ONE shelter, but visible to all shelters)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $msg = mysqli_real_escape_string($conn, $msg);
        $sender_id = $owner_id;

        // ✅ Select ONE shelter (e.g., the first shelter user in DB)
        $shelter_q = mysqli_query($conn, "SELECT id FROM users WHERE role='shelter' ORDER BY id ASC LIMIT 1");
        $shelter = mysqli_fetch_assoc($shelter_q);
        $receiver_id = $shelter['id'] ?? null;

        if ($receiver_id) {
            $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $msg);
            mysqli_stmt_execute($stmt);
        }

        // Redirect back to refresh chat
        header("Location: owner_dashboard.php");
        exit();
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Owner Dashboard - FurShield</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg, #0d6efd, #6a11cb); min-height: 100vh; color: #fff; margin: 0; overflow-x: hidden; position: relative; }
    .floating-shape { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.15); animation: float 6s ease-in-out infinite; z-index: 0; }
    @keyframes float { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(45deg); } }
    .navbar { background: rgba(0,0,0,0.85); padding: 15px; position: relative; z-index: 2; }
    .navbar-brand { font-weight: bold; font-size: 1.5rem; background: linear-gradient(90deg, #ff4d6d, #ffcc00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: textGradient 3s ease-in-out infinite; }
    @keyframes textGradient { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    .btn-logout { background: linear-gradient(90deg,#ff4d6d,#ffcc00); border: none; color: #fff; font-weight: 500; transition: 0.3s; }
    .btn-logout:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
    .wrapper { display: flex; height: calc(100vh - 70px); position: relative; z-index: 2; }
    .sidebar { width: 260px; background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); padding: 20px; overflow-y: auto; }
    .sidebar a { display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; color: #fff; text-decoration: none; transition: 0.3s; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(90deg,#ff4d6d,#ffcc00); color: #fff; }
    .content { flex: 1; padding: 20px; overflow-y: auto; }
    .section { background: rgba(0,0,0,0.4); backdrop-filter: blur(10px); padding: 25px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); transition: transform 0.3s, box-shadow 0.3s; }
    .section:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
    .section h3 { margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .product-card { background: rgba(0,0,0,0.5); border-radius: 12px; padding: 15px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.5); }
    .product-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; }
    .btn { border-radius: 8px; font-weight: 500; }
    .highlight { color: #ffcc00; font-weight: 600; }

    /* Responsive */
    @media (max-width: 992px) {
      .wrapper { flex-direction: column; }
      .sidebar { width: 100%; display: flex; flex-wrap: wrap; justify-content: space-around; position: sticky; top: 0; z-index: 10; }
      .sidebar a { flex: 1 1 auto; text-align: center; margin: 5px; font-size: 14px; padding: 8px; }
      .content { padding: 15px; }
      .section { padding: 15px; }
    }
    @media (max-width: 576px) {
      .navbar-brand { font-size: 1.2rem; }
      .btn-logout { font-size: 0.9rem; padding: 6px 10px; }
      .products-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
      .product-card img { height: 120px; }
      .section h3 { font-size: 1.1rem; }
    }

/* Chat styles */
.chat-btn { position: fixed; bottom: 20px; right: 20px; background: #0d6efd; border: none; border-radius: 50%; width: 60px; height: 60px; font-size: 28px; color: #fff; cursor: pointer; z-index: 999; }
.chat-popup { display: none; position: fixed; bottom: 90px; right: 20px; width: 320px; max-height: 400px; background: #1e1e2f; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); flex-direction: column; overflow: hidden; z-index: 1000; }
.chat-header { background: #0d6efd; padding: 10px; color: #fff; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
.chat-body { flex: 1; padding: 10px; overflow-y: auto; display: flex; flex-direction: column; }
.chat-footer { display: flex; gap: 5px; padding: 8px; background: #2a2a3c; }
.chat-msg { padding: 8px 12px; border-radius: 15px; margin-bottom: 8px; max-width: 80%; word-wrap: break-word; color: #fff; }
.msg-sender { align-self: flex-end; background: #007bff; }
.msg-receiver { align-self: flex-start; background: #6a11cb; }
.time { font-size: 10px; opacity: 0.7; margin-top: 3px; text-align: right; }



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
      <a class="navbar-brand"><i class="fa-solid fa-dog"></i> Owner Dashboard</a>
      <a href="../logout.php" class="btn btn-logout">Logout</a>
    </div>
  </nav>

  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
      <a href="#welcome" class="active"><i class="fa-solid fa-house"></i> Welcome</a>
      <a href="#pets"><i class="fa-solid fa-paw"></i> Pet Profiles</a>
      <a href="#health"><i class="fa-solid fa-notes-medical"></i> Health Records</a>
      <a href="#appointments"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
      <a href="#products"><i class="fa-solid fa-basket-shopping"></i> Products</a>
      <a href="#cart"><i class="fa-solid fa-cart-shopping"></i> My Cart</a>
      <a href="#orders"><i class="fa-solid fa-box"></i> My Orders</a>
      <a href="#care"><i class="fa-solid fa-lightbulb"></i> Care Tips</a>
      <a href="#notifications"><i class="fa-solid fa-bell"></i> Notifications</a>
      <a href="#feedback"><i class="fa-solid fa-star"></i> Feedback</a>
    </div>

    <!-- Main Content -->
    <div class="content">

      <!-- Welcome -->
      <div id="welcome" class="section">
        <h3>Welcome, <span class="highlight"><?php echo $_SESSION['name']; ?></span> 👋</h3>
        <p>Manage your pets, health records, appointments, products, and care tips all in one place.</p>
      </div>

      <!-- Pet Profiles -->
      <div id="pets" class="section">
        <h3><i class="fa-solid fa-paw"></i> Manage Pet Profiles</h3>
        <a href="add_pet.php" class="btn btn-primary mb-3">+ Add Pet</a>
        <div id="petList">
          <?php
          $sql = "SELECT * FROM pets WHERE owner_id=?";
          $stmt = mysqli_prepare($conn, $sql);
          mysqli_stmt_bind_param($stmt, "i", $owner_id);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          if (mysqli_num_rows($result) > 0) {
              while ($pet = mysqli_fetch_assoc($result)) {
                  echo "<div class='pet-item mb-2'>
                          <strong>{$pet['name']}</strong> ({$pet['species']} - {$pet['breed']}, Age: {$pet['age']})
                          <a href='edit_pet.php?id={$pet['pet_id']}' class='btn btn-sm btn-warning'>Edit</a>
                          <a href='delete_pet.php?id={$pet['pet_id']}' class='btn btn-sm btn-danger'>Delete</a>
                        </div>";
              }
          } else {
              echo "<p>No pets added yet.</p>";
          }
          ?>
        </div>
      </div>

     <!-- Health Records -->
<div id="health" class="section">
  <h3><i class="fa-solid fa-notes-medical"></i> Health Records</h3>
  <a href="add_health.php" class="btn btn-success mb-3">+ Add Record</a>
  <div class="table-responsive">
    <table class="table table-dark table-striped table-bordered">
      <thead>
        <tr>
          <th>Date</th>
          <th>Pet</th>
          <th>Diagnosis</th>
          <th>Treatment</th>
          <th>Notes</th>
          <th>Vet</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT h.record_id, h.visit_date, h.diagnosis, h.treatment, h.notes, 
                       p.name AS pet_name, u.name AS vet_name
                FROM health_records h
                JOIN pets p ON h.pet_id = p.pet_id
                LEFT JOIN users u ON h.vet_id = u.id
                WHERE p.owner_id = ?
                ORDER BY h.visit_date DESC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $owner_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['visit_date']}</td>
                            <td>{$row['pet_name']}</td>
                            <td>{$row['diagnosis']}</td>
                            <td>{$row['treatment']}</td>
                            <td>{$row['notes']}</td>
                            <td>" . ($row['vet_name'] ? "👨‍⚕️ {$row['vet_name']}" : "—") . "</td>
                            <td>
                              <a href='delete_health.php?id={$row['record_id']}' 
                                 class='btn btn-sm btn-danger'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No health records yet.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='7'>❌ SQL error: " . mysqli_error($conn) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>



      <!-- ✅ Appointments -->
<div id="appointments" class="section">
  <h3><i class="fa-solid fa-calendar-check"></i> Appointments</h3>
  <a href="book_appointment.php" class="btn btn-success mb-3">+ Book Appointment</a>

  <div class="table-responsive">
    <table class="table table-dark table-striped table-bordered">
      <thead>
        <tr>
          <th>Date</th>
          <th>Pet</th>
          <th>Vet</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT a.appointment_id, a.appointment_date, a.reason, a.status,
                       p.name AS pet_name, v.name AS vet_name
                FROM appointments a
                JOIN pets p ON a.pet_id = p.pet_id
                LEFT JOIN users v ON a.vet_id = v.id
                WHERE a.owner_id = ?
                ORDER BY a.appointment_date DESC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $owner_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $vet = $row['vet_name'] ? $row['vet_name'] : "Not Assigned";

                    // ✅ Status badge
                    $status_color = "secondary";
                    if ($row['status'] == "Pending") $status_color = "warning";
                    if ($row['status'] == "Approved") $status_color = "info";
                    if ($row['status'] == "Completed") $status_color = "success";
                    if ($row['status'] == "Cancelled") $status_color = "danger";

                    echo "<tr>
                            <td>{$row['appointment_date']}</td>
                            <td>{$row['pet_name']}</td>
                            <td>{$vet}</td>
                            <td>{$row['reason']}</td>
                            <td><span class='badge bg-$status_color'>{$row['status']}</span></td>
                            <td>
                              <a href='delete_appointment.php?id={$row['appointment_id']}' 
                                 class='btn btn-sm btn-danger'
                                 onclick=\"return confirm('Are you sure you want to cancel this appointment?');\">
                                 Cancel
                              </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No appointments booked yet.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-danger'>❌ SQL error: " . mysqli_error($conn) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>


    
      <!-- Cart Section -->
      <div id="cart" class="section">
        <h3><i class="fa-solid fa-cart-shopping"></i> My Cart</h3>
        <?php
        $sql = "SELECT c.cart_id, c.quantity, p.name, p.price 
                FROM cart c
                JOIN products p ON c.product_id = p.product_id
                WHERE c.owner_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $owner_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            echo "<form method='POST' action='place_order.php'>";
            echo "<table class='table table-dark table-bordered table-striped'>";
            echo "<thead>
                    <tr>
                      <th>Product</th>
                      <th>Price</th>
                      <th>Quantity</th>
                      <th>Total</th>
                      <th>Action</th>
                    </tr>
                  </thead><tbody>";
            $grand_total = 0;

            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row['price'] * $row['quantity'];
                $grand_total += $total;

                echo "<tr>
                        <td>{$row['name']}</td>
                        <td>\${$row['price']}</td>
                        <td>{$row['quantity']}</td>
                        <td>\$$total</td>
                        <td>
                          <a href='remove_from_cart.php?id={$row['cart_id']}' 
                             class='btn btn-sm btn-danger'>Remove</a>
                        </td>
                      </tr>";
            }

            echo "<tr>
                    <td colspan='3'><strong>Grand Total</strong></td>
                    <td colspan='2'><strong>\$$grand_total</strong></td>
                  </tr>";
            echo "</tbody></table>";
            echo "<button type='submit' class='btn btn-success'>Place Order</button>";
            echo "</form>";
        } else {
            echo "<p>Your cart is empty.</p>";
        }
        ?>
      </div>

      <!-- Products Section -->
      <div id="products" class="section">
        <h3><i class="fa-solid fa-basket-shopping"></i> Products</h3>
        <div class="products-grid">
          <?php
          $sql = "SELECT * FROM products ORDER BY product_id DESC";
          $result = mysqli_query($conn, $sql);

          if ($result && mysqli_num_rows($result) > 0) {
              while ($product = mysqli_fetch_assoc($result)) {
                  echo "
                  <div class='product-card'>
                    <img src='../assets/products/{$product['image']}' alt='{$product['name']}'>
                    <h5>{$product['name']}</h5>
                    <p class='text-warning'>\${$product['price']}</p>
                    <form method='POST' action='add_to_cart.php'>
                      <input type='hidden' name='product_id' value='{$product['product_id']}'>
                      <input type='number' name='quantity' value='1' min='1' class='form-control mb-2'>
                      <button type='submit' class='btn btn-primary btn-sm'>Add to Cart</button>
                    </form>
                  </div>";
              }
          } else {
              echo "<p>No products available yet.</p>";
          }
          ?>
        </div>
      </div>

      <!-- Orders Section -->
      <div id="orders" class="section">
        <h3><i class="fa-solid fa-box"></i> My Orders</h3>
        <?php
       $sql = "SELECT o.order_id, o.order_date, o.status, 
               p.name, oi.quantity, p.price
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.owner_id = ?
        ORDER BY o.order_date DESC";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $owner_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            echo "<table class='table table-dark table-bordered table-striped'>";
            echo "<thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Product</th>
                      <th>Quantity</th>
                      <th>Price</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th>Date</th>
                    </tr>
                  </thead><tbody>";

            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row['quantity'] * $row['price'];
                $status_color = "secondary";
                if ($row['status'] == "Pending") $status_color = "warning";
                if ($row['status'] == "Shipped") $status_color = "info";
                if ($row['status'] == "Completed") $status_color = "success";
                if ($row['status'] == "Cancelled") $status_color = "danger";

                echo "<tr>
                        <td>#{$row['order_id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>\${$row['price']}</td>
                        <td>\$$total</td>
                        <td><span class='badge bg-$status_color'>{$row['status']}</span></td>
                        <td>{$row['order_date']}</td>
                      </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<p>No orders placed yet.</p>";
        }
        ?>
      </div>

      <!-- Care Tips Section -->
      <div id="care" class="section">
        <h3><i class="fa-solid fa-lightbulb"></i> Care Tips</h3>
        <div class="products-grid">
          <div class="product-card"><h5>🐕 Grooming</h5><p>Brush your dog’s coat twice a week to reduce shedding and keep fur healthy.</p></div>
          <div class="product-card"><h5>🍖 Feeding</h5><p>Feed pets at the same time daily and avoid giving too many treats.</p></div>
          <div class="product-card"><h5>🐾 Exercise</h5><p>Take your pets on daily walks to keep them active and prevent obesity.</p></div>
          <div class="product-card"><h5>💉 Health</h5><p>Keep track of vaccinations and schedule annual vet check-ups.</p></div>
          <div class="product-card"><h5>🌦️ Seasonal Care</h5><p>Protect pets from extreme weather — sweaters in winter, shade in summer.</p></div>
        </div>
      </div>

      <!-- Notifications Section -->
<div id="notifications" class="section">
  <h3><i class="fa-solid fa-bell"></i> Notifications</h3>
  <ul class="list-group">
    <?php
    $has_notifications = false;

    // 🔔 Custom vet/admin notifications
    $sql = "SELECT message, created_at FROM notifications 
            WHERE owner_id = ? 
            ORDER BY created_at DESC LIMIT 10";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $has_notifications = true;
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li class='list-group-item bg-dark text-white'>
                    🔔 {$row['message']}
                    <br><small class='text-muted'>{$row['created_at']}</small>
                  </li>";
        }
    }

    // 📅 Upcoming appointments (next 7 days)
    $sql = "SELECT a.appointment_date, a.reason, p.name AS pet_name
            FROM appointments a
            JOIN pets p ON a.pet_id = p.pet_id
            WHERE a.owner_id = ? 
              AND a.appointment_date >= CURDATE() 
              AND a.appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY a.appointment_date ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $has_notifications = true;
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li class='list-group-item bg-dark text-white'>
                    📅 Upcoming Appointment for <strong>{$row['pet_name']}</strong> on 
                    <span class='text-warning'>{$row['appointment_date']}</span> – {$row['reason']}
                  </li>";
        }
    }

    // 💉 Recent health records (last 14 days)
    $sql = "SELECT h.visit_date, h.diagnosis, p.name AS pet_name
            FROM health_records h
            JOIN pets p ON h.pet_id = p.pet_id
            WHERE p.owner_id = ? 
              AND h.visit_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            ORDER BY h.visit_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $owner_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $has_notifications = true;
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li class='list-group-item bg-dark text-white'>
                    💉 Recent Health Record for <strong>{$row['pet_name']}</strong> 
                    on <span class='text-warning'>{$row['visit_date']}</span> – {$row['diagnosis']}
                  </li>";
        }
    }

    // If nothing at all
    if (!$has_notifications) {
        echo "<li class='list-group-item bg-dark text-white text-center'>
                No notifications at the moment.
              </li>";
    }
    ?>
  </ul>
</div>


      <!-- Feedback Section -->
      <div id="feedback" class="section">
        <h3><i class="fa-solid fa-star"></i> Feedback</h3>
        <form method="POST" action="submit_feedback.php" class="mb-3">
          <div class="mb-2">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-control" required>
              <option value="">-- Choose Rating --</option>
              <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
              <option value="4">⭐⭐⭐⭐ Good</option>
              <option value="3">⭐⭐⭐ Average</option>
              <option value="2">⭐⭐ Poor</option>
              <option value="1">⭐ Very Bad</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Comment</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Your feedback..."></textarea>
          </div>
          <button type="submit" class="btn btn-success">Submit Feedback</button>
        </form>
        <h5 class="mt-4">Your Previous Feedback</h5>
        <ul class="list-group">
          <?php
          $sql = "SELECT rating, comment, created_at FROM feedback WHERE owner_id = ? ORDER BY created_at DESC";
          $stmt = mysqli_prepare($conn, $sql);
          mysqli_stmt_bind_param($stmt, "i", $owner_id);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                  echo "<li class='list-group-item bg-dark text-white'>
                          ⭐ Rating: {$row['rating']}<br>
                          💬 {$row['comment']}<br>
                          <small class='text-muted'>Submitted on {$row['created_at']}</small>
                        </li>";
              }
          } else {
              echo "<li class='list-group-item bg-dark text-white text-center'>No feedback submitted yet.</li>";
          }
          ?>
        </ul>
      </div>

    </div>
  </div>
  <!-- Chat Popup -->
<div id="chatPopup" class="chat-popup">
  <div class="chat-header">
    <span>🐾 Chat with Shelter</span>
    <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
  </div>
  <div class="chat-body" id="chatBody">
    <?php
    $sql = "SELECT m.*, u.name AS sender_name 
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.sender_id = ? OR m.receiver_id = ?
            ORDER BY m.created_at ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $owner_id, $owner_id);
    mysqli_stmt_execute($stmt);
    $messages = mysqli_stmt_get_result($stmt);

    while ($msg = mysqli_fetch_assoc($messages)) {
        $isSender = $msg['sender_id'] == $owner_id;
        $class = $isSender ? "msg-sender" : "msg-receiver";
        echo "<div class='chat-msg {$class}'>"
              . htmlspecialchars($msg['message']) .
              "<div class='time'>" . date("H:i", strtotime($msg['created_at'])) . "</div>
            </div>";
    }
    ?>
  </div>

  <form method="POST" id="chatForm" class="chat-footer">
    <input type="text" name="message" id="chatMessage" class="form-control" placeholder="Type a message..." required>
    <button type="submit" class="btn btn-primary">Send</button>
  </form>
</div>

<!-- Floating Chat Button -->
<button class="chat-btn" onclick="toggleChat()"><i class="fa-solid fa-comments"></i></button>

  <script>
function toggleChat() {
  let popup = document.getElementById("chatPopup");
  popup.style.display = popup.style.display === "flex" ? "none" : "flex";
  popup.style.flexDirection = "column";
}
</script>

</body>
</html>