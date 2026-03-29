<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = 'Custom Forms';

// ===== CREATE TABLES IF NOT EXISTS (WITHOUT DROPPING) =====
try {
    // Create custom_forms table - DO NOT DROP
    $sql_create_forms = "CREATE TABLE IF NOT EXISTS custom_forms (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        form_name VARCHAR(255) NOT NULL,
        form_description LONGTEXT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql_create_forms)) {
        error_log("Error creating custom_forms table: " . $conn->error);
    }
    
    // Create form_fields table - DO NOT DROP
    $sql_create_fields = "CREATE TABLE IF NOT EXISTS form_fields (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        form_id INT NOT NULL,
        field_name VARCHAR(255) NOT NULL,
        field_type VARCHAR(50) NOT NULL,
        field_label VARCHAR(255) NOT NULL,
        is_required TINYINT(1) DEFAULT 0,
        field_placeholder TEXT NULL,
        field_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_form_id (form_id),
        FOREIGN KEY (form_id) REFERENCES custom_forms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql_create_fields)) {
        error_log("Error creating form_fields table: " . $conn->error);
    }
    
    // Verify form_description column exists - ADD IF MISSING
    $check_column = $conn->query("SHOW COLUMNS FROM custom_forms LIKE 'form_description'");
    
    if ($check_column->num_rows === 0) {
        // Column doesn't exist, add it
        $alter_query = "ALTER TABLE custom_forms ADD COLUMN form_description LONGTEXT NULL AFTER form_name";
        if (!$conn->query($alter_query)) {
            error_log("Error adding form_description column: " . $conn->error);
        }
    }
    
} catch (Exception $e) {
    error_log("Table creation error: " . $e->getMessage());
}

// Process form creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $form_name = trim($_POST['form_name'] ?? '');
    $form_description = trim($_POST['form_description'] ?? '');
    
    if (empty($form_name)) {
        $_SESSION['message'] = 'Form name is required';
        $_SESSION['message_type'] = 'danger';
    } else {
        try {
            // INSERT INTO custom_forms
            $sql_insert = "INSERT INTO custom_forms (form_name, form_description, created_by) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_insert);
            
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ssi", $form_name, $form_description, $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $form_id = $stmt->insert_id;
            $stmt->close();
            
            $_SESSION['message'] = 'Form created successfully!';
            $_SESSION['message_type'] = 'success';
            
            header("Location: form-builder.php?id=" . $form_id);
            exit;
            
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            error_log("Form creation error: " . $e->getMessage());
        }
    }
}

// Process form deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $form_id = intval($_GET['delete']);
    
    try {
        // SELECT to verify ownership
        $sql_check = "SELECT id FROM custom_forms WHERE id = ? AND created_by = ?";
        $check = $conn->prepare($sql_check);
        
        if ($check === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check->bind_param("ii", $form_id, $user_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            // DELETE FROM form_fields
            $sql_del_fields = "DELETE FROM form_fields WHERE form_id = ?";
            $del_fields = $conn->prepare($sql_del_fields);
            
            if ($del_fields === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $del_fields->bind_param("i", $form_id);
            
            if (!$del_fields->execute()) {
                throw new Exception("Delete fields failed: " . $del_fields->error);
            }
            
            $del_fields->close();
            
            // DELETE FROM custom_forms
            $sql_del_form = "DELETE FROM custom_forms WHERE id = ? AND created_by = ?";
            $del_form = $conn->prepare($sql_del_form);
            
            if ($del_form === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $del_form->bind_param("ii", $form_id, $user_id);
            
            if (!$del_form->execute()) {
                throw new Exception("Delete form failed: " . $del_form->error);
            }
            
            $del_form->close();
            
            $_SESSION['message'] = 'Form deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Form not found';
            $_SESSION['message_type'] = 'warning';
        }
        $check->close();
        
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        error_log("Form deletion error: " . $e->getMessage());
    }
    
    header("Location: forms.php");
    exit;
}

// Get all forms
$forms_list = array();
try {
    // SELECT all forms for current user
    $sql_select = "SELECT id, form_name, form_description, created_at FROM custom_forms WHERE created_by = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql_select);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $forms_list[] = $row;
    }
    $stmt->close();
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error fetching forms: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    error_log("Error fetching forms: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="container-fluid container-wrapper">
    <div class="page-header">
        <h1><i class="bi bi-file-earmark-text"></i> Custom Forms</h1>
    </div>

    <!-- Display Messages -->
    <?php 
    if (isset($_SESSION['message'])): 
        $type = $_SESSION['message_type'] ?? 'info';
    ?>
        <div class="alert alert-<?php echo htmlspecialchars($type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif; 
    ?>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createFormModal">
                <i class="bi bi-plus-circle"></i> Create New Form
            </button>
        </div>
    </div>

    <!-- Forms List -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list"></i> Your Forms</h5>
        </div>
        <div class="card-body">
            <?php if (count($forms_list) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Form Name</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forms_list as $form): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($form['form_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $desc = $form['form_description'] ?? '';
                                        echo htmlspecialchars(strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc);
                                        ?>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($form['created_at'])); ?></td>
                                    <td>
                                        <a href="form-builder.php?id=<?php echo $form['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $form['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this form and all its fields?');">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-3 mb-0">No forms created yet</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createFormModal">
                        Create Your First Form
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Create Form -->
<div class="modal fade" id="createFormModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create New Form</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">

                    <div class="mb-3">
                        <label for="form_name" class="form-label fw-bold">
                            Form Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="form_name" name="form_name" 
                               placeholder="e.g., Customer Feedback Form" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="form_description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="form_description" name="form_description" 
                                  rows="4" placeholder="Optional description of the form..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> After creating, you'll be redirected to the Form Builder where you can add fields.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Create Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
