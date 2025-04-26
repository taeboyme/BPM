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


$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get task details with related information
$query = "SELECT t.*, p.project_name, p.project_id, 
          CONCAT(e.first_name, ' ', e.last_name) AS assigned_to_name,
          d.department_name
          FROM project_tasks t 
          LEFT JOIN projects p ON t.project_id = p.project_id
          LEFT JOIN employees e ON t.assigned_to = e.employee_id
          LEFT JOIN departments d ON p.department_id = d.department_id
          WHERE t.task_id = $task_id";
$task = $conn->query($query)->fetch_assoc();

// Get task comments
$comments = $conn->query("SELECT c.*, CONCAT(e.first_name, ' ', e.last_name) AS employee_name 
                         FROM task_comments c
                         LEFT JOIN employees e ON c.created_by = e.employee_id
                         WHERE c.task_id = $task_id
                         ORDER BY c.created_at DESC");

// Handle comment addition
if (isset($_POST['add_comment'])) {
    $comment = trim($_POST['comment']);
    $created_by = $_SESSION['employee_id']; // Assuming user is logged in

    // Check if the user exists in the employees table before inserting the comment
    $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $stmt->bind_param("i", $created_by);
    $stmt->execute();
    $stmt->store_result();

if ($stmt->num_rows > 0) {
    $conn->begin_transaction(); // Start the transaction

    try {
    // Insert into task_comments
    $insert_query = "INSERT INTO task_comments (task_id, comment, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param('iss', $task_id, $comment, $employee_id);
    $stmt->execute();

    // Commit the transaction if no error occurred
    $conn->commit();
    echo "<script>alert('Successfully added your comment.'); window.location.href = 'view_task.php?id=$task_id';</script>";
    } catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Error adding comment: " . $e->getMessage() . "');</script>";
    }
    } else {
        echo "<script>alert('Error: Invalid user session.');</script>";
    }
}


// Handle status update
if(isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    $completion_date = $new_status === 'Completed' ? date('Y-m-d') : null;
    
    $conn->query("UPDATE project_tasks 
                  SET status = '$new_status', 
                      completion_date = " . ($completion_date ? "'$completion_date'" : "NULL") . "
                  WHERE task_id = $task_id");
    
    echo "<script>alert('Status updated.'); window.location.href = 'view_task.php?id=$task_id';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - <?php echo htmlspecialchars($task['task_name']); ?> - Project Management System</title>
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
        <!-- Task Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?php echo htmlspecialchars($task['task_name']); ?></h2>
                <p class="text-muted">
                    Project: <a href="view_project.php?id=<?php echo $task['project_id']; ?>">
                        <?php echo htmlspecialchars($task['project_name']); ?>
                    </a>
                </p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    Update Status
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <form method="POST">
                            <input type="hidden" name="new_status" value="Pending">
                            <button type="submit" name="update_status" class="dropdown-item">Pending</button>
                        </form>
                    </li>
                    <li>
                        <form method="POST">
                            <input type="hidden" name="new_status" value="In Progress">
                            <button type="submit" name="update_status" class="dropdown-item">In Progress</button>
                        </form>
                    </li>
                    <li>
                        <form method="POST">
                            <input type="hidden" name="new_status" value="Completed">
                            <button type="submit" name="update_status" class="dropdown-item">Completed</button>
                        </form>
                    </li>
                </ul>
                <button 
                    class="btn btn-warning" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editTaskModal"
                    id="openEditModalBtn"
                    style = "margin-left:10px; border-radius:5px;">
                    <i class="bi bi-pencil"></i> Edit
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Task Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Task Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <span class="badge bg-<?php 
                                    echo $task['status'] === 'Completed' ? 'success' : 
                                        ($task['status'] === 'In Progress' ? 'primary' : 'secondary'); 
                                ?>">
                                    <?php echo $task['status']; ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Priority:</strong>
                                <span class="badge bg-<?php 
                                    echo $task['priority'] === 'High' ? 'danger' : 
                                        ($task['priority'] === 'Medium' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo $task['priority']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Assigned To:</strong>
                                <p><?php echo htmlspecialchars($task['assigned_to_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Department:</strong>
                                <p><?php echo htmlspecialchars($task['department_name']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Start Date:</strong>
                                <p><?php echo date('M d, Y', strtotime($task['start_date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Due Date:</strong>
                                <p><?php echo date('M d, Y', strtotime($task['due_date'])); ?></p>
                            </div>
                        </div>

                        <?php if($task['status'] === 'Completed'): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i>
                                Completed on <?php echo date('M d, Y', strtotime($task['completion_date'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Comments</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="3" 
                                          placeholder="Add a comment..." required></textarea>
                            </div>
                            <button type="submit" name="add_comment" class="btn btn-primary">
                                Add Comment
                            </button>
                        </form>

                        <div class="comments-list">
                            <?php while($comment = $comments->fetch_assoc()): ?>
                                <div class="comment-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($comment['employee_name']); ?></strong>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 mt-2">
                                        <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Timeline -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Task Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6>Task Created</h6>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                    </small>
                                </div>
                            </div>

                            <?php if($task['status'] !== 'Pending'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Started</h6>
                                        <small class="text-muted">Task in progress</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($task['status'] === 'Completed'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Completed</h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($task['completion_date'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="edit_task.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">

            <div class="mb-3">
                <label class="form-label">Task Name</label>
                <input type="text" class="form-control" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" name="due_date" value="<?php echo $task['due_date']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority" required>
                    <option value="High" <?php if($task['priority'] == 'High') echo 'selected'; ?>>High</option>
                    <option value="Medium" <?php if($task['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
                    <option value="Low" <?php if($task['priority'] == 'Low') echo 'selected'; ?>>Low</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <option value="Pending" <?php if($task['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="In Progress" <?php if($task['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="Completed" <?php if($task['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
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


    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
        }
        .timeline-marker {
            position: absolute;
            left: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-top: 5px;
        }
        .timeline-content {
            padding-bottom: 20px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
</body>
</html>