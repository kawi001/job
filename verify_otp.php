<?php
/*
 * ไฟล์: /job_match/verify_otp.php
 * หน้าที่: ฟอร์มสำหรับกรอก OTP (6 หลัก) ที่ส่งไปทางอีเมล
 */
session_start();

// (รับอีเมลมาจาก URL ...?email=... เพื่อส่งต่อ)
// (เราต้องรู้ว่ากำลังยืนยัน "อีเมล" ไหน)
$email = isset($_GET['email']) ? $_GET['email'] : '';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยัน OTP</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 90vh; background: #f0f2f5; }
        form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; text-align: center; font-size: 1.2em; }
        button { background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; background: #ffe0e0; padding: 10px; }
        .success { color: green; background: #e0ffe0; padding: 10px; }
    </style>
</head>
<body>

    <form action="verify_process.php" method="POST">
        <h2>ยืนยันบัญชีของคุณ</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <p>เราได้ส่งรหัส OTP 6 หลักไปที่ <strong><?php echo htmlspecialchars($email); ?></strong> (กรุณาเช็ก Junk Mail)</p>
        
        <div>
            <label for="otp_code">กรอกรหัส OTP:</label>
            <input type="text" id="otp_code" name="otp_code" maxlength="6" required>
        </div>
        
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        
        <button type="submit">ยืนยัน OTP</button>
        </form>

</body>
</html>