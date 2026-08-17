<?php
require_once __DIR__ . '/includes/boot.php';

if (!empty($_SESSION['user'])) {
    unset($_SESSION['user']);
}
flash('message', 'ออกจากระบบเรียบร้อยแล้ว');
header('Location: ' . url('index.php'));
exit;
