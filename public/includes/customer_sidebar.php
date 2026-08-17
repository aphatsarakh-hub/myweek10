<?php
$customerLinks = [
    'Customer/register.php' => 'สมัครสมาชิก',
    'Customer/profile.php' => 'โปรไฟล์',
    'Customer/booking.php' => 'จองห้อง',
    'Customer/payment_history.php' => 'ประวัติการชำระเงิน',
    'Customer/booking_history.php' => 'ประวัติการจอง',
];
?>
<aside style="width:220px;padding:16px;background:#f4f4f4;">
  <h3>เมนูลูกค้า</h3>
  <ul style="list-style:none;padding:0;">
    <?php foreach ($customerLinks as $url => $label): ?>
      <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
    <?php endforeach; ?>
  </ul>
</aside>
