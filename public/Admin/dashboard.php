<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'แดชบอร์ดผู้ดูแล';
$showPrimaryAction = false;
$summary = fetch_booking_summary();
$bookings = fetch_bookings();
$recentBookings = array_slice($bookings, 0, 5);
include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-[#FAFAF8] p-6 lg:p-10 font-sans text-stone-800">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 pb-6 border-b border-stone-200/80 gap-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-[#C86428] mb-2">
                <i class="fas fa-chart-line"></i>
                <span>Property Management</span>
                <span>&bull;</span>
                <span>Dashboard</span>
            </div>
            <h1 class="text-3xl font-light text-stone-900 tracking-tight">ภาพรวมระบบการจอง</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <div class="relative bg-white p-6 rounded-3xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-lg hover:border-blue-200 transition-all duration-300 overflow-hidden group">
            <i class="fas fa-calendar-check absolute -bottom-6 -right-4 text-7xl text-blue-50 opacity-50 group-hover:scale-110 group-hover:-rotate-12 transition-transform duration-500"></i>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-400 mt-2">Total Bookings</span>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white group-hover:-translate-y-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                </div>
                <div>
                    <div class="text-4xl font-light text-stone-900 mb-1"><?= number_format((float)$summary['total']) ?> <span class="text-sm font-normal text-stone-400">รายการ</span></div>
                    <div class="text-xs text-stone-500">รวมการจองทั้งหมดในระบบ</div>
                </div>
            </div>
        </div>

        <div class="relative bg-white p-6 rounded-3xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-lg hover:border-purple-200 transition-all duration-300 overflow-hidden group">
            <i class="fas fa-clock absolute -bottom-6 -right-4 text-7xl text-purple-50 opacity-50 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500"></i>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-400 mt-2">Pending</span>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white group-hover:-translate-y-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-hourglass-half text-xl"></i>
                    </div>
                </div>
                <div>
                    <div class="text-4xl font-light text-stone-900 mb-1"><?= number_format((float)$summary['checkin']) ?> <span class="text-sm font-normal text-stone-400">รายการ</span></div>
                    <div class="text-xs text-purple-600 font-medium">รอยืนยันที่ต้องตรวจสอบ</div>
                </div>
            </div>
        </div>

        <div class="relative bg-white p-6 rounded-3xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-lg hover:border-[#C86428]/30 transition-all duration-300 overflow-hidden group">
            <i class="fas fa-cat absolute -bottom-6 -right-4 text-7xl text-orange-50 opacity-50 group-hover:scale-110 group-hover:-rotate-12 transition-transform duration-500"></i>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-400 mt-2">Staying</span>
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#C86428] flex items-center justify-center group-hover:bg-[#C86428] group-hover:text-white group-hover:-translate-y-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-door-open text-xl"></i>
                    </div>
                </div>
                <div>
                    <div class="text-4xl font-light text-stone-900 mb-1"><?= number_format((float)$summary['staying']) ?> <span class="text-sm font-normal text-stone-400">รายการ</span></div>
                    <div class="text-xs text-[#C86428] font-medium">น้องแมวกำลังพักอยู่ที่โรงแรม</div>
                </div>
            </div>
        </div>

        <div class="relative bg-white p-6 rounded-3xl border border-stone-200/60 shadow-[0_4px_20px_rgb(0,0,0,0.02)] hover:shadow-lg hover:border-red-200 transition-all duration-300 overflow-hidden group">
            <i class="fas fa-file-invoice-dollar absolute -bottom-6 -right-4 text-7xl text-red-50 opacity-50 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500"></i>
            
            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-400 mt-2">Payment</span>
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center group-hover:bg-red-500 group-hover:text-white group-hover:-translate-y-1 transition-all duration-300 shadow-sm">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <div>
                    <div class="text-4xl font-light text-stone-900 mb-1"><?= number_format((float)$summary['pending_payment']) ?> <span class="text-sm font-normal text-stone-400">รายการ</span></div>
                    <div class="text-xs text-red-500 font-medium">รายการที่ต้องติดตามการชำระเงิน</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
        
        <section class="bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.02)] border border-stone-200/60 p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-semibold text-stone-900">การจองล่าสุด</h3>
                    <p class="text-sm text-stone-400 mt-1">ภาพรวมรายการจองที่เพิ่งเข้ามาในระบบ</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= htmlspecialchars(url('Admin/bookings/booking_list.php')) ?>" class="bg-[#2C2523] hover:bg-[#C86428] text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-sm hover:shadow-md transition duration-300 flex items-center justify-center gap-2 group">
                        ดูรายการทั้งหมด <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            <div class="space-y-3">
                <?php if (empty($recentBookings)): ?>
                    <div class="bg-stone-50/80 rounded-2xl p-12 text-center border border-dashed border-stone-200">
                        <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mx-auto mb-4 text-stone-300">
                            <i class="fas fa-box-open text-2xl"></i>
                        </div>
                        <div class="font-medium text-stone-700">ยังไม่มีการจองในระบบ</div>
                        <p class="text-stone-400 text-xs mt-1">รายการจองใหม่จะปรากฏที่นี่</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentBookings as $booking): ?>
                        <div class="group bg-white rounded-2xl p-5 border border-stone-100 hover:border-[#C86428]/40 hover:shadow-md transition-all duration-300 flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative overflow-hidden">
                            
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#C86428] opacity-0 group-hover:opacity-100 transition-opacity"></div>

                            <div class="flex items-center gap-4 pl-2">
                                <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center text-stone-400 font-bold text-lg border border-white shadow-sm group-hover:bg-orange-100 group-hover:text-[#C86428] group-hover:rotate-6 transition-all duration-300">
                                    <?= htmlspecialchars($booking['customer_first_name'][0] ?? 'C') ?>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider group-hover:text-[#C86428]/70 transition-colors">ID: #<?= htmlspecialchars($booking['booking_id']) ?></p>
                                    </div>
                                    <h4 class="text-base font-semibold text-stone-800 leading-tight group-hover:text-[#C86428] transition-colors">
                                        <?= htmlspecialchars($booking['customer_first_name'] ?? 'Unknown') ?> <?= htmlspecialchars($booking['customer_last_name'] ?? '') ?>
                                    </h4>
                                    <p class="text-xs text-stone-500 flex items-center gap-1.5 mt-1">
                                        <i class="fas fa-bed text-stone-300 group-hover:text-[#C86428]/60 transition-colors"></i>
                                        <?= htmlspecialchars($booking['roomtype_name'] ?? '-') ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col md:items-end gap-3 md:gap-2 shrink-0">
                                <div class="flex items-center md:justify-end gap-2 text-xs font-medium text-stone-500 bg-stone-50 px-3 py-1.5 rounded-lg group-hover:bg-orange-50/50 transition-colors">
                                    <i class="far fa-calendar-alt text-stone-400 group-hover:text-[#C86428]"></i>
                                    <?= htmlspecialchars($booking['start_date']) ?> <span class="text-stone-300">/</span> <?= htmlspecialchars($booking['end_date']) ?>
                                </div>
                                <?php
                                    $status = trim($booking['status'] ?? 'Unknown');
                                    // โทนสี Badge อิงตามสไตล์มินิมอลที่เน้นความสว่าง
                                    $badgeClass = 'bg-stone-50 text-stone-600 border-stone-200';
                                    $dotClass = 'bg-stone-400';

                                    if ($status === 'Confirmed') {
                                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        $dotClass = 'bg-emerald-500';
                                    } elseif ($status === 'Pending') {
                                        $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                                        $dotClass = 'bg-purple-500';
                                    } elseif ($status === 'Completed') {
                                        $badgeClass = 'bg-stone-100 text-stone-700 border-stone-300';
                                        $dotClass = 'bg-stone-600';
                                    } elseif ($status === 'Cancelled') {
                                        $badgeClass = 'bg-red-50 text-red-700 border-red-200';
                                        $dotClass = 'bg-red-500';
                                    } elseif ($status === 'Checked In') {
                                        $badgeClass = 'bg-orange-50 text-[#C86428] border-orange-200';
                                        $dotClass = 'bg-[#C86428]';
                                    }
                                ?>
                                <div class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold border <?= $badgeClass ?> shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?> group-hover:animate-pulse"></span>
                                    <?= htmlspecialchars($status) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="bg-[#2C2523] p-8 rounded-3xl text-white shadow-lg relative overflow-hidden group">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#C86428] rounded-full blur-[60px] opacity-30 group-hover:opacity-50 group-hover:scale-125 transition-all duration-700"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-stone-500 rounded-full blur-[50px] opacity-20"></div>
                
                <div class="relative z-10">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-stone-400 mb-3 font-semibold">Admin Portal</p>
                    <h4 class="text-2xl font-light tracking-tight text-white mb-2">ระบบจัดการโรงแรม</h4>
                    <p class="text-sm leading-relaxed text-stone-300 font-light">
                        ตรวจสอบสถานะห้องพัก จัดการการจอง และเข้าถึงรายงานสรุปผลรายได้
                    </p>
                    
                    <div class="mt-8 flex items-center justify-between">
                        <a href="<?= htmlspecialchars(url('Admin/rooms/room_list.php')) ?>" class="text-sm font-medium text-white hover:text-[#C86428] transition-colors flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl backdrop-blur-sm border border-white/10 hover:bg-white hover:shadow-md group/btn">
                            ผังห้องพัก <i class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1 transition-transform"></i>
                        </a>
                        <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-[#C86428] text-xl shadow-inner group-hover:rotate-[360deg] transition-transform duration-700">
                            <i class="fas fa-paw"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-7 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.02)] border border-stone-200/60">
                <h4 class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-5 flex items-center gap-2">
                    <i class="fas fa-link text-[#C86428]"></i> Quick Links
                </h4>
                <div class="space-y-3">
                    <a href="<?= htmlspecialchars(url('Admin/rooms/room_list.php')) ?>" class="flex items-center justify-between rounded-2xl border border-stone-100 px-4 py-3.5 text-sm font-medium text-stone-600 hover:bg-orange-50/50 hover:text-[#C86428] hover:border-[#C86428]/30 transition-all duration-300 group">
                        <span class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-stone-50 flex items-center justify-center text-stone-400 group-hover:bg-white group-hover:text-[#C86428] group-hover:shadow-sm transition-all">
                                <i class="fas fa-door-open text-sm"></i>
                            </div>
                            จัดการห้องพัก
                        </span>
                        <i class="fas fa-chevron-right text-[10px] text-stone-300 group-hover:text-[#C86428] group-hover:translate-x-1 transition-all"></i>
                    </a>
                    
                    <a href="<?= htmlspecialchars(url('Admin/reports/report_booking.php')) ?>" class="flex items-center justify-between rounded-2xl border border-stone-100 px-4 py-3.5 text-sm font-medium text-stone-600 hover:bg-orange-50/50 hover:text-[#C86428] hover:border-[#C86428]/30 transition-all duration-300 group">
                        <span class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-stone-50 flex items-center justify-center text-stone-400 group-hover:bg-white group-hover:text-[#C86428] group-hover:shadow-sm transition-all">
                                <i class="fas fa-chart-pie text-sm"></i>
                            </div>
                            รายงานการจอง
                        </span>
                        <i class="fas fa-chevron-right text-[10px] text-stone-300 group-hover:text-[#C86428] group-hover:translate-x-1 transition-all"></i>
                    </a>
                    
                    <a href="<?= htmlspecialchars(url('Admin/reports/report_income.php')) ?>" class="flex items-center justify-between rounded-2xl border border-stone-100 px-4 py-3.5 text-sm font-medium text-stone-600 hover:bg-orange-50/50 hover:text-[#C86428] hover:border-[#C86428]/30 transition-all duration-300 group">
                        <span class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-stone-50 flex items-center justify-center text-stone-400 group-hover:bg-white group-hover:text-[#C86428] group-hover:shadow-sm transition-all">
                                <i class="fas fa-wallet text-sm"></i>
                            </div>
                            รายงานรายได้
                        </span>
                        <i class="fas fa-chevron-right text-[10px] text-stone-300 group-hover:text-[#C86428] group-hover:translate-x-1 transition-all"></i>
                    </a>
                    
                    <a href="<?= htmlspecialchars(url('Admin/payments/payment_list.php')) ?>" class="flex items-center justify-between rounded-2xl border border-stone-100 px-4 py-3.5 text-sm font-medium text-stone-600 hover:bg-orange-50/50 hover:text-[#C86428] hover:border-[#C86428]/30 transition-all duration-300 group">
                        <span class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-stone-50 flex items-center justify-center text-stone-400 group-hover:bg-white group-hover:text-[#C86428] group-hover:shadow-sm transition-all">
                                <i class="fas fa-file-invoice-dollar text-sm"></i>
                            </div>
                            ประวัติการชำระเงิน
                        </span>
                        <i class="fas fa-chevron-right text-[10px] text-stone-300 group-hover:text-[#C86428] group-hover:translate-x-1 transition-all"></i>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>