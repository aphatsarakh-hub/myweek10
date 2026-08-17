<?php
// 1. เพิ่มฟังก์ชัน url() เพื่อแก้ปัญหา Call to undefined function url()
if (!function_exists('url')) {
    function url($path = '') {
        // อ้างอิงจาก Error (C:\xamppz\htdocs\BamBam_Cat_Hotel) 
        // Base URL ของคุณน่าจะเป็น /BamBam_Cat_Hotel/
        $baseUrl = '/BamBam_Cat_Hotel/'; 
        return $baseUrl . ltrim($path, '/');
    }
}

// 2. โค้ดเดิมของ header.php เริ่มต่อจากตรงนี้ครับ
if (!isset($pageTitle)) {
    $pageTitle = 'BamBam Cat Hotel';
}

$currentFile = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'guest';
// ... (โค้ดส่วนที่เหลือปล่อยไว้เหมือนเดิมได้เลยครับ) ...
$menuItems = [];
if (empty($user)) {
    $menuItems = [
        ['label' => 'หน้าแรก', 'href' => url('index.php'), 'icon' => 'fa-home', 'path' => 'index.php'],
        ['label' => 'เกี่ยวกับ', 'href' => url('about.php'), 'icon' => 'fa-info-circle', 'path' => 'about.php'],
        ['label' => 'ห้องพัก', 'href' => url('room.php'), 'icon' => 'fa-door-open', 'path' => 'room.php'],
        ['label' => 'บริการเสริม', 'href' => url('service.php'), 'icon' => 'fa-concierge-bell', 'path' => 'service.php'],
        ['label' => 'ติดต่อ', 'href' => url('contact.php'), 'icon' => 'fa-phone', 'path' => 'contact.php'],
        ['label' => 'เข้าสู่ระบบ', 'href' => url('login.php'), 'icon' => 'fa-sign-in-alt', 'path' => 'login.php'],
        ['label' => 'สมัครสมาชิก', 'href' => url('register.php'), 'icon' => 'fa-user-plus', 'path' => 'register.php'],
    ];
    $sidebarTitle = 'BamBam Hotel';
    $sidebarSubtitle = 'Customer & Staff Portal';
    $settingsLink = url('login.php');
    $settingsLabel = 'เข้าสู่ระบบ';
    $primaryActionLink = url('register.php');
    $primaryActionLabel = 'สมัครสมาชิก';
} elseif ($role === 'admin') {
    $menuItems = [
        ['label' => 'แดชบอร์ด', 'href' => url('Admin/dashboard.php'), 'icon' => 'fa-th-large', 'path' => 'dashboard.php'],
        ['label' => 'การจอง', 'href' => url('Admin/bookings/booking_list.php'), 'icon' => 'fa-calendar-alt', 'path' => 'booking_list.php'],
        ['label' => 'ลูกค้า', 'href' => url('Admin/customers/customer_list.php'), 'icon' => 'fa-users', 'path' => 'customer_list.php'],
        ['label' => 'น้องแมว', 'href' => url('Admin/cats/cat_list.php'), 'icon' => 'fa-users', 'path' => 'cat_list.php'],
        ['label' => 'ห้องพัก', 'href' => url('Admin/rooms/room_list.php'), 'icon' => 'fa-door-open', 'path' => 'room_list.php'],
        ['label' => 'บริการ', 'href' => url('Admin/services/service_list.php'), 'icon' => 'fa-concierge-bell', 'path' => 'service_list.php'],
        ['label' => 'ชำระเงิน', 'href' => url('Admin/payments/payment_list.php'), 'icon' => 'fa-money-bill-wave', 'path' => 'payment_list.php'],
        ['label' => 'ยกเลิก', 'href' => url('Admin/cancellations/cancel_list.php'), 'icon' => 'fa-calendar-times', 'path' => 'cancel_list.php'],
        ['label' => 'พนักงาน', 'href' => url('Admin/staff/staff_list.php'), 'icon' => 'fa-user-cog', 'path' => 'staff_list.php'],
    ];
    $sidebarTitle = 'BamBam Hotel';
    $sidebarSubtitle = 'Admin Dashboard';
    $settingsLink = url('Admin/profile/profile.php');
    $settingsLabel = 'โปรไฟล์';
    $primaryActionLink = url('Admin/reports/report_booking.php');
    $primaryActionLabel = 'รายงาน';
} elseif ($role === 'cashier') {
    $menuItems = [
        ['label' => 'รายการจอง', 'href' => url('Cashier/booking_list.php'), 'icon' => 'fa-calendar-alt', 'path' => 'booking_list.php'],
        ['label' => 'เช็คอิน', 'href' => url('Cashier/quick_checkin.php'), 'icon' => 'fa-sign-in-alt', 'path' => 'quick_checkin.php'],
        ['label' => 'เช็คเอาท์', 'href' => url('Cashier/quick_checkout.php'), 'icon' => 'fa-sign-out-alt', 'path' => 'quick_checkout.php'],
        ['label' => 'รับชำระเงิน', 'href' => url('Cashier/payment.php'), 'icon' => 'fa-money-bill-wave', 'path' => 'payment.php'],
        ['label' => 'ประวัติชำระเงินลูกค้า', 'href' => url('Cashier/payment_history.php'), 'icon' => 'fa-history', 'path' => 'payment_history.php'],
        ['label' => 'โปรไฟล์', 'href' => url('Cashier/profile.php'), 'icon' => 'fa-user-circle', 'path' => 'profile.php'],
    ];
    $sidebarTitle = 'BamBam Hotel';
    $sidebarSubtitle = 'Cashier Panel';
    $settingsLink = url('Cashier/profile.php');
    $settingsLabel = 'โปรไฟล์';
    $primaryActionLink = url('Cashier/booking_list.php');
    $primaryActionLabel = 'จัดการจอง';
} else {
    $menuItems = [
        ['label' => 'ข้อมูลแมว', 'href' => url('Customer/cat_list.php'), 'icon' => 'fa-paw', 'path' => 'cat_list.php'],
        ['label' => 'จองห้องพัก', 'href' => url('Customer/booking.php'), 'icon' => 'fa-calendar-check', 'path' => 'booking.php'],
        ['label' => 'บริการเสริม', 'href' => url('Customer/service.php'), 'icon' => 'fa-calendar-check', 'path' => 'service.php'],
        ['label' => 'ชำระเงินมัดจำ', 'href' => url('Customer/payment_history.php'), 'icon' => 'fa-calendar-check', 'path' => 'payment_history.php'],
        ['label' => 'ประวัติการจอง', 'href' => url('Customer/booking_history.php'), 'icon' => 'fa-history', 'path' => 'booking_history.php'],
        ['label' => 'ประวัติชำระเงิน', 'href' => url('Customer/payment_history.php'), 'icon' => 'fa-file-invoice-dollar', 'path' => 'payment_history.php'],
    ];
    $sidebarTitle = 'BamBam Hotel';
    $sidebarSubtitle = 'Customer Portal';
    $settingsLink = url('Customer/profile.php');
    $settingsLabel = 'โปรไฟล์';
    $primaryActionLink = url('Customer/booking.html');
    $primaryActionLabel = 'จองห้อง';
}

