<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get payroll summary for selected period
$summary_query = "SELECT 
                    COUNT(DISTINCT p.employee_id) as total_employees,
                    SUM(p.gross_pay) as total_gross,
                    SUM(p.deductions) as total_deductions,
                    SUM(p.net_pay) as total_net,
                    AVG(p.gross_pay) as avg_salary
                 FROM payroll_runs pr
                 JOIN payslips p ON pr.run_id = p.run_id
                 WHERE MONTH(pr.period_start) = ? 
                 AND YEAR(pr.period_start) = ?
                 AND pr.status = 'COMPLETED'";

$stmt = $conn->prepare($summary_query);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Get department-wise breakdown
$dept_query = "SELECT 
                d.department_name,
                COUNT(DISTINCT p.employee_id) as emp_count,
                SUM(p.gross_pay) as dept_gross,
                SUM(p.net_pay) as dept_net
              FROM payroll_runs pr
              JOIN payslips p ON pr.run_id = p.run_id
              JOIN employees e ON p.employee_id = e.employee_id
              JOIN departments d ON e.department_id = d.department_id
              WHERE MONTH(pr.period_start) = ? 
              AND YEAR(pr.period_start) = ?
              AND pr.status = 'COMPLETED'
              GROUP BY d.department_id";

$stmt = $conn->prepare($dept_query);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$departments = $stmt->get_result();

// Get component-wise breakdown
$comp_query = "SELECT 
                sc.component_name,
                sc.component_type,
                COUNT(DISTINCT es.employee_id) as emp_count,
                SUM(es.amount) as total_amount
              FROM salary_components sc
              JOIN employee_salary es ON sc.component_id = es.component_id
              WHERE es.effective_from <= LAST_DAY(?) 
              AND (es.effective_to IS NULL OR es.effective_to >= ?)
              GROUP BY sc.component_id";

$period_start = "$year-$month-01";
$stmt = $conn->prepare($comp_query);
$stmt->bind_param("ss", $period_start, $period_start);
$stmt->execute();
$components = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll Summary Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payroll Summary Report</h2>
            <form method="GET" class="form-inline">
                <select name="month" class="form-control mr-2">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-control mr-2">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>

        <!-- Overall Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Employees</h6>
                        <h3><?php echo number_format($summary['total_employees']); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Total Gross Pay</h6>
                        <h3>₱<?php echo number_format($summary['total_gross'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Total Deductions</h6>
                        <h3>₱<?php echo number_format($summary['total_deductions'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Average Salary</h6>
                        <h3>₱<?php echo number_format($summary['avg_salary'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Department Breakdown -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Department Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="deptChart"></canvas>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Employees</th>
                                        <th>Total Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($dept = $departments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $dept['department_name']; ?></td>
                                        <td><?php echo $dept['emp_count']; ?></td>
                                        <td>₱<?php echo number_format($dept['dept_net'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Component Breakdown -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Salary Components</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="componentChart"></canvas>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($comp = $components->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $comp['component_name']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $comp['component_type'] == 'EARNING' ? 'success' : 
                                                    ($comp['component_type'] == 'DEDUCTION' ? 'danger' : 'info'); 
                                            ?>">
                                                <?php echo $comp['component_type']; ?>
                                            </span>
                                        </td>
                                        <td>₱<?php echo number_format($comp['total_amount'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Department Chart
    new Chart(document.getElementById('deptChart'), {
        type: 'pie',
        data: {
            labels: [<?php 
                $departments->data_seek(0);
                echo implode(',', array_map(function($dept) {
                    return '"' . $dept['department_name'] . '"';
                }, iterator_to_array($departments)));
            ?>],
            datasets: [{
                data: [<?php 
                    $departments->data_seek(0);
                    echo implode(',', array_map(function($dept) {
                        return $dept['dept_net'];
                    }, iterator_to_array($departments)));
                ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        }
    });

    // Component Chart
    new Chart(document.getElementById('componentChart'), {
        type: 'bar',
        data: {
            labels: [<?php 
                $components->data_seek(0);
                echo implode(',', array_map(function($comp) {
                    return '"' . $comp['component_name'] . '"';
                }, iterator_to_array($components)));
            ?>],
            datasets: [{
                label: 'Amount',
                data: [<?php 
                    $components->data_seek(0);
                    echo implode(',', array_map(function($comp) {
                        return $comp['total_amount'];
                    }, iterator_to_array($components)));
                ?>],
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>
</html>