<?php
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

function siteTitle($pageTitle = 'BamBam Cat Hotel') {
    return htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
}

function flash($name = 'message', $message = null) {
    if ($message !== null) {
        $_SESSION[$name] = $message;
        return;
    }
    if (!empty($_SESSION[$name])) {
        $msg = $_SESSION[$name];
        unset($_SESSION[$name]);
        return $msg;
    }
    return '';
}

function renderAlert($message, $type = 'info') {
    if (!$message) {
        return;
    }
    $type = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
}

function send_password_reset_otp_email($email, $otp, $token) {
    $subject = 'OTP รีเซ็ตรหัสผ่าน BamBam Cat Hotel';
    $resetLink = url('customer_portal.php?mode=verify&token=' . urlencode($token));
    $message = "เรียนผู้ใช้งาน,\n\n" .
               "รหัส OTP สำหรับรีเซ็ตรหัสผ่านของคุณคือ: $otp\n\n" .
               "กรุณากรอกรหัส OTP ในหน้าต่อไปนี้:\n$resetLink\n\n" .
               "รหัสนี้จะหมดอายุภายใน 15 นาที\n\n" .
               "หากคุณไม่ได้ร้องขอการรีเซ็ตรหัสผ่านนี้ กรุณาเพิกเฉยหรือแจ้งผู้ดูแลระบบ\n";
    $headers = 'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" .
               'Content-Type: text/plain; charset=UTF-8\r\n';

    return @mail($email, $subject, $message, $headers);
}

