<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Income - ' . APP_NAME;

$user_id = $_SESSION['user_id'];
$reload_page = false;

// Handle add income
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;
    $amount = floatval($_POST['amount'] ?? 0);
    $income_date = $_POST['income_date'] ?? date('Y-m-d');
    $description = $_POST['description'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $reference_no = $_POST['reference_no'] ?? '';

    if ($amount <= 0) {
        setMessage('❌ Amount must be greater than 0', 'danger');
    } else {
        $stmt = $conn->prepare("INSERT INTO income (customer_id, amount, income_date, description, payment_method, reference_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idssssi", $customer_id, $amount, $income_date, $description, $payment_method, $reference_no, $user_id);
        
        if ($stmt->execute()) {
            setMessage('✅ Income record added successfully', 'success');
            $reload_page = true;
            $stmt->close();
        } else {
            setMessage('❌ Failed to add income record: ' . $stmt->error, 'danger');
            $stmt->close();
        }
    }
}

// Handle delete income
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM income WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setMessage('✅ Income record deleted successfully', 'success');
        $reload_page = true;
    } else {
        setMessage('❌ Failed to delete', 'danger');
    }
    $stmt->close();
}

include '../includes/header.php';

// Get all income
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
            <?php if ($income_records && $income_records->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="incomeTable">
                        <thead class="table-dark">
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
                                    <td><?php echo date('d-m-Y', strtotime($row['income_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><span class="badge bg-success">₹<?php echo number_format($row['amount'], 2); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['reference_no'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="4" class="text-end">Total Income:</td>
                                <td colspan="4">₹<?php echo number_format($total_income, 2); ?></td>
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Add Income</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">-- Select Customer --</option>
                                <?php if ($customers && $customers->num_rows > 0) {
                                    $customers->data_seek(0);
                                    while ($c = $customers->fetch_assoc()) {
                                        echo "<option value='" . $c['id'] . "'>" . htmlspecialchars($c['name']) . "</option>";
                                    }
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="income_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="income_date" name="income_date" value="<?php echo date('Y-m-d'); ?>" required>
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

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reference_no" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
