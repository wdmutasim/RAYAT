<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// جلب الطلاب
$stmt_students = $conn->prepare("SELECT * FROM users WHERE role = 'student'");
$stmt_students->execute();
$students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

// جلب المقررات
$stmt_courses = $conn->prepare("SELECT * FROM courses");
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

// إضافة درجة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $grade = $_POST['grade'];

    $stmt = $conn->prepare("INSERT INTO grades (user_id, course_id, grade) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $course_id, $grade]);
    echo "<script>alert('تم إضافة الدرجة بنجاح!');</script>";
}

// إضافة طالب جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // تشفير كلمة المرور

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
    $stmt->execute([$name, $email, $password]);
    echo "<script>alert('تم إضافة الطالب بنجاح!');</script>";
}

// إضافة كورس جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code']; // تم استخدام course_code بدلاً من course_description

    $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code) VALUES (?, ?)");
    $stmt->execute([$course_name, $course_code]);
    echo "<script>alert('تم إضافة الكورس بنجاح!');</script>";
}

// تعديل بيانات الطالب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $student_id]);
    echo "<script>alert('تم تعديل بيانات الطالب بنجاح!');</script>";
}
// إضافة نسبة الحضور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_attendance'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $attendance_percentage = $_POST['attendance_percentage'];

    // التحقق هل هناك سجل حضور سابق لهذا الطالب والكورس
    $stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$student_id, $course_id]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // إذا كان موجود، نحدثه
        $stmt = $conn->prepare("UPDATE attendance SET attendance_percentage = ? WHERE id = ?");
        $stmt->execute([$attendance_percentage, $attendance['id']]);
    } else {
        // إذا لم يكن موجود، نضيف سجل جديد
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, course_id, attendance_percentage) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $course_id, $attendance_percentage]);
    }

    echo "<script>alert('تم إضافة/تحديث نسبة الحضور بنجاح!');</script>";
}

// حذف طالب
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$delete_id]);
    echo "<script>alert('تم حذف الطالب بنجاح!'); window.location.href='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة المسؤول</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>لوحة تحكم المسؤول</h1>

    <h2>إضافة درجة</h2>
    <form method="post">
        <select name="student_id" required>
            <?php foreach ($students as $student) { ?>
                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
            <?php } ?>
        </select>
        <select name="course_id" required>
            <?php foreach ($courses as $course) { ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
            <?php } ?>
        </select>
        <input type="number" name="grade" placeholder="الدرجة" required>
        <button type="submit" name="add_grade">إضافة</button>
    </form>

    <h2>إضافة طالب جديد</h2>
    <form method="post">
        <input type="text" name="name" placeholder="الاسم" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit" name="add_student">إضافة الطالب</button>
    </form>

    <h2>إضافة كورس جديد</h2>
    <form method="post">
        <input type="text" name="course_name" placeholder="اسم الكورس" required>
        <input type="text" name="course_code" placeholder="كود الكورس" required> <!-- تم تعديل حقل الوصف إلى كود الكورس -->
        <button type="submit" name="add_course">إضافة الكورس</button>
    </form>
    <h2>إضافة نسبة حضور</h2>
<form method="POST">
    <label for="student_id">اختر الطالب:</label>
    <select name="student_id" required>
        <?php foreach ($students as $student): ?>
            <option value="<?= $student['id']; ?>"><?= htmlspecialchars($student['name']); ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="course_id">اختر الكورس:</label>
    <select name="course_id" required>
        <?php foreach ($courses as $course): ?>
            <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="attendance_percentage">نسبة الحضور (%):</label>
    <input type="number" name="attendance_percentage" min="0" max="100" step="0.01" required><br><br>

    <button type="submit" name="add_attendance">إضافة الحضور</button>
</form>
<hr>


    <h2>إدارة الطلاب</h2>
    <table border="1">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student) { ?>
                <tr>
                    <form method="post">
                        <td>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>">
                        </td>
                        <td>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
                        </td>
                        <td>
                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                            <button type="submit" name="edit_student">تعديل</button>
                            <a href="?delete_id=<?php echo $student['id']; ?>" onclick="return confirm('هل أنت متأكد من الحذف؟');">حذف</a>
                        </td>
                    </form>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="logout.php">تسجيل خروج</a>
</body>
</html>
