<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // استعلام مع متغيرات محمية
    $result = pg_query_params($conn, "SELECT * FROM users WHERE email = $1 AND role = $2", array($email, $role));

    if ($user = pg_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'student') {
                header('Location: student_dashboard.php');
            } elseif ($user['role'] == 'admin') {
                header('Location: admin_dashboard.php');
            }
            exit;
        }
    }

    // إذا لم تنجح عملية تسجيل الدخول
    echo "<script>alert('❌ بيانات الدخول غير صحيحة'); window.location.href='login.html';</script>";
}
?>
