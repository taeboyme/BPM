<?php
class PurchaseOrder {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function createOrder($requestId, $supplierName, $totalAmount) {
        $status = 'PENDING';
        $date = date('Y-m-d');
        
        $sql = "INSERT INTO purchase_orders (request_id, supplier_name, order_date, total_amount, status) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issds", $requestId, $supplierName, $date, $totalAmount, $status);
        
        return $stmt->execute();
    }
    
    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE purchase_orders SET status = ? WHERE order_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $orderId);
        
        return $stmt->execute();
    }
}
?>