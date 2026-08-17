<?php
require_once __DIR__ . '/../includes/boot.php';

$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$customer_id = $_SESSION['user']['customer_id'] ?? null;

if (!$booking || $booking['customer_id'] !== $customer_id) {
    flash('message', 'ไม่พบข้อมูลการจอง');
    header('Location: booking.php');
    exit;
}

$error = '';
$success = '';

// คำนวณยอดมัดจำ 50%
$total_price = floatval($booking['total_price']);
$deposit_amount = $total_price * 0.50;

// ประมวลผลการอัปโหลดสลีปเมื่อลูกค้ากดปุ่ม Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slip_image'])) {
    $file = $_FILES['slip_image'];
    
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'กรุณาเลือกไฟล์สลีปก่อนกดส่ง';
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'รองรับเฉพาะไฟล์รูปภาพ JPG, JPEG และ PNG เท่านั้น';
        } else {
            // สร้างโฟลเดอร์สำหรับเก็บสลีป (ถ้ายังไม่มี)
            $upload_dir = __DIR__ . '/../uploads/slips/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'slip_' . $booking_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // บันทึกชื่อไฟล์ลงฐานข้อมูลในตาราง booking โดยตรง
                global $mysqli;
                $stmt = $mysqli->prepare("UPDATE booking SET slip_image = ? WHERE booking_id = ?");
                $stmt->bind_param('si', $new_filename, $booking_id);
                if ($stmt->execute()) {
                    $success = 'อัปโหลดหลักฐานการโอนเงินสำเร็จเรียบร้อย! เจ้าหน้าที่จะตรวจสอบใบจองของคุณโดยเร็ว';
                    // รีเฟรชข้อมูลหลังบันทึกสำเร็จ
                    $booking = fetch_booking_by_id($booking_id);
                } else {
                    $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลลงระบบ';
                }
                $stmt->close();
            } else {
                $error = 'ไม่สามารถอัปโหลดไฟล์ไปยังเซิร์ฟเวอร์ได้';
            }
        }
    }
}

$pageTitle = 'ยืนยันการจอง & ชำระเงิน (Customer)';
include __DIR__ . '/../includes/header.php';
?>

  <div class="page-title">
    <div>
      <h1>ยืนยันการจอง & ชำระเงินมัดจำ</h1>
      <p>การจองของคุณถูกบันทึกแล้ว กรุณาโอนเงินมัดจำเพื่อยืนยันสิทธิ์</p>
    </div>
  </div>

  <div class="card" style="margin-bottom: 20px;">
    <?php if ($error) renderAlert($error, 'error'); ?>
    <?php if ($success) renderAlert($success, 'success'); ?>

    <p>ขอบคุณที่จองกับ BamBam Cat Hotel!</p>
    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2B6777;">
        <p style="margin: 4px 0;"><strong>หมายเลขการจอง:</strong> #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
        <p style="margin: 4px 0;"><strong>ชื่อแมว:</strong> <?php echo htmlspecialchars($booking['cat_name']); ?></p>
        <p style="margin: 4px 0;"><strong>ประเภทห้องพัก:</strong> <?php echo htmlspecialchars($booking['roomtype_name']); ?></p>
        <p style="margin: 4px 0;"><strong>วันที่เข้าพัก:</strong> <?php echo htmlspecialchars($booking['start_date'] . ' ถึง ' . $booking['end_date']); ?></p>
        <p style="margin: 4px 0; color: #6C7C80;"><strong>ราคารวมทั้งหมด:</strong> <?php echo htmlspecialchars(number_format($total_price, 2)); ?> บาท</p>
        <p style="margin: 4px 0; font-size: 1.2rem; color: #C8862A;"><strong>ยอดมัดจำที่ต้องโอน (50%):</strong> <strong><?php echo htmlspecialchars(number_format($deposit_amount, 2)); ?> บาท</strong></p>
    </div>

    <?php if (empty($booking['slip_image'])): ?>
    <div style="background: #FFF9E6; border: 1px solid #FFEAA7; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        <h3 style="margin-top: 0; color: #C8862A;">🏦 ช่องทางการชำระเงิน</h3>
        <p style="margin: 5px 0;">ธนาคารกสิกรไทย (KBank)</p>
        <p style="margin: 5px 0; font-size: 1.3rem; font-weight: bold; color: #1E2A2E;">123-4-56789-0</p>
        <p style="margin: 5px 0; font-size: 0.9rem; color: #6C7C80;">ชื่อบัญชี: บจก. แบมแบม แคท โฮเทล</p>
    </div>

    <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold; margin-bottom: 8px;">แนบรูปภาพสลีปการโอนเงิน:</label>
            <input type="file" name="slip_image" accept="image/*" required style="display: block; width: 100%; padding: 10px; border: 1px dashed #6C7C80; border-radius: 6px;">
        </div>
        <button type="submit" class="button button-primary" style="width: 100%; padding: 12px; font-size: 1rem;">ส่งหลักฐานการมัดจำ</button>
    </form>
    <?php else: ?>
        <div style="background: #E7F5EC; color: #2E9E5B; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 15px;">
            ✓ คุณได้ส่งหลักฐานการโอนเงินแล้ว สถานะ: รอเจ้าหน้าที่ตรวจสอบสลีป
        </div>
        <div style="text-align: center;">
            <p style="font-size: 0.85rem; color: var(--cb-text-muted);">รูปภาพสลีปของคุณในระบบ:</p>
            <img src="../uploads/slips/<?php echo htmlspecialchars($booking['slip_image']); ?>" alt="Slip" style="max-width: 200px; border: 1px solid #ddd; border-radius: 6px; padding: 4px;">
        </div>
    <?php endif; ?>

    <div style="margin-top: 20px; text-align: center;">
        <a href="booking_history.php" class="button button-secondary">ดูประวัติการจองทั้งหมด</a>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>