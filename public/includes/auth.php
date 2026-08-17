<?php
require_once __DIR__ . '/db.php';

function find_account_by_username_or_email($identifier) {
    global $mysql;
    $stmt = $mysql->prepare(
        'SELECT a.account_id, a.username, a.password, a.role, a.customer_id, a.staff_id,
                a.first_name AS admin_first_name, a.last_name AS admin_last_name,
                c.customer_id, c.first_name, c.last_name, c.email AS email, c.phone, c.address,
                s.staff_id AS staff_id, s.first_name AS staff_first_name, s.last_name AS staff_last_name, s.position
         FROM account a
         LEFT JOIN customer c ON a.customer_id = c.customer_id
         LEFT JOIN staff s ON a.staff_id = s.staff_id
         WHERE a.username = ? OR c.email = ?
         LIMIT 1'
    );
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $res = $stmt->get_result();
    $account = $res->fetch_assoc();
    $stmt->close();
    return $account ?: null;
}

function username_exists($username) {
    global $mysql;
    $stmt = $mysql->prepare('SELECT 1 FROM account WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res->fetch_row() ? true : false;
    $stmt->close();
    return $exists;
}

function email_exists($email) {
    global $mysql;
    $stmt = $mysql->prepare(
        'SELECT 1 FROM account a
         LEFT JOIN customer c ON a.customer_id = c.customer_id
         WHERE c.email = ?
         LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res->fetch_row() ? true : false;
    $stmt->close();
    return $exists;
}

function cleanup_expired_password_reset_requests() {
    global $mysql;
    $stmt = $mysql->prepare("UPDATE password_reset_requests SET status = 'expired' WHERE status IN ('pending', 'verified') AND expires_at < NOW()");
    $stmt->execute();
    $stmt->close();
}

function create_password_reset_request($account_id, $email) {
    global $mysql;
    cleanup_expired_password_reset_requests();

    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 15 * 60);

    $stmt = $mysql->prepare('INSERT INTO password_reset_requests (account_id, email, otp, token, expires_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issss', $account_id, $email, $otp, $token, $expires_at);
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        return false;
    }
    $request_id = $stmt->insert_id;
    $stmt->close();

    return [
        'request_id' => $request_id,
        'account_id' => $account_id,
        'email' => $email,
        'otp' => $otp,
        'token' => $token,
        'expires_at' => $expires_at,
    ];
}

function fetch_password_reset_request_by_token($token) {
    global $mysql;
    cleanup_expired_password_reset_requests();
    $stmt = $mysql->prepare('SELECT * FROM password_reset_requests WHERE token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $request = $res->fetch_assoc();
    $stmt->close();
    return $request ?: null;
}

function verify_password_reset_otp($request_id, $otp) {
    global $mysql;
    cleanup_expired_password_reset_requests();

    $stmt = $mysql->prepare('SELECT otp, status, expires_at FROM password_reset_requests WHERE request_id = ? LIMIT 1');
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $request = $res->fetch_assoc();
    $stmt->close();

    if (!$request || $request['status'] !== 'pending') {
        return false;
    }

    if (strtotime($request['expires_at']) < time()) {
        expire_password_reset_request($request_id);
        return false;
    }

    if (!hash_equals($request['otp'], $otp)) {
        return false;
    }

    $stmt = $mysql->prepare("UPDATE password_reset_requests SET status = 'verified' WHERE request_id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $stmt->close();

    return true;
}

function expire_password_reset_request($request_id) {
    global $mysql;
    $stmt = $mysql->prepare("UPDATE password_reset_requests SET status = 'expired' WHERE request_id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $stmt->close();
}

function complete_password_reset_request($request_id) {
    global $mysql;
    $stmt = $mysql->prepare("UPDATE password_reset_requests SET status = 'completed' WHERE request_id = ?");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $stmt->close();
}

function register_customer($username, $password, $first_name, $last_name, $email, $phone = null, $address = '') {
    global $mysql;
    if (username_exists($username) || email_exists($email)) {
        return false;
    }

    $stmt = $mysql->prepare('INSERT INTO customer (first_name, last_name, email, phone, address) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $first_name, $last_name, $email, $phone, $address);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $customer_id = $stmt->insert_id;
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysql->prepare('INSERT INTO account (username, password, role, customer_id) VALUES (?, ?, ?, ?)');
    $role = 'customer';
    $stmt->bind_param('sssi', $username, $hash, $role, $customer_id);
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        $mysql->query('DELETE FROM customer WHERE customer_id = ' . (int)$customer_id);
        return false;
    }
    $account_id = $stmt->insert_id;
    $stmt->close();
    return ['account_id' => $account_id, 'customer_id' => $customer_id];
}

function verify_account($identifier, $password) {
    global $mysql;
    $account = find_account_by_username_or_email($identifier);
    if (!$account) {
        return false;
    }

    $storedPassword = $account['password'];
    // Trim accidental trailing newlines/spaces from stored passwords (fixes imports/entry artifacts)
    if (is_string($storedPassword)) {
        $storedPassword = trim($storedPassword);
    }
    $passwordInfo = password_get_info($storedPassword);
    $isHashed = isset($passwordInfo['algo']) && $passwordInfo['algo'] !== 0;
    $valid = false;

    if ($isHashed) {
        $valid = password_verify($password, $storedPassword);
    } else {
        $valid = $password === $storedPassword;
    }

    if (!$valid) {
        return false;
    }

    if (!$isHashed) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysql->prepare('UPDATE account SET password = ? WHERE account_id = ?');
        $stmt->bind_param('si', $newHash, $account['account_id']);
        $stmt->execute();
        $stmt->close();
    }

    $nameParts = [];
    if ($account['role'] === 'admin') {
        // admin แยกขาดจากตาราง staff แล้ว: ใช้ชื่อของตัวเองที่เก็บใน account โดยตรง
        $nameParts[] = trim(($account['admin_first_name'] ?? '') . ' ' . ($account['admin_last_name'] ?? ''));
    } elseif (!empty($account['customer_id'])) {
        $nameParts[] = trim($account['first_name'] . ' ' . $account['last_name']);
    } elseif (!empty($account['staff_id'])) {
        $nameParts[] = trim($account['staff_first_name'] . ' ' . $account['staff_last_name']);
    }
    $name = trim(implode(' ', array_filter($nameParts)));
    if ($name === '') {
        $name = $account['username'];
    }

    return [
        'id' => $account['account_id'],
        'username' => $account['username'],
        'role' => $account['role'],
        'customer_id' => $account['customer_id'],
        'staff_id' => $account['staff_id'],
        'name' => $name,
        'email' => $account['email'] ?? null,
    ];
}

function require_login() {
    if (empty($_SESSION['user'])) {
        flash('message', 'กรุณาเข้าสู่ระบบก่อนดำเนินการ');
        header('Location: ' . url('login.php'));
        exit;
    }
}

function require_role($roles) {
    if (is_string($roles)) {
        $roles = [$roles];
    }
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles, true)) {
        flash('message', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        header('Location: ' . url('login.php'));
        exit;
    }
}