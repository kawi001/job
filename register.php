<?php
// เริ่ม session เพื่อใช้แสดงข้อความ (เช่น "สมัครสำเร็จ", "Email ซ้ำ")
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก - Job Match</title>
    <style>
        /* (ใส่ CSS ชั่วคราวเพื่อให้ดูง่าย) */
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 90vh; background: #f0f2f5; }
        form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .error { color: red; } .success { color: green; }
    </style>
</head>
<body>

    <form action="register_process.php" method="POST">
        <h2>สร้างบัญชีผู้ใช้</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div>
            <label for="name">ชื่อ (หรือ ชื่อร้านค้า):</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">รหัสผ่าน:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div>
            <label>คุณคือใคร?</label>
            <label>
                <input type="radio" name="role_id" value="1" required> ผู้หางาน
            </label>
            <label>
                <input type="radio" name="role_id" value="2"> ผู้จ้างงาน (ร้านค้า)
            </label>
        </div>
        <button type="submit">สมัครสมาชิก</button>
        <p style="text-align: center;">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
    </form>

</body>
</html>