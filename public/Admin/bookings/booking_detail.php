<?php
require_once __DIR__ . '/../../includes/boot.php';

$booking_id = intval($_GET['id'] ?? 0);
$booking = fetch_booking_by_id($booking_id);

if (!$booking) {
    if (function_exists('flash')) {
        flash('message', 'ไม่พบรายการการจอง');
    }
    header('Location: booking_list.php');
    exit;
}

$pageTitle = 'รายละเอียดการจอง (Admin)';
include __DIR__ . '/../../includes/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap');
    
    :root {
        --theme-bg: #FDFBF9;         /* ขาวครีมสบายตา */
        --theme-card: #FFFFFF;       /* ขาวบริสุทธิ์ */
        --theme-brown: #5D4037;      /* น้ำตาลกาแฟ (ตัวหนังสือหลัก) */
        --theme-brown-dark: #3E2723; /* น้ำตาลเข้ม (กล่องสรุปราคา) */
        --theme-orange: #E66A38;     /* ส้ม Terracotta (จุดเด่น/ปุ่ม) */
        --theme-orange-light: #FFF0E6; /* ส้มอ่อน (พื้นหลังบางจุด) */
        --theme-border: #EFEBE9;     /* สีเส้นขอบ */
    }

    .admin-wrapper {
        font-family: 'Prompt', sans-serif;
        background-color: var(--theme-bg);
        color: var(--theme-brown);
        padding: 2.5rem 2rem;
        min-height: 100vh;
        box-sizing: border-box;
    }
    
    .admin-wrapper * { box-sizing: border-box; }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--theme-border);
        gap: 1rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: var(--theme-brown);
    }

    .page-header p {
        margin: 0;
        color: #8D6E63;
        font-size: 1rem;
    }

    .detail-container {
        max-width: 950px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .detail-container { grid-template-columns: 1fr; }
    }

    .detail-card {
        background: var(--theme-card);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(93, 64, 55, 0.05);
        border: 1px solid var(--theme-border);
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    /* แถบสีส้มด้านบนการ์ด */
    .detail-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-color: var(--theme-orange);
    }

    .card-title {
        font-size: 1.15rem;
        font-weight: 500;
        color: var(--theme-brown);
        margin-top: 0;
        margin-bottom: 1.5rem;
        padding-bottom: 0.8rem;
        border-b: 1px dashed #D7CCC8;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .card-title .icon {
        background-color: var(--theme-orange-light);
        color: var(--theme-orange);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.85rem;
        color: #8D6E63;
        margin-bottom: 0.3rem;
        font-weight: 400;
    }

    .info-value {
        font-size: 1.05rem;
        color: var(--theme-brown);
        font-weight: 500;
    }

    .info-value.highlight {
        color: var(--theme-orange);
        font-size: 1.2rem;
        font-weight: 600;
    }

    /* กล่องสรุปยอดเงิน (สีน้ำตาลเข้ม ตัดส้ม) */
    .summary-box {
        background: var(--theme-brown-dark);
        color: var(--theme-card);
        padding: 2.5rem 2rem;
        border-radius: 16px;
        position: sticky;
        top: 2rem;
        box-shadow: 0 10px 30px rgba(62, 39, 35, 0.15);
        border-bottom: 6px solid var(--theme-orange);
    }

    .summary-title {
        font-size: 0.9rem;
        color: #D7CCC8;
        margin-bottom: 0.5rem;
    }

    .price-large {
        font-size: 2.5rem;
        font-weight: 600;
        color: var(--theme-orange);
        margin-bottom: 1.5rem;
        line-height: 1;
    }

    .price-large span {
        font-size: 1.2rem;
        color: var(--theme-card);
        font-weight: 400;
    }

    .status-badge {
        display: block;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        text-align: center;
        background-color: var(--theme-orange);
        color: var(--theme-card);
        width: 100%;
        margin-top: 1rem;
        box-shadow: 0 4px 10px rgba(230, 106, 56, 0.3);
    }

    .btn {
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-family: 'Prompt', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-secondary {
        background-color: var(--theme-card);
        color: var(--theme-brown);
        border: 1px solid #D7CCC8;
    }

    .btn-secondary:hover {
        background-color: var(--theme-orange-light);
        border-color: var(--theme-orange);
        color: var(--theme-orange);
    }

    .divider {
        height: 1px;
        background-color: rgba(255,255,255,0.1);
        margin: 1.5rem 0;
    }
</style>

<div class="admin-wrapper">
    
    <div class="page-header">
        <div>
            <h1>รายละเอียดการจอง</h1>
            <p>ข้อมูลการเข้าพักรหัส #<?php echo str_pad($booking_id, 5, '0', STR_PAD_LEFT); ?></p>
        </div>
        <a href="booking_list.php" class="btn btn-secondary">
            ⬅️ กลับหน้ารายการ
        </a>
    </div>

    <div class="detail-container">
        
        <div>
            <div class="detail-card">
                <h2 class="card-title"><span class="icon">👤</span> ข้อมูลเจ้าของ</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ชื่อ - นามสกุล</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars(trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name'])); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">อีเมลติดต่อ</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($booking['customer_email']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2 class="card-title"><span class="icon">🐾</span> ข้อมูลผู้เข้าพัก</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ชื่อเจ้านาย (น้องแมว)</div>
                        <div class="info-value highlight">
                            <?php echo htmlspecialchars($booking['cat_name']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ประเภทห้องพัก</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($booking['roomtype_name']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2 class="card-title"><span class="icon">📅</span> ระยะเวลาเข้าพัก</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">เช็คอิน (Check-in)</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($booking['start_date']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">เช็คเอาท์ (Check-out)</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($booking['end_date']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="summary-box">
                <div class="summary-title">ยอดชำระทั้งหมด</div>
                <div class="price-large">
                    <span>฿</span><?php echo htmlspecialchars(number_format($booking['total_price'] ?? 0, 2)); ?>
                </div>
                
                <div class="divider"></div>
                
                <div class="summary-title">สถานะรายการ</div>
                <div class="status-badge">
                    ✓ ยืนยันการจองแล้ว
                </div>
                
                <p style="font-size: 0.8rem; color: #8D6E63; text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
                    Luxury Cat Hotel System
                </p>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>