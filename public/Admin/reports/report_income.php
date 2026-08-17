<?php
// 1. ต้องประกาศ session_start() เป็นสิ่งแรกสุด เพื่อให้อ่านค่าคนที่ล็อกอินมาได้
session_start();

// 2. ป้องกันคนนอกเข้าถึง
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php"); 
    exit();
}

// 3. เรียกไฟล์เชื่อมต่อฐานข้อมูล (ลบคอมเมนต์ออกและเช็ค Path ให้ตรงกับไฟล์ของคุณ)
// require_once '../../includes/db.php'; 
// สมมติว่าไฟล์ db.php ของคุณมีตัวแปร $conn สำหรับเชื่อมต่อฐานข้อมูล (PDO หรือ MySQLi)

// =========================================================================
// ส่วนที่ 1: เตรียมตัวแปรและดึงข้อมูลจากฐานข้อมูลจริง
// (กรุณาแก้ไขชื่อตาราง เช่น 'payments' และชื่อคอลัมน์ ให้ตรงกับ DB ของคุณ)
// =========================================================================

// กำหนดเดือน/ปี ปัจจุบัน สำหรับใช้ดึงข้อมูล
$currentMonth = date('m');
$currentYear = date('Y');
$monthNameThai = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
$displayMonth = $monthNameThai[(int)$currentMonth - 1] . " " . ($currentYear + 543); // เช่น ตุลาคม 2566

// ประกาศตัวแปรเริ่มต้น (เผื่อกรณีฐานข้อมูลไม่มีข้อมูล)
$totalIncome = 0; $totalTxn = 0; $netProfit = 0; $avgTxn = 0;
$incomeQR = 0; $incomeCash = 0;
$roomIncome = 0; $addonIncome = 0;
$groomingIncome = 0; $foodIncome = 0; $taxiIncome = 0;
$weeklyData = [0, 0, 0, 0];
$recentPayments = [];

/* --- ตัวอย่าง SQL สำหรับดึงข้อมูล (ให้เปิดคอมเมนต์เมื่อต่อ DB แล้ว) ---
try {
    // 1. ดึงรายได้รวมและจำนวนธุรกรรมของเดือนนี้
    $stmt = $conn->prepare("SELECT SUM(amount) as total, COUNT(id) as txn FROM payments WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ? AND status = 'Success'");
    $stmt->execute([$currentMonth, $currentYear]);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalIncome = $kpi['total'] ?? 0;
    $totalTxn = $kpi['txn'] ?? 0;
    $netProfit = $totalIncome * 0.95; // สมมติหักค่าใช้จ่าย 5%
    $avgTxn = $totalTxn > 0 ? $totalIncome / $totalTxn : 0;

    // 2. ดึงช่องทางชำระเงิน
    $stmt = $conn->prepare("SELECT payment_method, SUM(amount) as total FROM payments WHERE MONTH(payment_date) = ? GROUP BY payment_method");
    $stmt->execute([$currentMonth]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if(strtoupper($row['payment_method']) == 'QR') $incomeQR = $row['total'];
        if(strtoupper($row['payment_method']) == 'CASH') $incomeCash = $row['total'];
    }

    // 3. ดึงรายได้แยกตามประเภท (Room vs Add-on ฯลฯ) - ปรับ SQL ตามโครงสร้าง DB ของคุณ
    // $roomIncome = ...; $addonIncome = ...;

    // 4. แนวโน้มรายสัปดาห์ (กราฟเส้น) 
    // ตัวอย่าง: ดึงรายได้สัปดาห์ที่ 1-4 ของเดือนนี้ใส่ Array $weeklyData

    // 5. ดึงรายการชำระเงินล่าสุด 5 รายการ
    $stmt = $conn->prepare("
        SELECT p.ref_no, c.name as customer_name, cat.name as cat_name, p.service_type, p.payment_method, p.amount, p.status 
        FROM payments p 
        LEFT JOIN customers c ON p.customer_id = c.id
        LEFT JOIN cats cat ON p.cat_id = cat.id
        ORDER BY p.created_at DESC LIMIT 5
    ");
    $stmt->execute();
    $recentPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
------------------------------------------------------------------------- */

