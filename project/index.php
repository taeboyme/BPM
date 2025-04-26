<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Project.php';
require_once 'classes/Task.php';

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

$project = new Project($conn);
$task = new Task($conn);

// Project stats
$totalProjects = $conn->query("SELECT COUNT(*) as total FROM projects")->fetch_assoc()['total'];
$activeProjects = $conn->query("SELECT COUNT(*) as total FROM projects WHERE status = 'Active'")->fetch_assoc()['total'];
$completedProjects = $conn->query("SELECT COUNT(*) as total FROM projects WHERE status = 'Completed'")->fetch_assoc()['total'];

// Task stats
$totalTasks = $conn->query("SELECT COUNT(*) as total FROM project_tasks")->fetch_assoc()['total'];
$pendingTasks = $conn->query("SELECT COUNT(*) as total FROM project_tasks WHERE status = 'Pending'")->fetch_assoc()['total'];
$completedTasks = $conn->query("SELECT COUNT(*) as total FROM project_tasks WHERE status = 'Completed'")->fetch_assoc()['total'];

// Recent projects
$recentProjects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");

// Upcoming tasks
$upcomingTasks = $conn->query("
    SELECT pt.*, p.project_name 
    FROM project_tasks pt 
    JOIN projects p ON pt.project_id = p.project_id 
    WHERE pt.due_date >= CURDATE() 
    ORDER BY pt.due_date ASC LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Dashboard</h2>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Projects</h5>
                    <p class="card-text display-4"><?php echo $totalProjects; ?></p>
                    <a href="projects.php" class="text-white">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Active Projects</h5>
                    <p class="card-text display-4"><?php echo $activeProjects; ?></p>
                    <a href="projects.php?status=Active" class="text-white">View Active <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Tasks</h5>
                    <p class="card-text display-4"><?php echo $totalTasks; ?></p>
                    <a href="task.php" class="text-white">View Tasks <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="list-group">
                        <a href="projects.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-briefcase"></i>&nbsp;&nbsp;Project
                        </a>
                        <a href="task.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tasks"></i>&nbsp;&nbsp;Task
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line"></i>&nbsp;&nbsp;Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Projects</h5>
                    <div class="list-group">
                        <?php if ($recentProjects->num_rows > 0): ?>
                            <?php while ($proj = $recentProjects->fetch_assoc()): ?>
                                <a href="view_project.php?id=<?php echo $proj['project_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($proj['project_name']); ?></h6>
                                        <small><?php echo htmlspecialchars($proj['status']); ?></small>
                                    </div>
                                    <small>Created: <?php echo date('M d, Y', strtotime($proj['created_at'])); ?></small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent projects.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Task Deadlines</h5>
                    <div class="list-group">
                        <?php if ($upcomingTasks->num_rows > 0): ?>
                            <?php while ($task = $upcomingTasks->fetch_assoc()): ?>
                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($task['task_name']); ?></h6>
                                        <small class="text-danger"><?php echo date('M d, Y', strtotime($task['due_date'])); ?></small>
                                    </div>
                                    <small>Project: <?php echo htmlspecialchars($task['project_name']); ?></small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No upcoming tasks.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
</body>
</html>
