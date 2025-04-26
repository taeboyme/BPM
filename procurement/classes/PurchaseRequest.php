<?php
class PurchaseRequest {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function createRequest($employeeId, $departmentId, $purpose) {
        $status = 'PENDING';
        $date = date('Y-m-d');
        
        $sql = "INSERT INTO purchase_requests (employee_id, department_id, request_date, status, purpose) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisss", $employeeId, $departmentId, $date, $status, $purpose);
        
        return $stmt->execute();
    }
    
    public function addRequestItem($requestId, $itemName, $quantity, $unitPrice) {
        $sql = "INSERT INTO request_items (request_id, item_name, quantity, unit_price) 
                VALUES (?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isid", $requestId, $itemName, $quantity, $unitPrice);
        
        return $stmt->execute();
    }
    
    public function getRequestDetails($requestId) {
        $sql = "SELECT pr.*, ri.item_name, ri.quantity, ri.unit_price 
                FROM purchase_requests pr 
                LEFT JOIN request_items ri ON pr.request_id = ri.request_id 
                WHERE pr.request_id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    public function updateStatus($requestId, $status) {
    $stmt = $this->conn->prepare("UPDATE purchase_requests SET status = ? WHERE request_id = ?");
    $stmt->bind_param("si", $status, $requestId);
    return $stmt->execute();
    }
}
?>