<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Get filter parameters
$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'ACTIVE';

// Fetch departments for filter
$dept_query = "SELECT * FROM departments ORDER BY department_name";
$departments = $conn->query($dept_query);

// Build employee query with filters
$emp_query = "SELECT e.*, d.department_name, p.position_title,
                (SELECT SUM(amount) FROM employee_salary es 
                 WHERE es.employee_id = e.employee_id 
                 AND (es.effective_to IS NULL OR es.effective_to >= CURDATE())
                ) as current_salary
              FROM employees e
              LEFT JOIN departments d ON e.department_id = d.department_id
              LEFT JOIN positions p ON e.position_id = p.position_id
              WHERE e.employment_status = ?
              " . ($department_id ? "AND e.department_id = ?" : "");

$stmt = $conn->prepare($emp_query);
if ($department_id) {
    $stmt->bind_param("si", $status, $department_id);
} else {
    $stmt->bind_param("s", $status);
}
$stmt->execute();
$employees = $stmt->get_result();

// Calculate statistics
$stats_query = "SELECT 
                COUNT(*) as total_count,
                AVG(TIMESTAMPDIFF(YEAR, hire_date, CURDATE())) as avg_tenure,
                COUNT(CASE WHEN gender = 'Male' THEN 1 END) as male_count,
                COUNT(CASE WHEN gender = 'Female' THEN 1 END) as female_count
               FROM employees 
               WHERE employment_status = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("s", $status);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Reports</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Employee Reports</h2>
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
                <select name="status" class="form-control mr-2">
                    <option value="ACTIVE" <?php echo $status == 'ACTIVE' ? 'selected' : ''; ?>>Active</option>
                    <option value="TERMINATED" <?php echo $status == 'TERMINATED' ? 'selected' : ''; ?>>Terminated</option>
                    <option value="PROBATION" <?php echo $status == 'PROBATION' ? 'selected' : ''; ?>>Probation</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Employees</h6>
                        <h3><?php echo $stats['total_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Average Tenure</h6>
                        <h3><?php echo number_format($stats['avg_tenure'], 1); ?> years</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Gender Distribution</h6>
                        <h3>M: <?php echo $stats['male_count']; ?> | F: <?php echo $stats['female_count']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Average Salary</h6>
                        <h3>₱<?php 
                            $total_salary = 0;
                            $count = 0;
                            $employees->data_seek(0);
                            while($emp = $employees->fetch_assoc()) {
                                if($emp['current_salary']) {
                                    $total_salary += $emp['current_salary'];
                                    $count++;
                                }
                            }
                            echo number_format($count ? $total_salary/$count : 0, 2);
                        ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee List -->
        <div class="card">
            <div class="card-body">
                <table id="employeeTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Hire Date</th>
                            <th>Tenure</th>
                            <th>Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $employees->data_seek(0);
                        while($emp = $employees->fetch_assoc()): 
                            $tenure = date_diff(date_create($emp['hire_date']), date_create('now'))->y;
                        ?>
                        <tr>
                            <td><?php echo $emp['employee_id']; ?></td>
                            <td><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></td>
                            <td><?php echo $emp['department_name']; ?></td>
                            <td><?php echo $emp['position_title']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($emp['hire_date'])); ?></td>
                            <td><?php echo $tenure . ' year' . ($tenure != 1 ? 's' : ''); ?></td>
                            <td>₱<?php echo number_format($emp['current_salary'] ?? 0, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#employeeTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    title: 'Employee Report - ' + new Date().toLocaleDateString(),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                }
            ],
            order: [[1, 'asc']]
        });
    });
    </script>
</body>
</html>