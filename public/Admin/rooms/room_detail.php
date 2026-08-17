<?php
require_once __DIR__ . '/../../includes/boot.php';

$pageTitle = 'รายละเอียดห้อง (Admin)';
$roomtype_id = intval($_GET['id'] ?? 0);

if (function_exists('fetch_roomtype_by_id')) {
    $room = fetch_roomtype_by_id($roomtype_id);
} else {
    // Mock data กรณีไม่มีฟังก์ชันดึงฐานข้อมูล
    $room = [
        'type_name' => 'Deluxe Cat Suite',
        'price_per_night' => 650.00,
        'capacity' => 3,
        'description' => "ห้องพักสุดหรูขนาดกว้างขวาง เหมาะสำหรับเจ้านายที่ชอบความส่วนตัว มีคอนโดแมวไม้แท้ขนาดใหญ่ พื้นที่ปีนป่ายบุผ้ากำมะหยี่นุ่มสบาย พร้อมระบบหมุนเวียนอากาศบริสุทธิ์ เครื่องปรับอากาศคุมอุณหภูมิ 24 องศา และกล้อง CCTV ส่วนตัวที่ทาสสามารถส่องดูพฤติกรรมได้ตลอด 24 ชั่วโมง ฟรีทรายแมวพรีเมียมและน้ำพุน้ำแร่ธรรมชาติ",
        'image_url' => null // หรือใส่ชื่อไฟล์รูปภาพ เช่น 'room_65a123.jpg'
    ];
}

if (!$room) {
    if (function_exists('flash')) {
        flash('message', 'ไม่พบประเภทห้องพัก');
    }
    header('Location: room_list.php');
    exit;
}

// ฟังก์ชันดึงรูปภาพสไตล์ Luxury (อิงตามหน้า room_list.php)
function get_detail_luxury_image($room) {
    if (!empty($room['image_url'])) {
        return '../../uploads/room/' . $room['image_url'];
    }
    // ใช้รูป Default สไตล์โรงแรมแมวหรูหรากรณีไม่มีภาพในระบบ
    return 'https://placehold.co/1200x600/FAFAF8/2C2523?text=' . urlencode($room['type_name']);
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen bg-[#FAFAF8] p-6 lg:p-10 font-sans text-stone-800">
    
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-stone-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-[#C86428] mb-2">
                <span>Property Management</span>
                <span>&bull;</span>
                <a href="room_list.php" class="hover:text-[#2C2523] transition">Suites & Units</a>
                <span>&bull;</span>
                <span>Room Details</span>
            </div>
            <h1 class="text-3xl font-light text-stone-900 tracking-tight">รายละเอียดประเภทห้องพัก</h1>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="room_list.php" class="px-4 py-2 bg-white border border-stone-200 rounded-xl text-xs font-medium text-stone-600 hover:bg-stone-50 transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายการ
            </a>
            <a href="room_edit.php?id=<?php echo $roomtype_id; ?>" class="px-4 py-2 bg-[#2C2523] hover:bg-[#C86428] text-white rounded-xl text-xs font-medium shadow-sm transition flex items-center gap-1.5">
                <i class="fas fa-edit"></i> แก้ไขข้อมูล
            </a>
            <a href="room_delete.php?id=<?php echo $roomtype_id; ?>" 
               onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบประเภทห้องพักนี้? ข้อมูลจะไม่สามารถกู้คืนได้');" 
               class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl text-xs font-medium transition flex items-center gap-1.5">
                <i class="far fa-trash-alt"></i> ลบห้อง
            </a>
        </div>
    </div>

    <div class="max-w-5xl bg-white rounded-2xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] overflow-hidden">
        
        <div class="relative h-72 sm:h-96 bg-stone-100 overflow-hidden">
            <img src="<?php echo get_detail_luxury_image($room); ?>" 
                 alt="<?php echo htmlspecialchars($room['type_name']); ?>" 
                 onerror="this.src='https://placehold.co/1200x600/FAFAF8/2C2523?text=No+Image+Available';"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <div class="absolute bottom-6 left-6 right-6 text-white">
                <span class="inline-block px-3 py-1 bg-[#C86428] text-xs font-medium tracking-wide uppercase rounded-md mb-2 shadow-sm">
                    Room Category
                </span>
                <h2 class="text-2xl sm:text-3xl font-normal tracking-tight drop-shadow-sm">
                    <?php echo htmlspecialchars($room['type_name']); ?>
                </h2>
            </div>
        </div>

        <div class="p-8 lg:p-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="md:col-span-1 space-y-4">
                    
                    <div class="bg-[#FAFAF8] border border-stone-200/60 p-5 rounded-xl flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center border border-stone-100 text-[#C86428] text-lg shadow-sm">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-stone-400 block uppercase tracking-wider">ราคาต่อคืน</span>
                            <span class="text-xl font-bold text-stone-900">฿<?php echo number_format($room['price_per_night'], 2); ?></span>
                        </div>
                    </div>

                    <div class="bg-[#FAFAF8] border border-stone-200/60 p-5 rounded-xl flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center border border-stone-100 text-[#C86428] text-lg shadow-sm">
                            <i class="fas fa-cat"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-stone-400 block uppercase tracking-wider">รองรับผู้เข้าพัก</span>
                            <span class="text-lg font-semibold text-stone-800"><?php echo htmlspecialchars($room['capacity']); ?> ตัว</span>
                        </div>
                    </div>

                    <div class="p-5 border border-stone-200/60 rounded-xl space-y-3">
                        <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider block">Standard Perks</span>
                        <div class="space-y-2 text-xs text-stone-600">
                            <div class="flex items-center gap-2"><i class="fas fa-check text-emerald-600 text-[10px]"></i> ระบบคุมความเย็น 24°C</div>
                            <div class="flex items-center gap-2"><i class="fas fa-check text-emerald-600 text-[10px]"></i> กล้องวงจรปิด CCTV ในห้อง</div>
                            <div class="flex items-center gap-2"><i class="fas fa-check text-emerald-600 text-[10px]"></i> บริการทรายแมวเกรดพรีเมียม</div>
                        </div>
                    </div>

                </div>

                <div class="md:col-span-2 space-y-6">
                    <div>
                        <h3 class="text-xs font-semibold text-stone-400 uppercase tracking-widest mb-3">รายละเอียดและสิ่งอำนวยความสะดวก</h3>
                        <div class="text-sm text-stone-600 leading-relaxed bg-[#FAFAF8] p-6 rounded-xl border border-stone-200/40 min-h-[16rem]">
                            <?php if (!empty($room['description'])): ?>
                                <?php echo nl2br(htmlspecialchars($room['description'])); ?>
                            <?php else: ?>
                                <span class="text-stone-400 italic">ไม่ได้ระบุคำอธิบายหรือรายละเอียดเพิ่มเติมสำหรับห้องพักประเภทนี้</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>