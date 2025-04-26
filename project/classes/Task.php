<?php
class Task {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function createTask($project_id, $task_name, $assigned_to, $start_date, $due_date, $status, $priority) {
        $query = "INSERT INTO project_tasks (project_id, task_name, assigned_to, start_date, due_date, status, priority) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issssss", $project_id, $task_name, $assigned_to, $start_date, $due_date, $status, $priority);
        
        return $stmt->execute();
    }
    
    public function getTasksByProject($project_id) {
        $query = "SELECT t.*, e.employee_name 
                 FROM project_tasks t 
                 LEFT JOIN employees e ON t.assigned_to = e.employee_id 
                 WHERE t.project_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function updateTask($task_id, $task_name, $assigned_to, $start_date, $due_date, $completion_date, $status, $priority) {
        $query = "UPDATE project_tasks 
                 SET task_name=?, assigned_to=?, start_date=?, due_date=?, completion_date=?, status=?, priority=? 
                 WHERE task_id=?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssi", $task_name, $assigned_to, $start_date, $due_date, $completion_date, $status, $priority, $task_id);
        
        return $stmt->execute();
    }
    
    public function deleteTask($task_id) {
        $query = "DELETE FROM project_tasks WHERE task_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $task_id);
        return $stmt->execute();
    }
}
?>