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

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get project details
$query = "SELECT p.*, d.department_name, CONCAT(e.first_name, ' ', e.last_name) as manager_name 
          FROM projects p 
          LEFT JOIN departments d ON p.department_id = d.department_id 
          LEFT JOIN employees e ON p.created_by = e.employee_id 
          WHERE p.project_id = $project_id";
$project = $conn->query($query)->fetch_assoc();

// Get project tasks
$query = "SELECT t.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
          FROM project_tasks t 
          LEFT JOIN employees e ON t.assigned_to = e.employee_id 
          WHERE t.project_id = $project_id 
          ORDER BY t.priority DESC, t.due_date ASC";
$tasks = $conn->query($query);

// Get list of employees for the edit modal
$employees = $conn->query("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS employee_name FROM employees");

// Calculate project progress
$taskStats = $conn->query("
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks
    FROM project_tasks 
    WHERE project_id = $project_id
")->fetch_assoc();

$progress = $taskStats['total_tasks'] > 0 
    ? round(($taskStats['completed_tasks'] / $taskStats['total_tasks']) * 100) 
    : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($project['project_name']); ?> - Project Details - Project Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

  <!-- Header -->
  <div class="row mb-4">
    <div class="col-md-8">
      <h2><?php echo htmlspecialchars($project['project_name']); ?></h2>
      <p class="text-muted">
        Department: <?php echo htmlspecialchars($project['department_name']); ?> |
        Manager: <?php echo htmlspecialchars($project['manager_name']); ?>
      </p>
    </div>
    <div class="col-md-4 text-end">
      <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProjectModal">
        <i class="bi bi-pencil"></i> Edit Project
      </button>
      <a href="manage_task.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">
        <i class="bi bi-list-task"></i> Manage Tasks
      </a>
    </div>
  </div>

  <!-- Project Details -->
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Project Details</h5>
          <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>

          <div class="row mt-4">
            <div class="col-md-6">
              <h6>Start Date</h6>
              <p><?php echo date('M d, Y', strtotime($project['start_date'])); ?></p>
            </div>
            <div class="col-md-6">
              <h6>End Date</h6>
              <p><?php echo date('M d, Y', strtotime($project['end_date'])); ?></p>
            </div>
          </div>

          <h6 class="mt-3">Project Progress</h6>
          <div class="progress">
            <div class="progress-bar" role="progressbar"
                 style="width: <?php echo $progress; ?>%"
                 aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
              <?php echo $progress; ?>%
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Card -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Project Status</h5>
          <span class="badge bg-<?php 
              echo $project['status'] === 'Completed' ? 'success' : 
                  ($project['status'] === 'Active' ? 'primary' : 
                  ($project['status'] === 'On Hold' ? 'warning' : 'secondary')); 
          ?> fs-6 mb-3">
            <?php echo $project['status']; ?>
          </span>

          <h6 class="mt-4">Task Summary</h6>
          <ul class="list-unstyled">
            <li>Total Tasks: <?php echo $taskStats['total_tasks']; ?></li>
            <li>Completed: <?php echo $taskStats['completed_tasks']; ?></li>
            <li>Remaining: <?php echo $taskStats['total_tasks'] - $taskStats['completed_tasks']; ?></li>
          </ul>

          <div class="mt-4">
            <h6>Created</h6>
            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($project['created_at'])); ?></small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Task List -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Project Tasks</h5>
    </div>
    <div class="card-body">
      <table class="table table-hover">
        <thead>
        <tr>
          <th>Task Name</th>
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
            <td><?php echo htmlspecialchars($task['employee_name']); ?></td>
            <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
            <td><span class="badge bg-<?php 
              echo $task['priority'] === 'High' ? 'danger' : 
                   ($task['priority'] === 'Medium' ? 'warning' : 'info'); 
            ?>"><?php echo $task['priority']; ?></span></td>
            <td><?php echo $task['status']; ?></td>
            <td>
              <a href="view_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-sm btn-info">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="update_project.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
          <div class="mb-3">
            <label for="project_name" class="form-label">Project Name</label>
            <input type="text" class="form-control" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="<?php echo $project['start_date']; ?>" required>
          </div>
          <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" value="<?php echo $project['end_date']; ?>" required>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" required>
              <option value="Planning" <?php if ($project['status'] === 'Planning') echo 'selected'; ?>>Planning</option>
              <option value="Active" <?php if ($project['status'] === 'Active') echo 'selected'; ?>>Active</option>
              <option value="On Hold" <?php if ($project['status'] === 'On Hold') echo 'selected'; ?>>On Hold</option>
              <option value="Completed" <?php if ($project['status'] === 'Completed') echo 'selected'; ?>>Completed</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="created_by" class="form-label">Project Manager</label>
            <select class="form-select" name="created_by" disabled>
              <?php while($emp = $employees->fetch_assoc()): ?>
                <option value="<?php echo $emp['employee_id']; ?>"
                        <?php if ($emp['employee_id'] == $project['created_by']) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($emp['employee_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
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
</body>
</html>
