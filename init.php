<?php
// معلومات الاتصال
$host = "dpg-d00d45qli9vc739pj8ag-a";
$dbname = "rayat_db";
$username = "rayat_db_user";
$password = "9yi6q8Ui4GS8EoApQQmk40g1m65AYdEB";

try {
    $conn = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // إنشاء الجداول
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        role VARCHAR(10) CHECK (role IN ('student', 'admin'))
    );

    CREATE TABLE IF NOT EXISTS courses (
        id SERIAL PRIMARY KEY,
        course_name VARCHAR(100),
        course_code VARCHAR(50)
    );

    CREATE TABLE IF NOT EXISTS grades (
        id SERIAL PRIMARY KEY,
        user_id INT REFERENCES users(id),
        course_id INT REFERENCES courses(id),
        grade FLOAT
    );

    CREATE TABLE IF NOT EXISTS attendance (
        id SERIAL PRIMARY KEY,
        user_id INT REFERENCES users(id),
        course_id INT REFERENCES courses(id),
        attendance_percentage FLOAT
    );
    ";
    $conn->exec($sql);

    // إنشاء مستخدم أدمن
    $adminName = "Admin";
    $adminEmail = "admin@example.com";
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
    $role = "admin";

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminName, $adminEmail, $adminPassword, $role]);
        echo "✅ تم إنشاء الجداول ومستخدم الأدمن بنجاح.";
    } else {
        echo "ℹ️ مستخدم الأدمن موجود مسبقًا.";
    }
} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال أو التنفيذ: " . $e->getMessage();
}
?>
