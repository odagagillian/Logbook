<?php
include('connect.php');
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle booking form submission
if(isset($_POST['book_session'])) {
    try {
        $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
        $service_name = mysqli_real_escape_string($conn, $_POST['service_name']);

        // Validate inputs
        if(empty($appointment_date) || empty($service_name)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Get staff assigned to this service
        $staff_query = "SELECT staff_id, staff_name, staff_phone, room_number 
                        FROM staff WHERE service_type = ? LIMIT 1";
        $stmt2 = mysqli_prepare($conn, $staff_query);
        
        if(!$stmt2) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt2, 's', $service_name);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $staff_id, $staff_name, $staff_phone, $room_number);
        mysqli_stmt_fetch($stmt2);
        mysqli_stmt_close($stmt2);

        if (!$staff_id) {
            throw new Exception("No staff available for this service right now.");
        }
        
        $_SESSION['pending_appointment'] = [
            'service_name' => $service_name,
            'appointment_date' => $appointment_date,
            'staff_id' => $staff_id,
            'staff_name' => $staff_name,
            'staff_phone' => $staff_phone,
            'room_number' => $room_number
        ];
        
        $success_message = "Your session for '$service_name' with $staff_name is ready to be approved.";
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Approve appointment
if (isset($_POST['approve_session']) && isset($_SESSION['pending_appointment'])) {
    try {
        $data = $_SESSION['pending_appointment'];
        
        $insert = "INSERT INTO appointments (user_id, service_name, staff_id, appointment_date) 
                   VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        
        if(!$stmt) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'ssis', $user_id, $data['service_name'], 
                               $data['staff_id'], $data['appointment_date']);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to book appointment: " . mysqli_stmt_error($stmt));
        }
        
        $appointment_id = mysqli_insert_id($conn);
        $_SESSION['appointment_id'] = $appointment_id;
        mysqli_stmt_close($stmt);

        unset($_SESSION['pending_appointment']);
        $success_message = "Your appointment has been approved and confirmed.";
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Cancel pending appointment (before approval)
if (isset($_POST['cancel_session']) && isset($_SESSION['pending_appointment'])) {
    unset($_SESSION['pending_appointment']);
    $success_message = "Your pending appointment has been cancelled.";
}

// Cancel confirmed appointment (from database) - THIS IS THE KEY FIX
if (isset($_POST['cancel_confirmed']) && isset($_POST['appointment_id'])) {
    try {
        $appointment_id = intval($_POST['appointment_id']);
        
        // Verify this appointment belongs to the user
        $verify_query = "SELECT appointment_id FROM appointments 
                        WHERE appointment_id = ? AND user_id = ?";
        $stmt_verify = mysqli_prepare($conn, $verify_query);
        mysqli_stmt_bind_param($stmt_verify, 'is', $appointment_id, $user_id);
        mysqli_stmt_execute($stmt_verify);
        mysqli_stmt_store_result($stmt_verify);
        
        if(mysqli_stmt_num_rows($stmt_verify) === 0) {
            throw new Exception("Appointment not found or unauthorized.");
        }
        mysqli_stmt_close($stmt_verify);
        
        // Delete the appointment
        $delete_query = "DELETE FROM appointments WHERE appointment_id = ? AND user_id = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, 'is', $appointment_id, $user_id);
        
        if(!mysqli_stmt_execute($stmt_delete)) {
            throw new Exception("Failed to cancel appointment: " . mysqli_stmt_error($stmt_delete));
        }
        
        mysqli_stmt_close($stmt_delete);
        $success_message = "Your appointment has been successfully cancelled.";
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Fetch upcoming confirmed appointment
$appt_id = null;
$appt_service = null;
$appt_date = null;
$appt_staff_name = null;
$appt_staff_phone = null;
$appt_room = null;

try {
    $appt_query = "SELECT a.appointment_id, a.service_name, a.appointment_date, 
                   s.staff_name, s.staff_phone, s.room_number
                   FROM appointments a
                   JOIN staff s ON a.staff_id = s.staff_id
                   WHERE a.user_id = ? AND a.appointment_date >= NOW()
                   ORDER BY a.appointment_date ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $appt_query);
    
    if($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $appt_id, $appt_service, $appt_date, 
                               $appt_staff_name, $appt_staff_phone, $appt_room);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    $error_message = "Error fetching appointments: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Jamii Resource Centre Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter','Segoe UI',sans-serif;background:linear-gradient(135deg,#0f0c1d 0%,#1a1530 100%);display:left;min-height:100vh;color:#fff;overflow-x:hidden;}
.sidebar{width:280px;background:rgba(15,12,29,0.95);backdrop-filter:blur(10px);color:#fff;height:100vh;padding:30px 20px;position:left;border-right:1px solid rgba(162,89,255,0.2);overflow-y:auto;}
.sidebar h2{color:#a259ff;margin-bottom:30px;font-size:22px;font-weight:700;letter-spacing:1px;}
.sidebar ul{list-style:none;padding:0;}
.sidebar ul li{margin:20px 0;}
.sidebar ul li a{color:#c8c8d8;text-decoration:none;font-weight:500;display:fixed;align-items:center;padding:12px 15px;border-radius:10px;transition:all 0.3s ease;}
.sidebar ul li a:hover{background:rgba(162,89,255,0.15);color:#a259ff;transform:translateX(5px);}
.main{margin-left:280px;padding:40px;width:100%;max-width:1400px;padding-top:20px;}
.banner{background:linear-gradient(135deg,#7c3aed 0%,#a259ff 100%);color:#fff;padding:40px;border-radius:16px;margin-bottom:30px;box-shadow:0 10px 40px rgba(162,89,255,0.3);}
.banner h1{margin:0;font-size:28px;font-weight:700;line-height:1.4;}
.banner button{margin-top:20px;background:#fff;color:#7c3aed;padding:12px 28px;border:none;border-radius:10px;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.3s ease;text-transform:uppercase;letter-spacing:0.5px;}
.banner button:hover{background:#f0f0f0;transform:translateY(-2px);box-shadow:0 5px 20px rgba(255,255,255,0.3);}
.card{background:rgba(26,21,48,0.8);backdrop-filter:blur(10px);padding:30px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.3);margin-bottom:25px;border:1px solid rgba(162,89,255,0.2);transition:all 0.3s ease;}
.card:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(162,89,255,0.2);}
.card h3{color:#a259ff;margin-bottom:20px;font-size:20px;font-weight:600;}
.card p{color:#c8c8d8;margin:12px 0;line-height:1.6;}
.card strong{color:#fff;font-weight:600;}
.success{background:rgba(46,204,113,0.15);color:#2ecc71;padding:15px 20px;border-radius:10px;margin-bottom:20px;border-left:4px solid #2ecc71;font-weight:500;}
.error{background:rgba(231,76,60,0.15);color:#e74c3c;padding:15px 20px;border-radius:10px;margin-bottom:20px;border-left:4px solid #e74c3c;font-weight:500;}
.service-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin:20px 0;}
.service-card{background:rgba(162,89,255,0.1);padding:20px;border-radius:12px;position:relative;cursor:pointer;border:2px solid rgba(162,89,255,0.2);transition:all 0.3s ease;}
.service-card:hover{background:rgba(162,89,255,0.2);border-color:#a259ff;transform:translateY(-3px);box-shadow:0 8px 25px rgba(162,89,255,0.3);}
.service-card input[type="radio"]{margin-right:10px;accent-color:#a259ff;}
.tooltip{display:none;position:absolute;top:100%;left:0;background:rgba(15,12,29,0.98);color:#fff;padding:12px 15px;border-radius:10px;width:220px;font-size:13px;z-index:10;margin-top:10px;border:1px solid rgba(162,89,255,0.3);box-shadow:0 5px 20px rgba(0,0,0,0.5);}
.service-card:hover .tooltip{display:block;}
button{padding:12px 24px;border:none;border-radius:10px;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.3s ease;text-transform:uppercase;letter-spacing:0.5px;}
button:hover{transform:translateY(-2px);}
.approve-btn{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:#fff;box-shadow:0 5px 15px rgba(46,204,113,0.3);}
.approve-btn:hover{box-shadow:0 8px 25px rgba(46,204,113,0.4);}
.cancel-btn{background:linear-gradient(135deg,#e74c3c 0%,#c0392b 100%);color:#fff;box-shadow:0 5px 15px rgba(231,76,60,0.3);}
.cancel-btn:hover{box-shadow:0 8px 25px rgba(231,76,60,0.4);}
input[type="datetime-local"]{background:rgba(162,89,255,0.1);border:2px solid rgba(162,89,255,0.3);color:#fff;padding:12px;border-radius:10px;font-size:14px;margin-top:10px;width:100%;max-width:300px;}
input[type="datetime-local"]:focus{outline:none;border-color:#a259ff;box-shadow:0 0 0 3px rgba(162,89,255,0.2);}
label{color:#c8c8d8;font-weight:500;display:block;margin-bottom:10px;font-size:15px;}
a{text-decoration:none;}
</style>
</head>
<body>
<div class="sidebar">
  <h2>Jamii Centre</h2>
  <ul>
    <li><a href="#">Dashboard</a></li>
    <li><a href="appointments.php">Appointments</a></li>
    <li><a href="#">Help & Settings</a></li>
  </ul>
</div>
<div class="main">
  <div class="banner">
    <h1>Looking for support? Choose a service and book your session.</h1>
    <button onclick="document.getElementById('booking').scrollIntoView()">BOOK NOW</button>
  </div>
  
  <?php if(!empty($success_message)): ?>
    <div class='success'><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>
  
  <?php if(!empty($error_message)): ?>
    <div class='error'><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>
  
  <!-- Pending Appointment -->
  <?php if(isset($_SESSION['pending_appointment'])): $data=$_SESSION['pending_appointment']; ?>
  <div class="card">
    <h3>Pending Appointment</h3>
    <p><strong>Service:</strong> <?php echo htmlspecialchars($data['service_name']); ?></p>
    <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a",strtotime($data['appointment_date'])); ?></p>
    <p><strong>Staff:</strong> <?php echo htmlspecialchars($data['staff_name']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($data['staff_phone']); ?></p>
    <p><strong>Room:</strong> <?php echo htmlspecialchars($data['room_number']); ?></p>
    <form method="POST" style="margin-top:10px;">
      <button type="submit" name="approve_session" class="approve-btn">Approve</button>
      <button type="submit" name="cancel_session" class="cancel-btn">Cancel</button>
    </form>
  </div>
  <?php endif; ?>
  
  <!-- Confirmed Appointment -->
  <?php if(!empty($appt_id)): ?>
  <div class="card">
    <h3>Upcoming Appointment</h3>
    <p><strong>Service:</strong> <?php echo htmlspecialchars($appt_service); ?></p>
    <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a",strtotime($appt_date)); ?></p>
    <p><strong>Staff:</strong> <?php echo htmlspecialchars($appt_staff_name); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($appt_staff_phone); ?></p>
    <p><strong>Room:</strong> <?php echo htmlspecialchars($appt_room); ?></p>
    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');" style="margin-top:10px;">
      <input type="hidden" name="appointment_id" value="<?php echo $appt_id; ?>">
      <button type="submit" name="cancel_confirmed" class="cancel-btn">Cancel Appointment</button>
    </form>
  </div>
  <?php endif; ?>
  
  <!-- Booking Form -->
  <?php if(!isset($_SESSION['pending_appointment'])): ?>
  <div class="card" id="booking">
    <h3>Book a Session</h3>
    <form method="POST">
      <label>Choose Service</label>
      <div class="service-grid">
        <label class="service-card">
          <input type="radio" name="service_name" value="Case Management and Advocacy" required>
          Case Management
          <div class="tooltip">Support with navigating social services, legal aid, and personalized case follow-up.</div>
        </label>
        <label class="service-card">
          <input type="radio" name="service_name" value="Counselling" required>
          Counselling
          <div class="tooltip">Professional guidance and emotional support sessions for individuals and groups.</div>
        </label>
        <label class="service-card">
          <input type="radio" name="service_name" value="Workshop and Classes" required>
          Workshops & Classes
          <div class="tooltip">Interactive training sessions and skill‑building classes for community empowerment.</div>
        </label>
        <label class="service-card">
          <input type="radio" name="service_name" value="Jamii SACCO" required>
          Jamii SACCO
          <div class="tooltip">Financial support, savings, and loan services through the Jamii cooperative.</div>
        </label>
      </div>
      <br>
      <label>Choose Date & Time</label><br>
      <input type="datetime-local" name="appointment_date" required><br><br>
      <button type="submit" name="book_session">Book Session</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- Extra actions -->
  <div class="card">
    <p><strong>Need Help?</strong></p>
    <p>📞 +254 712 345 678</p>
  </div>

  <a href="appointments.php"><button>View Appointment History</button></a>
  <a href="logout.php"><button class="cancel-btn">Logout</button></a>
</div>
</body>
</html>