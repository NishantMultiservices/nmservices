<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/Auth.php';
requireLogin();

$page_title = 'Change Password - ' . APP_NAME;
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($new_password !== $confirm_password) {
        setMessage('New passwords do not match', 'danger');
    } elseif (strlen($new_password) < 6) {
        setMessage('Password must be at least 6 characters long', 'danger');
    } else {
        $auth = new Auth($conn);
        $result = $auth->changePassword($_SESSION['user_id'], $old_password, $new_password);
        setMessage($result['message'], $result['success'] ? 'success' : 'danger');
    }
}
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-lock"></i> Change Password</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-shield-lock"></i> Update Your Password
                </div>
                <div class="card-body">
                    <form method="POST" data-validate="true">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                            <small class="form-text text-muted">Enter your current password for verification</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <small class="form-text text-muted">Password must be at least 6 characters long</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                            <small class="form-text text-muted">Re-enter your new password</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Password
                            </button>
                            <a href="profile.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-warning mt-4">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Security Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Use a strong password with uppercase, lowercase, numbers, and symbols</li>
                    <li>Don't share your password with anyone</li>
                    <li>Change your password regularly</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
