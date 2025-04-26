<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Supplier.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$supplier = new Supplier($conn);

// Handle form submission
if (isset($_POST['save_supplier'])) {
    $data = [
        'company_name' => $_POST['company_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'tax_id' => $_POST['tax_id'],
        'payment_terms' => $_POST['payment_terms'],
        'notes' => $_POST['notes'],
        'status' => 'Active',
        'rating' => 0
    ];
    $supplier->create($data);
    header("Location: supplier.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management - Procurement System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">Add New Supplier</button>
    </div>

    <!-- Supplier List -->
    <div class="card">
        <div class="card-body">
            <table id="supplierTable" class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $result = $conn->query("SELECT * FROM suppliers ORDER BY company_name");
                while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['supplier_id']; ?></td>
                        <td><?= $row['company_name']; ?></td>
                        <td><?= $row['contact_person']; ?></td>
                        <td><?= $row['email']; ?></td>
                        <td><?= $row['phone']; ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Active' ? 'success' : 'danger'; ?>"><?= $row['status']; ?></span></td>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++) echo '<i class="bi bi-star' . ($i <= $row['rating'] ? '-fill' : '') . '"></i>'; ?>
                        </td>
                        <td>
                            <button 
    class="btn btn-sm btn-info viewSupplierBtn" 
    data-bs-toggle="modal" 
    data-bs-target="#viewSupplierModal"
    data-id="<?= $row['supplier_id']; ?>"
    data-company="<?= htmlspecialchars($row['company_name']); ?>"
    data-contact="<?= htmlspecialchars($row['contact_person']); ?>"
    data-email="<?= htmlspecialchars($row['email']); ?>"
    data-phone="<?= htmlspecialchars($row['phone']); ?>"
    data-address="<?= htmlspecialchars($row['address']); ?>"
    data-tax="<?= htmlspecialchars($row['tax_id']); ?>"
    data-terms="<?= htmlspecialchars($row['payment_terms']); ?>"
    data-notes="<?= htmlspecialchars($row['notes']); ?>"
    data-status="<?= $row['status']; ?>"
    data-rating="<?= $row['rating']; ?>"
>
    View
</button>
                            <button 
    class="btn btn-sm btn-warning editSupplierBtn" 
    data-bs-toggle="modal" 
    data-bs-target="#editSupplierModal"
    data-id="<?= $row['supplier_id']; ?>"
    data-company="<?= htmlspecialchars($row['company_name']); ?>"
    data-contact="<?= htmlspecialchars($row['contact_person']); ?>"
    data-email="<?= htmlspecialchars($row['email']); ?>"
    data-phone="<?= htmlspecialchars($row['phone']); ?>"
    data-address="<?= htmlspecialchars($row['address']); ?>"
    data-tax="<?= htmlspecialchars($row['tax_id']); ?>"
    data-terms="<?= htmlspecialchars($row['payment_terms']); ?>"
    data-notes="<?= htmlspecialchars($row['notes']); ?>"
>
    Edit
</button>
                            <a href="toggle_supplier_status.php?id=<?= $row['supplier_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to deactivate this user?');">
                                <?= $row['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tax ID</label>
                            <input type="text" name="tax_id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Terms</label>
                            <select name="payment_terms" class="form-select">
                                <option value="NET30">NET 30</option>
                                <option value="NET45">NET 45</option>
                                <option value="NET60">NET 60</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="save_supplier" class="btn btn-primary">Save Supplier</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- View Supplier Modal -->
<div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-labelledby="viewSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSupplierModalLabel">Supplier Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Company Name:</strong> <span id="viewCompanyName"></span>
          </div>
          <div class="col-md-6">
            <strong>Contact Person:</strong> <span id="viewContactPerson"></span>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Email:</strong> <span id="viewEmail"></span>
          </div>
          <div class="col-md-6">
            <strong>Phone:</strong> <span id="viewPhone"></span>
          </div>
        </div>
        <div class="mb-3">
          <strong>Address:</strong> <span id="viewAddress"></span>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Tax ID:</strong> <span id="viewTaxId"></span>
          </div>
          <div class="col-md-6">
            <strong>Payment Terms:</strong> <span id="viewPaymentTerms"></span>
          </div>
        </div>
        <div class="mb-3">
          <strong>Notes:</strong> <span id="viewNotes"></span>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Status:</strong> <span id="viewStatus" class="badge"></span>
          </div>
          <div class="col-md-6">
            <strong>Rating:</strong> <span id="viewRating"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="update_supplier.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="supplier_id" id="editSupplierId">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Company Name</label>
              <input type="text" name="company_name" id="editCompanyName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Person</label>
              <input type="text" name="contact_person" id="editContactPerson" class="form-control" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" id="editPhone" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" id="editAddress" class="form-control" rows="3"></textarea>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tax ID</label>
              <input type="text" name="tax_id" id="editTaxId" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment Terms</label>
              <select name="payment_terms" id="editPaymentTerms" class="form-select">
                <option value="NET30">NET 30</option>
                <option value="NET45">NET 45</option>
                <option value="NET60">NET 60</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" id="editNotes" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_supplier" class="btn btn-primary">Update Supplier</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables@1.10.24/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#supplierTable').DataTable();
    });
</script>
<script>
$(document).ready(function () {
    $('.viewSupplierBtn').on('click', function () {
        $('#viewCompanyName').text($(this).data('company'));
        $('#viewContactPerson').text($(this).data('contact'));
        $('#viewEmail').text($(this).data('email'));
        $('#viewPhone').text($(this).data('phone'));
        $('#viewAddress').text($(this).data('address'));
        $('#viewTaxId').text($(this).data('tax'));
        $('#viewPaymentTerms').text($(this).data('terms'));
        $('#viewNotes').text($(this).data('notes'));
        const status = $(this).data('status');
        $('#viewStatus').text(status).removeClass('bg-success bg-danger')
            .addClass(status === 'Active' ? 'bg-success' : 'bg-danger');
        
        const rating = $(this).data('rating');
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<i class="bi bi-star${i <= rating ? '-fill' : ''}"></i>`;
        }
        $('#viewRating').html(stars);
    });
});
$('.editSupplierBtn').on('click', function () {
    $('#editSupplierId').val($(this).data('id'));
    $('#editCompanyName').val($(this).data('company'));
    $('#editContactPerson').val($(this).data('contact'));
    $('#editEmail').val($(this).data('email'));
    $('#editPhone').val($(this).data('phone'));
    $('#editAddress').val($(this).data('address'));
    $('#editTaxId').val($(this).data('tax'));
    $('#editPaymentTerms').val($(this).data('terms'));
    $('#editNotes').val($(this).data('notes'));
});
</script>
</body>
</html>
