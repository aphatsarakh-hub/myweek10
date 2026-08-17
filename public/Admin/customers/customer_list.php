<?php
require_once __DIR__ . '/../../includes/boot.php';

$pageTitle = 'จัดการข้อมูลลูกค้า';
$showPrimaryAction = false;

try {
    $sql = "
        SELECT
            c.customer_id,
            c.first_name,
            c.last_name,
            c.phone,
            c.email,
            c.address,
            a.username,
            a.role
        FROM customer c
        LEFT JOIN account a ON c.customer_id = a.customer_id
        ORDER BY c.customer_id DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $customers = [];
}

include __DIR__ . '/../../includes/header.php';
?>

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <p class="text-sm font-medium text-stone-500">ค้นหาและจัดการข้อมูลลูกค้าทั้งหมดในระบบ</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative min-w-[260px] flex-1 sm:flex-initial">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-sm"></i>
                        <input 
                            type="text" 
                            data-admin-search-input 
                            data-admin-search=".card-customer-grid .admin-search-item" 
                            placeholder="ค้นหาชื่อ, เบอร์, อีเมล, รหัส..." 
                            class="w-full pl-11 pr-4 py-2.5 bg-white border border-stone-200 rounded-2xl text-sm text-stone-800 placeholder-stone-400 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 focus:outline-none shadow-sm transition-all"
                        >
                    </div>
                    <a href="customer_add.php" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2.5 rounded-2xl text-sm font-bold shadow-sm shadow-orange-500/20 hover:shadow-md transition-all shrink-0">
                        <i class="fas fa-plus text-xs"></i> เพิ่มลูกค้า
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-stone-200/80 flex items-center justify-between hover:border-orange-200 transition-colors">
                    <div>
                        <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">ลูกค้าทั้งหมด</p>
                        <h3 class="text-3xl font-black text-stone-800"><?= number_format(count($customers)) ?> <span class="text-sm font-medium text-stone-500">คน</span></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-50 border border-orange-100 rounded-2xl flex items-center justify-center text-orange-600 text-lg shadow-inner">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="card-customer-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (empty($customers)): ?>
                    <div class="col-span-full bg-white text-center p-16 rounded-3xl border border-stone-200/80 shadow-sm">
                        <div class="w-20 h-20 bg-stone-50 text-stone-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl border border-stone-100">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <p class="font-bold text-lg text-stone-700">ไม่พบข้อมูลลูกค้าในระบบ</p>
                        <p class="mt-1 text-sm text-stone-400">ยังไม่มีรายชื่อลูกค้าในขณะนี้ คุณสามารถเริ่มต้นได้โดยการคลิกปุ่ม "เพิ่มลูกค้า"</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($customers as $index => $c):
                        // สลับโทนสี Avatar ให้อยู่ในโทน ขาว-ส้ม-น้ำตาล-เอิร์ธโทน
                        $bgColors = [
                            'bg-orange-50 text-orange-600 border-orange-100', 
                            'bg-amber-50 text-amber-700 border-amber-100', 
                            'bg-stone-100 text-stone-700 border-stone-200'
                        ];
                        $selectedColor = $bgColors[$index % count($bgColors)];
                        
                        // ดึงตัวอักษรย่อจากชื่อ
                        $initials = mb_substr($c['first_name'] ?? '', 0, 1, 'UTF-8') . mb_substr($c['last_name'] ?? '', 0, 1, 'UTF-8');
                        if (empty(trim($initials))) {
                            $initials = 'C';
                        }

                        // คำที่ใช้สำหรับระบบค้นหา (Search Strings)
                        $searchText = implode(' ', [
                            'CUS-' . str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT),
                            $c['first_name'] ?? '',
                            $c['last_name'] ?? '',
                            $c['phone'] ?? '',
                            $c['email'] ?? '',
                            $c['address'] ?? '',
                            $c['username'] ?? '',
                            $c['role'] ?? '',
                        ]);

                        // คุมโทนป้ายสถานะ Role
                        $role = !empty($c['role']) ? $c['role'] : 'Member';
                        $roleBadgeClass = 'bg-stone-100 text-stone-600 border-stone-200';
                        if (strtolower($role) === 'user' || strtolower($role) === 'member') {
                            $roleBadgeClass = 'bg-orange-50 text-orange-700 border-orange-200/80 font-medium';
                        } elseif (strtolower($role) === 'admin') {
                            $roleBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300 font-semibold';
                        }
                    ?>
                        <div class="bg-white rounded-3xl shadow-sm border border-stone-200/80 p-6 flex flex-col hover:shadow-md hover:border-orange-300 transition-all duration-200 admin-search-item group" data-searchable="<?= htmlspecialchars($searchText) ?>">
                            
                            <div class="flex items-start gap-4 mb-5">
                                <div class="w-14 h-14 rounded-2xl <?= $selectedColor ?> border flex items-center justify-center font-bold text-xl tracking-tighter shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                                    <?= htmlspecialchars($initials) ?>
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="text-lg font-bold text-stone-800 leading-tight truncate group-hover:text-orange-600 transition-colors">
                                        <?= htmlspecialchars(trim($c['first_name'] . ' ' . $c['last_name'])) ?: 'ไม่ระบุชื่อ' ?>
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-bold text-orange-600">#CUS-<?= str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT) ?></span>
                                        <span class="text-stone-300">&bull;</span>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] border <?= $roleBadgeClass ?>">
                                            <?= htmlspecialchars(ucfirst($role)) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2.5 mb-6 flex-grow">
                                <div class="flex items-center gap-3 text-sm text-stone-600 bg-stone-50/80 rounded-2xl px-3.5 py-2.5 border border-stone-100/80">
                                    <i class="fas fa-phone text-orange-500 w-4 text-center shrink-0"></i>
                                    <span class="font-bold text-stone-700 truncate"><?= htmlspecialchars($c['phone'] ?? '-') ?></span>
                                </div>

                                <div class="flex items-center gap-3 text-sm text-stone-600 bg-stone-50/80 rounded-2xl px-3.5 py-2.5 border border-stone-100/80">
                                    <i class="fas fa-envelope text-orange-500 w-4 text-center shrink-0"></i>
                                    <span class="font-medium truncate text-xs text-stone-600" title="<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email'] ?? '-') ?></span>
                                </div>

                                <div class="flex items-start gap-3 text-sm text-stone-600 bg-stone-50/80 rounded-2xl px-3.5 py-2.5 border border-stone-100/80">
                                    <i class="fas fa-map-marker-alt text-orange-500 w-4 mt-0.5 text-center shrink-0"></i>
                                    <p class="text-xs text-stone-500 leading-relaxed line-clamp-2" title="<?= htmlspecialchars($c['address'] ?? '') ?>">
                                        <?= !empty(trim($c['address'])) ? htmlspecialchars($c['address']) : '<span class="text-stone-400 italic">ไม่ระบุที่อยู่</span>' ?>
                                    </p>
                                </div>

                                <?php if (!empty($c['username'])): ?>
                                    <div class="flex items-center gap-3 text-xs text-stone-500 px-1 pt-1">
                                        <i class="fas fa-user-circle text-stone-400"></i>
                                        <span>บัญชีผู้ใช้: <strong class="text-stone-700 font-semibold"><?= htmlspecialchars($c['username']) ?></strong></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="border-t border-stone-100 pt-4 mt-auto flex items-center justify-between gap-2.5">
                                <a href="customer_edit.php?id=<?= urlencode($c['customer_id']) ?>" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-stone-800 px-4 py-2 text-xs font-bold text-white hover:bg-orange-600 transition-colors shadow-sm" title="ดูรายละเอียด/แก้ไข">
                                    <i class="fas fa-pencil-alt"></i> แก้ไขข้อมูล
                                </a>
                                <a href="customer_delete.php?id=<?= urlencode($c['customer_id']) ?>" onclick="return confirm('คุณต้องการลบข้อมูลลูกค้า คุณ <?= htmlspecialchars(addslashes($c['first_name'] . ' ' . $c['last_name'])) ?> หรือไม่?')" class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-xl bg-white border border-stone-200 text-stone-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-colors" title="ลบข้อมูล">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>