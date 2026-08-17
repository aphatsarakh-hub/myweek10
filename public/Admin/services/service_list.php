<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'จัดการบริการเสริม';

$services = [];
// แก้ไขคำสั่ง SQL โดยเอา WHERE booking_id IS NULL ออก เพราะเราปรับโครงสร้าง DB แล้ว
$sql = "SELECT * FROM service ORDER BY service_id ASC";
$result = $mysqli->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* ตั้งค่าฟอนต์ใหม่ให้ดูทันสมัยและเรียบหรู */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #FDFBF8; color: #4A403A; }

    .minimal-card {
        background: #FFFFFF;
        border: 1px solid #F0ECE9;
        border-radius: 20px;
        transition: all 0.4s ease;
    }
    .minimal-card:hover {
        border-color: #D6C9C2;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    
    /* ไอคอนแบบเส้นบาง (Line Icons Style) */
    .icon-box { color: #C05800; font-size: 1.2rem; }
    
    /* ปรับโทนสีส้มให้ดูแพง (Muted Rust) */
    .text-premium-orange { color: #B35800; }
    .bg-premium-orange { background-color: #B35800; }
</style>

<div class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-16">
        <div>
            <h1 class="text-4xl font-light tracking-tight text-[#332B27]">Service Management</h1>
            <p class="text-[#8C7B75] mt-2 text-sm uppercase tracking-widest font-semibold">Premium Cat Hotel Services</p>
        </div>
        <a href="service_add.php" class="bg-[#332B27] hover:bg-[#1A1614] text-white px-8 py-3 rounded-full text-sm font-semibold transition-all shadow-md">
            + New Service
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach($services as $s): 
            if (!empty($s['image_path'])) {
                // ลบ / ข้างหน้าออก (ถ้ามี) เพื่อป้องกัน path ซ้อนกัน
                $fileName = ltrim($s['image_path'], '/');
                
                // วิธีที่ 1: ถอยหลัง 2 โฟลเดอร์ให้ตรงกับโครงสร้างจริง
                $img = "../../uploads/services/" . $fileName;
                
                // วิธีที่ 2 (แนะนำสำหรับ localhost): ใช้ Path เต็มจากชื่อโฟลเดอร์โปรเจกต์
                // $img = "/bambam_cat_hotel/uploads/services/" . $fileName;
            } else {
                $img = "https://images.unsplash.com/photo-1548767797-d8c844146e4c?auto=format&fit=crop&w=800&q=80";
            }
        ?>
            <div class="minimal-card overflow-hidden flex flex-col">
                <div class="h-64 overflow-hidden relative">
                    <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover opacity-90 hover:opacity-100 transition-opacity">
                    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full shadow-sm">
                        <span class="text-xs font-bold <?= $s['status'] == 'Active' ? 'text-green-600' : 'text-gray-400' ?>">
                            <?= $s['status'] == 'Active' ? '• Active' : '• Inactive' ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-8 flex-grow flex flex-col">
                    <div class="mb-4">
                        <h2 class="text-xl font-semibold text-[#332B27]"><?= htmlspecialchars($s['service_name']) ?></h2>
                    </div>
                    
                    <p class="text-[#8C7B75] text-sm leading-relaxed mb-8 h-12 overflow-hidden italic">
                        "<?= htmlspecialchars($s['service_detail'] ?: 'ไม่มีรายละเอียด') ?>"
                    </p>
                    
                    <div class="mt-auto flex items-center justify-between border-t border-[#F0ECE9] pt-6">
                        <span class="text-2xl font-bold text-premium-orange">฿<?= number_format($s['price'], 0) ?></span>
                        <div class="flex gap-4">
                            <a href="service_edit.php?id=<?= $s['service_id'] ?>" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-[#8C7B75] hover:text-[#C05800] hover:bg-orange-50 transition-colors" title="แก้ไข">
                                <i class="far fa-edit"></i>
                            </a>
                            <a href="service_delete.php?id=<?= $s['service_id'] ?>" onclick="return confirm('ยืนยันการลบบริการนี้?');" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-[#8C7B75] hover:text-red-500 hover:bg-red-50 transition-colors" title="ลบ">
                                <i class="far fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($services)): ?>
            <div class="col-span-full py-12 text-center text-[#8C7B75]">
                <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                <p>ยังไม่มีข้อมูลบริการในระบบ</p>
            </div>
        <?php endif; ?>
    </div>
</div>