<?php
// اسم المضيف
$host = "dpg-d00d45qli9vc739pj8ag-a";  // اسم المضيف من لوحة Render

// اسم قاعدة البيانات
$dbname = "rayat_db";  // اسم قاعدة البيانات في Render

// اسم المستخدم
$username = "rayat_db_user";  // اسم المستخدم في Render

// كلمة المرور
$password = "YOUR_PASSWORD";  // ضع كلمة المرور هنا

try {
    // الاتصال بقاعدة البيانات باستخدام PDO
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "تم الاتصال بقاعدة البيانات بنجاح!";
} catch (PDOException $e) {
    echo "فشل الاتصال: " . $e->getMessage();
}
?>