// ** ข้อมูลจำลองสำหรับทดสอบ UI (ให้ลบออกเมื่อใช้คำสั่ง SQL ด้านบนแล้ว) **
$totalIncome = 245800; $netProfit = 233350; $totalTxn = 342; $avgTxn = 718;
$incomeQR = 168400; $incomeCash = 77400;
$roomIncome = 184200; $addonIncome = 61600;
$groomingIncome = 32400; $foodIncome = 18200; $taxiIncome = 11000;
$weeklyData = [42000, 58000, 72000, 73500];
$recentPayments = [
    ['ref_no'=>'#BK-9021', 'customer_name'=>'สมชาย', 'cat_name'=>'Milo', 'service_type'=>'Room Only', 'payment_method'=>'QR', 'amount'=>2400, 'status'=>'Success', 'initial'=>'ส'],
    ['ref_no'=>'#BK-9015', 'customer_name'=>'อารยา', 'cat_name'=>'Cookie', 'service_type'=>'Room + Taxi', 'payment_method'=>'Cash', 'amount'=>3200, 'status'=>'Refunded', 'initial'=>'อ'],
    ['ref_no'=>'#BK-9020', 'customer_name'=>'นรินทร์', 'cat_name'=>'Sushi', 'service_type'=>'Room + Grooming', 'payment_method'=>'Cash', 'amount'=>4850, 'status'=>'Success', 'initial'=>'น']
];
// ******************************************************************


// 4. เรียกไฟล์ header 
require_once '../../includes/header.php';
?>

