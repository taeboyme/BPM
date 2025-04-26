<?php
//session_start();
require_once 'config/database.php';

//if (!isset($_SESSION['employee_id'])) {
    //header("Location: login.php");
    //exit();
//}
//$employee_id = $_SESSION['employee_id'];

// Fetch employee data
$query = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Stats
$totalEmployees = $conn->query("SELECT COUNT(*) AS total FROM employees")->fetch_assoc()['total'];
$totalDepartments = $conn->query("SELECT COUNT(*) AS total FROM departments")->fetch_assoc()['total'];
$totalVehicles = $conn->query("SELECT COUNT(*) AS total FROM vehicles")->fetch_assoc()['total'];
$totalProjects = $conn->query("SELECT COUNT(*) AS total FROM projects")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(*) AS total FROM purchase_requests")->fetch_assoc()['total'];

// Recent employees
$recentEmployees = $conn->query("SELECT * FROM employees ORDER BY created_at DESC LIMIT 5");

// Recent purchase requests
$recentRequests = $conn->query("
    SELECT pr.*, d.department_name 
    FROM purchase_requests pr 
    JOIN departments d ON pr.department_id = d.department_id 
    ORDER BY pr.request_date DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Hospital Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidenav.php'; ?>

<div class="container mt-4" style = "margin-left:370px;">
    <h2>Dashboard</h2>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Employees</h5>
                    <p class="card-text display-4"><?php echo $totalEmployees; ?></p>
                    <i class="fas fa-users fa-2x"></i>
                    
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Departments</h5>
                    <p class="card-text display-4"><?php echo $totalDepartments; ?></p>
                    <i class="fas fa-building fa-2x"></i>
                    
                </div>
            </div>
        </div>
    
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
