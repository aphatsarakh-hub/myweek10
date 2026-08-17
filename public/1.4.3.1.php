<?php
require_once __DIR__ . '/includes/boot.php';
require_login();
$pageTitle = '1.4.3.1 จองห้องพักสำหรับน้องแมว';
$customer_id = $_SESSION['user']['customer_id'] ?? null;
$customer_name = $_SESSION['user']['name'] ?? 'สมาชิก';
$roomtypes = fetch_roomtypes();
$bookings = fetch_bookings_by_customer($customer_id);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_name = trim($_POST['cat_name'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $roomtype_id = intval($_POST['roomtype_id'] ?? 0);
    $medical_history = trim($_POST['medical_history'] ?? '');
    $room = fetch_roomtype_by_id($roomtype_id);

    if ($cat_name === '' || $breed === '' || $age <= 0 || $gender === '' || !$start_date || !$end_date || !$room) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน และเลือกประเภทห้องพัก';
    } elseif ($start_date > $end_date) {
        $error = 'วันที่สิ้นสุดต้องอยู่หลังหรือเท่ากับวันที่เริ่มต้น';
    } else {
        $cat_id = insert_cat($cat_name, $breed, $age, $gender, $medical_history, $customer_id);
        if ($cat_id) {
            $total_price = calculate_booking_total($start_date, $end_date, $room['price_per_night']);
            $booking_id = insert_booking($customer_id, $cat_id, $roomtype_id, $start_date, $end_date, $total_price);
            if ($booking_id) {
                $success = 'การจองสำเร็จแล้ว! อ่านรายละเอียดได้ที่ด้านล่าง';
                $bookings = fetch_bookings_by_customer($customer_id);
            } else {
                $error = 'บันทึกการจองไม่สำเร็จ';
            }
        } else {
            $error = 'ไม่สามารถบันทึกข้อมูลน้องแมวได้';
        }
    }
}
include __DIR__ . '/includes/header.php';

function render_template($template_path, $placeholders = []) {
    $template = file_get_contents($template_path);
    if ($template === false) {
        return 'Template loading error.';
    }
    return str_replace(array_keys($placeholders), array_values($placeholders), $template);
}

$room_options = '';
foreach ($roomtypes as $room) {
    $selected = ((int)($_POST['roomtype_id'] ?? 0) === $room['roomtype_id']) ? ' selected' : '';
    $room_options .= '<option value="' . $room['roomtype_id'] . '" data-price="' . htmlspecialchars($room['price_per_night']) . '" data-capacity="' . htmlspecialchars($room['capacity']) . '"' . $selected . '>' . htmlspecialchars($room['type_name'] . ' - ' . number_format($room['price_per_night'], 2) . ' ฿') . '</option>';
}

$room_rows = '';
foreach ($roomtypes as $room) {
    $room_rows .= '<tr>' .
        '<td>' . htmlspecialchars($room['type_name']) . '</td>' .
        '<td>' . htmlspecialchars($room['description']) . '</td>' .
        '<td>' . htmlspecialchars(number_format($room['price_per_night'], 2)) . ' ฿</td>' .
        '<td>' . htmlspecialchars($room['capacity']) . ' คน</td>' .
        '</tr>';
}

$booking_history = '';
if (empty($bookings)) {
    $booking_history = '<p>ยังไม่มีประวัติการเข้าพัก</p>';
} else {
    $booking_history = '<div class="table-responsive"><table><thead><tr><th>#</th><th>ชื่อแมว</th><th>ห้อง</th><th>วันที่</th><th>ยอดรวม</th><th>สถานะ</th></tr></thead><tbody>';
    foreach ($bookings as $booking) {
        $booking_history .= '<tr>' .
            '<td>' . htmlspecialchars($booking['booking_id']) . '</td>' .
            '<td>' . htmlspecialchars($booking['cat_name']) . '</td>' .
            '<td>' . htmlspecialchars($booking['roomtype_name']) . '</td>' .
            '<td>' . htmlspecialchars($booking['start_date'] . ' ถึง ' . $booking['end_date']) . '</td>' .
            '<td>' . htmlspecialchars(number_format($booking['total_price'], 2)) . ' ฿</td>' .
            '<td>' . htmlspecialchars($booking['status']) . '</td>' .
            '</tr>';
    }
    $booking_history .= '</tbody></table></div>';
}

$alerts = '';
if ($error) {
    $alerts .= '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>';
}
if ($success) {
    $alerts .= '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
}

$template_path = __DIR__ . '/templates/booking_page.html';
$placeholders = [
    '{{page_title}}' => 'ยินดีต้อนรับ, ' . htmlspecialchars($customer_name),
    '{{alerts}}' => $alerts,
    '{{cat_name}}' => htmlspecialchars($_POST['cat_name'] ?? ''),
    '{{breed}}' => htmlspecialchars($_POST['breed'] ?? ''),
    '{{age}}' => htmlspecialchars($_POST['age'] ?? ''),
    '{{gender_male}}' => (($_POST['gender'] ?? '') === 'ชาย') ? ' selected' : '',
    '{{gender_female}}' => (($_POST['gender'] ?? '') === 'หญิง') ? ' selected' : '',
    '{{start_date}}' => htmlspecialchars($_POST['start_date'] ?? ''),
    '{{end_date}}' => htmlspecialchars($_POST['end_date'] ?? ''),
    '{{room_options}}' => $room_options,
    '{{medical_history}}' => htmlspecialchars($_POST['medical_history'] ?? ''),
    '{{room_rows}}' => $room_rows,
    '{{booking_history}}' => $booking_history,
    '{{script_src}}' => BASE_URL . '/assets/app.js',
];

echo render_template($template_path, $placeholders);
?>

<?php include __DIR__ . '/includes/footer.php'; ?>
