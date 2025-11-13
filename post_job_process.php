<?php
/*
 * ไฟล์: /post_job_process.php
 * (เวอร์ชัน "แก้ไขสมบูรณ์" - แก้บั๊ก is_active และ category_id)
 */

session_start();
require 'includes/config.php'; 

// 1. "ยาม" (Role 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit;
}

// 2. เช็ก (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: post_job.php");
    exit;
}

// 3. รับค่า
$shop_id = $_POST['shop_id'];
$job_title = trim($_POST['title']);
$description = trim($_POST['description']);
$wage_per_hour = trim($_POST['wage_per_hour']);

// (เช็กค่าว่าง - แค่ 'ชื่องาน')
if (empty($job_title) || empty($shop_id)) {
    $_SESSION['error'] = "กรุณากรอกชื่องาน";
    header("Location: post_job.php");
    exit;
}

// (แปลงค่าจ้างให้เป็น 'null' ถ้ามัน "ว่าง")
$wage_final = !empty($wage_per_hour) ? $wage_per_hour : null;


// 4. (ขั้นสุดท้าย) INSERT ลง DB
try { 
    
    // VVVVV แก้ไข SQL VVVVV
    // 1. เปลี่ยนคอลัมน์ 'is_active' เป็น 'status' (ตาม DB ของคุณ)
    // 2. เปลี่ยนค่า '1' เป็น 'open' (สถานะเปิดรับ)
    $sql = "INSERT INTO JOBS (shop_id, job_title, description, wage_per_hour, status, category_id) 
            VALUES (?, ?, ?, ?, 'open', 3)"; 
    // ^^^^^ แก้ไข SQL ^^^^^
            
    // Bind Values ยังคงเป็น 4 ค่า ตามจำนวน ?
    $bind_values = [$shop_id, $job_title, $description, $wage_final];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind_values);

    // 5. ดึง Job ID ล่าสุด
    $new_job_id = $pdo->lastInsertId();

    // 6. Redirect ไปหน้าเพิ่มกะงาน (edit_job.php ที่เราสร้างไว้)
    $_SESSION['success'] = "สร้างงานสำเร็จ! กรุณาเพิ่มกะงานที่ต้องการ";
    header("Location: edit_job.php?job_id=" . $new_job_id);
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "สร้างงานล้มเหลว (DB): " . $e->getMessage();
    header("Location: post_job.php");
    exit;
}
?>