<?php
class Project {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create a new project (project_id is auto-incremented by MySQL)
    public function createProject($name, $description, $department_id, $start_date, $end_date, $status, $created_by) {
        // Exclude the primary key (`project_id`) from the INSERT query
        $query = "INSERT INTO projects (project_name, description, department_id, start_date, end_date, status, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the parameters
        $stmt->bind_param("ssissss", $name, $description, $department_id, $start_date, $end_date, $status, $created_by);
        
        // Execute and return the result
        return $stmt->execute();
    }
    
    // Get all projects
    public function getAllProjects() {
        $query = "SELECT p.*, d.department_name 
                 FROM projects p 
                 LEFT JOIN departments d ON p.department_id = d.department_id 
                 ORDER BY p.created_at DESC";
        
        $result = $this->conn->query($query);
        return $result;
    }
    
    // Get a project by its ID
    public function getProjectById($id) {
        $query = "SELECT p.*, d.department_name 
                 FROM projects p 
                 LEFT JOIN departments d ON p.department_id = d.department_id 
                 WHERE p.project_id = ?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the parameters
        $stmt->bind_param("i", $id);
        
        // Execute the query and return the result
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Update project details
    public function updateProject($id, $name, $description, $department_id, $start_date, $end_date, $status) {
        $query = "UPDATE projects 
                 SET project_name=?, description=?, department_id=?, start_date=?, end_date=?, status=? 
                 WHERE project_id=?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the parameters
        $stmt->bind_param("ssisssi", $name, $description, $department_id, $start_date, $end_date, $status, $id);
        
        // Execute and return the result
        return $stmt->execute();
    }
    
    // Delete a project by its ID
    public function deleteProject($id) {
        $query = "DELETE FROM projects WHERE project_id = ?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind the parameter
        $stmt->bind_param("i", $id);
        
        // Execute and return the result
        return $stmt->execute();
    }
}
?>
