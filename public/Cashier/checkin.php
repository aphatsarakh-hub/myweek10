<?php
require_once __DIR__ . '/../includes/boot.php';

$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$staff_id = $_SESSION['user']['staff_id'] ?? null;

if (!$booking) {
    flash('message', 'ไม่พบรายการการจอง');
    header('Location: booking_list.php');
    exit;
}

// ข้อมูลจริงจาก DB ที่ต้องใช้ประกอบการเช็คอิน
$customer = !empty($booking['customer_id']) ? fetch_customer_by_id($booking['customer_id']) : null;
$cat = !empty($booking['cat_id']) ? fetch_cat_by_id($booking['cat_id']) : null;
$payments = fetch_payments_by_booking($booking_id);
$existingCheckin = fetch_checkin_by_booking($booking_id);

$hasCompletedPayment = false;
foreach ($payments as $p) {
    if (($p['status'] ?? '') === 'Completed') {
        $hasCompletedPayment = true;
        break;
    }
}

// เช็คอินได้เมื่อ: ยังไม่เคยเช็คอิน และการจองยังไม่ถูกยกเลิก
$canCheckin = !$existingCheckin && $booking['status'] !== 'Cancelled';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canCheckin) {
    $condition_note = trim($_POST['condition_note'] ?? '');
    $confirm_unpaid = isset($_POST['confirm_unpaid']);

    if ($condition_note === '') {
        $error = 'กรุณากรอกบันทึกสภาพน้องแมวก่อนยืนยันเช็คอิน';
    } elseif (!$hasCompletedPayment && !$confirm_unpaid) {
        $error = 'การจองนี้ยังไม่มีรายการชำระเงินสถานะ "Completed" กรุณาติ๊กยืนยันด้านล่างหากต้องการเช็คอินต่อ';
    } else {
        $result = perform_checkin($booking_id, $staff_id, $condition_note);
        if ($result['ok']) {
            flash('message', 'เช็คอินสำเร็จแล้ว');
            header('Location: booking_list.php');
            exit;
        }
        $error = $result['error'];
    }
}

