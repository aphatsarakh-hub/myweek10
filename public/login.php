<?php
require_once __DIR__ . '/includes/boot.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ (Staff) - BamBam Cat Hotel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', 'IBM Plex Sans Thai', sans-serif;
            /* เปลี่ยน URL ภาพพื้นหลังตรงนี้ได้ตามต้องการ */
            background-image: url('https://images.unsplash.com/photo-1608848461950-0fe51dfc41cb?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-white/20 text-center my-6">
        
        <div class="flex flex-col items-center mb-6">
            <div class="bg-[#e98438] text-white w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-md mb-3">
                <i class="fas fa-paw"></i>
            </div>
            <h2 class="text-xl font-bold text-[#8c52ff] text-neutral-800" style="color: #8b5a2b;">BamBam Cat Hotel</h2>
            <p class="text-xs text-neutral-400 tracking-wider font-semibold uppercase mt-0.5">Admin & Staff Portal</p>
            <h3 class="text-lg font-bold text-neutral-800 mt-4">เข้าสู่ระบบ (Staff)</h3>
        </div>

        <form action="login_process.php" method="POST" class="text-left">
            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">ชื่อผู้ใช้งาน หรือ อีเมล</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-400">
                        <i class="fas fa-user-alt text-sm"></i>
                    </span>
                    <input type="text" name="username" class="w-full pl-9 pr-3 py-2.5 bg-neutral-50/50 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-[#e98438] focus:border-[#e98438] focus:outline-none text-neutral-800 transition text-sm" placeholder="example@bambam.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">รหัสผ่าน</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" class="w-full pl-9 pr-10 py-2.5 bg-neutral-50/50 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-[#e98438] focus:border-[#e98438] focus:outline-none text-neutral-800 transition text-sm" placeholder="••••••••" required>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-neutral-600 transition">
                        <i id="eye-icon" class="fas fa-eye-slash text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between mb-5">
                <label class="flex items-center text-sm text-neutral-600 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-neutral-300 text-[#e98438] focus:ring-[#e98438] mr-2"> 
                    จดจำฉัน
                </label>
                <a href="customer/booking.php?mode=forgot" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">ลืมรหัสผ่าน?</a>
            </div>

            <button type="submit" class="w-full bg-[#e98438] hover:bg-[#d67327] text-white py-3 rounded-xl font-medium shadow-md transition duration-200 text-center block text-base mb-4">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="relative flex py-3 items-center my-2">
            <div class="flex-grow border-t border-neutral-200"></div>
            <span class="flex-shrink mx-4 text-xs text-neutral-400">หรือ</span>
            <div class="flex-grow border-t border-neutral-200"></div>
        </div>

        <div class="text-xs text-center text-neutral-500 space-y-1.5">
            <div>ยังไม่มีบัญชี? <a href="register.php" class="text-[#e98438] font-semibold hover:underline">สมัครสมาชิก</a></div>
            <div><a href="index.php" class="text-neutral-400 hover:text-neutral-600 hover:underline"><i class="fas fa-arrow-left text-[10px] mr-1"></i>กลับหน้าแรก</a></div>
        </div>

        <div class="mt-6 pt-3 border-t border-neutral-100 flex flex-col items-center justify-center text-[11px] text-neutral-400">
            <div class="flex items-center gap-1.5 font-medium mb-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-emerald-600">System Online</span>
            </div>
            <p class="font-mono">Version 2.4.0 (Build 882)</p>
        </div>

    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>
</html>