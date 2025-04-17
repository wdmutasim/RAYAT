<?php
$host = "dpg-d00d45qli9vc739pj8ag-a";
$dbname = "rayat_db";
$username = "rayat_db_user";
$password = "9yi6q8Ui4GS8EoApQQmk40g1m65AYdEB";

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // التحقق هل الأدمن موجود
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();

    if ($adminExists == 0) {
        // إنشاء الجداول
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255),
            role VARCHAR(20) CHECK (role IN ('student', 'admin'))
        );

        CREATE TABLE IF NOT EXISTS courses (
            id SERIAL PRIMARY KEY,
            course_name VARCHAR(100),
            course_code VARCHAR(50)
        );

        CREATE TABLE IF NOT EXISTS grades (
            id SERIAL PRIMARY KEY,
            user_id INT REFERENCES users(id) ON DELETE CASCADE,
            course_id INT REFERENCES courses(id) ON DELETE CASCADE,
            grade FLOAT
        );

        CREATE TABLE IF NOT EXISTS attendance (
            id SERIAL PRIMARY KEY,
            user_id INT REFERENCES users(id) ON DELETE CASCADE,
            course_id INT REFERENCES courses(id) ON DELETE CASCADE,
            attendance_percentage FLOAT
        );
        ";
        $pdo->exec($sql);

        // تشفير كلمة المرور
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

        // إضافة مستخدم الأدمن
        $insertAdmin = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $insertAdmin->execute([
            ':name' => 'Admin User',
            ':email' => 'admin@example.com',
            ':password' => $adminPassword,
            ':role' => 'admin'
        ]);

        echo "✅ تم إنشاء الجداول وإضافة الأدمن مع تشفير كلمة المرور.";
    } else {
        echo "ℹ️ الأدمن موجود بالفعل، لم يتم تنفيذ الكود مرة أخرى.";
    }

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال أو التنفيذ: " . $e->getMessage();
}
?>
