<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/Auth.php';

Auth::logout();
redirect('index.php');
?>
