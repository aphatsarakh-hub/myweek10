<?php
require_once __DIR__ . '/../includes/boot.php';
$pageTitle = 'รายการการจอง (Cashier)';
$bookings = fetch_bookings();
include __DIR__ . '/../includes/header.php';

// --- สรุปตัวเลขสำหรับแถบสถิติด้านบน ---
$totalCount     = count($bookings);
$pendingCount   = 0;
$confirmedCount = 0;
$todayRevenue   = 0.0;
$today          = date('Y-m-d');

foreach ($bookings as $b) {
    if ($b['status'] === 'Pending')   $pendingCount++;
    if ($b['status'] === 'Confirmed') $confirmedCount++;
    if (isset($b['start_date']) && $b['start_date'] === $today) {
        $todayRevenue += (float) $b['total_price'];
    }
}
?>

<style>
  .cb-page{ --cb-bg:#F6F8F8; --cb-surface:#FFFFFF; --cb-primary:#2B6777;
    --cb-primary-dark:#1C4A56; --cb-accent:#E0A458; --cb-success:#2E9E5B;
    --cb-success-bg:#E7F5EC; --cb-warning:#C8862A; --cb-warning-bg:#FBF0DE;
    --cb-danger:#C4453F; --cb-danger-bg:#FBEAE9; --cb-text:#1E2A2E;
    --cb-text-muted:#6C7C80; --cb-border:#E2E8E9; --cb-radius:12px; }

  .cb-page{ font-family:'Noto Sans Thai',system-ui,sans-serif; color:var(--cb-text); }
  .cb-page h1{ font-family:'Noto Serif Thai',serif; font-weight:700;
    letter-spacing:.01em; margin:0 0 4px; font-size:1.7rem; }
  .cb-page .page-title p{ color:var(--cb-text-muted); margin:0; font-size:.95rem; }
  .cb-mono{ font-family:'IBM Plex Mono',ui-monospace,monospace; font-variant-numeric:tabular-nums; }

  /* --- แถบสถิติ --- */
  .cb-stats{ display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:20px 0; }
  .cb-stat{ background:var(--cb-surface); border:1px solid var(--cb-border);
    border-radius:var(--cb-radius); padding:16px 18px; }
  .cb-stat .cb-stat-label{ font-size:.8rem; color:var(--cb-text-muted); margin-bottom:6px; }
  .cb-stat .cb-stat-value{ font-size:1.5rem; font-weight:600; }
  .cb-stat.cb-accent-primary .cb-stat-value{ color:var(--cb-primary-dark); }
  .cb-stat.cb-accent-warning .cb-stat-value{ color:var(--cb-warning); }
  .cb-stat.cb-accent-success .cb-stat-value{ color:var(--cb-success); }
  .cb-stat.cb-accent-accent .cb-stat-value{ color:var(--cb-primary); }

  /* --- แถบค้นหา/กรอง --- */
  .cb-toolbar{ display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:14px 18px;
    border-bottom:1px solid var(--cb-border); }
  .cb-search{ flex:1; min-width:200px; position:relative; }
  .cb-search input{ width:100%; padding:9px 14px 9px 34px; border:1px solid var(--cb-border);
    border-radius:8px; font-family:inherit; font-size:.92rem; background:var(--cb-bg); }
  .cb-search input:focus{ outline:2px solid var(--cb-primary); outline-offset:1px; background:#fff; }
  .cb-search::before{ content:"⚲"; position:absolute; left:12px; top:50%; transform:translateY(-50%) rotate(45deg);
    color:var(--cb-text-muted); font-size:.9rem; }
  .cb-filters{ display:flex; gap:6px; flex-wrap:wrap; }
  .cb-filter-btn{ border:1px solid var(--cb-border); background:var(--cb-surface); color:var(--cb-text-muted);
    padding:7px 14px; border-radius:999px; font-size:.85rem; cursor:pointer; font-family:inherit; }
  .cb-filter-btn.is-active{ background:var(--cb-primary); border-color:var(--cb-primary); color:#fff; }

  /* --- ตาราง --- */
  .cb-page table{ width:100%; border-collapse:collapse; }
  .cb-page thead th{ text-align:left; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;
    color:var(--cb-text-muted); padding:10px 18px; background:var(--cb-bg); border-bottom:1px solid var(--cb-border);
    position:sticky; top:0; }
  .cb-page tbody td{ padding:13px 18px; border-bottom:1px solid var(--cb-border); font-size:.92rem; vertical-align:middle; }
  .cb-page tbody tr:hover{ background:#FAFBFB; }
  .cb-page tbody tr:last-child td{ border-bottom:none; }

  .cb-customer{ display:flex; align-items:center; gap:10px; }
  .cb-avatar{ width:32px; height:32px; border-radius:50%; background:var(--cb-primary); color:#fff;
    display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:600; flex-shrink:0; }
  .cb-cat{ color:var(--cb-text-muted); font-size:.85rem; }

  /* --- ป้ายสถานะทรงห้อยคอ (signature) --- */
  .cb-tag{ position:relative; display:inline-flex; align-items:center; gap:6px; padding:4px 12px 4px 16px;
    border-radius:6px; font-size:.8rem; font-weight:600; }
  .cb-tag::before{ content:""; position:absolute; left:6px; top:50%; transform:translateY(-50%);
    width:5px; height:5px; border-radius:50%; background:currentColor; opacity:.55; }
  .cb-tag-success{ background:var(--cb-success-bg); color:var(--cb-success); }
  .cb-tag-warning{ background:var(--cb-warning-bg); color:var(--cb-warning); }
  .cb-tag-danger{ background:var(--cb-danger-bg); color:var(--cb-danger); }

  .cb-actions{ display:flex; gap:6px; flex-wrap:wrap; }
  .cb-btn{ display:inline-block; padding:6px 12px; border-radius:7px; font-size:.82rem; font-weight:500;
    text-decoration:none; border:1px solid transparent; white-space:nowrap; }
  .cb-btn-ghost{ border-color:var(--cb-border); color:var(--cb-text); background:var(--cb-surface); }
  .cb-btn-ghost:hover{ background:var(--cb-bg); }
  .cb-btn-primary{ background:var(--cb-accent); color:#fff; }
  .cb-btn-primary:hover{ background:#C98C3A; }

  .cb-empty{ text-align:center; padding:56px 20px; color:var(--cb-text-muted); }
  .cb-empty .cb-empty-icon{ font-size:2rem; margin-bottom:10px; }

  @media (max-width:720px){
    .cb-stats{ grid-template-columns:repeat(2,1fr); }
    .cb-page table thead{ display:none; }
    .cb-page table, .cb-page tbody, .cb-page tr, .cb-page td{ display:block; width:100%; }
    .cb-page tbody tr{ border:1px solid var(--cb-border); border-radius:var(--cb-radius); margin-bottom:12px; padding:6px 0; }
    .cb-page tbody td{ border-bottom:none; padding:6px 18px; display:flex; justify-content:space-between; gap:12px; text-align:right; }
    .cb-page tbody td::before{ content:attr(data-label); color:var(--cb-text-muted); font-size:.78rem; text-align:left; }
    .cb-page tbody td.cb-cell-customer, .cb-page tbody td.cb-cell-actions{ display:block; text-align:left; }
    .cb-page tbody td.cb-cell-customer::before, .cb-page tbody td.cb-cell-actions::before{ display:block; margin-bottom:6px; }
  }
</style>

<div class="cb-page">

  <div class="page-title">
    <div>
      <h1>รายการการจอง</h1>
      <p>ดูและจัดการการจองสำหรับลูกค้า</p>
    </div>
  </div>

  <div class="cb-stats">
    <div class="cb-stat cb-accent-primary">
      <div class="cb-stat-label">การจองทั้งหมด</div>
      <div class="cb-stat-value cb-mono"><?php echo $totalCount; ?></div>
    </div>
    <div class="cb-stat cb-accent-warning">
      <div class="cb-stat-label">รอดำเนินการ</div>
      <div class="cb-stat-value cb-mono"><?php echo $pendingCount; ?></div>
    </div>
    <div class="cb-stat cb-accent-success">
      <div class="cb-stat-label">ยืนยันแล้ว</div>
      <div class="cb-stat-value cb-mono"><?php echo $confirmedCount; ?></div>
    </div>
    <div class="cb-stat cb-accent-accent">
      <div class="cb-stat-label">ยอดรับวันนี้</div>
      <div class="cb-stat-value cb-mono">฿<?php echo number_format($todayRevenue, 2); ?></div>
    </div>
  </div>

  <div class="card table-responsive" style="padding:0;">
    <?php if (empty($bookings)): ?>
      <div class="cb-empty">
        <div class="cb-empty-icon">🐾</div>
        <p>ยังไม่มีการจองในระบบ เมื่อมีลูกค้าจอง รายการจะแสดงที่นี่</p>
      </div>
    <?php else: ?>

      <div class="cb-toolbar">
        <div class="cb-search">
          <input type="text" id="cbSearch" placeholder="ค้นหาชื่อลูกค้าหรือชื่อแมว...">
        </div>
        <div class="cb-filters" id="cbFilters">
          <button type="button" class="cb-filter-btn is-active" data-filter="all">ทั้งหมด</button>
          <button type="button" class="cb-filter-btn" data-filter="Pending">รอดำเนินการ</button>
          <button type="button" class="cb-filter-btn" data-filter="Confirmed">ยืนยันแล้ว</button>
          <button type="button" class="cb-filter-btn" data-filter="Cancelled">ยกเลิก</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>ลูกค้า</th>
            <th>ห้องพัก</th>
            <th>วันที่</th>
            <th>ราคารวม</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody id="cbTableBody">
          <?php foreach ($bookings as $booking):
            $customerName = trim($booking['customer_first_name'] . ' ' . $booking['customer_last_name']);
            $initial = mb_substr($customerName !== '' ? $customerName : '?', 0, 1, 'UTF-8');
            $status = $booking['status'];
            $tagClass = $status === 'Confirmed' ? 'cb-tag-success' : ($status === 'Pending' ? 'cb-tag-warning' : 'cb-tag-danger');
            $statusLabel = $status === 'Confirmed' ? 'ยืนยันแล้ว' : ($status === 'Pending' ? 'รอดำเนินการ' : 'ยกเลิก');
          ?>
            <tr data-status="<?php echo htmlspecialchars($status); ?>" data-search="<?php echo htmlspecialchars(mb_strtolower($customerName . ' ' . $booking['cat_name'])); ?>">
              <td data-label="#" class="cb-mono"><?php echo htmlspecialchars($booking['booking_id']); ?></td>
              <td data-label="ลูกค้า" class="cb-cell-customer">
                <div class="cb-customer">
                  <div class="cb-avatar"><?php echo htmlspecialchars($initial); ?></div>
                  <div>
                    <div><?php echo htmlspecialchars($customerName); ?></div>
                    <div class="cb-cat">🐱 <?php echo htmlspecialchars($booking['cat_name']); ?></div>
                  </div>
                </div>
              </td>
              <td data-label="ห้องพัก"><?php echo htmlspecialchars($booking['roomtype_name']); ?></td>
              <td data-label="วันที่"><?php echo htmlspecialchars($booking['start_date'] . ' ถึง ' . $booking['end_date']); ?></td>
              <td data-label="ราคารวม" class="cb-mono">฿<?php echo htmlspecialchars(number_format($booking['total_price'], 2)); ?></td>
              <td data-label="สถานะ">
                <span class="cb-tag <?php echo $tagClass; ?>"><?php echo $statusLabel; ?></span>
              </td>
              <td data-label="จัดการ" class="cb-cell-actions">
                <div class="cb-actions">
                  <a href="booking_detail.php?id=<?php echo $booking['booking_id']; ?>" class="cb-btn cb-btn-ghost">ดู</a>
                  <a href="payment.php?id=<?php echo $booking['booking_id']; ?>" class="cb-btn cb-btn-primary">ชำระเงิน</a>
                  <a href="checkin.php?id=<?php echo $booking['booking_id']; ?>" class="cb-btn cb-btn-ghost">เช็คอิน</a>
                  <a href="checkout.php?id=<?php echo $booking['booking_id']; ?>" class="cb-btn cb-btn-ghost">เช็คเอาท์</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@600;700&family=Noto+Sans+Thai:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">

<script>
(function () {
  var searchInput = document.getElementById('cbSearch');
  var filterButtons = document.querySelectorAll('.cb-filter-btn');
  var rows = document.querySelectorAll('#cbTableBody tr');
  var activeFilter = 'all';

  function applyFilters() {
    var term = (searchInput ? searchInput.value : '').trim().toLowerCase();
    rows.forEach(function (row) {
      var matchesStatus = activeFilter === 'all' || row.getAttribute('data-status') === activeFilter;
      var matchesSearch = !term || row.getAttribute('data-search').indexOf(term) !== -1;
      row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);

  filterButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterButtons.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      activeFilter = btn.getAttribute('data-filter');
      applyFilters();
    });
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>