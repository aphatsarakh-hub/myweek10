<?php
$sidebarLinks = [
    'index.php' => 'หน้าแรก',
    'about.php' => 'เกี่ยวกับ',
    'room.php' => 'ห้องพัก',
    'service.php' => 'บริการเสริม',
    'contact.php' => 'ติดต่อ',
    'login.php' => 'เข้าสู่ระบบ'
];
?>
<aside style="width:220px;padding:16px;background:#f4f4f4;">
  <h3>เมนู</h3>
  <ul style="list-style:none;padding:0;">
    <?php foreach ($sidebarLinks as $url => $label): ?>
      <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
    <?php endforeach; ?>
  </ul>
</aside>
