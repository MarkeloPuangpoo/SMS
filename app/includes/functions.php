<?php
// ไฟล์: includes/functions.php

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    // ตรวจสอบว่า URL ขึ้นต้นด้วย / หรือไม่
    if (strpos($url, '/') !== 0) {
        // ปรับ path ให้เป็นสัมบูรณ์สำหรับ students/*
        if (strpos($url, 'students/') === 0 || $url === 'list.php') {
            $url = '/students/' . ltrim($url, '/');
        } else {
            $url = '/' . ltrim($url, '/');
        }
    }
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

function isLoggedIn() {
    // ตรวจสอบทั้ง session และเวลาที่ไม่ใช้งานนานเกินไป (30 นาที)
    return isset($_SESSION['user_id'], $_SESSION['last_active']) && 
           (time() - $_SESSION['last_active'] < 1800);
}

function checkLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อน";
        // ล้าง session เก่า
        $_SESSION = [];
        session_destroy();
        redirect('login.php');
    }
}