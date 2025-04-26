<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get quick statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM employees WHERE employment_status = 'ACTIVE') as active_employees,
    (SELECT COUNT(*) FROM payroll_runs WHERE MONTH(run_date) = MONTH(CURRENT_DATE())) as monthly_runs,
    (SELECT SUM(net_pay) FROM payslips p 
     JOIN payroll_runs pr ON p.run_id = pr.run_id 
     WHERE MONTH(pr.run_date) = MONTH(CURRENT_DATE())) as monthly_payout";

$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Reports Dashboard</h2>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active Employees</h6>
                                <h2><?php echo number_format($stats['active_employees']); ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Payroll Runs This Month</h6>
                                <h2><?php echo number_format($stats['monthly_runs']); ?></h2>
                            </div>
                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Monthly Payout</h6>
                                <h2>$<?php echo number_format($stats['monthly_payout'], 2); ?></h2>
                            </div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Categories -->
        <div class="row">
            <!-- Payroll Reports -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i> Payroll Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="payroll_summary.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Payroll Summary</h6>
                                        <small>Monthly payroll overview and analysis</small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            <a href="payroll_history.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Payroll History</h6>
                                        <small>Historical payroll records and trends</small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Reports -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users"></i> Employee Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="employee_reports.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Employee Statistics</h6>
                                        <small>Demographics and employment metrics</small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            <a href="salary_reports.php" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Salary Analysis</h6>
                                        <small>Compensation trends and benchmarks</small>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>