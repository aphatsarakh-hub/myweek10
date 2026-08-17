<?php
require_once __DIR__ . '/includes/boot.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=missing_fields');
    exit;
}

$user = verify_account($username, $password);
if (!$user) {
    header('Location: login.php?error=invalid_credentials');
    exit;
}

$_SESSION['user'] = $user;

// Redirect users to role-appropriate dashboard
$redirect = url('index.php');
switch (($user['role'] ?? '')) {
    case 'admin':
        $redirect = url('Admin/dashboard.php');
        break;
    case 'cashier':
        $redirect = url('Cashier/booking_list.php');
        break;
    case 'customer':
        $redirect = url('Customer/booking.php?mode=booking');
        break;
}
header('Location: ' . $redirect);
exit;