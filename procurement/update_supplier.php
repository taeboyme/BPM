<?php
session_start();
require_once 'config/database.php';       // Database connection
require_once 'classes/Supplier.php';      // Supplier class

$supplier = new Supplier($conn);          // Instantiate the Supplier class

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supplier'])) {
    $id = $_POST['supplier_id'];
    $data = [
        'company_name' => $_POST['company_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'tax_id' => $_POST['tax_id'],
        'payment_terms' => $_POST['payment_terms'],
        'notes' => $_POST['notes']
    ];

    $supplier->update($id, $data);  // ✅ Use the initialized object
    header("Location: supplier.php");
    exit;
}
?>