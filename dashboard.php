<?php
/*
 * ไฟล์: /job_match/dashboard.php
 * หน้าที่: หน้าหลัก (ตัวจริง) หลังล็อกอิน
 */

//
// VVVVV (จุดที่ต้องแก้ Path) VVVVV
//
// 1. เรียก "ส่วนหัว" (ที่อยู่ใน ../includes/)
require 'includes/header.php';
//
// ^^^^^ (จบจุดที่ต้องแก้ Path) ^^^^^
//

// (เนื้อหาใน $role_id, $user_id, $email มาจาก header.php แล้ว)
?>

<h1>ยินดีต้อนรับสู่ Dashboard</h1>
<p>คุณล็อกอินสำเร็จในฐานะ:</p>

<?php if ($role_id == 1): // Role 1 = 'Job Seeker' ?>
    <h2 style="color: blue;">ผู้หางาน (Job Seeker)</h2>
    <p>คุณสามารถเริ่ม <a href="search.php">ค้นหางาน</a> ได้เลย</p>

<?php elseif ($role_id == 2): // Role 2 = 'Employer' ?>
    <h2 style="color: green;">ผู้จ้างงาน (Employer)</h2>
    <p>คุณสามารถ <a href="post_job.php">โพสต์งานใหม่</a> ได้</p>

<?php endif; ?>


<?php
//
// VVVVV (จุดที่ต้องแก้ Path) VVVVV
//
// 2. เรียก "ส่วนท้าย" (ที่อยู่ใน ../includes/)

//
// ^^^^^ (จบจุดที่ต้องแก้ Path) ^^^^^
//
?>