<?php
class Inventory {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addItem($warehouse_id, $item_name, $description, $quantity, $unit) {
        $sql = "INSERT INTO inventory_items (warehouse_id, item_name, description, quantity, unit) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $warehouse_id, $item_name, $description, $quantity, $unit);
        return $stmt->execute();
    }

    public function updateStock($item_id, $quantity, $movement_type, $reference, $date) {
    $direction = $movement_type === 'IN' ? '+' : '-';

    $stmt = $this->conn->prepare("
        UPDATE inventory_items 
        SET quantity = quantity $direction ? 
        WHERE item_id = ?
    ");
    $stmt->bind_param("ii", $quantity, $item_id);
    $stmt->execute();

    $stmt2 = $this->conn->prepare("
        INSERT INTO inventory_movements (item_id, quantity, movement_type, reference, movement_date) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param("iisss", $item_id, $quantity, $movement_type, $reference, $date);
    return $stmt2->execute();
}

    public function getInventoryByWarehouse($warehouse_id) {
        $sql = "SELECT * FROM inventory_items WHERE warehouse_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $warehouse_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllInventoryItems() {
        $sql = "SELECT i.*, w.name as warehouse_name 
                FROM inventory_items i 
                LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id 
                ORDER BY i.item_name ASC";

        $result = $this->conn->query($sql);

        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function getLowStockItems($threshold = 10) {
        $sql = "SELECT i.*, w.name as warehouse_name 
                FROM inventory_items i 
                LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id 
                WHERE i.quantity <= ? 
                ORDER BY i.quantity ASC, i.item_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $threshold);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }

    public function getItemById($item_id) {
        $sql = "SELECT i.*, w.name as warehouse_name 
                FROM inventory_items i 
                LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id 
                WHERE i.item_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_assoc();
        }

        return null;
    }
}
?>
