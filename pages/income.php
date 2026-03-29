<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Income - ' . APP_NAME;
include '../includes/header.php';

// Handle add income
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $description = escape($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = escape($_POST['payment_method'] ?? '');
    $reference_number = escape($_POST['reference_number'] ?? '');
    $income_date = escape($_POST['income_date'] ?? date('Y-m-d'));
    $user_id = $_SESSION['user_id'];

    if ($amount <= 0) {
        setMessage('Amount must be greater than 0', 'danger');
    } else {
        $stmt = $conn->prepare("INSERT INTO income (customer_id, description, amount, payment_method, reference_number, income_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsss", $customer_id, $description, $amount, $payment_method, $reference_number, $income_date);
        
        if ($stmt->execute()) {
            setMessage('Income record added successfully', 'success');
        } else {
            setMessage('Failed to add income record', 'danger');
        }
    }
}

// Handle delete income
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM income WHERE id = $id")) {
        setMessage('Income record deleted successfully', 'success');
    }
    header("Location: income.php");
}

// Get all income with customer details
$income_records = $conn->query("SELECT i.*, c.name FROM income i LEFT JOIN customers c ON i.customer_id = c.id ORDER BY i.income_date DESC");

// Get customers for dropdown
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-cash-in"></i> Income</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="bi bi-plus-lg"></i> Add Income
            </button>
            <button class="btn btn-secondary" onclick="exportTableToCSV('incomeTable', 'income.csv')">
                <i class="bi bi-download"></i> Export to CSV
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> Income Records
        </div>
        <div class="card-body">
            <?php if ($income_records->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="incomeTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_income = 0;
                            while ($row = $income_records->fetch_assoc()): 
                                $total_income += $row['amount'];
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo formatDate($row['income_date']); ?></td>
                                    <td><?php echo sanitize($row['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize($row['description']); ?></td>
                                    <td><span class="badge bg-success"><?php echo formatCurrency($row['amount']); ?></span></td>
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
                                <td colspan="4" class="text-end"><strong>Total Income:</strong></td>
                                <td colspan="4"><strong><?php echo formatCurrency($total_income); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No income records found. <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#addIncomeModal">Add one now</button></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Add Income</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" data-validate="true">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">-- Select Customer --</option>
                                <?php 
                                $customers->data_seek(0);
                                while ($cust = $customers->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $cust['id']; ?>">
                                        <?php echo sanitize($cust['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="income_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="income_date" name="income_date" value="<?php echo date('Y-m-d'); ?>" required>
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
                    </div>

                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