$pageTitle = 'เช็คอิน (Cashier)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>เช็คอิน</h1>
      <p>ตรวจสอบข้อมูลลูกค้าและน้องแมว แล้วยืนยันการเช็คอินสำหรับการจองนี้</p>
    </div>
    <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" class="button button-secondary">กลับไปยังการจอง</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if ($booking['status'] === 'Cancelled'): ?>
    <div class="alert alert-error">การจองนี้ถูกยกเลิกไปแล้ว ไม่สามารถเช็คอินได้</div>
  <?php endif; ?>

  <?php if ($existingCheckin): ?>
    <div class="alert alert-success">
      เช็คอินไปแล้วเมื่อ <?php echo htmlspecialchars($existingCheckin['checkin_date']); ?>
      โดยพนักงาน
      <?php echo htmlspecialchars(trim(($existingCheckin['staff_first_name'] ?? '') . ' ' . ($existingCheckin['staff_last_name'] ?? '')) ?: 'ไม่ระบุ'); ?>
    </div>
  <?php endif; ?>

  <!-- ข้อมูลการจอง -->
  <div class="card">
    <h3>ข้อมูลการจอง</h3>
    <p><strong>รหัสการจอง:</strong> #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
    <p><strong>วันที่จอง:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
    <p><strong>ห้องพัก:</strong> <?php echo htmlspecialchars($booking['roomtype_name'] ?? '-'); ?></p>
    <p><strong>วันที่เข้าพัก:</strong> <?php echo htmlspecialchars($booking['start_date'] . ' ถึง ' . $booking['end_date']); ?></p>
    <p><strong>ราคารวม:</strong> <?php echo htmlspecialchars(number_format((float) $booking['total_price'], 2)); ?> บาท</p>
    <p>
      <strong>สถานะ:</strong>
      <span class="badge <?php echo $booking['status'] === 'Confirmed' || $booking['status'] === 'Checked In' ? 'badge-success' : ($booking['status'] === 'Pending' ? 'badge-warning' : 'badge-danger'); ?>">
        <?php echo htmlspecialchars($booking['status']); ?>
      </span>
    </p>
  </div>

  <!-- ข้อมูลลูกค้า -->
  <div class="card">
    <h3>ข้อมูลลูกค้า</h3>
    <?php if ($customer): ?>
      <p><strong>ชื่อ-นามสกุล:</strong> <?php echo htmlspecialchars(trim($customer['first_name'] . ' ' . $customer['last_name'])); ?></p>
      <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></p>
      <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($customer['email'] ?? '-'); ?></p>
      <p><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($customer['address'] ?? '-'); ?></p>
    <?php else: ?>
      <p>ไม่พบข้อมูลลูกค้าที่ผูกกับการจองนี้</p>
    <?php endif; ?>
  </div>

  <!-- ข้อมูลน้องแมว -->
  <div class="card">
    <h3>ข้อมูลน้องแมว</h3>
    <?php if ($cat): ?>
      <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($cat['name']); ?></p>
      <p><strong>สายพันธุ์:</strong> <?php echo htmlspecialchars($cat['breed'] ?? '-'); ?></p>
      <p><strong>อายุ:</strong> <?php echo htmlspecialchars($cat['age'] !== null ? $cat['age'] . ' ปี' : '-'); ?></p>
      <p><strong>เพศ:</strong> <?php echo htmlspecialchars($cat['gender'] ?? '-'); ?></p>
      <p><strong>ประวัติสุขภาพ:</strong> <?php echo nl2br(htmlspecialchars($cat['medical_history'] ?? '-')); ?></p>
    <?php else: ?>
      <p>การจองนี้ไม่ได้ระบุข้อมูลแมวไว้</p>
    <?php endif; ?>
  </div>

  <!-- ประวัติการชำระเงิน -->
  <div class="card table-responsive">
    <h3>ประวัติการชำระเงิน</h3>
    <?php if (empty($payments)): ?>
      <p>ยังไม่มีรายการชำระเงินสำหรับการจองนี้</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>วันที่</th>
            <th>จำนวนเงิน</th>
            <th>ช่องทาง</th>
            <th>สถานะ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['payment_date']); ?></td>
              <td><?php echo htmlspecialchars(number_format((float) $p['amount'], 2)); ?></td>
              <td><?php echo htmlspecialchars($p['payment_method'] ?? '-'); ?></td>
              <td>
                <span class="badge <?php echo $p['status'] === 'Completed' ? 'badge-success' : 'badge-warning'; ?>">
                  <?php echo htmlspecialchars($p['status']); ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- ฟอร์มยืนยันเช็คอิน -->
  <?php if ($canCheckin): ?>
    <div class="card">
      <h3>ยืนยันเช็คอิน</h3>
      <form method="post">
        <label>บันทึกสภาพน้องแมว (ก่อนรับฝาก)
          <textarea name="condition_note" rows="4" style="width:100%;" required
            placeholder="เช่น สุขภาพแข็งแรงดี, มีบาดแผลเล็กน้อยที่ขาหน้า, ทานอาหารปกติ..."><?php echo htmlspecialchars($_POST['condition_note'] ?? ''); ?></textarea>
        </label>

        <?php if (!$hasCompletedPayment): ?>
          <p>
            <label>
              <input type="checkbox" name="confirm_unpaid" value="1" <?php echo isset($_POST['confirm_unpaid']) ? 'checked' : ''; ?>>
              ยืนยันเช็คอินโดยที่ยังไม่มีการชำระเงินสถานะ "Completed"
            </label>
          </p>
        <?php endif; ?>

        <div class="button-group">
          <button type="submit" class="button button-primary">ยืนยันเช็คอิน</button>
          <a href="booking_list.php" class="button button-secondary">ยกเลิก</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>