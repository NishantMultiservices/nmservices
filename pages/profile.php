<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Profile - ' . APP_NAME;
include '../includes/header.php';

$user = Auth::getCurrentUser();
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-person-circle"></i> User Profile</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-user-circle"></i> Profile Information
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo sanitize($user['full_name']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo sanitize($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> To change your password, please visit the <a href="change-password.php" class="alert-link">Change Password</a> page.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-shield-check"></i> Account Status
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div style="font-size: 3rem;">
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                    </div>
                    <p><strong>Account is Active</strong></p>
                    <p class="text-muted small">Your account is in good standing</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
