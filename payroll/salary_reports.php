<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Get filter parameters
$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch departments for filter
$dept_query = "SELECT * FROM departments ORDER BY department_name";
$departments = $conn->query($dept_query);

// Get salary statistics
$stats_query = "SELECT 
                AVG(current_salary) as avg_salary,
                MIN(current_salary) as min_salary,
                MAX(current_salary) as max_salary,
                COUNT(*) as emp_count
               FROM (
                   SELECT e.employee_id,
                   (SELECT SUM(amount) FROM employee_salary es 
                    WHERE es.employee_id = e.employee_id 
                    AND es.effective_to IS NULL) as current_salary
                   FROM employees e
                   WHERE e.employment_status = 'ACTIVE'
                   " . ($department_id ? "AND e.department_id = ?" : "") . "
               ) salary_data";

$stmt = $conn->prepare($stats_query);
if ($department_id) {
    $stmt->bind_param("i", $department_id);
}
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get department-wise salary distribution
$dept_salary_query = "SELECT 
                        d.department_name,
                        COUNT(e.employee_id) as emp_count,
                        AVG(current_salary) as avg_salary,
                        MIN(current_salary) as min_salary,
                        MAX(current_salary) as max_salary
                     FROM departments d
                     LEFT JOIN employees e ON d.department_id = e.department_id
                     LEFT JOIN (
                         SELECT employee_id, SUM(amount) as current_salary
                         FROM employee_salary
                         WHERE effective_to IS NULL
                         GROUP BY employee_id
                     ) current_sal ON e.employee_id = current_sal.employee_id
                     WHERE e.employment_status = 'ACTIVE'
                     GROUP BY d.department_id
                     ORDER BY avg_salary DESC";
$dept_salaries = $conn->query($dept_salary_query);

// Get salary trends
$trend_query = "SELECT 
                YEAR(es.effective_from) as year,
                MONTH(es.effective_from) as month,
                AVG(es.amount) as avg_amount
                FROM employee_salary es
                WHERE YEAR(es.effective_from) = ?
                GROUP BY YEAR(es.effective_from), MONTH(es.effective_from)
                ORDER BY year, month";
$stmt = $conn->prepare($trend_query);
$stmt->bind_param("i", $year);
$stmt->execute();
$salary_trends = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salary Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Salary Analysis Report</h2>
            <form method="GET" class="form-inline">
                <select name="department_id" class="form-control mr-2">
                    <option value="">All Departments</option>
                    <?php while($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['department_id']; ?>"
                                <?php echo $dept['department_id'] == $department_id ? 'selected' : ''; ?>>
                            <?php echo $dept['department_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="year" class="form-control mr-2">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </form>
        </div>

        <!-- Salary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Average Salary</h6>
                        <h3>₱<?php echo number_format($stats['avg_salary'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Minimum Salary</h6>
                        <h3>₱<?php echo number_format($stats['min_salary'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Maximum Salary</h6>
                        <h3>₱<?php echo number_format($stats['max_salary'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Total Employees</h6>
                        <h3><?php echo number_format($stats['emp_count']); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Department Salary Distribution -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Department Salary Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="deptTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Employees</th>
                                        <th>Average</th>
                                        <th>Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($dept = $dept_salaries->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $dept['department_name']; ?></td>
                                        <td><?php echo $dept['emp_count']; ?></td>
                                        <td>₱<?php echo number_format($dept['avg_salary'], 2); ?></td>
                                        <td>
                                            ₱<?php echo number_format($dept['min_salary'], 2); ?> - 
                                            ₱<?php echo number_format($dept['max_salary'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salary Trends -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Monthly Salary Trends (<?php echo $year; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Distribution Chart -->
        <div class="card">
            <div class="card-header">
                <h5>Salary Range Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#deptTable').DataTable({
            order: [[2, 'desc']],
            pageLength: 10
        });

        // Salary Trends Chart
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: [<?php 
                    $salary_trends->data_seek(0);
                    echo implode(',', array_map(function($trend) {
                        return '"' . date('M', mktime(0,0,0,$trend['month'],1)) . '"';
                    }, iterator_to_array($salary_trends)));
                ?>],
                datasets: [{
                    label: 'Average Salary',
                    data: [<?php 
                        $salary_trends->data_seek(0);
                        echo implode(',', array_map(function($trend) {
                            return $trend['avg_amount'];
                        }, iterator_to_array($salary_trends)));
                    ?>],
                    borderColor: '#36A2EB',
                    fill: false
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

        // Distribution Chart
        new Chart(document.getElementById('distributionChart'), {
            type: 'bar',
            data: {
                labels: [<?php 
                    $dept_salaries->data_seek(0);
                    echo implode(',', array_map(function($dept) {
                        return '"' . $dept['department_name'] . '"';
                    }, iterator_to_array($dept_salaries)));
                ?>],
                datasets: [{
                    label: 'Average Salary',
                    data: [<?php 
                        $dept_salaries->data_seek(0);
                        echo implode(',', array_map(function($dept) {
                            return $dept['avg_salary'];
                        }, iterator_to_array($dept_salaries)));
                    ?>],
                    backgroundColor: '#4BC0C0'
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
    });
    </script>
</body>
</html>