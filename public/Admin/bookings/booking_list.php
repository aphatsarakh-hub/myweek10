<?php
require_once __DIR__ . '/../../includes/boot.php';
$pageTitle = 'รายการจองทั้งหมด';
$showPrimaryAction = false;
$bookings = fetch_bookings();
include __DIR__ . '/../../includes/header.php';
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
    <p class="text-sm text-gray-500">ตรวจสอบและจัดการการจองลูกค้าได้จากหน้านี้</p>
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" data-admin-search-input data-admin-search=".admin-search-item" placeholder="ค้นหาการจอง..." class="pl-11 pr-4 py-2 w-64 bg-white border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none shadow-sm">
        </div>
        <a href="<?= htmlspecialchars(url('Admin/reports/report_booking.php')) ?>" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-chart-bar"></i> รายงาน
        </a>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">ยังไม่มีการจองในระบบ</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($bookings as $b): 
            $searchText = implode(' ', [$b['booking_id'], $b['customer_first_name'], $b['cat_name'], $b['status']]);
        ?>
            <div class="admin-search-item bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all" data-searchable="<?= htmlspecialchars($searchText) ?>">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-bold text-orange-500 bg-orange-50 px-3 py-1 rounded-full">#<?= htmlspecialchars($b['booking_id']) ?></span>
                    <?php
                        $status = $b['status'] ?? 'Unknown';
                        $badge = 'bg-gray-100 text-gray-600';
                        if ($status === 'Confirmed') $badge = 'bg-green-100 text-green-700';
                        elseif ($status === 'Pending') $badge = 'bg-orange-100 text-orange-700';
                        elseif ($status === 'Completed') $badge = 'bg-blue-100 text-blue-700';
                        elseif ($status === 'Cancelled') $badge = 'bg-red-100 text-red-700';
                    ?>
                    <span class="text-[10px] font-bold px-3 py-1 rounded-full <?= $badge ?>"><?= htmlspecialchars($status) ?></span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($b['customer_first_name'] . ' ' . $b['customer_last_name']) ?></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500">แมว:</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($b['cat_name']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">ห้อง:</span>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($b['roomtype_name']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <i class="fas fa-calendar-alt text-gray-400"></i>
                        <span><?= htmlspecialchars($b['start_date']) ?> → <?= htmlspecialchars($b['end_date']) ?></span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">฿<?= number_format($b['total_price'], 2) ?></span>
                    <div class="flex gap-1">
                        <a href="booking_detail.php?id=<?= urlencode($b['booking_id']) ?>" class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-full transition"><i class="fas fa-eye"></i></a>
                        <a href="booking_edit.php?id=<?= urlencode($b['booking_id']) ?>" class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-full transition"><i class="fas fa-pencil-alt"></i></a>
                        <a href="booking_delete.php?id=<?= urlencode($b['booking_id']) ?>" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition"><i class="fas fa-trash-alt"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>