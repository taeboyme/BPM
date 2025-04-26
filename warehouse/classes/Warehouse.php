<?php
class Warehouse {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Helper: Check if a manager exists
    private function managerExists($manager_id) {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $manager_id); // Use "i" if employee_id is an INT
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    public function addWarehouse($name, $location, $capacity, $manager_id) {
        // Validate manager_id exists
        if (!$this->managerExists($manager_id)) {
            echo "Error: Manager with ID $manager_id does not exist.";
            return false;
        }

        $sql = "INSERT INTO warehouses (name, location, capacity, manager_id) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            echo "Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error;
            return false;
        }

        $stmt->bind_param("ssis", $name, $location, $capacity, $manager_id); // "s" for manager_id if VARCHAR
        if ($stmt->execute()) {
            return true;
        } else {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            return false;
        }
    }
    
    public function getWarehouses() {
        $sql = "SELECT w.*, CONCAT(e.first_name, ' ', e.last_name) as manager_name 
                FROM warehouses w 
                LEFT JOIN employees e ON w.manager_id = e.employee_id";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getWarehouseById($id) {
        $sql = "SELECT * FROM warehouses WHERE warehouse_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateWarehouse($id, $name, $location, $capacity, $manager_id) {
        // Validate manager_id exists
        if (!$this->managerExists($manager_id)) {
            echo "Error: Manager with ID $manager_id does not exist.";
            return false;
        }

        $sql = "UPDATE warehouses SET name = ?, location = ?, capacity = ?, manager_id = ? WHERE warehouse_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisi", $name, $location, $capacity, $manager_id, $id); // adjust based on data types
        return $stmt->execute();
    }

    public function deleteWarehouse($id) {
        $sql = "DELETE FROM warehouses WHERE warehouse_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
