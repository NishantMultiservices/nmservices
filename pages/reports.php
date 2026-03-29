<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Reports - ' . APP_NAME;
include '../includes/header.php';

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-01-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'summary';

// Income summary
$income_result = $conn->query("
    SELECT 
        COUNT(*) as count,
        SUM(amount) as total,
        AVG(amount) as average
    FROM income 
    WHERE income_date BETWEEN '$start_date' AND '$end_date'
");
$income_summary = $income_result->fetch_assoc();

// Expense summary
$expense_result = $conn->query("
    SELECT 
        COUNT(*) as count,
        SUM(amount) as total,
        AVG(amount) as average
    FROM expense 
    WHERE expense_date BETWEEN '$start_date' AND '$end_date'
");
$expense_summary = $expense_result->fetch_assoc();

// Expense by category
$expense_by_category = $conn->query("
    SELECT 
        category,
        SUM(amount) as total,
        COUNT(*) as count
    FROM expense 
    WHERE expense_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY category
    ORDER BY total DESC
");

// Top customers
$top_customers = $conn->query("
    SELECT 
        c.id,
        c.name,
        COUNT(i.id) as transaction_count,
        SUM(i.amount) as total_amount
    FROM customers c
    LEFT JOIN income i ON c.id = i.customer_id AND i.income_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY c.id, c.name
    HAVING total_amount IS NOT NULL
    ORDER BY total_amount DESC
    LIMIT 10
");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-graph-up"></i> Reports</h1>
    </div>

    <?php displayMessage(); ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-funnel"></i> Filter Reports
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary" data-action="print">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card income">
                <i class="bi bi-cash-in" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency($income_summary['total'] ?? 0); ?></h3>
                <p>Total Income</p>
                <small><?php echo $income_summary['count'] ?? 0; ?> transactions</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card expense">
                <i class="bi bi-cash-out" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency($expense_summary['total'] ?? 0); ?></h3>
                <p>Total Expense</p>
                <small><?php echo $expense_summary['count'] ?? 0; ?> transactions</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency(($income_summary['total'] ?? 0) - ($expense_summary['total'] ?? 0)); ?></h3>
                <p>Net Profit</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card customers">
                <i class="bi bi-people" style="font-size: 2rem;"></i>
                <h3><?php 
                    $customer_result = $conn->query("SELECT COUNT(*) as count FROM customers");
                    echo $customer_result->fetch_assoc()['count'];
                ?></h3>
                <p>Total Customers</p>
            </div>
        </div>
    </div>

    <!-- Expense by Category -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pie-chart"></i> Expense by Category
                </div>
                <div class="card-body">
                    <?php if ($expense_by_category->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $expense_by_category->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?php echo sanitize($row['category']); ?></span></td>
                                            <td><?php echo $row['count']; ?></td>
                                            <td><?php echo formatCurrency($row['total']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-star"></i> Top Customers
                </div>
                <div class="card-body">
                    <?php if ($top_customers->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Transactions</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $top_customers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo sanitize($row['name']); ?></td>
                                            <td><?php echo $row['transaction_count']; ?></td>
                                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No customer data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
