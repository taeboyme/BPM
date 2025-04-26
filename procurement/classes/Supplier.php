<?php
class Supplier {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $sql = "SELECT * FROM suppliers ORDER BY company_name";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function getById($id) {
        $sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $sql = "INSERT INTO suppliers (company_name, contact_person, email, phone, address, tax_id, payment_terms, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $data['company_name'],
            $data['contact_person'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['tax_id'],
            $data['payment_terms'],
            $data['notes']
        );

        return $stmt->execute();
    }

    public function update($id, $data) {
        $sql = "UPDATE suppliers SET 
                company_name = ?,
                contact_person = ?,
                email = ?,
                phone = ?,
                address = ?,
                tax_id = ?,
                payment_terms = ?,
                notes = ?
                WHERE supplier_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssi", 
            $data['company_name'],
            $data['contact_person'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['tax_id'],
            $data['payment_terms'],
            $data['notes'],
            $id
        );

        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM suppliers WHERE supplier_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE suppliers SET status = ? WHERE supplier_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function updateRating($id, $rating) {
        if ($rating < 0 || $rating > 5) {
            return false;
        }
        
        $sql = "UPDATE suppliers SET rating = ? WHERE supplier_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $rating, $id);
        return $stmt->execute();
    }

    public function search($term) {
        $term = "%$term%";
        $sql = "SELECT * FROM suppliers 
                WHERE company_name LIKE ? 
                OR contact_person LIKE ? 
                OR email LIKE ?
                ORDER BY company_name";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $term, $term, $term);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getActiveSuppliers() {
        $sql = "SELECT * FROM suppliers WHERE status = 'Active' ORDER BY company_name";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function validateEmail($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM suppliers WHERE email = ?";
        $params = ["s", $email];
        
        if ($excludeId) {
            $sql .= " AND supplier_id != ?";
            $params = ["si", $email, $excludeId];
        }
        
        $stmt = $this->conn->prepare($sql);
        call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['count'] == 0;
    }

    public function getSupplierStats() {
        $stats = [];
        
        // Total suppliers
        $sql = "SELECT COUNT(*) as total FROM suppliers";
        $result = $this->conn->query($sql);
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Active suppliers
        $sql = "SELECT COUNT(*) as active FROM suppliers WHERE status = 'Active'";
        $result = $this->conn->query($sql);
        $stats['active'] = $result->fetch_assoc()['active'];
        
        // Average rating
        $sql = "SELECT AVG(rating) as avg_rating FROM suppliers WHERE rating > 0";
        $result = $this->conn->query($sql);
        $stats['avg_rating'] = round($result->fetch_assoc()['avg_rating'], 1);
        
        return $stats;
    }
}
?>