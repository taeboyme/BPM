<?php
session_start();
require_once 'config/database.php';
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

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$department = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$assigned_to = isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : 0;

// Base query
$query = "SELECT t.*, p.project_name, CONCAT(e.first_name, ' ', e.last_name) as employee_name, d.department_name 
          FROM project_tasks t 
          LEFT JOIN projects p ON t.project_id = p.project_id
          LEFT JOIN employees e ON t.assigned_to = e.employee_id
          LEFT JOIN departments d ON p.department_id = d.department_id
          WHERE 1=1";

// Apply filters
if($status) $query .= " AND t.status = '$status'";
if($priority) $query .= " AND t.priority = '$priority'";
if($department) $query .= " AND p.department_id = $department";
if($assigned_to) $query .= " AND t.assigned_to = $assigned_to";

// Get tasks
$tasks = $conn->query($query);

// Get filter options
$departments = $conn->query("SELECT * FROM departments");
$employees = $conn->query("SELECT * FROM employees");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Dashboard - Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Task Dashboard</h2>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?php echo $status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All Priorities</option>
                            <option value="High" <?php echo $priority === 'High' ? 'selected' : ''; ?>>High</option>
                            <option value="Medium" <?php echo $priority === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="Low" <?php echo $priority === 'Low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>" 
                                        <?php echo $department == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">All Employees</option>
                            <?php while($emp = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['employee_id']; ?>"
                                        <?php echo $assigned_to == $emp['employee_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['employee_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="task_dashboard.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Task Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Tasks</h5>
                        <h2><?php echo $tasks->num_rows; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h2><?php 
                            $pending = $conn->query("SELECT COUNT(*) as count FROM project_tasks WHERE status = 'Pending'")->fetch_assoc();
                            echo $pending['count'];
                        ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">In Progress</h5>
                        <h2><?php 
                            $inProgress = $conn->query("SELECT COUNT(*) as count FROM project_tasks WHERE status = 'In Progress'")->fetch_assoc();
                            echo $inProgress['count'];
                        ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completed</h5>
                        <h2><?php 
                            $completed = $conn->query("SELECT COUNT(*) as count FROM project_tasks WHERE status = 'Completed'")->fetch_assoc();
                            echo $completed['count'];
                        ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Task Name</th>
                                <th>Project</th>
                                <th>Department</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($task = $tasks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['employee_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $task['priority'] === 'High' ? 'danger' : 
                                            ($task['priority'] === 'Medium' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo $task['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $task['status'] === 'Completed' ? 'success' : 
                                            ($task['status'] === 'In Progress' ? 'primary' : 'secondary'); 
                                    ?>">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_task.php?id=<?php echo $task['task_id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button 
                                        class="btn btn-sm btn-warning editTaskBtn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTaskModal"
                                        data-id="<?php echo $task['task_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($task['task_name']); ?>"
                                        data-priority="<?php echo $task['priority']; ?>"
                                        data-status="<?php echo $task['status']; ?>"
                                        data-due="<?php echo $task['due_date']; ?>"
                                        >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_task.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="task_id" id="editTaskId">

            <div class="mb-3">
                <label class="form-label">Task Name</label>
                <input type="text" class="form-control" name="task_name" id="editTaskName" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" name="due_date" id="editDueDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority" id="editPriority" required>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" id="editStatus" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Task</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
    $('.editTaskBtn').click(function() {
        const button = $(this);
        $('#editTaskId').val(button.data('id'));
        $('#editTaskName').val(button.data('name'));
        $('#editDueDate').val(button.data('due'));
        $('#editPriority').val(button.data('priority'));
        $('#editStatus').val(button.data('status'));
    });
});
</script>
</body>
</html>