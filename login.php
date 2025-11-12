<?php
/*
 * ไฟล์: /job_match/login.php
 * หน้าที่: หน้าฟอร์มสำหรับเข้าสู่ระบบ
 */
session_start(); 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - Job Match</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 90vh; background: #f0f2f5; }
        form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="password"] { width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; background: #ffe0e0; padding: 10px; border-radius: 4px; }
        .success { color: green; background: #e0ffe0; padding: 10px; border-radius: 4px; }
        .links { display: flex; justify-content: space-between; font-size: 0.9em; }
    </style>
</head>
<body>

    <form action="login_process.php" method="POST">
        <h2>เข้าสู่ระบบ</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">รหัสผ่าน:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">เข้าสู่ระบบ</button>
        
        <div class="links">
            <p><a href="forgot_password.php">ลืมรหัสผ่าน?</a></p>
            <p>ยังไม่มีบัญชี? <a href="register.php">สมัครที่นี่</a></p>
        </div>
    </form>

</body>
</html>