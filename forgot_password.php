<?php
/*
 * ไฟล์: /forgot_password.php
 * หน้าที่: ฟอร์มสำหรับกรอกอีเมล เพื่อขอรีเซ็ตพาสเวิร์ด
 */
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลืมรหัสผ่าน</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 90vh; background: #f0f2f5; }
        form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"] { width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; background: #ffe0e0; padding: 10px; }
        .success { color: green; background: #e0ffe0; padding: 10px; }
    </style>
</head>
<body>

    <form action="forgot_process.php" method="POST">
        <h2>ลืมรหัสผ่าน</h2>
        <p>กรอกอีเมลที่คุณใช้สมัคร เราจะส่งลิงก์รีเซ็ตไปให้</p>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div>
            <label for="email">อีเมล:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <button type="submit">ส่งลิงก์รีเซ็ต</button>
        <p style="text-align: center;"><a href="login.php">กลับไปหน้าล็อกอิน</a></p>
    </form>

</body>
</html>