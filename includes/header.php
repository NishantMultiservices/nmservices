<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>public/css/style.css">
    <?php if(isset($additional_css)) echo $additional_css; ?>
</head>
<body>
    <?php if(isset($_SESSION['logged_in'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>dashboard.php">
                <i class="bi bi-shop"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-briefcase"></i> Modules
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/income.php">
                                <i class="bi bi-cash-in"></i> Income
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/expense.php">
                                <i class="bi bi-cash-out"></i> Expense
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/forms.php">
                                <i class="bi bi-file-text"></i> Custom Forms
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a></li>
                        </ul>
                    </li>
                    <?php if(hasRole('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>pages/users.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo sanitize($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/profile.php">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>pages/change-password.php">
                                <i class="bi bi-lock"></i> Change Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <div class="<?php echo isset($_SESSION['logged_in']) ? 'content-wrapper' : ''; ?>">
