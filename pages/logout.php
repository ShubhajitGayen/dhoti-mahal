<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
setFlash('info', 'You have been logged out successfully.');
redirect(BASE_URL . 'pages/login.php');
