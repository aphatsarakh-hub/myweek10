<?php
$adminLinks = [
    'Admin/dashboard.php' => 'แดชบอร์ด',
    'Admin/cats/cat_list.php' => 'จัดการแมว',
    'Admin/customers/customer_list.php' => 'จัดการลูกค้า',
    'Admin/rooms/room_list.php' => 'จัดการห้อง',
    'Admin/staff/staff_list.php' => 'จัดการพนักงาน',
    'Admin/services/service_list.php' => 'จัดการบริการ',
    'Admin/bookings/booking_list.php' => 'จัดการการจอง',
    'Admin/payments/payment_list.php' => 'จัดการชำระเงิน',
];
?>
<aside style="width:220px;padding:16px;background:#f4f4f4;">
  <h3>เมนูผู้ดูแล</h3>
  <ul style="list-style:none;padding:0;">
    <?php foreach ($adminLinks as $url => $label): ?>
      <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
    <?php endforeach; ?>
  </ul>
</aside>
