<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
requireLogin();

$page_title = 'Dashboard - ' . APP_NAME;
include 'includes/header.php';

// Get dashboard statistics
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$total_income = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM income")->fetch_assoc()['total'];
$total_expense = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM expense")->fetch_assoc()['total'];
$net_profit = $total_income - $total_expense;

// Get recent income
$recent_income = $conn->query("SELECT i.*, c.name FROM income i LEFT JOIN customers c ON i.customer_id = c.id ORDER BY i.income_date DESC LIMIT 5");

// Get recent expense
$recent_expense = $conn->query("SELECT * FROM expense ORDER BY expense_date DESC LIMIT 5");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    </div>
    
    <?php displayMessage(); ?>

    <!-- Quick Actions (MOVED TO TOP) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="pages/customers.php" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> Add Customer
                        </a>
                        <a href="pages/income.php" class="btn btn-outline-success">
                            <i class="bi bi-plus-lg"></i> Add Income
                        </a>
                        <a href="pages/expense.php" class="btn btn-outline-danger">
                            <i class="bi bi-plus-lg"></i> Add Expense
                        </a>
                        <a href="pages/reports.php" class="btn btn-outline-info">
                            <i class="bi bi-graph-up"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card customers">
                <i class="bi bi-people" style="font-size: 2rem;"></i>
                <h3><?php echo $total_customers; ?></h3>
                <p>Total Customers</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card income">
                <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency($total_income); ?></h3>
                <p>Total Income</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card expense">
                <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency($total_expense); ?></h3>
                <p>Total Expense</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                <h3><?php echo formatCurrency($net_profit); ?></h3>
                <p>Net Profit</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Income -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-cash-in"></i> Recent Income
                </div>
                <div class="card-body">
                    <?php if ($recent_income->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_income->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo formatDate($row['income_date']); ?></td>
                                            <td><?php echo $row['name'] ?? 'N/A'; ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo formatCurrency($row['amount']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="pages/income.php" class="btn btn-sm btn-primary">View All</a>
                    <?php else: ?>
                        <p class="text-muted">No income records yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Expense -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-cash-out"></i> Recent Expense
                </div>
                <div class="card-body">
                    <?php if ($recent_expense->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_expense->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo formatDate($row['expense_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?php echo formatCurrency($row['amount']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="pages/expense.php" class="btn btn-sm btn-primary">View All</a>
                    <?php else: ?>
                        <p class="text-muted">No expense records yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
