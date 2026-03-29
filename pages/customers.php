<?php

require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Customers - ' . APP_NAME;
include '../includes/header.php';

// Handle add customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = escape($_POST['name'] ?? '');
    $email = escape($_POST['email'] ?? '');
    $phone = escape($_POST['phone'] ?? '');
    $address = escape($_POST['address'] ?? '');
    $city = escape($_POST['city'] ?? '');
    $state = escape($_POST['state'] ?? '');
    $postal_code = escape($_POST['postal_code'] ?? '');
    $country = escape($_POST['country'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($name)) {
        setMessage('Customer name is required', 'danger');
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, city, state, postal_code, country, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $name, $email, $phone, $address, $city, $state, $postal_code, $country, $user_id);
        
        if ($stmt->execute()) {
            setMessage('Customer added successfully', 'success');
        } else {
            setMessage('Failed to add customer', 'danger');
        }
    }
}

// Handle delete customer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("DELETE FROM customers WHERE id = $id");
    if ($result) {
        setMessage('Customer deleted successfully', 'success');
    } else {
        setMessage('Failed to delete customer', 'danger');
    }
    header("Location: customers.php");
}

// Get all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-people"></i> Customers</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-plus-lg"></i> Add New Customer
            </button>
            <button class="btn btn-secondary" onclick="exportTableToCSV('customersTable', 'customers.csv')">
                <i class="bi bi-download"></i> Export to CSV
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> All Customers
        </div>
        <div class="card-body">
            <?php if ($customers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="customersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo sanitize($row['name']); ?></td>
                                    <td><?php echo sanitize($row['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize($row['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize($row['city'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize($row['country'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatDate($row['created_at']); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No customers found. <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add one now</button></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" data-validate="true">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>