<div class="p-6 bg-[#fdfaf6] min-h-screen font-sans">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#4a3f35]">สรุปรายงานรายได้</h1>
            <p class="text-stone-500 text-sm mt-1">ข้อมูลประจำเดือน <?= $displayMonth ?></p>
        </div>
        <div class="flex gap-3">
            <button class="bg-white border border-stone-200 px-4 py-2 rounded-lg text-sm text-stone-600 shadow-sm hover:bg-stone-50 transition">
                <i class="far fa-calendar-alt mr-2 text-stone-400"></i> 1 <?= $monthNameThai[(int)$currentMonth - 1] ?> - สิ้นเดือน
            </button>
            <button class="bg-[#0f6c65] hover:bg-[#0b534d] text-white px-4 py-2 rounded-lg text-sm shadow-sm transition">
                <i class="fas fa-file-export mr-2"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-[#fcf8f3] opacity-50 text-8xl">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[#8c827a] text-xs font-semibold mb-1">รายได้รวมทั้งหมด</p>
                <h2 class="text-4xl font-bold text-[#c45b15] my-2 tracking-tight"><?= number_format($totalIncome) ?></h2>
                <p class="text-[#0f6c65] text-xs font-medium bg-[#e6f4f1] inline-block px-2 py-1 rounded-md">
                    <i class="fas fa-arrow-trend-up mr-1"></i> +12.5% เทียบเดือนก่อน
                </p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm relative overflow-hidden">
            <div class="absolute -right-2 -top-2 text-[#fcf8f3] opacity-50 text-8xl">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[#8c827a] text-xs font-semibold mb-1">รายได้สุทธิ (NET PROFIT)</p>
                <h2 class="text-4xl font-bold text-[#1a7771] my-2 tracking-tight"><?= number_format($netProfit) ?></h2>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm relative overflow-hidden">
            <div class="absolute -right-2 -top-2 text-[#fcf8f3] opacity-50 text-8xl">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[#8c827a] text-xs font-semibold mb-1">จำนวนธุรกรรม</p>
                <h2 class="text-4xl font-bold text-[#2a2522] my-2 tracking-tight"><?= number_format($totalTxn) ?></h2>
                <p class="text-stone-400 text-xs">เฉลี่ย ฿<?= number_format($avgTxn) ?> ต่อการชำระ</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-[#4a3f35] text-lg">แนวโน้มรายได้รายสัปดาห์</h3>
                    <p class="text-xs text-stone-400 mt-1">เปรียบเทียบความเคลื่อนไหวในแต่ละสัปดาห์</p>
                </div>
                <span class="text-xs bg-[#fdf6f0] text-[#c45b15] px-3 py-1 rounded-full border border-[#f8e5d6] flex items-center">
                    <div class="w-2 h-2 rounded-full bg-[#c45b15] mr-2"></div> รายได้รวม
                </span>
            </div>
            <div class="h-72 w-full">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm flex flex-col">
            <h3 class="font-bold text-[#4a3f35] text-lg mb-6">ช่องทางการชำระเงิน</h3>
            <div class="flex-grow flex items-center justify-center mb-6">
                <div class="h-48 w-48 relative">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
            <div class="space-y-2 mt-auto">
                <div class="flex justify-between items-center text-sm bg-[#f8f7f5] p-3 rounded-lg border border-[#f0ebe1]">
                    <div class="flex items-center text-[#4a3f35]">
                        <i class="fas fa-qrcode text-[#95541e] mr-3 text-lg"></i>
                        <span>QR PromptPay</span>
                    </div>
                    <span class="font-bold text-[#2a2522]">฿<?= number_format($incomeQR) ?></span>
                </div>
                <div class="flex justify-between items-center text-sm bg-[#f8f7f5] p-3 rounded-lg border border-[#f0ebe1]">
                    <div class="flex items-center text-[#4a3f35]">
                        <i class="fas fa-money-bill-wave text-[#0f6c65] mr-3"></i>
                        <span>เงินสด</span>
                    </div>
                    <span class="font-bold text-[#2a2522]">฿<?= number_format($incomeCash) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-[#4a3f35] text-lg">ยอดขายแยกตามประเภทบริการ</h3>
                <i class="fas fa-ellipsis-v text-stone-300 cursor-pointer hover:text-stone-500"></i>
            </div>
            <div class="space-y-5">
                <div>
                    <div class="flex justify-between text-sm mb-2 text-[#4a3f35] font-medium">
                        <span>ที่พัก (Rooms)</span> 
                        <span class="text-[#2a2522]">฿<?= number_format($roomIncome) ?></span>
                    </div>
                    <div class="w-full bg-[#f3efeb] h-2.5 rounded-full overflow-hidden">
                        <?php $roomPct = ($totalIncome > 0) ? ($roomIncome / $totalIncome) * 100 : 0; ?>
                        <div class="bg-[#8b4d1b] h-full rounded-full" style="width: <?= $roomPct ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2 text-[#4a3f35] font-medium">
                        <span>บริการเสริม (Add-ons)</span> 
                        <span class="text-[#2a2522]">฿<?= number_format($addonIncome) ?></span>
                    </div>
                    <div class="w-full bg-[#f3efeb] h-2.5 rounded-full overflow-hidden">
                        <?php $addonPct = ($totalIncome > 0) ? ($addonIncome / $totalIncome) * 100 : 0; ?>
                        <div class="bg-[#0f6c65] h-full rounded-full" style="width: <?= $addonPct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-[#4a3f35] text-lg">สัดส่วนบริการเสริม</h3>
                <i class="fas fa-ellipsis-v text-stone-300 cursor-pointer hover:text-stone-500"></i>
            </div>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2 text-[#4a3f35]">
                        <span>Grooming / Bath</span> 
                        <span class="font-medium text-[#2a2522]">฿<?= number_format($groomingIncome) ?></span>
                    </div>
                    <div class="w-full bg-[#f3efeb] h-2.5 rounded-full overflow-hidden">
                        <?php $grPct = ($addonIncome > 0) ? ($groomingIncome / $addonIncome) * 100 : 0; ?>
                        <div class="bg-[#f28b33] h-full rounded-full" style="width: <?= $grPct ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2 text-[#4a3f35]">
                        <span>Premium Food</span> 
                        <span class="font-medium text-[#2a2522]">฿<?= number_format($foodIncome) ?></span>
                    </div>
                    <div class="w-full bg-[#f3efeb] h-2.5 rounded-full overflow-hidden">
                        <?php $foodPct = ($addonIncome > 0) ? ($foodIncome / $addonIncome) * 100 : 0; ?>
                        <div class="bg-[#66d2de] h-full rounded-full" style="width: <?= $foodPct ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2 text-[#4a3f35]">
                        <span>Cat Taxi</span> 
                        <span class="font-medium text-[#2a2522]">฿<?= number_format($taxiIncome) ?></span>
                    </div>
                    <div class="w-full bg-[#f3efeb] h-2.5 rounded-full overflow-hidden">
                        <?php $taxiPct = ($addonIncome > 0) ? ($taxiIncome / $addonIncome) * 100 : 0; ?>
                        <div class="bg-[#8b91a3] h-full rounded-full" style="width: <?= $taxiPct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-[#f0ebe1] shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-[#4a3f35] text-lg">รายการชำระเงินล่าสุด</h3>
            <a href="#" class="text-[#c45b15] text-sm font-semibold hover:underline">ดูทั้งหมด</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f7f5f0] text-stone-500 text-xs font-semibold">
                        <th class="py-3 px-4 rounded-l-lg whitespace-nowrap">เลขที่อ้างอิง</th>
                        <th class="py-3 px-4 whitespace-nowrap">ลูกค้า/น้องแมว</th>
                        <th class="py-3 px-4 whitespace-nowrap">ประเภทบริการ</th>
                        <th class="py-3 px-4 whitespace-nowrap">ช่องทาง</th>
                        <th class="py-3 px-4 whitespace-nowrap">จำนวนเงิน</th>
                        <th class="py-3 px-4 rounded-r-lg whitespace-nowrap">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#4a3f35]">
                    <?php if(!empty($recentPayments)): ?>
                        <?php foreach($recentPayments as $row): ?>
                        <tr class="border-b border-[#f0ebe1] hover:bg-stone-50 transition">
                            <td class="py-4 px-4 font-medium text-[#2a2522]"><?= htmlspecialchars($row['ref_no']) ?></td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#fdeede] text-[#c45b15] flex items-center justify-center font-bold text-sm">
                                        <?= htmlspecialchars(mb_substr($row['customer_name'] ?? 'C', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#2a2522]">คุณ<?= htmlspecialchars($row['customer_name']) ?></p>
                                        <p class="text-xs text-stone-400"><?= htmlspecialchars($row['cat_name'] ?? 'ไม่มีข้อมูลแมว') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4"><span class="bg-[#f0ece9] text-[#5e534a] px-2 py-1 rounded text-xs"><?= htmlspecialchars($row['service_type']) ?></span></td>
                            <td class="py-4 px-4 text-stone-500">
                                <?php if(strtoupper($row['payment_method']) == 'QR'): ?>
                                    <i class="fas fa-qrcode mr-2 text-stone-400"></i> QR
                                <?php else: ?>
                                    <i class="fas fa-wallet mr-2 text-stone-400"></i> Cash
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 font-bold text-[#2a2522]">฿<?= number_format($row['amount']) ?></td>
                            <td class="py-4 px-4">
                                <?php if($row['status'] == 'Success'): ?>
                                    <span class="bg-[#e6f4f1] text-[#0f6c65] px-3 py-1 rounded-full text-xs font-medium">Success</span>
                                <?php elseif($row['status'] == 'Refunded'): ?>
                                    <span class="bg-[#fce8e8] text-[#c92a2a] px-3 py-1 rounded-full text-xs font-medium">Refunded</span>
                                <?php else: ?>
                                    <span class="bg-stone-100 text-stone-600 px-3 py-1 rounded-full text-xs font-medium"><?= htmlspecialchars($row['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-6 text-stone-500">ยังไม่มีรายการชำระเงินในขณะนี้</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Line Chart (Trend)
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    
    let gradient = lineCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(196, 91, 21, 0.15)');
    gradient.addColorStop(1, 'rgba(196, 91, 21, 0)');

    new Chart(lineCtx, {
        type: 'line',
        data: { 
            labels: ['สัปดาห์ 1', 'สัปดาห์ 2', 'สัปดาห์ 3', 'สัปดาห์ 4'], 
            datasets: [{ 
                // ใช้ข้อมูลจาก Database (PHP Array)
                data: [<?= implode(',', $weeklyData) ?>], 
                borderColor: '#c45b15', 
                borderWidth: 3,
                pointBackgroundColor: '#c45b15',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                tension: 0.4, 
                fill: true, 
                backgroundColor: gradient 
            }] 
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '฿' + (value / 1000) + 'k';
                        },
                        color: '#a8a29e',
                        font: { size: 11 }
                    },
                    grid: { color: '#f5f5f4', drawBorder: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#a8a29e', font: { size: 11 } }
                }
            }
        }
    });

    // Donut Chart
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: { 
            labels: ['QR', 'Cash'], 
            datasets: [{ 
                // ใช้ข้อมูลจาก Database
                data: [<?= $incomeQR ?>, <?= $incomeCash ?>], 
                backgroundColor: ['#0f6c65', '#95541e'],
                borderWidth: 0
            }] 
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            cutout: '75%', 
            plugins: { legend: { display: false } }
        }
    });
</script>