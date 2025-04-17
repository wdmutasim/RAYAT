<?php
// بيانات الاتصال بقاعدة البيانات PostgreSQL
$host = "dpg-d00d45qli9vc739pj8ag-a";
$port = "5432";
$dbname = "rayat_db";
$user = "rayat_db_user";
$password = "9yi6q8Ui4GS8EoApQQmk40g1m65AYdEB";

// الاتصال بقاعدة البيانات
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("❌ فشل الاتصال بقاعدة البيانات");
}

// SQL لإنشاء الجداول
$queries = [
    // users table
    "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        role VARCHAR(20)
    )",
    // courses table
    "CREATE TABLE IF NOT EXISTS courses (
        id SERIAL PRIMARY KEY,
        course_name VARCHAR(100),
        course_code VARCHAR(50)
    )",
    // grades table
    "CREATE TABLE IF NOT EXISTS grades (
        id SERIAL PRIMARY KEY,
        user_id INT REFERENCES users(id),
        course_id INT REFERENCES courses(id),
        grade FLOAT
    )",
    // attendance table
    "CREATE TABLE IF NOT EXISTS attendance (
        id SERIAL PRIMARY KEY,
        user_id INT REFERENCES users(id),
        course_id INT REFERENCES courses(id),
        attendance_percentage FLOAT
    )"
];

// تنفيذ استعلامات إنشاء الجداول
foreach ($queries as $query) {
    $result = pg_query($conn, $query);
    if (!$result) {
        die("❌ خطأ في إنشاء جدول: " . pg_last_error($conn));
    }
}

// إضافة مستخدم أدمن
$name = "Admin";
$email = "admin@example.com";
$raw_password = "admin123";
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
$role = "admin";

$check = pg_query_params($conn, "SELECT * FROM users WHERE email = $1", [$email]);
if (pg_num_rows($check) == 0) {
    $insert = pg_query_params($conn,
        "INSERT INTO users (name, email, password, role) VALUES ($1, $2, $3, $4)",
        [$name, $email, $hashed_password, $role]
    );
    if ($insert) {
        echo "✅ تمت إضافة مستخدم الأدمن بنجاح.";
    } else {
        echo "❌ فشل في إدخال المستخدم: " . pg_last_error($conn);
    }
} else {
    echo "ℹ️ المستخدم الأدمن موجود مسبقاً.";
}

pg_close($conn);
?>
