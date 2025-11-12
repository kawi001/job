<?php
/*
 * ไฟล์: /update_application_status.php
 * หน้าที่: "สมอง" (ไม่มีหน้าตา) รับคำสั่ง Approve/Reject
 */

// 1. เรียก "ยาม" และ "สะพาน"
session_start();
require 'includes/config.php';

// 2. "ยาม" (Role 2 เท่านั้น)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    // (ถ้าไม่ใช่ Role 2 ไล่กลับ)
    header("Location: dashboard.php");
    exit;
}

// 3. (สำคัญ!) รับค่าจาก URL
// (เช่น ...?app_id=5&status=approved)
if (!isset($_GET['app_id']) || !isset($_GET['status'])) {
    $_SESSION['error'] = "ข้อมูลไม่ครบถ้วน";
    header("Location: my_jobs.php"); // ไล่กลับไปหน้าหลักของร้าน
    exit;
}

$application_id = $_GET['app_id'];
$new_status = $_GET['status']; // (เช่น 'approved' หรือ 'rejected')

// 4. (กันยิงมั่ว) เช็กว่าค่า status ถูกต้อง
if ($new_status != 'approved' && $new_status != 'rejected') {
    $_SESSION['error'] = "สถานะไม่ถูกต้อง";
    header("Location: my_jobs.php");
    exit;
}

// 5. (สำคัญ!) "ยาม" ชั้นที่ 2: เช็กว่าร้านนี้ "เป็นเจ้าของ" ใบสมัครนี้จริง
$user_id = $_SESSION['user_id'];
$job_id_to_return = null; // (สร้างตัวแปรไว้ส่งกลับ)

try {
    // 5.1 หา shop_id ของร้านที่ล็อกอินอยู่
    $stmt_shop = $pdo->prepare("SELECT shop_id FROM SHOP_PROFILES WHERE user_id = ?");
    $stmt_shop->execute([$user_id]);
    $shop = $stmt_shop->fetch();
    $my_shop_id = $shop['shop_id'];

    // 5.2 หาว่า application_id นี้... เป็นของ shop_id นี้หรือไม่?
    // (JOIN APPLICATIONS -> JOBS)
    $sql_check = "SELECT J.shop_id, J.job_id
                  FROM APPLICATIONS AS A
                  JOIN JOBS AS J ON A.job_id = J.job_id
                  WHERE A.application_id = ?";
    
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$application_id]);
    $app_data = $stmt_check->fetch();

    if (!$app_data || $app_data['shop_id'] != $my_shop_id) {
        // (ถ้าหาไม่เจอ หรือ shop_id ไม่ตรง)
        $_SESSION['error'] = "คุณไม่ใช่เจ้าของใบสมัครนี้";
        header("Location: my_jobs.php");
        exit;
    }
    
    // (ถ้าผ่าน) เก็บ job_id ไว้ใช้ส่งกลับ
    $job_id_to_return = $app_data['job_id'];

} catch (Exception $e) {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์: " . $e.getMessage();
    header("Location: my_jobs.php");
    exit;
}


// 6. (ขั้นสุดท้าย) ถ้าสิทธิ์ถูกต้อง + ทุกอย่าง OK -> UPDATE สถานะ
try {
    $sql_update = "UPDATE APPLICATIONS SET status = ? WHERE application_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_status, $application_id]);

    // (สำเร็จ!)
    $_SESSION['success'] = "อัปเดตสถานะใบสมัครเรียบร้อยแล้ว!";

} catch (Exception $e) {
    $_SESSION['error'] = "อัปเดตสถานะล้มเหลว: " . $e.getMessage();
}

// 7. (สำคัญ!) ส่งกลับไปหน้าเดิม
// (เราใช้ $job_id_to_return ที่เก็บไว้ในข้อ 5.2)
header("Location: view_applicants.php?job_id=" . $job_id_to_return);
exit;
?>