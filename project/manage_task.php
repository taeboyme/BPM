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


$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

// Securely get project details
$project = null;
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $project = $result->fetch_assoc();
} else {
    die("Project not found.");
}

// Get all tasks for the project
$query = "SELECT t.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
          FROM project_tasks t 
          LEFT JOIN employees e ON t.assigned_to = e.employee_id 
          WHERE t.project_id = $project_id 
          ORDER BY t.priority DESC, t.due_date ASC";
$tasks = $conn->query($query);

// Get employees for assignment
$employees = $conn->query("SELECT employee_id, CONCAT(first_name, ' ', last_name) as employee_name FROM employees");

// Handle task addition
if (isset($_POST['add_task'])) {
    $task = new Task($conn);
    $result = $task->createTask(
        $project_id,
        $_POST['task_name'],
        $_POST['assigned_to'],
        $_POST['start_date'],
        $_POST['due_date'],
        $_POST['status'],
        $_POST['priority']
    );

    if ($result) {
        echo "<script>alert('Successfully add task.'); window.location.href = 'manage_task.php?project_id=$project_id&success=1';</script>";
    }else{
        echo "<script>alert('Error adding task.');</script>";
        exit();
    }
}

// Handle task status update
if (isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['new_status'];
    $completion_date = $new_status === 'Completed' ? date('Y-m-d') : null;

    $stmt = $conn->prepare("UPDATE project_tasks SET status = ?, completion_date = ? WHERE task_id = ?");
    $stmt->bind_param("ssi", $new_status, $completion_date, $task_id);
    $stmt->execute();
    
    echo "<script>alert('Successfully update status.'); window.location.href = 'manage_task.php?project_id=$project_id';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Management - Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Tasks for <?php echo htmlspecialchars($project['project_name']); ?></h2>
            <p class="text-muted">Project Status: <?php echo $project['status']; ?></p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">Add New Task</button>
    </div>

    <!-- Task List -->
    <div class="row">
        <?php
        $statuses = ['Pending' => 'secondary', 'In Progress' => 'primary', 'Completed' => 'success'];

        foreach ($statuses as $status => $color):
        ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-<?php echo $color; ?> text-white"><?php echo $status; ?> Tasks</div>
                <div class="card-body">
                    <?php
                    $tasks->data_seek(0);
                    while ($task = $tasks->fetch_assoc()):
                        if ($task['status'] !== $status) continue;

                        $is_overdue = strtotime($task['due_date']) < strtotime(date('Y-m-d')) && $status !== 'Completed';
                    ?>
                    <div class="task-item mb-3 p-2 border rounded">
                        <h6><?php echo htmlspecialchars($task['task_name']); ?></h6>
                        <p class="mb-1"><small>Assigned to: <?php echo htmlspecialchars($task['employee_name']); ?></small></p>
                        <p class="mb-1"><small>Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?></small></p>
                        <?php if ($is_overdue): ?>
                            <p class="text-danger small">Overdue</p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php 
                                echo $task['priority'] === 'High' ? 'danger' : 
                                     ($task['priority'] === 'Medium' ? 'warning' : 'info');
                            ?>"><?php echo $task['priority']; ?></span>
                            <?php if ($status !== 'Completed'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $status === 'Pending' ? 'In Progress' : 'Completed'; ?>">
                                <button type="submit" name="update_status" class="btn btn-sm btn-<?php echo $status === 'Pending' ? 'primary' : 'success'; ?>">
                                    <?php echo $status === 'Pending' ? 'Start' : 'Complete'; ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="task_name" class="form-label">Task Name</label>
                        <input type="text" class="form-control" name="task_name" id="task_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select class="form-select" name="assigned_to" id="assigned_to" required>
                            <option value="">Select Employee</option>
                            <?php
                            $employees->data_seek(0);
                            while ($emp = $employees->fetch_assoc()):
                            ?>
                            <option value="<?php echo $emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-2">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" name="priority" id="priority" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <input type="hidden" name="status" value="Pending">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script>
    document.getElementById('due_date').addEventListener('change', function () {
        const start = document.getElementById('start_date').value;
        if (start && this.value && start > this.value) {
            alert('Due date cannot be earlier than start date');
            this.value = '';
        }
    });
</script>
</body>
</html>
