<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Custom Forms - ' . APP_NAME;
include '../includes/header.php';
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-file-text"></i> Custom Forms</h1>
    </div>

    <?php displayMessage(); ?>

    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFormModal">
                <i class="bi bi-plus-lg"></i> Create New Form
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-list"></i> Available Forms
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Custom forms feature allows you to create and manage custom forms for data collection.
            </div>
            <p class="text-muted">This feature is coming soon. You'll be able to create, manage, and print custom forms.</p>
        </div>
    </div>
</div>

<!-- Create Form Modal -->
<div class="modal fade" id="createFormModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Create New Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="form_name" class="form-label">Form Name *</label>
                        <input type="text" class="form-control" id="form_name" name="form_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="form_description" class="form-label">Description</label>
                        <textarea class="form-control" id="form_description" name="form_description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
