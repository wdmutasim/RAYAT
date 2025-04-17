<?php
$host = "dpg-d00d45qli9vc739pj8ag-a";
$port = "5432";
$dbname = "rayat_db";
$user = "rayat_db_user";
$password = "9yi6q8Ui4GS8EoApQQmk40g1m65AYdEB";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("❌ فشل الاتصال بقاعدة البيانات.");
}

// تشفير كلمة المرور
$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);

// تنفيذ أمر إدخال المستخدم
$query = "INSERT INTO users (name, email, password, role) VALUES ('مدير', 'admin@example.com', '$hashed_password', 'admin');";

$result = pg_query($conn, $query);

if ($result) {
    echo "✅ تم إدخال المستخدم الأدمن بنجاح.";
} else {
    echo "❌ حدث خطأ أثناء الإدخال: " . pg_last_error($conn);
}
?>
