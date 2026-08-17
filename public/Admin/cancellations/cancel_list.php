<?php
require_once __DIR__ . '/../../includes/boot.php';

$cancellations = fetch_cancellations();

foreach ($cancellations as &$row) {
    $customerName = trim(($row['customer_first_name'] ?? '') . ' ' . ($row['customer_last_name'] ?? ''));
    $row['customer_name'] = $customerName !== '' ? $customerName : 'ไม่พบข้อมูลลูกค้า';
    $row['cancel_reason']  = $row['reason'] ?: 'ไม่ระบุเหตุผล';
    $row['request_date']   = $row['cancel_date'];
}
unset($row);

$pageTitle = 'ประวัติการยกเลิกการจอง';
$showPrimaryAction = false;
include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .fade-enter { opacity: 0; transform: translateY(10px); }
    .fade-enter-active { opacity: 1; transform: translateY(0); transition: all 0.3s ease-out; }
</style>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-black text-gray-800">ประวัติการยกเลิกการจอง</h1>
    <a href="../rooms/room_list.php" class="text-sm text-gray-500 hover:text-red-500 flex items-center gap-2">
        <i class="fas fa-arrow-left text-xs"></i> กลับไปหน้าจัดการห้องพัก
    </a>
</div>

<div class="flex flex-col lg:flex-row gap-6 flex-1 min-h-[600px]">
    <div class="w-full lg:w-[400px] bg-white rounded-[24px] shadow-sm border border-gray-100 flex flex-col overflow-hidden shrink-0">
        <div class="p-5 border-b border-gray-50 bg-gray-50/50">
            <input type="text" id="searchInput" placeholder="ค้นหารหัส, ชื่อลูกค้า..." class="w-full pl-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-400 outline-none">
        </div>
        <div class="flex-1 overflow-y-auto hide-scrollbar p-3 space-y-2" id="requestList"></div>
    </div>

    <div class="flex-1 bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden flex flex-col relative" id="detailPane">
        <div id="emptyState" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50/50 z-10">
            <h3 class="text-lg font-bold text-gray-400">
                <?= empty($cancellations) ? 'ยังไม่มีรายการยกเลิกการจอง' : 'เลือกรายการทางซ้ายเพื่อดูรายละเอียด' ?>
            </h3>
        </div>
        <div id="detailContent" class="hidden flex-col h-full fade-enter">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-2xl font-black text-gray-800" id="det-cancel-id">CX-0000</h2>
                <p class="text-sm text-gray-500 mt-1" id="det-date">-</p>
            </div>
            <div class="p-6 flex-1 overflow-y-auto">
                <p class="text-xs font-bold text-gray-400 uppercase">ชื่อลูกค้า</p>
                <p class="text-lg font-black text-gray-800 mb-1" id="det-customer">-</p>
                <p class="text-xs text-gray-400 mb-4" id="det-phone">-</p>

                <p class="text-xs font-bold text-gray-400 uppercase">ห้องพัก / แมว</p>
                <p class="text-sm text-gray-700 mb-1" id="det-room">-</p>
                <p class="text-sm text-gray-700 mb-4" id="det-cat">-</p>

                <p class="text-xs font-bold text-gray-400 uppercase">ช่วงวันที่จอง</p>
                <p class="text-sm text-gray-700 mb-4" id="det-dates">-</p>

                <p class="text-xs font-bold text-gray-400 uppercase">เหตุผลการยกเลิก</p>
                <div class="bg-gray-50 p-4 rounded-xl mt-1 text-gray-600" id="det-reason">-</div>

                <p class="text-xs font-bold text-gray-400 uppercase mt-4">ยอดคืนเงิน</p>
                <p class="text-sm font-semibold text-red-600" id="det-refund">-</p>
            </div>
            <div class="p-6 border-t border-gray-100 flex gap-3">
                <a href="../bookings/booking_detail.php?id=" id="det-booking-link"
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium transition">
                    ดูรายละเอียดการจองเดิม
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const cancellations = <?= json_encode($cancellations) ?>;

    function renderList() {
        const listContainer = document.getElementById('requestList');
        listContainer.innerHTML = '';
        if (cancellations.length === 0) return;

        cancellations.forEach(c => {
            let item = document.createElement('div');
            item.className = "cursor-pointer border rounded-xl p-4 hover:bg-red-50 transition";
            item.innerHTML = `<div class="font-bold text-gray-800">CX-${c.cancel_id}</div>
                               <div class="text-xs text-gray-500">${c.customer_name}</div>
                               <div class="text-[11px] text-gray-400 mt-0.5">${c.roomtype_name ?? ''} ${c.room_number ?? ''}</div>`;
            item.onclick = () => selectItem(c.cancel_id);
            listContainer.appendChild(item);
        });
    }

    function selectItem(id) {
        const data = cancellations.find(c => c.cancel_id == id);
        if (!data) return;
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('detailContent').classList.remove('hidden');
        document.getElementById('det-cancel-id').innerText = 'CX-' + data.cancel_id;
        document.getElementById('det-customer').innerText = data.customer_name;
        document.getElementById('det-phone').innerText = data.customer_phone || '';
        document.getElementById('det-room').innerText = (data.roomtype_name || '-') + ' ' + (data.room_number || '');
        document.getElementById('det-cat').innerText = 'แมว: ' + (data.cat_name || 'ไม่ระบุ');
        document.getElementById('det-dates').innerText = (data.start_date || '-') + ' ถึง ' + (data.end_date || '-');
        document.getElementById('det-reason').innerText = data.cancel_reason;
        document.getElementById('det-refund').innerText =
            (parseFloat(data.refund_amount || 0)).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท (ไม่คืนเงินตามนโยบาย)';
        document.getElementById('det-date').innerText = data.request_date;
        document.getElementById('det-booking-link').href = '../bookings/booking_detail.php?id=' + data.booking_id;
    }

    document.getElementById('searchInput').addEventListener('input', function (e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('#requestList > div').forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    renderList();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>