$activeLink = function ($path) use ($currentFile): bool {
    return basename($path) === $currentFile;
};
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | BamBam Cat Hotel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
    <?php if (($role ?? '') === 'admin'): ?>
    <script src="<?= htmlspecialchars(url('includes/admin_search.js')) ?>" defer></script>
    <?php endif; ?>
</head>
<body class="bg-white text-[#4A3B2E]">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar: flat white, hairline border, no shadow, no gradient -->
    <aside class="w-60 bg-white border-r border-[#EDE7DF] flex flex-col justify-between flex-shrink-0">
        <div>
            <div class="px-7 py-8 flex items-center space-x-3 border-b border-[#EDE7DF]">
                <i class="fas fa-paw text-lg text-[#C05800]"></i>
                <div>
                    <h1 class="text-base font-semibold text-[#2E2115] leading-tight"><?= htmlspecialchars($sidebarTitle) ?></h1>
                    <p class="text-[10px] text-[#B08968] tracking-wider mt-0.5"><?= htmlspecialchars($sidebarSubtitle) ?></p>
                </div>
            </div>

            <nav class="mt-6 px-3 space-y-0.5 text-sm">
                <?php foreach ($menuItems as $item): ?>
                    <?php $isActive = $activeLink($item['path']); ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="flex items-center space-x-3 pl-4 pr-4 py-2.5 rounded-md border-l-2 transition-colors duration-150 <?= $isActive ? 'border-[#C05800] bg-[#FBF3EC] text-[#C05800] font-medium' : 'border-transparent text-[#6B5B4B] hover:text-[#C05800] hover:bg-[#FBF3EC]/60' ?>">
                        <i class="w-4 fas <?= htmlspecialchars($item['icon']) ?> text-center text-[13px]"></i>
                        <span class="tracking-wide"><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="px-3 py-5 border-t border-[#EDE7DF] space-y-0.5 text-sm">
            <a href="<?= htmlspecialchars($settingsLink) ?>" class="flex items-center space-x-3 pl-4 pr-4 py-2.5 rounded-md text-[#6B5B4B] hover:text-[#2E2115] hover:bg-[#FAF8F5] transition-colors duration-150">
                <i class="w-4 fas fa-cog text-center text-[13px]"></i>
                <span class="tracking-wide"><?= htmlspecialchars($settingsLabel) ?></span>
            </a>
            <a href="<?= url('logout.php') ?>" class="flex items-center space-x-3 pl-4 pr-4 py-2.5 rounded-md text-[#6B5B4B] hover:text-[#B23B1E] hover:bg-[#FAF8F5] transition-colors duration-150">
                <i class="w-4 fas fa-sign-out-alt text-center text-[13px]"></i>
                <span class="tracking-wide">ออกจากระบบ</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-y-auto">

        <!-- Header: flat white, single hairline rule, no blur, no shadow -->
        <header class="sticky top-0 z-10 flex justify-between items-center bg-white px-10 py-6 border-b border-[#EDE7DF]">
            <h2 class="text-xl font-semibold text-[#2E2115] tracking-tight"><?= htmlspecialchars($pageTitle) ?></h2>

            <div class="flex items-center space-x-5">
                <?php if (!isset($showPrimaryAction) || $showPrimaryAction): ?>
                <a href="<?= htmlspecialchars($primaryActionLink) ?>" class="hidden sm:inline-flex items-center rounded-md bg-[#C05800] px-4 py-2 text-sm font-medium text-white hover:bg-[#9C4700] transition-colors duration-150">
                    <i class="fas fa-plus mr-2 text-xs"></i> <?= htmlspecialchars($primaryActionLabel) ?>
                </a>
                <?php endif; ?>

                <div class="flex items-center space-x-3 pl-4 border-l border-[#EDE7DF]">
                    <div class="w-8 h-8 rounded-full border border-[#EDE0D2] flex items-center justify-center text-[#C05800] font-medium text-xs">
                        <?= htmlspecialchars(strtoupper(substr($user['name'] ?? $user['username'] ?? 'U', 0, 1))) ?>
                    </div>
                    <div class="text-left leading-tight hidden sm:block">
                        <p class="text-sm font-medium text-[#2E2115]"><?= htmlspecialchars($user['name'] ?? $user['username'] ?? 'ผู้ใช้') ?></p>
                        <p class="text-[10px] text-[#B08968] uppercase tracking-widest mt-0.5"><?= htmlspecialchars($role === 'admin' ? 'Admin' : ($role === 'cashier' ? 'Cashier' : 'Customer')) ?></p>
                    </div>
                </div>
            </div>
        </header>

        <div class="pt-8 px-10 pb-10 max-w-[1600px] mx-auto w-full">