<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role = :role");
    $stmt->execute(['email' => $email, 'role' => $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'student') {
            header('Location:student_dashboard.php');
        } elseif ($user['role'] == 'admin') {
            header('Location:admin_dashboard.php');
        }
        exit;
    } else {
        echo "<script>alert('بيانات الدخول غير صحيحة'); window.location.href='login.html';</script>";
    }
}
?>
