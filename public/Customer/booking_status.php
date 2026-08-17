<?php
require_once __DIR__ . '/../includes/boot.php';

$pageTitle = 'สถานะการจองปัจจุบัน - BamBam Cat Hotel';
include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-[#FDFBF7] py-16 px-4 font-sans flex items-center justify-center">
    <div class="max-w-xl w-full text-center space-y-8">
        
        <div>
            <h1 class="text-3xl font-extrabold text-[#3B2D20]">ตรวจสอบสถานะการเข้าพัก</h1>
            <p class="text-sm text-[#7A6856] mt-2">ติดตามผลการอนุมัติและกระบวนการเข้าพักของเจ้านายตัวน้อยได้อย่างเรียลไทม์</p>
        </div>

        <div class="bg-white rounded-3xl border border-[#E8E1D5] shadow-2xl shadow-[#E8E1D5]/60 p-10 space-y-6">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-[#FDF8F0] text-orange-500 text-3xl border border-orange-100 shadow-inner">
                <i class="fas fa-shield-cat"></i>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg font-bold text-[#3B2D20]">ต้องการติดตามผลรายการจองใช่หรือไม่?</h3>
                <p class="text-[#7A6856] text-xs max-w-sm mx-auto leading-relaxed">
                    ระบบจะเชื่อมต่อกับห้องพักจริงของฝั่งผู้ดูแลทันที ท่านสามารถเข้าไปตรวจสอบสถานะการจอง และรายละเอียดทั้งหมดได้อย่างปลอดภัยผ่านทางระบบบันทึกประวัติ
                </p>
            </div>
            
            <div class="pt-2">
                <a href="booking_history.php" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 px-8 py-4 text-sm font-bold text-white shadow-xl shadow-orange-500/25 hover:brightness-105 transition w-full sm:w-auto">
                    <i class="fas fa-list-check"></i> ไปยังหน้าประวัติการจองของฉัน
                </a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>