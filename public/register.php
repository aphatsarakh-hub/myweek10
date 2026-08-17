<?php
require_once __DIR__ . '/includes/boot.php';
$pageTitle = 'สมัครสมาชิก - BamBam Cat Hotel';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($username === '' || $first_name === '' || $last_name === '' || $email === '' || $password === '') {
        flash('message', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
    } else {
        $accountId = register_customer($username, $password, $first_name, $last_name, $email, $phone, $address);
        if ($accountId) {
            $_SESSION['user'] = [
                'id' => $accountId['account_id'],
                'username' => $username,
                'name' => $first_name . ' ' . $last_name,
                'email' => $email,
                'role' => 'customer',
                'customer_id' => $accountId['customer_id'],
                'staff_id' => null,
            ];
            flash('message', 'สมัครสมาชิกสำเร็จ');
            header('Location: ' . url('Customer/booking.php'));
            exit;
        } else {
            flash('message', 'ไม่สามารถสมัครสมาชิกได้ (ชื่อผู้ใช้หรืออีเมลอาจถูกใช้งานแล้ว)');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-orange: #D97736;
            --primary-hover: #BF6326;
            --dark-brown: #4A3022;
            --light-brown: #8C6A4F;
            --bg-white: #FCFBF9;
            --border-color: #E2D7CE;
            --text-dark: #33251D;
            --text-gray: #7A6960;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Sarabun', sans-serif; 
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* ตั้งค่าพื้นหลังเป็นรูปที่ให้มา (เปลี่ยนชื่อไฟล์ให้ตรงกับ path จริงของคุณหากจำเป็น) */
            background-image: url('dc51700716036b9e789b87d7fa8d0f8f.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }

        /* ฟิลเตอร์สีน้ำตาลอ่อนๆ คลุมพื้นหลังนิดหน่อย เพื่อให้กล่องข้อความโดดเด่น แต่ไม่เบลอภาพ */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(74, 48, 34, 0.25);
            z-index: 0;
        }

        /* Container หลัก */
        .auth-container {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            max-width: 1100px;
            background: transparent;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* 🖼️ ฝั่งซ้าย (ข้อความโปรโมทและรีวิว) */
        .auth-info-pane {
            flex: 1.1;
            /* ใช้สีน้ำตาลโปร่งแสง เพื่อให้เห็นพื้นหลังทะลุมาได้แบบเรียบหรู */
            background: linear-gradient(135deg, rgba(74, 48, 34, 0.85) 0%, rgba(42, 27, 18, 0.95) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px;
            color: #F9F6F0;
        }

        .pane-content { position: relative; z-index: 2; }

        .auth-info-pane h1 { 
            font-size: 42px; 
            font-weight: 800; 
            margin-bottom: 15px; 
            line-height: 1.2;
            color: var(--primary-orange);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .auth-info-pane p.subtitle { 
            font-size: 16px; 
            opacity: 0.9; 
            max-width: 380px; 
            line-height: 1.6; 
            font-weight: 300;
        }

        /* กล่องรีวิว */
        .testimonial-box {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 16px;
            max-width: 400px;
        }
        .testimonial-box .stars { color: #F5A623; margin-bottom: 15px; font-size: 14px; }
        .testimonial-box .quote { font-size: 15px; font-weight: 400; line-height: 1.6; margin-bottom: 12px; font-style: italic; }
        .testimonial-box .author { font-size: 14px; color: var(--primary-orange); font-weight: 600; }

        /* 📝 ฝั่งขวา (ฟอร์ม) */
        .auth-form-pane {
            flex: 1;
            padding: 45px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: var(--bg-white);
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header h2 { font-size: 28px; font-weight: 800; color: var(--dark-brown); }
        .form-header p { font-size: 15px; color: var(--text-gray); margin-top: 8px; }

        /* แท็บสลับหน้า */
        .auth-tabs {
            display: flex;
            background: #EFEBE6;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 30px;
        }
        .auth-tabs a {
            flex: 1;
            text-align: center;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-gray);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .auth-tabs a.active { 
            background: var(--dark-brown); 
            color: #fff; 
            box-shadow: 0 4px 10px rgba(74, 48, 34, 0.2); 
        }
        .auth-tabs a:not(.active):hover { color: var(--dark-brown); }

        /* อินพุตฟอร์ม */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 8px;
        }
        .form-group label span.optional { color: #A89F98; font-weight: 400; font-size: 12px; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i.icon-left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-brown);
            font-size: 15px;
        }
        .input-wrapper i.icon-top { top: 16px; transform: none; }
        
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-dark);
            transition: all 0.3s ease;
            background-color: #FFFFFF;
        }
        textarea.form-control { resize: none; padding-top: 14px; }
        .form-control::placeholder { color: #C4BBB4; }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(217, 119, 54, 0.1);
        }

        /* Checkbox เงื่อนไข */
        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 13px;
            color: var(--text-gray);
        }
        .terms-check input { margin-top: 3px; cursor: pointer; accent-color: var(--primary-orange); }
        .terms-check a { color: var(--primary-orange); text-decoration: none; font-weight: 600; }
        .terms-check a:hover { text-decoration: underline; }

        /* ปุ่ม Submit */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-hover) 100%);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(217, 119, 54, 0.25);
            transition: all 0.3s ease;
        }
        .btn-submit:hover { 
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(217, 119, 54, 0.35); 
        }
        .btn-submit:active { transform: scale(0.98); }

        /* Social Login */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: var(--light-brown);
            font-size: 13px;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid var(--border-color); }
        .divider:not(:empty)::before { margin-right: 1em; }
        .divider:not(:empty)::after { margin-left: 1em; }

        .social-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
            color: var(--dark-brown);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-social:hover { 
            background: #F9F6F0; 
            border-color: var(--light-brown);
        }

        /* 📱 โหมดมือถือ (Responsive) */
        @media (max-width: 900px) {
            .auth-container { flex-direction: column; border-radius: 20px; }
            .auth-info-pane { 
                padding: 30px; 
                flex: none;
            }
            .testimonial-box { display: none; }
            .auth-info-pane h1 { font-size: 28px; }
            
            .auth-form-pane { padding: 30px 20px; }
            .form-header h2 { font-size: 24px; }
            
            .name-grid { display: grid; grid-template-columns: 1fr; gap: 0; }
        }

        @media (min-width: 901px) {
            .name-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-info-pane">
            <div class="pane-content">
                <h1>BamBam Cat Hotel</h1>
                <p class="subtitle">ที่พักระดับพรีเมียมสำหรับเพื่อนสี่ขาคนสำคัญของคุณ ให้ทุกการพักผ่อนเต็มไปด้วยความสุขและความใส่ใจในบรรยากาศที่หรูหรา</p>
            </div>
            
            <div class="pane-content testimonial-box">
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="quote">"บริการดีเยี่ยมมากค่ะ น้องแมวชอบมาก ห้องพักสะอาดและเป็นส่วนตัวจริงๆ วัสดุที่ใช้พรีเมียมมาก ไว้จะมาใช้บริการอีกแน่นอน"</p>
                <p class="author">— คุณแม่น้องสำลี</p>
            </div>
        </div>

        <div class="auth-form-pane">
            
            <div class="form-header">
                <h2>Join the Family</h2>
                <p>เริ่มต้นการดูแลที่เหนือระดับเพื่อแมวของคุณ</p>
            </div>

            <div class="auth-tabs">
                <a href="#" class="active">สมัครสมาชิก</a>
                <a href="login.php">เข้าสู่ระบบ</a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div style="background: #FDF1ED; color: #D97736; padding: 12px; border-radius: 8px; font-size: 14px; text-align: center; margin-bottom: 20px; font-weight: 600; border: 1px solid #F7D4C1;">
                    <?= $_SESSION['message'] ?>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>ชื่อผู้ใช้ (Username)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-tag icon-left"></i>
                        <input type="text" name="username" class="form-control" required placeholder="ตั้งชื่อผู้ใช้ของคุณ" />
                    </div>
                </div>

                <div class="name-grid">
                    <div class="form-group">
                        <label>ชื่อ (First Name)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user icon-left"></i>
                            <input type="text" name="first_name" class="form-control" required placeholder="กรอกชื่อ" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>นามสกุล (Last Name)</label>
                        <div class="input-wrapper">
                            <i class="far fa-user icon-left"></i>
                            <input type="text" name="last_name" class="form-control" required placeholder="กรอกนามสกุล" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>อีเมล (Email)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope icon-left"></i>
                        <input type="email" name="email" class="form-control" required placeholder="example@email.com" />
                    </div>
                </div>

                <div class="form-group">
                    <label>รหัสผ่าน (Password)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock icon-left"></i>
                        <input type="password" name="password" class="form-control" required placeholder="อย่างน้อย 8 ตัวอักษร" />
                    </div>
                </div>

                <div class="form-group">
                    <label>เบอร์โทรศัพท์ (Phone) <span class="optional">(ตัวเลือก)</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone-alt icon-left"></i>
                        <input type="text" name="phone" class="form-control" placeholder="08x-xxx-xxxx" />
                    </div>
                </div>

                <div class="form-group">
                    <label>ที่อยู่ (Address) <span class="optional">(ตัวเลือก)</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-home icon-left icon-top"></i>
                        <textarea name="address" class="form-control" rows="2" placeholder="กรอกที่อยู่สำหรับติดต่อ"></textarea>
                    </div>
                </div>

                <label class="terms-check">
                    <input type="checkbox" required>
                    <span>ฉันยอมรับ <a href="#">ข้อกำหนดการใช้งาน</a> และ <a href="#">นโยบายความเป็นส่วนตัว</a> ของโรงแรม</span>
                </label>

                <button type="submit" class="btn-submit">ยืนยันการสมัคร</button>
            </form>

            <div class="divider">หรือสมัครผ่าน</div>
            
            <div class="social-grid">
                <a href="#" class="btn-social">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" width="18" alt="Google"> Google
                </a>
                <a href="#" class="btn-social">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b8/2021_Facebook_icon.svg" width="18" alt="Facebook"> Facebook
                </a>
            </div>

            <p style="text-align: center; margin-top: 30px; font-size: 12px; color: var(--text-gray);">
                © 2024 BamBam Cat Hotel. All rights reserved.
            </p>
        </div>
    </div>

</body>
</html>