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
$projects = $project->getAllProjects();

$departments = $conn->query("SELECT * FROM departments");

// Get employees for dropdown
$employees = $conn->query("SELECT * FROM employees");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = new Project($conn);
    
    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $department_id = $_POST['department_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $created_by = $_POST['created_by'];

    if ($project->createProject($project_name, $description, $department_id, $start_date, $end_date, $status, $created_by)) {
        echo "<script>alert('Successfully create project.'); window.location.href = 'projects.php?success=1';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to create project');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects - Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Projects</h2>
        <a href="add_project.php" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#addProjectModal">Add New Project</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-bordered table-secondary">
                    <tr>
                        <th>Project Name</th>
                        <th>Department</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $projects->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['project_name']); ?></td>
                        <td><?= htmlspecialchars($row['department_name']); ?></td>
                        <td><?= htmlspecialchars($row['start_date']); ?></td>
                        <td><?= htmlspecialchars($row['end_date']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="view_project.php?id=<?= $row['project_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                            <button class="btn btn-warning btn-sm editBtn"
                                data-id="<?= $row['project_id']; ?>"
                                data-name="<?= htmlspecialchars($row['project_name']); ?>"
                                data-description="<?= htmlspecialchars($row['description']); ?>"
                                data-department="<?= $row['department_id']; ?>"
                                data-start="<?= $row['start_date']; ?>"
                                data-end="<?= $row['end_date']; ?>"
                                data-status="<?= $row['status']; ?>"
                                data-manager="<?= $row['created_by']; ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <a href="delete_project.php?id=<?= $row['project_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<!-- Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="project_name" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="project_name" name="project_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                    <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                                    <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Planning">Planning</option>
                                    <option value="Active">Active</option>
                                    <option value="On Hold">On Hold</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="created_by" class="form-label">Project Manager</label>
                                <select class="form-select" id="created_by" name="created_by" required>
                                    <option value="">Select Project Manager</option>
                                        <?php while($emp = $employees->fetch_assoc()): ?>
                                    <option value="<?php echo $emp['employee_id']; ?>">
                                        <?php echo htmlspecialchars("$emp[first_name] $emp[last_name]"); ?>
                                    </option>
                                        <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create Project</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="edit_project.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="project_id" id="edit_project_id">

                    <div class="mb-3">
                        <label for="edit_project_name" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="edit_project_name" name="project_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_department_id" class="form-label">Department</label>
                        <select class="form-select" name="department_id" id="edit_department_id" required>
                            <?php
                            $deptList = $conn->query("SELECT * FROM departments");
                            while($dept = $deptList->fetch_assoc()):
                            ?>
                                <option value="<?= $dept['department_id']; ?>"><?= htmlspecialchars($dept['department_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="Planning">Planning</option>
                            <option value="Active">Active</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_created_by" class="form-label">Project Manager</label>
                        <select class="form-select" name="created_by" id="edit_created_by" disabled>
                            <?php
                            $empList = $conn->query("SELECT * FROM employees");
                            while ($emp = $empList->fetch_assoc()):
                            ?>
                                <option value="<?= $emp['employee_id']; ?>"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </div>
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
    $(document).ready(function () {
        $('#projectsTable').DataTable();

        $('.editBtn').on('click', function () {
            const btn = $(this);
            $('#edit_project_id').val(btn.data('id'));
            $('#edit_project_name').val(btn.data('name'));
            $('#edit_description').val(btn.data('description'));
            $('#edit_department_id').val(btn.data('department'));
            $('#edit_start_date').val(btn.data('start'));
            $('#edit_end_date').val(btn.data('end'));
            $('#edit_status').val(btn.data('status'));
            $('#edit_created_by').val(btn.data('manager'));

            $('#editProjectModal').modal('show');
        });
    });
</script>
</body>
</html>
