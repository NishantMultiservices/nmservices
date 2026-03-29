<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Expense - ' . APP_NAME;
include '../includes/header.php';

// Handle add expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $category = escape($_POST['category'] ?? '');
    $description = escape($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = escape($_POST['payment_method'] ?? '');
    $reference_number = escape($_POST['reference_number'] ?? '');
    $expense_date = escape($_POST['expense_date'] ?? date('Y-m-d'));
    $vendor_name = escape($_POST['vendor_name'] ?? '');
    $user_id = $_SESSION['user_id'];

    if ($amount <= 0) {
        setMessage('Amount must be greater than 0', 'danger');
    } else {
        $stmt = $conn->prepare("INSERT INTO expense (category, description, amount, payment_method, reference_number, expense_date, vendor_name, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssi", $category, $description, $amount, $payment_method, $reference_number, $expense_date, $vendor_name, $user_id);
        
        if ($stmt->execute()) {
            setMessage('Expense record added successfully', 'success');
        } else {
            setMessage('Failed to add expense record', 'danger');
        }
    }
}

// Handle delete expense
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM expense WHERE id = $id")) {
        setMessage('Expense record deleted successfully', 'success');
    }
    header("Location: expense.php");
}

// Get all expense records
$expense_records = $conn->query("SELECT * FROM expense ORDER BY expense_date DESC");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-cash-out"></i> Expense</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-lg"></i> Add Expense
            </button>
            <button class="btn btn-secondary" onclick="exportTableToCSV('expenseTable', 'expense.csv')">
                <i class="bi bi-download"></i> Export to CSV
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> Expense Records
        </div>
        <div class="card-body">
            <?php if ($expense_records->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="expenseTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Vendor</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_expense = 0;
                            while ($row = $expense_records->fetch_assoc()): 
                                $total_expense += $row['amount'];
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo formatDate($row['expense_date']); ?></td>
                                    <td><span class="badge bg-warning"><?php echo sanitize($row['category']); ?></span></td>
                                    <td><?php echo sanitize($row['description']); ?></td>
                                    <td><?php echo sanitize($row['vendor_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-danger"><?php echo formatCurrency($row['amount']); ?></span></td>
                                    <td><?php echo sanitize($row['payment_method'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize($row['reference_number'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="table-light">
                                <td colspan="5" class="text-end"><strong>Total Expense:</strong></td>
                                <td colspan="4"><strong><?php echo formatCurrency($total_expense); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No expense records found. <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#addExpenseModal">Add one now</button></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" data-validate="true">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Salary">Salary</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Rent">Rent</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount *</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="vendor_name" class="form-label">Vendor Name</label>
                            <input type="text" class="form-control" id="vendor_name" name="vendor_name">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">-- Select Method --</option>
                                <option value="Cash">Cash</option>
                                <option value="Check">Check</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-save"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
