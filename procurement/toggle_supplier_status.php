<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Supplier.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Supplier ID is required';
    header('Location: supplier.php');
    exit();
}

$supplier_id = intval($_GET['id']);
$supplier = new Supplier($conn);

// Get current supplier data
$currentSupplier = $supplier->getById($supplier_id);
if (!$currentSupplier) {
    $_SESSION['error'] = 'Supplier not found';
    header('Location: supplier.php');
    exit();
}

// Toggle status
$newStatus = $currentSupplier['status'] == 'Active' ? 'Inactive' : 'Active';

if ($supplier->updateStatus($supplier_id, $newStatus)) {
    $_SESSION['message'] = "Supplier status updated to $newStatus";
} else {
    $_SESSION['error'] = 'Failed to update supplier status';
}

header('Location: supplier.php');
exit();
?>