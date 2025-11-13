<?php
/*
 * ไฟล์: /profile_update_image.php
 * (จัดการการอัปโหลดรูปภาพโปรไฟล์ร้านค้า)
 */

session_start();
require 'includes/config.php'; 

// 1. "ยาม" (Role 2 และ POST method)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit;
}

// 2. รับค่าและไฟล์
$user_id = $_SESSION['user_id'];
$shop_id = $_POST['shop_id'] ?? null;
$file = $_FILES['shop_image'] ?? null;

// 3. ตรวจสอบความปลอดภัยและไฟล์
if (!$shop_id || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ หรือข้อมูลไม่ครบถ้วน";
    header("Location: profile.php"); // ส่งกลับไปหน้าแก้ไขโปรไฟล์
    exit;
}

// 4. การจัดการไฟล์
$target_dir = "uploads/shop_images/"; // <-- โฟลเดอร์ที่ต้องสร้างเอง
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 4.1 ตรวจสอบประเภทไฟล์
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    $_SESSION['error'] = "อัปโหลดได้เฉพาะไฟล์ JPG, PNG, GIF เท่านั้น";
    header("Location: profile.php");
    exit;
}

// 4.2 สร้างชื่อไฟล์ที่ไม่ซ้ำกัน
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_file_name = "shop_" . $shop_id . "_" . time() . "." . $file_extension;
$target_file_path = $target_dir . $new_file_name;
$db_path = $target_dir . $new_file_name; // พาธสำหรับบันทึกลงฐานข้อมูล

// 5. ย้ายไฟล์และอัปเดต DB
try {
    // 5.1 เช็กสิทธิ์ (ป้องกันการแก้ไขร้านค้าของคนอื่น)
    $stmt_check = $pdo->prepare("SELECT shop_id FROM SHOP_PROFILES WHERE shop_id = ? AND user_id = ?");
    $stmt_check->execute([$shop_id, $user_id]);
    if (!$stmt_check->fetch()) {
        $_SESSION['error'] = "คุณไม่มีสิทธิ์แก้ไขร้านค้านี้!";
        header("Location: profile.php");
        exit;
    }

    // 5.2 ย้ายไฟล์จาก temp ไปยังโฟลเดอร์ uploads
    if (!move_uploaded_file($file["tmp_name"], $target_file_path)) {
        $_SESSION['error'] = "ย้ายไฟล์ไม่สำเร็จ กรุณาลองใหม่ (ตรวจสอบสิทธิ์โฟลเดอร์ uploads)";
        header("Location: profile.php");
        exit;
    }
    
    // 5.3 อัปเดตพาธในฐานข้อมูล (ใช้คอลัมน์ image_path)
    $sql = "UPDATE SHOP_PROFILES SET image_path = ? WHERE shop_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$db_path, $shop_id]);

    $_SESSION['success'] = "อัปโหลดและบันทึกรูปโปรไฟล์ร้านค้าสำเร็จ!";

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
}

// 6. ส่งกลับไปหน้าแก้ไขโปรไฟล์
header("Location: profile.php");
exit;
?>