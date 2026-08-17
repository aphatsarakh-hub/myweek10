<?php
// เชื่อมฐานข้อมูล MySQL
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bambam_cat_hotel';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
$mysql = $mysqli;
