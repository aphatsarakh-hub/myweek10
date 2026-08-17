<?php
$cashierLinks = [
    'Cashier/booking_list.php' => 'รายการจอง',
    'Cashier/service_list.php' => 'รายการบริการ',
    'Cashier/payment_history.php' => 'ประวัติการชำระเงิน',
    'Cashier/profile.php' => 'โปรไฟล์',
];
?>
<aside style="width:220px;padding:16px;background:#f4f4f4;">
  <h3>เมนูพนักงานเก็บเงิน</h3>
  <ul style="list-style:none;padding:0;">
    <?php foreach ($cashierLinks as $url => $label): ?>
      <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
    <?php endforeach; ?>
  </ul>
</aside>
