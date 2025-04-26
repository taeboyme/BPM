<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch employee data
$query = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Project Statistics
$projectStats = $conn->query("
    SELECT 
        COUNT(*) as total_projects,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_projects,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_projects,
        SUM(CASE WHEN end_date < CURDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue_projects
    FROM projects
")->fetch_assoc();

// Department Project Distribution
$deptProjects = $conn->query("
    SELECT d.department_name, COUNT(p.project_id) as project_count
    FROM departments d
    LEFT JOIN projects p ON d.department_id = p.department_id
    GROUP BY d.department_id
    ORDER BY project_count DESC
");

// Task Statistics
$taskStats = $conn->query("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as ongoing_tasks,
        SUM(CASE WHEN due_date < CURDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue_tasks
    FROM project_tasks
")->fetch_assoc();

// Employee Performance
$employeePerformance = $conn->query("
    SELECT 
        CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
        COUNT(t.task_id) as total_tasks,
        SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN t.due_date < CURDATE() AND t.status != 'Completed' THEN 1 ELSE 0 END) as overdue_tasks
    FROM employees e
    LEFT JOIN project_tasks t ON e.employee_id = t.assigned_to
    GROUP BY e.employee_id
    ORDER BY completed_tasks DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    @media print {
    nav, .btn, .card-header, .dataTables_filter, .dataTables_length, .dataTables_paginate {
        display: none !important;
    }

    body {
        background-color: white !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .container {
        width: 100%;
    }
}
</style>

</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Project Analytics & Reports</h2>

        <!-- Project Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Projects</h5>
                        <h2><?php echo $projectStats['total_projects']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Projects</h5>
                        <h2><?php echo $projectStats['active_projects']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completed Projects</h5>
                        <h2><?php echo $projectStats['completed_projects']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Overdue Projects</h5>
                        <h2><?php echo $projectStats['overdue_projects']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Department Distribution Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Projects by Department
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Task Status Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Task Status Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="taskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Performance Table -->
        <div class="card mb-4">
            <div class="card-header">
                Employee Performance
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Total Tasks</th>
                            <th>Completed</th>
                            <th>Overdue</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($emp = $employeePerformance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emp['employee_name']); ?></td>
                                <td><?php echo $emp['total_tasks']; ?></td>
                                <td><?php echo $emp['completed_tasks']; ?></td>
                                <td><?php echo $emp['overdue_tasks']; ?></td>
                                <td>
                                    <?php 
                                    $rate = $emp['total_tasks'] > 0 
                                        ? round(($emp['completed_tasks'] / $emp['total_tasks']) * 100) 
                                        : 0;
                                    echo $rate . '%';
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
    <button class="btn btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer"></i> Print Report
    </button>
</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script>
        // Department Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $deptProjects->data_seek(0);
                    while($dept = $deptProjects->fetch_assoc()) {
                        echo "'" . $dept['department_name'] . "',";
                    }
                ?>],
                datasets: [{
                    label: 'Number of Projects',
                    data: [<?php 
                        $deptProjects->data_seek(0);
                        while($dept = $deptProjects->fetch_assoc()) {
                            echo $dept['project_count'] . ",";
                        }
                    ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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

        // Task Status Chart
        const taskCtx = document.getElementById('taskChart').getContext('2d');
        new Chart(taskCtx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'In Progress', 'Overdue'],
                datasets: [{
                    data: [
                        <?php echo $taskStats['completed_tasks']; ?>,
                        <?php echo $taskStats['ongoing_tasks']; ?>,
                        <?php echo $taskStats['overdue_tasks']; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
        options: {
        responsive: true,
        aspectRatio: 2, // Set the aspect ratio (1 for square, adjust as needed)
        }
        });
    </script>
</body>
</html>