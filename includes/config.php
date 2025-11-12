<?php

$db_host = "localhost";      // ส่วนใหญ่คือ localhost
$db_name = "job_match_db";   // ชื่อฐานข้อมูล
$db_user = "root";           // username ของ XAMPP
$db_pass = "";               // password ของ XAMPP
// ---------------------------------------------------
date_default_timezone_set('Asia/Bangkok');
// ตั้งค่า DSN และ Options
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ลองเชื่อมต่อ
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // ถ้าเชื่อมไม่ได้ -> หยุดการทำงานทันที
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e.getMessage());
}

// ถ้ามาถึงตรงนี้ได้ -> $pdo พร้อมใช้งาน
?>