function fetch_roomtypes() {
    global $mysqli;
    $result = $mysqli->query('SELECT * FROM roomtype ORDER BY roomtype_id DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ใช้สำหรับ dropdown "เลือกประเภทห้อง" ตอนจอง — กลุ่มตาม type_name ไม่โชว์ซ้ำเป็นห้องๆ
// (เพราะตอนนี้ roomtype 1 แถว = 1 ห้องจริง ถ้า SELECT ตรงๆจะเห็นชื่อประเภทซ้ำหลายแถว)
function fetch_room_type_names() {
    global $mysqli;
    $result = $mysqli->query(
        'SELECT type_name, MIN(price_per_night) AS price_per_night, MIN(description) AS description,
                COUNT(*) AS total_rooms,
                SUM(status != "Maintenance") AS active_rooms
         FROM roomtype
         GROUP BY type_name
         ORDER BY price_per_night ASC'
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_roomtype_by_id($roomtype_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM roomtype WHERE roomtype_id = ? LIMIT 1');
    $stmt->bind_param('i', $roomtype_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $room = $res->fetch_assoc();
    $stmt->close();
    return $room ?: null;
}

function insert_roomtype($type_name, $description, $price_per_night, $capacity, $room_number = null, $status = 'Available') {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO roomtype (type_name, room_number, description, price_per_night, capacity, status) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssdis', $type_name, $room_number, $description, $price_per_night, $capacity, $status);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function update_roomtype($roomtype_id, $type_name, $description, $price_per_night, $capacity, $room_number = null, $status = 'Available') {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE roomtype SET type_name = ?, room_number = ?, description = ?, price_per_night = ?, capacity = ?, status = ? WHERE roomtype_id = ?');
    $stmt->bind_param('sssdisi', $type_name, $room_number, $description, $price_per_night, $capacity, $status, $roomtype_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function delete_roomtype($roomtype_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('DELETE FROM roomtype WHERE roomtype_id = ?');
    $stmt->bind_param('i', $roomtype_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetch_services() {
    global $mysqli;
    $result = $mysqli->query('SELECT * FROM service ORDER BY service_id DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_service_by_id($service_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM service WHERE service_id = ? LIMIT 1');
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $service = $res->fetch_assoc();
    $stmt->close();
    return $service ?: null;
}

function insert_service($service_name, $service_detail, $price, $status) {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO service (service_name, service_detail, price, status) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssds', $service_name, $service_detail, $price, $status);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function update_service($service_id, $service_name, $service_detail, $price, $status) {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE service SET service_name = ?, service_detail = ?, price = ?, status = ? WHERE service_id = ?');
    $stmt->bind_param('ssdsi', $service_name, $service_detail, $price, $status, $service_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function delete_service($service_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('DELETE FROM service WHERE service_id = ?');
    $stmt->bind_param('i', $service_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetch_customers() {
    global $mysqli;
    $result = $mysqli->query('SELECT customer_id, first_name, last_name, email FROM customer ORDER BY customer_id DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_cats() {
    global $mysqli;
    $result = $mysqli->query('SELECT cat_id, name, breed FROM cat ORDER BY cat_id DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_cats_by_customer($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM cat WHERE customer_id = ? ORDER BY cat_id DESC');
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $cats = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $cats;
}

function fetch_cat_by_id($cat_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM cat WHERE cat_id = ? LIMIT 1');
    $stmt->bind_param('i', $cat_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $cat = $res->fetch_assoc();
    $stmt->close();
    return $cat ?: null;
}

function insert_cat($name, $breed, $age, $gender, $medical_history, $customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO cat (name, breed, age, gender, medical_history, customer_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssissi', $name, $breed, $age, $gender, $medical_history, $customer_id);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function update_cat($cat_id, $name, $breed, $age, $gender, $medical_history) {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE cat SET name = ?, breed = ?, age = ?, gender = ?, medical_history = ? WHERE cat_id = ?');
    $stmt->bind_param('ssissi', $name, $breed, $age, $gender, $medical_history, $cat_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function delete_cat($cat_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('DELETE FROM cat WHERE cat_id = ?');
    $stmt->bind_param('i', $cat_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetch_bookings() {
    global $mysqli;
    $query = 'SELECT b.*, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
              FROM booking b
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              ORDER BY b.booking_id DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_booking_by_id($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT b.*, c.first_name AS customer_first_name, c.last_name AS customer_last_name, c.email AS customer_email, cat.name AS cat_name, cat.breed AS cat_breed, rt.type_name AS roomtype_name, rt.price_per_night AS room_price
        FROM booking b
        LEFT JOIN customer c ON b.customer_id = c.customer_id
        LEFT JOIN cat ON b.cat_id = cat.cat_id
        LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
        WHERE b.booking_id = ?
        LIMIT 1');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $booking = $res->fetch_assoc();
    $stmt->close();
    return $booking ?: null;
}

function fetch_bookings_by_customer($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT b.*, cat.name AS cat_name, rt.type_name AS roomtype_name
        FROM booking b
        LEFT JOIN cat ON b.cat_id = cat.cat_id
        LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
        WHERE b.customer_id = ?
        ORDER BY b.booking_id DESC');
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $bookings = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $bookings;
}

function fetch_booking_summary() {
    global $mysqli;
    $summary = [
        'total' => 0,
        'checkin' => 0,
        'staying' => 0,
        'pending_payment' => 0,
    ];

    $result = $mysqli->query('SELECT COUNT(*) AS total FROM booking');
    if ($result) {
        $row = $result->fetch_assoc();
        $summary['total'] = $row['total'] ?? 0;
    }

    $result = $mysqli->query('SELECT COUNT(*) AS count FROM booking WHERE status = "Pending"');
    if ($result) {
        $row = $result->fetch_assoc();
        $summary['checkin'] = $row['count'] ?? 0;
    }

    $result = $mysqli->query('SELECT COUNT(*) AS count FROM booking WHERE status = "Checked In"');
    if ($result) {
        $row = $result->fetch_assoc();
        $summary['staying'] = $row['count'] ?? 0;
    }

    $result = $mysqli->query('SELECT COUNT(*) AS count FROM booking WHERE status = "Pending"');
    if ($result) {
        $row = $result->fetch_assoc();
        $summary['pending_payment'] = $row['count'] ?? 0;
    }

    return $summary;
}

// รับ $type_name (เช่น "standard", "VIP") ไม่ใช่ roomtype_id ตรงๆ เพราะตอนนี้ roomtype 1 แถว = 1 ห้องจริง
// ระบบจะเลือกห้องว่างของประเภทนี้ในช่วงวันที่ที่จองให้อัตโนมัติ แล้วบันทึก roomtype_id ของห้องนั้นลง booking
// คืนค่า:
//  - int (booking_id) เมื่อจองสำเร็จ
//  - false เมื่อ query ผิดพลาด
//  - 'NO_ROOM_AVAILABLE' เมื่อห้องประเภทนี้เต็มในช่วงวันที่เลือก (ต้อง handle ที่หน้า booking_add.php)
function insert_booking($customer_id, $cat_id, $type_name, $start_date, $end_date, $total_price, $status = 'Pending') {
    global $mysqli;

    $availableRooms = fetch_available_rooms($type_name, $start_date, $end_date);
    if (empty($availableRooms)) {
        return 'NO_ROOM_AVAILABLE';
    }
    $roomtype_id = $availableRooms[0]['roomtype_id'];

    $stmt = $mysqli->prepare('INSERT INTO booking (customer_id, cat_id, roomtype_id, start_date, end_date, total_price, status, booking_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiissds', $customer_id, $cat_id, $roomtype_id, $start_date, $end_date, $total_price, $status);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function calculate_booking_total($start_date, $end_date, $price_per_night) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = max(1, $end->diff($start)->days);
    return number_format($days * $price_per_night, 2, '.', '');
}

function update_booking($booking_id, $customer_id, $cat_id, $roomtype_id, $start_date, $end_date, $total_price, $status) {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE booking SET customer_id = ?, cat_id = ?, roomtype_id = ?, start_date = ?, end_date = ?, total_price = ?, status = ? WHERE booking_id = ?');
    $stmt->bind_param('iiisdssi', $customer_id, $cat_id, $roomtype_id, $start_date, $end_date, $total_price, $status, $booking_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function delete_booking($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('DELETE FROM booking WHERE booking_id = ?');
    $stmt->bind_param('i', $booking_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetch_payments() {
    global $mysqli;
    $query = 'SELECT p.*, b.booking_id, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
              FROM payment p
              LEFT JOIN booking b ON p.booking_id = b.booking_id
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              ORDER BY p.payment_date DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_payments_by_customer($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT p.*, b.booking_id, cat.name AS cat_name, rt.type_name AS roomtype_name
        FROM payment p
        LEFT JOIN booking b ON p.booking_id = b.booking_id
        LEFT JOIN cat ON b.cat_id = cat.cat_id
        LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
        WHERE b.customer_id = ?
        ORDER BY p.payment_date DESC');
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $payments = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

function fetch_payment_by_id($payment_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT p.*, b.booking_id, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
        FROM payment p
        LEFT JOIN booking b ON p.booking_id = b.booking_id
        LEFT JOIN customer c ON b.customer_id = c.customer_id
        LEFT JOIN cat ON b.cat_id = cat.cat_id
        LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
        WHERE p.payment_id = ?
        LIMIT 1');
    $stmt->bind_param('i', $payment_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $payment = $res->fetch_assoc();
    $stmt->close();
    return $payment ?: null;
}

function insert_payment($booking_id, $amount, $payment_method, $status = 'Completed') {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO payment (booking_id, amount, payment_method, status, payment_date) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('idss', $booking_id, $amount, $payment_method, $status);
    $ok = $stmt->execute();

    if (!$ok) {
        die("Booking Error : " . $stmt->error);
    }
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function fetch_checkins() {
    global $mysqli;
    $query = 'SELECT ci.*, b.booking_id, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
              FROM check_in ci
              LEFT JOIN booking b ON ci.booking_id = b.booking_id
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              ORDER BY ci.checkin_date DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insert_checkin($booking_id, $staff_id, $condition_note) {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO check_in (booking_id, staff_id, condition_note, checkin_date) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iis', $booking_id, $staff_id, $condition_note);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function fetch_checkouts() {
    global $mysqli;
    $query = 'SELECT co.*, b.booking_id, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
              FROM check_out co
              LEFT JOIN booking b ON co.booking_id = b.booking_id
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              ORDER BY co.checkout_date DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insert_checkout($booking_id, $staff_id, $additional_charges) {
    global $mysqli;
    $stmt = $mysqli->prepare('INSERT INTO check_out (booking_id, staff_id, additional_charges, checkout_date) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iid', $booking_id, $staff_id, $additional_charges);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

function fetch_bookings_pending_payment() {
    global $mysqli;
    $query = 'SELECT b.*, c.first_name AS customer_first_name, c.last_name AS customer_last_name, cat.name AS cat_name, rt.type_name AS roomtype_name
              FROM booking b
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              WHERE b.status IN ("Pending", "Confirmed")
              ORDER BY b.booking_id DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/*
 |--------------------------------------------------------------------------
 | ROOM — ผูกรวมกับ roomtype (1 แถวใน roomtype = 1 ห้องจริง 1 ห้อง)
 | ไม่มีตารางแยก room_number/status อยู่ใน roomtype เอง
 |--------------------------------------------------------------------------
*/

// ดึงห้องว่างของประเภท (type_name) นี้ ในช่วงวันที่ที่ต้องการ
// ไม่ชนกับการจองอื่นที่ยัง active และห้องต้องไม่ปิด Maintenance
function fetch_available_rooms($type_name, $start_date, $end_date) {
    global $mysqli;
    $stmt = $mysqli->prepare(
        'SELECT rt.roomtype_id, rt.room_number, rt.price_per_night
         FROM roomtype rt
         WHERE rt.type_name = ?
           AND rt.status != "Maintenance"
           AND rt.roomtype_id NOT IN (
               SELECT b.roomtype_id FROM booking b
               WHERE b.roomtype_id IS NOT NULL
                 AND b.status IN ("Pending", "Confirmed", "Checked In")
                 AND b.start_date <= ?
                 AND b.end_date >= ?
           )
         ORDER BY rt.room_number'
    );
    $stmt->bind_param('sss', $type_name, $end_date, $start_date);
    $stmt->execute();
    $res = $stmt->get_result();
    $rooms = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rooms;
}

// ดึงห้องทั้งหมด พร้อมข้อมูลการจอง/ลูกค้า/แมวที่พักอยู่ ณ วันนี้ (สำหรับหน้า room_list.php)
// แยกสถานะ Reserved (จองแล้วแต่ยังไม่เข้าพัก) ออกจาก Occupied (เข้าพักจริงแล้ว)
function fetch_rooms() {
    global $mysqli;
    $today = date('Y-m-d');
    $stmt = $mysqli->prepare(
        'SELECT rt.roomtype_id, rt.room_number, rt.type_name, rt.price_per_night, rt.status AS room_status,
                b.booking_id, b.start_date, b.end_date, b.status AS booking_status,
                c.first_name AS customer_first_name, c.last_name AS customer_last_name, c.phone AS customer_phone,
                cat.name AS cat_name
         FROM roomtype rt
         LEFT JOIN booking b ON b.roomtype_id = rt.roomtype_id
                AND b.status IN ("Confirmed", "Checked In")
                AND ? BETWEEN b.start_date AND b.end_date
         LEFT JOIN customer c ON b.customer_id = c.customer_id
         LEFT JOIN cat ON b.cat_id = cat.cat_id
         ORDER BY rt.room_number'
    );
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_map(function ($row) {
        // room_status คือสถานะทางกายภาพของห้อง (Available/Cleaning/Maintenance)
        // ถ้ามี booking ที่ active วันนี้ และห้องไม่ได้ถูกปิด (Cleaning/Maintenance) ให้แบ่งตาม booking_status:
        //   - Confirmed  (จองไว้แต่ยังไม่มาเช็คอิน) -> Reserved
        //   - Checked In (เข้าพักอยู่จริง)           -> Occupied
        $displayStatus = $row['room_status'];
        if (!empty($row['booking_id']) && $displayStatus === 'Available') {
            $displayStatus = ($row['booking_status'] === 'Checked In') ? 'Occupied' : 'Reserved';
        }

        $customerName = trim(($row['customer_first_name'] ?? '') . ' ' . ($row['customer_last_name'] ?? ''));

        return [
            'room_id'                => $row['roomtype_id'], // ใช้ roomtype_id เป็นรหัสห้องโดยตรง
            'room_number'            => $row['room_number'],
            'roomtype_id'            => $row['roomtype_id'],
            'type_name'              => $row['type_name'],
            'price_per_night'        => $row['price_per_night'],
            'status'                 => $displayStatus,
            'booking_status'         => $row['booking_status'],
            'temperature'            => '24.0°C',
            'booking_id'             => $row['booking_id'],
            'current_cat_name'       => $row['cat_name'],
            'current_customer_name'  => $customerName !== '' ? $customerName : null,
            'current_customer_phone' => $row['customer_phone'] ?? null,
            'checkin_date'           => $row['start_date'],
            'checkout_date'          => $row['end_date'],
        ];
    }, $rows);
}

// ใช้เปลี่ยนสถานะทางกายภาพของห้องเท่านั้น (เช่น mark_clean กด "เสร็จสิ้น" -> Available)
function update_room_status($roomtype_id, $status) {
    global $mysqli;
    $stmt = $mysqli->prepare('UPDATE roomtype SET status = ? WHERE roomtype_id = ?');
    $stmt->bind_param('si', $status, $roomtype_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/*
 |--------------------------------------------------------------------------
 | CANCELLATION — ยกเลิกการจอง (ไม่คืนเงินตามนโยบายโรงแรม)
 | ห้องจะกลับเป็นว่างอัตโนมัติ เพราะ fetch_rooms() join เฉพาะ booking ที่
 | status เป็น Confirmed/Checked In เท่านั้น — ไม่ต้อง UPDATE roomtype แยก
 |--------------------------------------------------------------------------
*/

function cancel_booking($booking_id, $reason = null) {
    global $mysqli;

    $stmt = $mysqli->prepare('SELECT status FROM booking WHERE booking_id = ? LIMIT 1');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        return false; // ไม่พบ booking นี้
    }
    if ($booking['status'] === 'Cancelled') {
        return true; // ยกเลิกไปแล้ว ไม่ต้องทำซ้ำ
    }

    $stmt = $mysqli->prepare('UPDATE booking SET status = "Cancelled" WHERE booking_id = ?');
    $stmt->bind_param('i', $booking_id);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        // บันทึกประวัติการยกเลิก refund_amount = 0 ตามนโยบายไม่คืนเงิน
        $stmt = $mysqli->prepare('INSERT INTO cancellation (booking_id, reason, refund_amount) VALUES (?, ?, 0.00)');
        $stmt->bind_param('is', $booking_id, $reason);
        $stmt->execute();
        $stmt->close();
    }

    return $ok;
}

// ดึงประวัติการยกเลิกทั้งหมด พร้อมข้อมูลลูกค้า/แมว/ห้องที่เกี่ยวข้อง (สำหรับ cancel_list.php)
function fetch_cancellations() {
    global $mysqli;
    $query = 'SELECT cx.cancel_id, cx.booking_id, cx.cancel_date, cx.reason, cx.refund_amount,
                     b.start_date, b.end_date, b.total_price, b.status AS booking_status,
                     c.first_name AS customer_first_name, c.last_name AS customer_last_name, c.phone AS customer_phone,
                     cat.name AS cat_name,
                     rt.type_name AS roomtype_name, rt.room_number
              FROM cancellation cx
              LEFT JOIN booking b ON cx.booking_id = b.booking_id
              LEFT JOIN customer c ON b.customer_id = c.customer_id
              LEFT JOIN cat ON b.cat_id = cat.cat_id
              LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
              ORDER BY cx.cancel_id DESC';
    $result = $mysqli->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ดึงรายละเอียดการยกเลิก 1 รายการ
function fetch_cancellation_by_id($cancel_id) {
    global $mysqli;
    $stmt = $mysqli->prepare(
        'SELECT cx.cancel_id, cx.booking_id, cx.cancel_date, cx.reason, cx.refund_amount,
                b.start_date, b.end_date, b.total_price, b.status AS booking_status,
                c.first_name AS customer_first_name, c.last_name AS customer_last_name, c.phone AS customer_phone,
                cat.name AS cat_name,
                rt.type_name AS roomtype_name, rt.room_number
         FROM cancellation cx
         LEFT JOIN booking b ON cx.booking_id = b.booking_id
         LEFT JOIN customer c ON b.customer_id = c.customer_id
         LEFT JOIN cat ON b.cat_id = cat.cat_id
         LEFT JOIN roomtype rt ON b.roomtype_id = rt.roomtype_id
         WHERE cx.cancel_id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $cancel_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}
/*
 |--------------------------------------------------------------------------
 | SERVICE BOOKING (ลูกค้าจองผ่านออนไลน์) — ใช้ตาราง service เดิม
 | booking_id IS NULL  = บริการในเมนู (template)
 | booking_id NOT NULL = บริการที่ถูกจองจริงแล้ว ผูกกับ booking นั้น
 |--------------------------------------------------------------------------
*/

// บริการในเมนูที่เปิดให้จอง (ไม่ใช่ instance ที่ถูกจองไปแล้ว)
function fetch_active_services() {
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM service WHERE booking_id IS NULL AND status = 'Active' ORDER BY service_id ASC"
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ลูกค้าจองบริการ: copy บริการ template แล้ว insert เป็นแถวใหม่ผูกกับ booking
// คืนค่า service_id ของแถวใหม่ หรือ false
function book_service($template_service_id, $booking_id) {
    global $mysqli;

    $template = fetch_service_by_id($template_service_id);
    if (!$template || $template['status'] !== 'Active' || !empty($template['booking_id'])) {
        return false; // ไม่ใช่ template ที่เปิดให้จอง
    }

    $stmt = $mysqli->prepare(
        'INSERT INTO service (service_name, service_detail, price, status, booking_id)
         VALUES (?, ?, ?, "Booked", ?)'
    );
    $stmt->bind_param('ssdi', $template['service_name'], $template['service_detail'], $template['price'], $booking_id);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

// ยกเลิกบริการที่ลูกค้าจองไว้ (ตรวจสอบว่าเป็นของ booking ตัวเองก่อนเสมอ)
function cancel_service($service_id, $customer_id) {
    global $mysqli;

    $stmt = $mysqli->prepare(
        'SELECT s.service_id, b.customer_id
         FROM service s
         JOIN booking b ON s.booking_id = b.booking_id
         WHERE s.service_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || (int)$row['customer_id'] !== (int)$customer_id) {
        return false; // ไม่ใช่บริการของลูกค้าคนนี้
    }

    $stmt = $mysqli->prepare("UPDATE service SET status = 'Cancelled' WHERE service_id = ? AND status = 'Booked'");
    $stmt->bind_param('i', $service_id);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $ok && $affected > 0;
}

// บริการทั้งหมดที่ลูกค้าคนนี้เคยจอง (ผ่าน booking ของตัวเอง)
function fetch_services_by_customer($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare(
        'SELECT s.*, b.start_date, b.end_date, b.booking_id
         FROM service s
         JOIN booking b ON s.booking_id = b.booking_id
         WHERE b.customer_id = ?
         ORDER BY s.service_id DESC'
    );
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// บริการทั้งหมดของ booking ใบเดียว (ใช้ฝั่งแอดมิน เช่น booking_detail.php)
function fetch_services_by_booking($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM service WHERE booking_id = ? ORDER BY service_id DESC');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
    /*
 |--------------------------------------------------------------------------
 | AUTH HELPER — หา customer_id ของผู้ใช้ที่ login อยู่ตอนนี้
 |--------------------------------------------------------------------------
*/
function current_customer_id() {
    if (!empty($_SESSION['customer_id'])) {
        return (int) $_SESSION['customer_id'];
    }
    $accountId = $_SESSION['account_id'] ?? $_SESSION['user_id'] ?? null;
    if ($accountId) {
        global $mysqli;
        $stmt = $mysqli->prepare('SELECT customer_id FROM account WHERE account_id = ? LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && $row['customer_id']) {
            return (int) $row['customer_id'];
        }
    }
    return null;
}

/*
 |--------------------------------------------------------------------------
 | SERVICE BOOKING — ใช้ตาราง service เดิม
 | booking_id IS NULL  = บริการในเมนู (template)
 | booking_id NOT NULL = บริการที่ถูกจองจริง ผูกกับ booking นั้น
 |--------------------------------------------------------------------------
*/

// บริการในเมนูที่เปิดให้จอง (ไม่รวม instance ที่ถูกจองไปแล้ว)
function fetch_active_services() {
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM service WHERE booking_id IS NULL AND status = 'Active' ORDER BY service_id ASC"
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// ลูกค้าจองบริการ: copy จาก template แล้ว insert แถวใหม่ผูกกับ booking
function book_service($template_service_id, $booking_id) {
    global $mysqli;

    $template = fetch_service_by_id($template_service_id);
    if (!$template || $template['status'] !== 'Active' || !empty($template['booking_id'])) {
        return false;
    }

    $stmt = $mysqli->prepare(
        'INSERT INTO service (service_name, service_detail, price, status, booking_id)
         VALUES (?, ?, ?, "Booked", ?)'
    );
    $stmt->bind_param('ssdi', $template['service_name'], $template['service_detail'], $template['price'], $booking_id);
    $ok = $stmt->execute();
    $id = $ok ? $stmt->insert_id : false;
    $stmt->close();
    return $id;
}

// ยกเลิกบริการที่ลูกค้าจองไว้ (เช็คสิทธิ์ก่อนเสมอว่าเป็นของ customer คนนี้จริง)
function cancel_service($service_id, $customer_id) {
    global $mysqli;

    $stmt = $mysqli->prepare(
        'SELECT s.service_id, b.customer_id
         FROM service s
         JOIN booking b ON s.booking_id = b.booking_id
         WHERE s.service_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || (int)$row['customer_id'] !== (int)$customer_id) {
        return false;
    }

    $stmt = $mysqli->prepare("UPDATE service SET status = 'Cancelled' WHERE service_id = ? AND status = 'Booked'");
    $stmt->bind_param('i', $service_id);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $ok && $affected > 0;
}

// บริการทั้งหมดที่ลูกค้าคนนี้เคยจอง (ทุก booking ของตัวเอง)
function fetch_services_by_customer($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare(
        'SELECT s.*, b.start_date, b.end_date, b.booking_id
         FROM service s
         JOIN booking b ON s.booking_id = b.booking_id
         WHERE b.customer_id = ?
         ORDER BY s.service_id DESC'
    );
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// บริการทั้งหมดที่ผูกกับ booking ใบเดียว
function fetch_services_by_booking($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM service WHERE booking_id = ? ORDER BY service_id DESC");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
/**
 * ==========================================================
 * เพิ่มฟังก์ชันเหล่านี้ใน includes/functions.php
 * (ปรับตาม schema จริงจาก bambam_cat_hotel.sql)
 *
 * ตารางที่เกี่ยวข้อง:
 *   booking(booking_id, customer_id, cat_id, roomtype_id,
 *           booking_date, start_date, end_date, total_price,
 *           status, services)
 *   customer(customer_id, first_name, last_name, phone, email, address)
 *   cat(cat_id, name, breed, age, gender, medical_history, photo, customer_id)
 *   roomtype(roomtype_id, type_name, room_number, description,
 *             price_per_night, capacity, status)
 *   cancellation(cancel_id, booking_id, cancel_date, reason, refund_amount)
 *     -- หมายเหตุ: ตารางนี้ไม่มีคอลัมน์ customer_id
 * ==========================================================
 */

/**
 * ดึงข้อมูลการจอง 1 รายการ พร้อมข้อมูลลูกค้า/แมว/ห้องพัก
 * ใช้ทั้งใน booking_detail.php และ cancel_booking.php
 * cat_id อาจเป็น NULL ได้ จึงใช้ LEFT JOIN กับตาราง cat
 */
function fetch_booking_by_id(int $booking_id): ?array
{
    global $mysqli;

    $sql = "SELECT
                b.booking_id, b.customer_id, b.cat_id, b.roomtype_id,
                b.booking_date, b.start_date, b.end_date,
                b.total_price, b.status, b.services,
                cu.first_name   AS customer_first_name,
                cu.last_name    AS customer_last_name,
                cu.email        AS customer_email,
                cu.phone        AS customer_phone,
                ct.name         AS cat_name,
                ct.breed        AS cat_breed,
                rt.type_name    AS roomtype_name,
                rt.room_number  AS room_number,
                rt.price_per_night
            FROM booking b
            JOIN customer  cu ON cu.customer_id = b.customer_id
            LEFT JOIN cat  ct ON ct.cat_id       = b.cat_id
            JOIN roomtype  rt ON rt.roomtype_id  = b.roomtype_id
            WHERE b.booking_id = ?
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

/**
 * บันทึกคำขอยกเลิกการจอง 1 รายการ
 * - อัปเดต booking.status เป็น Cancelled (เฉพาะของลูกค้าคนนั้น และต้องยัง Pending อยู่)
 * - insert เข้าตาราง cancellation พร้อมเหตุผล
 * ทำใน transaction เดียวกันเพื่อกันข้อมูลไม่ตรงกัน
 *
 * @return bool สำเร็จหรือไม่
 */
function create_cancellation(int $booking_id, int $customer_id, string $reason, float $refund_amount = 0.00): bool
{
    global $mysqli;

    $mysqli->begin_transaction();

    try {
        // อัปเดตสถานะก่อน เพื่อกันการยกเลิกซ้ำ/ยกเลิก booking ที่ไม่ใช่ของตัวเอง
        $stmt1 = $mysqli->prepare(
            "UPDATE booking
             SET status = 'Cancelled'
             WHERE booking_id = ? AND customer_id = ? AND status = 'Pending'"
        );
        $stmt1->bind_param('ii', $booking_id, $customer_id);
        $stmt1->execute();
        $affected = $stmt1->affected_rows;
        $stmt1->close();

        if ($affected === 0) {
            // ไม่ใช่เจ้าของ booking หรือสถานะไม่ใช่ Pending แล้ว (ถูกจัดการไปก่อนหน้านี้)
            $mysqli->rollback();
            return false;
        }

        $stmt2 = $mysqli->prepare(
            "INSERT INTO cancellation (booking_id, reason, refund_amount, cancel_date)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt2->bind_param('isd', $booking_id, $reason, $refund_amount);
        $stmt2->execute();
        $stmt2->close();

        $mysqli->commit();
        return true;
    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('create_cancellation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * ดึงรายการยกเลิกการจองทั้งหมด สำหรับหน้าแอดมิน (cancel_list.php)
 */
function fetch_cancellations(): array
{
    global $mysqli;

    $sql = "SELECT
                c.cancel_id, c.booking_id, c.reason, c.refund_amount, c.cancel_date,
                b.start_date, b.end_date,
                cu.first_name  AS customer_first_name,
                cu.last_name   AS customer_last_name,
                cu.phone       AS customer_phone,
                ct.name        AS cat_name,
                rt.type_name   AS roomtype_name,
                rt.room_number AS room_number
            FROM cancellation c
            JOIN booking   b  ON b.booking_id    = c.booking_id
            JOIN customer  cu ON cu.customer_id  = b.customer_id
            LEFT JOIN cat  ct ON ct.cat_id        = b.cat_id
            JOIN roomtype  rt ON rt.roomtype_id  = b.roomtype_id
            ORDER BY c.cancel_date DESC";

    $result = $mysqli->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}
}

/**
 * ==========================================================
 * ฟังก์ชันเพิ่มเติมสำหรับระบบเช็คอิน (Cashier/checkin.php)
 * ==========================================================
 */

function fetch_customer_by_id($customer_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM customer WHERE customer_id = ? LIMIT 1');
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $customer = $res->fetch_assoc();
    $stmt->close();
    return $customer ?: null;
}

function fetch_payments_by_booking($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT * FROM payment WHERE booking_id = ? ORDER BY payment_date DESC');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $payments = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

function fetch_checkin_by_booking($booking_id) {
    global $mysqli;
    $stmt = $mysqli->prepare(
        'SELECT ci.*, s.first_name AS staff_first_name, s.last_name AS staff_last_name
         FROM check_in ci
         LEFT JOIN staff s ON ci.staff_id = s.staff_id
         WHERE ci.booking_id = ?
         ORDER BY ci.checkin_id DESC
         LIMIT 1'
    );
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $checkin = $res->fetch_assoc();
    $stmt->close();
    return $checkin ?: null;
}

function perform_checkin(int $booking_id, ?int $staff_id, string $condition_note): array {
    global $mysqli;

    $booking = fetch_booking_by_id($booking_id);
    if (!$booking) {
        return ['ok' => false, 'error' => 'ไม่พบข้อมูลการจองนี้ในระบบ', 'checkin_id' => null];
    }
    if ($booking['status'] === 'Cancelled') {
        return ['ok' => false, 'error' => 'การจองนี้ถูกยกเลิกไปแล้ว ไม่สามารถเช็คอินได้', 'checkin_id' => null];
    }
    if ($booking['status'] === 'Checked In') {
        return ['ok' => false, 'error' => 'การจองนี้เช็คอินไปแล้ว', 'checkin_id' => null];
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare('INSERT INTO check_in (booking_id, staff_id, condition_note, checkin_date) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('iis', $booking_id, $staff_id, $condition_note);
        $stmt->execute();
        $checkinId = $stmt->insert_id;
        $stmt->close();

        $stmt2 = $mysqli->prepare('UPDATE booking SET status = "Checked In" WHERE booking_id = ?');
        $stmt2->bind_param('i', $booking_id);
        $stmt2->execute();
        $stmt2->close();

        $mysqli->commit();
        return ['ok' => true, 'error' => null, 'checkin_id' => $checkinId];
    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('perform_checkin failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'เกิดข้อผิดพลาดขณะบันทึกการเช็คอิน กรุณาลองใหม่', 'checkin_id' => null];
    }
}
