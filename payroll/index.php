<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get total number of employees
$emp_query = "SELECT COUNT(*) as total_employees FROM employees WHERE employment_status = 'ACTIVE'";
$emp_result = $conn->query($emp_query)->fetch_assoc();

// Get latest payroll run
$payroll_query = "SELECT * FROM payroll_runs ORDER BY run_date DESC LIMIT 1";
$payroll_result = $conn->query($payroll_query)->fetch_assoc();

// Get total payroll amount for latest run
$total_query = "SELECT SUM(gross_pay) as total_gross, SUM(net_pay) as total_net 
                FROM payslips WHERE run_id = " . ($payroll_result['run_id'] ?? 0);
$total_result = $conn->query($total_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll System - Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h2>Dashboard</h2>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Employees</h5>
                        <h2><?php echo $emp_result['total_employees']; ?></h2>
                        <a href="employees/index.php" class="text-white">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Last Payroll</h5>
                        <h2>₱<?php echo number_format($total_result['total_net'] ?? 0, 2); ?></h2>
                        <small><?php echo $payroll_result['run_date'] ?? 'No payroll run yet'; ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Average Salary</h5>
                        <h2>₱<?php 
                            $avg = ($total_result['total_gross'] ?? 0) / ($emp_result['total_employees'] ?: 1);
                            echo number_format($avg, 2); 
                        ?></h2>
                        <small>Gross Average</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Quick Actions
                    </div>
                    <div class="card-body">
                        <a href="run_payroll.php" class="btn btn-primary mr-2">
                            <i class="fas fa-money-check-alt"></i> Run Payroll
                        </a>
                        <a href="add_employee.php" class="btn btn-success mr-2">
                            <i class="fas fa-user-plus"></i> Add Employee
                        </a>
                        <a href="reports.php" class="btn btn-info mr-2">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Recent Payroll Runs
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Run Date</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent_query = "SELECT * FROM payroll_runs ORDER BY run_date DESC LIMIT 5";
                                $recent_result = $conn->query($recent_query);
                                while($run = $recent_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $run['run_date']; ?></td>
                                    <td><?php echo $run['period_start'] . ' to ' . $run['period_end']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $run['status'] == 'COMPLETED' ? 'success' : 'warning'; ?>">
                                            <?php echo $run['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_payroll_run.php?id=<?php echo $run['run_id']; ?>" 
                                           class="btn btn-sm btn-info">View Details</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>