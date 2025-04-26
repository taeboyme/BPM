<?php
require_once 'config/database.php';
require_once 'classes/Project.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project = new Project($conn);

    $id = $_POST['project_id'];
    $name = $_POST['project_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    // Fetch department_id from DB if needed, or pass it via form if editable
    $result = $conn->query("SELECT department_id FROM projects WHERE project_id = $id");
    $row = $result->fetch_assoc();
    $department_id = $row['department_id'];

    if ($project->updateProject($id, $name, $description, $department_id, $start_date, $end_date, $status)) {
        echo "<script>alert('Successfully updated your project.'); window.location.href = 'projects.php?id=$id&updated=1';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update project.');</script>";
    }
}
?>
