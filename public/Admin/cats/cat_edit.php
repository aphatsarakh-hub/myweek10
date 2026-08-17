<?php
require_once __DIR__ . '/../../includes/boot.php';

// --- แก้ไขการเชื่อมต่อฐานข้อมูลให้เรียกใช้ได้แน่นอน ---
$db_host = 'localhost';
$db_name = 'bambam_cat_hotel';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ดึงข้อมูลจากฐานข้อมูลจริง
    $stmt = $pdo->prepare("SELECT cat_id, name, breed, age, gender, medical_history, customer_id, photo FROM cat ORDER BY cat_id DESC");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . htmlspecialchars($e->getMessage()));
}

$pageTitle = 'จัดการข้อมูลแมว';
$fallbackPhotos = [
    url('assets/cats/cat_1.png'),
    url('assets/cats/cat_2.png'),
    url('assets/cats/cat_3.png'),
];

include __DIR__ . '/../../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #FFFFFF; font-family: 'Prompt', sans-serif; color: #4A3B2E; }
    .hotel-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .action-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
    .search-input { padding: 8px 16px 8px 40px; width: 240px; border: 1px solid #EDE7DF; border-radius: 8px; font-size: 13px; }
    .btn-icon-add { width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #C05800; color: #FFFFFF; text-decoration: none; }
    .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .cat-card { border: 1px solid #EDE7DF; border-radius: 12px; overflow: hidden; background: #FFFFFF; }
    .img-box { position: relative; width: 100%; height: 190px; background: #FAF8F5; }
    .img-box img { width: 100%; height: 100%; object-fit: cover; }
    .status-badge { position: absolute; top: 12px; left: 12px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500; backdrop-filter: blur(2px); }
    .status-ready { background-color: rgba(255,255,255,0.9); color: #B26A00; border: 1px solid #F0D9B5; }
    .status-wait { background-color: rgba(255,255,255,0.9); color: #9C4700; border: 1px solid #E8C4A0; }
    .status-adopted { background-color: rgba(255,255,255,0.9); color: #8A5A3B; border: 1px solid #E5D9CB; }
    .card-info-block { padding: 16px; border-top: 1px solid #F3EEE7; }
    .action-buttons-group { display: flex; gap: 8px; margin-top: 12px; }
    .btn-action-circle { width: 30px; height: 3