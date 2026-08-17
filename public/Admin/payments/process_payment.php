<?php
require_once __DIR__ . '/../../includes/boot.php';

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST และมีข้อมูลครบถ้วนหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id']) && isset($_POST['action'])) {
    
    $payment_id = (int)$_POST['payment_id'];
    $action = $_POST['action'];
    
    // กำหนดสถานะใหม่ตามปุ่มที่กดเข้ามา (คุณสามารถเปลี่ยนคำว่า 'completed' / 'rejected' ให้ตรงกับระบบเดิมของคุณได้)
    $new_status = '';
    if ($action === 'confirm') {
        $new_status = 'success';   // หรือ 'completed', 'ชำระเงินแล้ว'
    } elseif ($action === 'reject') {
        $new_status = 'rejected';  // หรือ 'failed', 'ปฏิเสธ'
    }

    if ($payment_id > 0 && !empty($new_status)) {
        try {
            // เชื่อมต่อฐานข้อมูล
            $db_host = 'localhost';
            $db_name = 'bambam_cat_hotel';
            $db_user = 'root';
            $db_pass = '';
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // เริ่มทำ Transaction (เผื่อต้องการบันทึกหลายตารางพร้อมกัน)
            $pdo->beginTransaction();

            // 1. อัปเดตสถานะในตาราง payment
            $stmt = $pdo->prepare("UPDATE payment SET status = :status WHERE payment_id = :id");
            $stmt->execute([
                'status' => $new_status,
                'id' => $payment_id
            ]);

            // 2. (Optional) ถ้ากดยืนยันสำเร็จ อาจจะไปอัปเดตสถานะตาราง booking ด้วย
            /*
            if ($action === 'confirm') {
                // ดึง booking_id ออกมาก่อน
                $stmtGet = $pdo->prepare("SELECT booking_id FROM payment WHERE payment_id = :id");
                $stmtGet->execute(['id' => $payment_id]);
                $payData = $stmtGet->fetch(PDO::FETCH_ASSOC);
                
                if ($payData) {
                    $stmtBooking = $pdo->prepare("UPDATE booking SET booking_status = 'confirmed' WHERE booking_id = :b_id");
                    $stmtBooking->execute(['b_id' => $payData['booking_id']]);
                }
            }
            */

            $pdo->commit();
            
            // ส่งกลับไปหน้าเดิมพร้อมสถานะสำเร็จ
            header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "status=success");
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Update Payment Error: " . $e->getMessage());
            // ส่งกลับไปหน้าเดิมพร้อมสถานะ Error
            header("Location: " . $_SERVER['HTTP_REFERER'] . (strpos($_SERVER['HTTP_REFERER'], '?') !== false ? '&' : '?') . "status=error");
            exit;
        }
    }
}

// ถ้าเข้าไฟล์นี้โดยตรงหรือไม่มีค่าส่งมา ให้เด้งกลับไปหน้ารายการหลัก
header("Location: payment_list.php");
exit;