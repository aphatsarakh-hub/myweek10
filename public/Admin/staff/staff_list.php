<?php
require_once __DIR__ . '/../../includes/boot.php';

// --- ตั้งค่าการเชื่อมต่อฐานข้อมูล ---
try {
    $db_host = 'localhost';
    $db_name = 'bambam_cat_hotel';
    $db_user = 'root';
    $db_pass = '';

    $local_conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $local_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงข้อมูลพนักงานจากฐานข้อมูลจริง
    // JOIN กับตาราง account เพื่อดึงรูปโปรไฟล์ล่าสุดที่พนักงานอัปโหลดเอง (account.profile_image)
    // ให้เป็นแหล่งข้อมูลหลักของรูป แทนที่จะใช้แค่ staff.image ที่อาจไม่ได้อัปเดตตาม
    $stmt = $local_conn->prepare("
        SELECT s.*, a.profile_image AS account_avatar
        FROM staff s
        LEFT JOIN account a ON a.staff_id = s.staff_id
        ORDER BY s.staff_id DESC
    ");
    $stmt->execute();
    $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // หากเชื่อมต่อหรือดึงข้อมูลไม่ได้ ให้คืนค่าเป็นอาร์เรย์ว่างเพื่อไม่ให้เว็บพัง
    $staffs = [];
}
// -------------------------------------------------------------------------

$pageTitle = 'จัดการข้อมูลพนักงาน';
$showPrimaryAction = false;
include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        /* สีพื้นหลังครีมอมขาว สบายตา เข้ากับโทนอบอุ่น */
        background-color: #FDFBF7; 
        font-family: 'Noto Sans Thai', sans-serif;
        color: #3D2C1D;
    }

    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* --- ส่วนหัว Header --- */
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    /* ปุ่มเพิ่มพนักงานสีส้ม-น้ำตาล */
    .btn-add-staff {
        background: linear-gradient(135deg, #E86A33 0%, #D9531E 100%);
        color: #FFFFFF;
        padding: 14px 28px;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 6px 16px rgba(232, 106, 51, 0.25);
        border: none;
        cursor: pointer;
    }

    .btn-add-staff:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(232, 106, 51, 0.35);
        color: #FFFFFF;
        background: linear-gradient(135deg, #F07843 0%, #E86A33 100%);
    }

    /* --- Grid Layout พนักงาน --- */
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 28px;
    }

    /* --- Staff Profile Card (การ์ดพนักงาน) --- */
    .staff-card {
        background: #FFFFFF;
        border-radius: 24px;
        padding: 28px 24px;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border: 1px solid #F5EBE6;
        box-shadow: 0 10px 25px rgba(61, 44, 29, 0.04);
        transition: all 0.3s ease;
    }

    .staff-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(61, 44, 29, 0.08);
        border-color: #E86A33;
    }

    /* สถานะมุมขวาบน */
    .status-dot {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .status-active { background-color: #E8F5E9; color: #2E7D32; }
    .status-inactive { background-color: #FEECEB; color: #C5221F; }

    /* รูปโปรไฟล์วงกลม */
    .avatar-box {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        padding: 4px;
        background: linear-gradient(135deg, #FFF1E6 0%, #E86A33 100%);
        margin-bottom: 16px;
        position: relative;
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #FFFFFF;
    }

    .staff-name {
        font-size: 18px;
        font-weight: 700;
        color: #3D2C1D;
        margin: 0 0 8px 0;
    }

    /* ป้ายตำแหน่งงาน (Badge โทนส้ม-น้ำตาล) */
    .position-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 999px;
        margin-bottom: 20px;
        display: inline-block;
    }
    .badge-orange { background-color: #FFF1E6; color: #E86A33; border: 1px solid #FCE5D3; }
    .badge-brown { background-color: #F5EBE6; color: #5C4033; border: 1px solid #EADBCE; }
    .badge-gold { background-color: #FFF9E6; color: #B45309; border: 1px solid #FEEBC8; }

    /* ข้อมูลติดต่อ (เบอร์โทร / อีเมล) */
    .contact-info {
        width: 100%;
        background: #FDFBF7;
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 24px;
        border: 1px solid #F5EBE6;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #6B5B52;
        text-decoration: none;
        transition: color 0.2s;
    }

    .contact-row:hover {
        color: #E86A33;
    }

    .contact-row i {
        color: #E86A33;
        width: 16px;
    }

    /* กลุ่มปุ่มจัดการด้านล่าง */
    .action-group {
        display: flex;
        gap: 12px;
        width: 100%;
        justify-content: center;
        border-top: 1px dashed #EADBCE;
        padding-top: 20px;
    }

    .btn-action {
        flex: 1;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-action:active {
        transform: scale(0.96);
    }

    /* ปุ่มแก้ไข (สีน้ำตาลอ่อน / ส้ม) */
    .btn-edit { background-color: #FFF1E6; color: #E86A33; border: 1px solid #FCE5D3; }
    .btn-edit:hover { background-color: #E86A33; color: #FFFFFF; box-shadow: 0 4px 12px rgba(232, 106, 51, 0.25); }

    /* ปุ่มลบ (สีแดงพาสเทล) */
    .btn-del { background-color: #FEECEB; color: #C5221F; border: 1px solid #FAD2CF; max-width: 45px; }
    .btn-del:hover { background-color: #C5221F; color: #FFFFFF; box-shadow: 0 4px 12px rgba(197, 34, 31, 0.25); }

    /* --- หน้าจอเวลาไม่มีข้อมูล (Empty State) --- */
    .empty-state-box {
        grid-column: 1 / -1;
        background: #FFFFFF;
        border-radius: 24px;
        padding: 60px 20px;
        text-align: center;
        border: 2px dashed #EADBCE;
        margin-top: 10px;
    }
    .empty-state-box i {
        font-size: 56px;
        color: #EADBCE;
        margin-bottom: 16px;
    }
</style>

<div class="admin-container">
    
    <div class="header-section">
        <p class="page-subtitle" style="color:#8C7A6B;font-size:14px;margin:0;">รายชื่อพนักงาน ผู้ดูแลแมว และสัตวแพทย์ทั้งหมด (ข้อมูลจริง)</p>
        <div class="flex items-center gap-3">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" data-admin-search-input data-admin-search=".staff-card" placeholder="ค้นหาพนักงาน..." class="pl-11 pr-4 py-2 w-64 bg-white border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none shadow-sm">
            </div>
            <a href="staff_add.php" class="btn-add-staff">
                <i class="fas fa-plus"></i> เพิ่มพนักงานใหม่
            </a>
        </div>
    </div>

    <div class="staff-grid">
        <?php if (empty($staffs)): ?>
            
            <div class="empty-state-box">
                <i class="fas fa-user-clock"></i>
                <h3 style="font-size: 18px; font-weight: 700; color: #3D2C1D; margin: 0 0 8px 0;">ยังไม่มีข้อมูลพนักงานในระบบ</h3>
                <p style="color: #8C7A6B; font-size: 14px; margin-bottom: 24px;">เริ่มต้นเพิ่มรายชื่อพนักงาน ผู้ดูแล หรือสัตวแพทย์คนแรกของคุณได้เลยครับ</p>
                <a href="staff_add.php" class="btn-add-staff">
                    <i class="fas fa-plus"></i> เพิ่มพนักงานคนแรก
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($staffs as $index => $s): 
                // 1. รวมชื่อและนามสกุลจาก Database จริง
                $fullname = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                if (empty($fullname)) {
                    $fullname = 'ไม่ระบุชื่อ';
                }

                // 2. จัดการสถานะการทำงาน (รองรับคำว่า 'ทำงาน' จาก DB จริงของคุณ)
                $statusVal = $s['status'] ?? '';
                $isActive = ($statusVal === 'ทำงาน' || $statusVal === 'active');
                $statusText = $isActive ? 'ปฏิบัติงาน' : 'พักงาน';
                $statusClass = $isActive ? 'status-active' : 'status-inactive';

                // 3. จัดการสีป้ายตำแหน่งอัตโนมัติตามข้อความตำแหน่งงาน
                $badgeType = 'badge-brown';
                $pos = $s['position'] ?? '';
                if (strpos($pos, 'ผู้จัดการ') !== false || strpos($pos, 'Admin') !== false || strpos($pos, 'เจ้าของ') !== false || strpos($pos, 'เก็บเงิน') !== false) {
                    $badgeType = 'badge-orange';
                } elseif (strpos($pos, 'สัตวแพทย์') !== false || strpos($pos, 'Vet') !== false || strpos($pos, 'แพทย์') !== false) {
                    $badgeType = 'badge-gold';
                }

                // 4. รูปโปรไฟล์พนักงาน
                // ลำดับความสำคัญ: รูปที่พนักงานอัปโหลดเองผ่านหน้าโปรไฟล์ (account.profile_image)
                // -> รูปที่แอดมินตั้งไว้ตอนเพิ่ม/แก้ไขพนักงาน (staff.image)
                // -> รูป fallback แบบสุ่ม กรณีไม่มีรูปเลย
                $fallbackAvatars = [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&auto=format&fit=crop&q=80'
                ];
                // account.profile_image เก็บ path แบบ relative จาก root เว็บ (มี "/" นำหน้า)
                // จึงตัด "/" นำหน้าออกก่อน แล้วเติม "../../" ให้ตรงกับตำแหน่งไฟล์นี้ (Admin/staff/)
                // (staff.image ถูกเลิกใช้และลบออกจาก DB แล้ว จึงไม่มี fallback ไปคอลัมน์นั้นอีกต่อไป)
                if (!empty($s['account_avatar'])) {
                    $avatarSrc = '../../' . ltrim($s['account_avatar'], '/');
                } else {
                    $avatarSrc = $fallbackAvatars[$index % count($fallbackAvatars)];
                }
            ?>
            
            <div class="staff-card admin-search-item" data-searchable="<?= htmlspecialchars($fullname . ' ' . ($s['position'] ?? '') . ' ' . ($s['phone'] ?? '') . ' ' . ($s['email'] ?? '')) ?>">
                
                <span class="status-dot <?= $statusClass ?>">
                    <i class="fas fa-circle" style="font-size: 8px;"></i> <?= $statusText ?>
                </span>

                <div class="avatar-box">
                    <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="<?= htmlspecialchars($fullname) ?>"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400&auto=format&fit=crop&q=80';">
                </div>

                <h3 class="staff-name"><?= htmlspecialchars($fullname) ?></h3>
                <span class="position-badge <?= $badgeType ?>"><?= htmlspecialchars($s['position'] ?? 'พนักงานทั่วไป') ?></span>

                <div class="contact-info">
                    <a href="tel:<?= htmlspecialchars($s['phone'] ?? '-') ?>" class="contact-row">
                        <i class="fas fa-phone-alt"></i>
                        <span><?= htmlspecialchars(!empty($s['phone']) ? $s['phone'] : 'ไม่ระบุเบอร์โทร') ?></span>
                    </a>
                    <a href="mailto:<?= htmlspecialchars($s['email'] ?? '-') ?>" class="contact-row">
                        <i class="fas fa-envelope"></i>
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 220px;">
                            <?= htmlspecialchars(!empty($s['email']) ? $s['email'] : 'ไม่ระบุอีเมล') ?>
                        </span>
                    </a>
                </div>

                <div class="action-group">
                    <a href="staff_edit.php?id=<?= $s['staff_id'] ?>" class="btn-action btn-edit">
                        <i class="fas fa-pen"></i> แก้ไขข้อมูล
                    </a>
                    <a href="staff_delete.php?id=<?= $s['staff_id'] ?>" onclick="return confirm('ยืนยันที่จะลบข้อมูลของ <?= htmlspecialchars($fullname) ?> หรือไม่?');" class="btn-action btn-del" title="ลบพนักงาน">
                        <i class="far fa-trash-can"></i>
                    </a>
                </div>

            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>