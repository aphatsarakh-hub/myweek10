<?php
require_once __DIR__ . '/../../includes/boot.php';

// ดึงข้อมูล ID จาก URL
$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);
$customers = fetch_customers();
$cats = fetch_cats();
$roomtypes = fetch_roomtypes();

// ตรวจสอบว่ามีข้อมูลการจองหรือไม่
if (!$booking) {
    flash('message', 'ไม่พบรายการการจอง');
    header('Location: booking_list.php');
    exit;
}

$error = '';
$success = false;

// จัดการเมื่อมีการกดปุ่ม SAVE CHANGES (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $cat_id = intval($_POST['cat_id'] ?? 0);
    $roomtype_id = intval($_POST['roomtype_id'] ?? 0);
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');
    $room = fetch_roomtype_by_id($roomtype_id);
    
    $total_price = $room ? calculate_booking_total($start_date, $end_date, $room['price_per_night']) : 0;

    if (!$customer_id || !$cat_id || !$roomtype_id || !$start_date || !$end_date || !$room) {
        $error = 'Please fill in all required fields (กรุณากรอกข้อมูลให้ครบถ้วน)';
    } else {
        if (update_booking($booking_id, $customer_id, $cat_id, $roomtype_id, $start_date, $end_date, $total_price, $status)) {
            $success = true;
            // อัปเดตตัวแปร $booking เพื่อให้ฟอร์มแสดงค่าใหม่ที่เพิ่งบันทึกไป ทันทีโดยไม่ต้องโหลดหน้าใหม่
            $booking['customer_id'] = $customer_id;
            $booking['cat_id'] = $cat_id;
            $booking['roomtype_id'] = $roomtype_id;
            $booking['start_date'] = $start_date;
            $booking['end_date'] = $end_date;
            $booking['status'] = $status;
        } else {
            $error = 'Unable to save changes (ไม่สามารถบันทึกการเปลี่ยนแปลงได้)';
        }
    }
}

$pageTitle = 'Booking Edit - Management';
include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    /* โทนสีและสไตล์ Minimal Luxury */
    :root {
        --color-brown: #5C4B44;      /* น้ำตาลเข้มลึก (ไม่ดำ) */
        --color-accent: #D97706;     /* ส้มอิฐ (Amber Accent) */
        --color-light: #FDFBF8;      /* ขาวครีมสว่าง */
        --color-white: #FFFFFF;      /* ขาวบริสุทธิ์ */
        --color-border: #DED6D0;     /* น้ำตาลเทาอ่อนสำหรับเส้น */
        --color-error: #991B1B;
    }

    body {
        font-family: 'Prompt', sans-serif;
        background-color: var(--color-light);
        color: var(--color-brown);
    }

    .booking-wrapper {
        max-width: 700px;
        margin: 60px auto;
        padding: 40px 50px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        box-shadow: 0 15px 35px rgba(92, 75, 68, 0.03);
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        margin-bottom: 5px;
        color: var(--color-brown);
        letter-spacing: 1px;
    }

    .page-header p.subtitle {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--color-accent);
        margin: 0;
    }

    /* ดีไซน์ปุ่มกลับแบบ Luxury Minimal (ขยับนุ่มนวลเมื่อ Hover) */
    .btn-back {
        display: inline-flex;
        align-items: center;
        margin-bottom: 35px;
        color: var(--color-brown);
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 2px;
        position: relative;
        padding-bottom: 4px;
        transition: color 0.3s ease;
    }

    .btn-back::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background-color: var(--color-accent);
        transition: width 0.3s ease;
    }

    .btn-back svg {
        margin-right: 8px;
        transition: transform 0.3s ease;
        fill: var(--color-brown);
    }

    .btn-back:hover {
        color: var(--color-accent);
    }

    .btn-back:hover svg {
        transform: translateX(-4px); /* ลูกศรเลื่อนถอยหลังเบาๆ */
        fill: var(--color-accent);
    }

    .btn-back:hover::after {
        width: 100%; /* เส้นใต้ขยายออก */
    }

    .form-group {
        margin-bottom: 30px;
    }

    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 8px;
        color: #A18D83;
    }

    .form-control {
        width: 100%;
        padding: 12px 0;
        border: none;
        border-bottom: 1px solid var(--color-border);
        background: transparent;
        font-size: 1rem;
        color: var(--color-brown);
        font-family: 'Prompt', sans-serif;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-bottom: 1px solid var(--color-accent);
    }

    /* ซ่อนลูกศร select ปกติ และใส่ Custom Elegant Arrow */
    select.form-control {
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg fill="%235C4B44" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
        background-repeat: no-repeat;
        background-position: right 0px center;
        cursor: pointer;
    }

    .btn-save {
        width: 100%;
        padding: 18px;
        background: var(--color-brown);
        color: #fff;
        border: none;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-weight: 600;
        font-family: 'Prompt', sans-serif;
        cursor: pointer;
        transition: background 0.4s ease;
        margin-top: 10px;
    }

    .btn-save:hover {
        background: var(--color-accent);
    }

    .alert-error {
        padding: 15px;
        border-left: 4px solid var(--color-error);
        background: #FEF2F2;
        color: var(--color-error);
        margin-bottom: 25px;
        font-size: 0.9rem;
    }

    /* สไตล์ของ Pop-up แจ้งเตือน (Toast Notification) */
    #toast {
        visibility: hidden;
        min-width: 300px;
        background-color: var(--color-brown);
        color: #fff;
        text-align: center;
        border-radius: 4px;
        padding: 16px;
        position: fixed;
        z-index: 1000;
        left: 50%;
        bottom: 40px;
        transform: translateX(-50%);
        font-size: 1rem;
        letter-spacing: 1px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s, visibility 0.5s;
    }

    #toast.show {
        visibility: visible;
        opacity: 1;
        bottom: 50px;
    }
