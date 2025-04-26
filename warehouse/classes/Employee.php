<?php
class Employee {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getManagers() {
        $sql = "SELECT employee_id, first_name, last_name, email, phone 
                FROM employees 
                WHERE position_id IS NOT NULL AND employee_id IN (
                    SELECT department_head_id FROM departments
                )
                ORDER BY first_name ASC";

        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getEmployeeById($employee_id) {
        $sql = "SELECT * FROM employees WHERE employee_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addEmployee($data) {
        $sql = "INSERT INTO employees (
                    employee_id, first_name, last_name, email, password, phone, 
                    date_of_birth, gender, nationality, marital_status, hire_date, 
                    employment_status, department_id, position_id, manager_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssssiiis",
            $data['employee_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['password'],
            $data['phone'],
            $data['date_of_birth'],
            $data['gender'],
            $data['nationality'],
            $data['marital_status'],
            $data['hire_date'],
            $data['employment_status'],
            $data['department_id'],
            $data['position_id'],
            $data['manager_id']
        );

        return $stmt->execute();
    }

    public function updateEmployee($employee_id, $data) {
        $sql = "UPDATE employees 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                    date_of_birth = ?, gender = ?, nationality = ?, 
                    marital_status = ?, hire_date = ?, employment_status = ?, 
                    department_id = ?, position_id = ?, manager_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE employee_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssiisss",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['date_of_birth'],
            $data['gender'],
            $data['nationality'],
            $data['marital_status'],
            $data['hire_date'],
            $data['employment_status'],
            $data['department_id'],
            $data['position_id'],
            $data['manager_id'],
            $employee_id
        );

        return $stmt->execute();
    }

    public function deleteEmployee($employee_id) {
        // Check if employee is a department head
        $sql = "SELECT COUNT(*) as count FROM departments WHERE department_head_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            return false; // Cannot delete if department head
        }

        // Proceed with deletion
        $sql = "DELETE FROM employees WHERE employee_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $employee_id);
        return $stmt->execute();
    }

    public function getAllEmployees() {
        $sql = "SELECT * FROM employees ORDER BY first_name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function searchEmployees($search_term) {
        $search_term = "%$search_term%";
        $sql = "SELECT * FROM employees 
                WHERE first_name LIKE ? 
                OR last_name LIKE ?
                OR employee_id LIKE ? 
                OR email LIKE ?
                ORDER BY first_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", 
            $search_term, 
            $search_term, 
            $search_term, 
            $search_term
        );

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getEmployeesByDepartment($department_id) {
        $sql = "SELECT * FROM employees WHERE department_id = ? ORDER BY first_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function validateEmployee($data) {
        $errors = [];

        if (empty($data['employee_id'])) $errors[] = "Employee ID is required";
        if (empty($data['first_name'])) $errors[] = "First name is required";
        if (empty($data['last_name'])) $errors[] = "Last name is required";
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if (!empty($data['phone']) && !preg_match("/^[0-9\-\(\)\/\+\s]*$/", $data['phone'])) {
            $errors[] = "Invalid phone number format";
        }

        return $errors;
    }

    public function isEmailUnique($email, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE email = ?";
        $params = [$email];
        $types = "s";

        if ($exclude_id) {
            $sql .= " AND employee_id != ?";
            $params[] = $exclude_id;
            $types .= "s";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result['count'] == 0;
    }
}
?>
