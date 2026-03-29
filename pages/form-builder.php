<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$page_title = 'Form Builder - ' . APP_NAME;
$user_id = $_SESSION['user_id'];
$form_id = intval($_GET['id'] ?? 0);

// Verify form exists and belongs to user
$stmt = $conn->prepare("SELECT * FROM custom_forms WHERE id = ? AND created_by = ?");
if ($stmt === false) {
    setMessage('❌ Database error: ' . $conn->error, 'danger');
    $form = null;
} else {
    $stmt->bind_param("ii", $form_id, $user_id);
    $stmt->execute();
    $form_result = $stmt->get_result();
    $form = $form_result->fetch_assoc();
    $stmt->close();

    if (!$form) {
        setMessage('❌ Form not found or access denied', 'danger');
    }
}

// Handle add field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_field') {
    $field_name = trim($_POST['field_name'] ?? '');
    $field_type = trim($_POST['field_type'] ?? '');
    $field_label = trim($_POST['field_label'] ?? '');
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $field_placeholder = trim($_POST['field_placeholder'] ?? '');

    // Validation
    if (empty($field_name)) {
        setMessage('❌ Field name is required', 'danger');
    } elseif (empty($field_type)) {
        setMessage('❌ Field type is required', 'danger');
    } elseif (empty($field_label)) {
        setMessage('❌ Field label is required', 'danger');
    } else {
        // Get next field order
        $order_result = $conn->query("SELECT COALESCE(MAX(field_order), 0) + 1 as next_order FROM form_fields WHERE form_id = '$form_id'");
        $order_row = $order_result->fetch_assoc();
        $field_order = $order_row['next_order'];

        $stmt = $conn->prepare("INSERT INTO form_fields (form_id, field_name, field_type, field_label, is_required, field_placeholder, field_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            setMessage('❌ Prepare error: ' . $conn->error, 'danger');
        } else {
            if (!$stmt->bind_param("isssssi", $form_id, $field_name, $field_type, $field_label, $is_required, $field_placeholder, $field_order)) {
                setMessage('❌ Bind error: ' . $stmt->error, 'danger');
                $stmt->close();
            } else {
                if ($stmt->execute()) {
                    setMessage('✅ Field added successfully', 'success');
                    $stmt->close();
                    header("Location: form-builder.php?id=$form_id&added=1", true, 303);
                    exit();
                } else {
                    setMessage('❌ Execute error: ' . $stmt->error, 'danger');
                    $stmt->close();
                }
            }
        }
    }
}

// Handle delete field
if (isset($_GET['delete_field']) && !empty($_GET['delete_field'])) {
    $field_id = intval($_GET['delete_field']);
    
    $stmt = $conn->prepare("DELETE FROM form_fields WHERE id = ? AND form_id = (SELECT id FROM custom_forms WHERE created_by = ?)");
    
    if ($stmt === false) {
        setMessage('❌ Prepare error: ' . $conn->error, 'danger');
    } else {
        if (!$stmt->bind_param("ii", $field_id, $user_id)) {
            setMessage('❌ Bind error: ' . $stmt->error, 'danger');
            $stmt->close();
        } else {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    setMessage('✅ Field deleted successfully', 'success');
                    $stmt->close();
                    header("Location: form-builder.php?id=$form_id&deleted=1", true, 303);
                    exit();
                } else {
                    setMessage('❌ Field not found', 'warning');
                    $stmt->close();
                }
            } else {
                setMessage('❌ Delete error: ' . $stmt->error, 'danger');
                $stmt->close();
            }
        }
    }
}

include '../includes/header.php';

// Get all fields for this form
$fields = null;
if ($form) {
    $fields = $conn->query("SELECT * FROM form_fields WHERE form_id = '$form_id' ORDER BY field_order ASC");
    if (!$fields) {
        setMessage('❌ Query error: ' . $conn->error, 'danger');
    }
}
?>

