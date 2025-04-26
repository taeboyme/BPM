<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = (int) $_POST['task_id'];
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $due_date = $conn->real_escape_string($_POST['due_date']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $status = $conn->real_escape_string($_POST['status']);
    $completion_date = $status === 'Completed' ? date('Y-m-d') : 'NULL';

    $sql = "UPDATE project_tasks SET 
                task_name = '$task_name', 
                due_date = '$due_date', 
                priority = '$priority', 
                status = '$status', 
                completion_date = " . ($status === 'Completed' ? "'$completion_date'" : "NULL") . "
            WHERE task_id = $task_id";

    if ($conn->query($sql)) {
        echo "<script>alert('Successfully update task.'); window.location.href = 'view_task.php?id=$task_id';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating task.');</script>";
    }
}
?>
