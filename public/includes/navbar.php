<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

$current = basename($_SERVER['PHP_SELF']);

$nav = [
    url('index.php') => 'หน้าแรก',
    url('about.php') => 'เกี่ยวกับ',
    url('room.php') => 'ห้องพัก',
    url('service.php') => 'บริการเสริม',
    url('contact.php') => 'ติดต่อ',
];

// Role specific links
if (!empty($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        $nav[url('Admin/rooms/room_list.php')] = 'จัดการห้อง';
        $nav[url('Admin/customers/customer_list.php')] = 'จัดการลูกค้า';
        $nav[url('Admin/services/service_list.php')] = 'จัดการบริการ';
        $nav[url('Admin/payments/payment_list.php')] = 'จัดการชำระเงิน';
        $nav[url('Admin/bookings/booking_list.php')] = 'การจอง (Admin)';
        $nav[url('Admin/reports/report_booking.php')] = 'รายงานการจอง';
        $nav[url('Admin/reports/report_income.php')] = 'รายงานรายได้';
        $nav[url('Admin/staff/staff_list.php')] = 'จัดการพนักงาน';
        $profileLink = url('Admin/profile/profile.php');
    } elseif ($role === 'cashier') {
      // Cashier should manage bookings; quick access to check-in list
      $nav[url('Cashier/booking_list.php')] = 'รายการจอง (พนักงาน)';
      $nav[url('Cashier/quick_checkin.php')] = 'เช็คอิน';
      $nav[url('Cashier/quick_checkout.php')] = 'เช็คอินเอาท์';
      $nav[url('Cashier/payment_history.php')] = 'ประวัติการชำระเงิน';
      $profileLink = url('Cashier/profile.php');
    } else { // customer
        $nav[url('Customer/booking.php')] = 'จองห้อง';
        $nav[url('Customer/booking_history.php')] = 'ประวัติการจอง';
        $nav[url('Customer/cat_list.php')] = 'ข้อมูลแมว';
        $nav[url('Customer/payment_history.php')] = 'ประวัติการชำระเงิน';
        $profileLink = url('Customer/profile.php');
    }
}

?>
<nav class="nav-links">
  <?php foreach ($nav as $href => $label):
    $isActive = strpos($href, $current) !== false ? ' style="font-weight:bold;"' : '';
  ?>
    <a href="<?php echo $href; ?>"<?php echo $isActive; ?>><?php echo $label; ?></a>
  <?php endforeach; ?>

  <?php if (!empty($_SESSION['user'])): ?>
    <span style="margin-left:12px;color:#fff;">สวัสดี, <?php echo htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8'); ?></span>
    <a href="<?php echo isset($profileLink) ? $profileLink : url('profile.php'); ?>">โปรไฟล์</a>
    <a href="<?php echo url('logout.php'); ?>">ออกจากระบบ</a>
  <?php else: ?>
    <a href="<?php echo url('login.php'); ?>">เข้าสู่ระบบ</a>
  <?php endif; ?>
</nav>
