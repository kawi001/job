<?php
/*
 * ไฟล์: /reset_password.php
 * หน้าที่: ตรวจสอบ Token จาก URL และแสดงฟอร์มตั้งรหัสใหม่
 */

session_start();
require 'includes/config.php'; // ($pdo)

// 1. รับ Token จาก URL
if (!isset($_GET['token'])) {
    die("ไม่พบ Token");
}
$token = $_GET['token'];

// 2. เช็ก Token ใน DB ว่า "มี" และ "ยังไม่หมดอายุ"
try {
    $sql_check = "SELECT user_id FROM ACCOUNT_VERIFICATIONS 
                  WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$token]);
    $verification = $stmt_check->fetch();

    if (!$verification) {
        die("ลิงก์รีเซ็ตนี้ไม่ถูกต้อง หรือหมดอายุแล้ว");
    }
    
} catch (Exception $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

// 3. (ถ้า Token ถูกต้อง) แสดงฟอร์ม
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งรหัสผ่านใหม่</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 90vh; background: #f0f2f5; }
        form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] { width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; background: #ffe0e0; padding: 10px; }
    </style>
</head>
<body>

    <form action="reset_process.php" method="POST">
        <h2>ตั้งรหัสผ่านใหม่</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        
        <div>
            <label for="password">รหัสผ่านใหม่:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="confirm_password">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit">บันทึกรหัสผ่านใหม่</button>
    </form>

</body>
</html>