<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['task_id'];
    $name = $conn->real_escape_string($_POST['task_name']);
    $due = $conn->real_escape_string($_POST['due_date']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE project_tasks 
            SET task_name = '$name', due_date = '$due', priority = '$priority', status = '$status'
            WHERE task_id = $id";

    if ($conn->query($sql)) {
        echo "<script>alert('Successfully update task.'); window.location.href = 'task.php';</script>";
    } else {
        echo "Error updating task.";
    }
}
?>
