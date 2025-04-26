<?php
class Inventory {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function addItem($warehouseId, $itemName, $description, $quantity, $unit) {
        $sql = "INSERT INTO inventory_items (warehouse_id, item_name, description, quantity, unit) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $warehouseId, $itemName, $description, $quantity, $unit);
        
        return $stmt->execute();
    }
    
    public function recordMovement($itemId, $movementType, $quantity, $reference) {
        $date = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO inventory_movements (item_id, movement_type, quantity, movement_date, reference) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isis", $itemId, $movementType, $quantity, $date, $reference);
        
        if ($stmt->execute()) {
            // Update inventory quantity
            $sql = "UPDATE inventory_items SET quantity = quantity + ? WHERE item_id = ?";
            if ($movementType == 'OUT') {
                $quantity = -$quantity;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $quantity, $itemId);
            return $stmt->execute();
        }
        
        return false;
    }

}
?>