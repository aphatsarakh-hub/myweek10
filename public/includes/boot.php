<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// กันไว้เผื่อ config.php ยังไม่ได้กำหนดค่า BASE_URL
// (functions.php ใช้ค่านี้ในฟังก์ชัน url() — ถ้าไม่ define ไว้ก่อน จะ Fatal error: Undefined constant "BASE_URL")
// เปลี่ยน '/BamBam_Cat_Hotel' ให้ตรงกับชื่อ Folder ของโปรเจกต์คุณบน XAMPP
if (!defined('BASE_URL')) {
    define('BASE_URL', '/BamBam_Cat_Hotel');
}

require_once __DIR__ . '/functions.php';