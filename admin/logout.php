<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
logoutAdmin();
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
