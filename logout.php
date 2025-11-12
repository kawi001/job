<?php
/*
 * ไฟล์: /job_match/logout.php
 * หน้าที่: ทำลาย Session และส่งกลับไปหน้า Login
 */

session_start();
session_unset();
session_destroy();

// (สำคัญ!) ต้อง "ถอยหลัง" 1 ก้าว เพื่อไปหา login.php
header("Location: ../job_match/login.php?status=loggedout");
exit;
?>