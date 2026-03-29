<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$page_title = 'User Management - ' . APP_NAME;
include '../includes/header.php';

// Handle status change
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];
    
    if ($action === 'deactivate') {
        $conn->query("UPDATE users SET status = 'inactive' WHERE id = $user_id");
        setMessage('User deactivated successfully', 'success');
    } elseif ($action === 'activate') {
        $conn->query("UPDATE users SET status = 'active' WHERE id = $user_id");
        setMessage('User activated successfully', 'success');
    }
    header("Location: users.php");
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-people"></i> User Management</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> All Users
        </div>
        <div class="card-body">
            <?php if ($users->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo sanitize($row['username']); ?></td>
                                    <td><?php echo sanitize($row['email']); ?></td>
                                    <td><?php echo sanitize($row['full_name']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($row['created_at']); ?></td>
                                    <td>
                                        <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                                            <?php if ($row['status'] === 'active'): ?>
                                                <a href="?action=deactivate&user_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this user?')">
                                                    <i class="bi bi-lock"></i> Deactivate
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=activate&user_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="bi bi-unlock"></i> Activate
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