<div class="container-fluid container-wrapper">
    <div class="page-header d-flex align-items-center justify-content-between">
        <div>
            <a href="forms.php" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <h1 class="d-inline-block ms-2">
                <i class="bi bi-pencil-square"></i> Form Builder
            </h1>
        </div>
    </div>

    <?php displayMessage(); ?>

    <?php if ($form): ?>
        <!-- Form Info -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text"></i> <?php echo htmlspecialchars($form['form_name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars($form['form_description'] ?? 'No description provided'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light shadow-sm">
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Form ID:</strong> <code><?php echo htmlspecialchars($form['id']); ?></code>
                        </p>
                        <p class="mb-2">
                            <strong>Created:</strong> <?php echo date('d-m-Y H:i', strtotime($form['created_at'])); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Total Fields:</strong> 
                            <span class="badge bg-info">
                                <?php echo $fields ? $fields->num_rows : 0; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                    <i class="bi bi-plus-circle"></i> Add New Field
                </button>
                <a href="form-preview.php?id=<?php echo $form_id; ?>" class="btn btn-info" target="_blank">
                    <i class="bi bi-eye"></i> Preview Form
                </a>
                <a href="forms.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Forms
                </a>
            </div>
        </div>

        <!-- Form Fields Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-list"></i> Form Fields
            </div>
            <div class="card-body">
                <?php if ($fields && $fields->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%">Order</th>
                                    <th width="15%">Field Name</th>
                                    <th width="20%">Label</th>
                                    <th width="12%">Type</th>
                                    <th width="10%">Required</th>
                                    <th width="28%">Placeholder</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $fields->data_seek(0);
                                while ($field = $fields->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary fs-6">
                                                <?php echo htmlspecialchars($field['field_order']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($field['field_name']); ?></code>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($field['field_label']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php 
                                                $type_map = [
                                                    'text' => 'Text Input',
                                                    'email' => 'Email',
                                                    'phone' => 'Phone',
                                                    'number' => 'Number',
                                                    'date' => 'Date',
                                                    'textarea' => 'Text Area',
                                                    'select' => 'Dropdown',
                                                    'checkbox' => 'Checkbox',
                                                    'radio' => 'Radio'
                                                ];
                                                echo $type_map[$field['field_type']] ?? ucfirst($field['field_type']);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($field['is_required']): ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-check-circle"></i> Required
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Optional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $placeholder = htmlspecialchars($field['field_placeholder']);
                                            echo strlen($placeholder) > 30 ? substr($placeholder, 0, 30) . '...' : $placeholder;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?delete_field=<?php echo $field['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Delete this field? This action cannot be undone.');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-4" role="alert">
                        <i class="bi bi-info-circle fs-3"></i>
                        <p class="mt-2 mb-0">No fields added yet.</p>
                        <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                            Add your first field now
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Error Message -->
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Error!</strong> Form not found or you don't have permission to access it.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <a href="forms.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Go Back to Forms
        </a>
    <?php endif; ?>
</div>

<!-- Add Field Modal -->
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addFieldModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Form Field
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_field">

                    <!-- Field Name & Type -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="field_name" class="form-label fw-bold">
                                Field Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="field_name" name="field_name" 
                                   placeholder="e.g., customer_email" required pattern="[a-z_]+" title="Use lowercase letters and underscores only">
                            <div class="form-text">Lowercase with underscores (a_z, 0_9)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="field_type" class="form-label fw-bold">
                                Field Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="field_type" name="field_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="text">Text Input</option>
                                <option value="email">Email Address</option>
                                <option value="phone">Phone Number</option>
                                <option value="number">Number</option>
                                <option value="date">Date Picker</option>
                                <option value="textarea">Text Area</option>
                                <option value="select">Dropdown / Select</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="radio">Radio Button</option>
                            </select>
                        </div>
                    </div>

                    <!-- Field Label -->
                    <div class="mb-3">
                        <label for="field_label" class="form-label fw-bold">
                            Field Label <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="field_label" name="field_label" 
                               placeholder="e.g., Customer Email Address" required>
                        <div class="form-text">This is what users will see on the form</div>
                    </div>

                    <!-- Placeholder -->
                    <div class="mb-3">
                        <label for="field_placeholder" class="form-label">Placeholder Text</label>
                        <input type="text" class="form-control" id="field_placeholder" name="field_placeholder" 
                               placeholder="e.g., Enter your email address">
                        <div class="form-text">Optional: Helper text shown inside the field</div>
                    </div>

                    <!-- Required Checkbox -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required">
                        <label class="form-check-label" for="is_required">
                            <strong>Mark this field as required</strong>
                        </label>
                        <div class="form-text">Users must fill this field to submit the form</div>
                    </div>

                    <!-- Tips -->
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-lightbulb"></i> 
                        <strong>Tips:</strong>
                        <ul class="mb-0 mt-2 ms-3">
                            <li>Fields appear on the form in the order they're added</li>
                            <li>Use descriptive names for better organization</li>
                            <li>You can delete fields anytime</li>
                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Add Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>