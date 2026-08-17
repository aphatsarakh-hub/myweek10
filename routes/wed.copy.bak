<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "ยินดีเข้าสู่เว็บไซต์ของฉัน <a href='" . route('login'). "'>Login</a>";
});

Route::get('about', function () {
    return "เกี่ยวกับเรา";
});

Route::get('blog/{id}', function ($id) {
    return "บทความทั้งหมด".$id;
});

Route::get('admin/user/da', function () {
    return "<h1>ยินดีต้อนรับ Admin</h1>";
})->name('login');

Route::get('fallback', function () {
    return "ไม่พบหน้า";
});