</style>

<div class="booking-wrapper">
    <a href="booking_list.php" class="btn-back">
        <svg width="14" height="14" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
        </svg>
        Back to Overview
    </a>
    
    <div class="page-header">
        <h2>Booking Edit</h2>
        <p class="subtitle">ปรับปรุงข้อมูลการจอง</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" id="bookingForm">
        <div class="form-group">
            <label class="form-label">Customer Name</label>
            <select name="customer_id" class="form-control" required>
                <option value="">-- Please Select --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?php echo $c['customer_id']; ?>" <?php echo (($booking['customer_id'] ?? '') == $c['customer_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Selected Cat</label>
            <select name="cat_id" class="form-control" required>
                <option value="">-- Please Select --</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?php echo $c['cat_id']; ?>" <?php echo (($booking['cat_id'] ?? '') == $c['cat_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['breed'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Room Type</label>
            <select name="roomtype_id" class="form-control" required>
                <option value="">-- Please Select --</option>
                <?php foreach ($roomtypes as $r): ?>
                    <option value="<?php echo $r['roomtype_id']; ?>" <?php echo (($booking['roomtype_id'] ?? '') == $r['roomtype_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($r['type_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="form-group">
                <label class="form-label">Check-In Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($booking['start_date'] ?? ''); ?>" required />
            </div>
            <div class="form-group">
                <label class="form-label">Check-Out Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($booking['end_date'] ?? ''); ?>" required />
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Booking Status</label>
            <select name="status" class="form-control">
                <option value="Pending" <?php echo (($booking['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending (รอการยืนยัน)</option>
                <option value="Confirmed" <?php echo (($booking['status'] ?? '') === 'Confirmed') ? 'selected' : ''; ?>>Confirmed (ยืนยันแล้ว)</option>
                <option value="Cancelled" <?php echo (($booking['status'] ?? '') === 'Cancelled') ? 'selected' : ''; ?>>Cancelled (ยกเลิก)</option>
            </select>
        </div>

        <button type="submit" class="btn-save">Save Changes</button>
    </form>
</div>

<div id="toast">บันทึกข้อมูลการจองเรียบร้อยแล้ว ✨</div>

<script>
    // เรียกทำงาน Pop-up เมื่อฝั่ง Server-side ประมวลผลว่าการอัปเดตสำเร็จ ($success = true)
    <?php if ($success): ?>
        document.addEventListener("DOMContentLoaded", function() {
            var toast = document.getElementById("toast");
            toast.className = "show";
            
            // ซ่อน Pop-up อัตโนมัติหลังจากแสดงผลไป 3.5 วินาที
            setTimeout(function(){ 
                toast.className = toast.className.replace("show", ""); 
            }, 3500);
        });
    <?php endif; ?>
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>