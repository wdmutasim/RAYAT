<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// جلب الطلاب
$query_students = "SELECT * FROM users WHERE role = 'student'";
$result_students = pg_query($conn, $query_students);
$students = pg_fetch_all($result_students);

// جلب المقررات
$query_courses = "SELECT * FROM courses";
$result_courses = pg_query($conn, $query_courses);
$courses = pg_fetch_all($result_courses);

// إضافة درجة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $grade = $_POST['grade'];

    $query = "INSERT INTO grades (user_id, course_id, grade) VALUES ($1, $2, $3)";
    $result = pg_query_params($conn, $query, array($student_id, $course_id, $grade));
    if ($result) {
        echo "<script>alert('تم إضافة الدرجة بنجاح!');</script>";
    }
}

// إضافة طالب جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // تشفير كلمة المرور

    // التحقق من البريد الإلكتروني المكرر
    $stmt_check = "SELECT COUNT(*) FROM users WHERE email = $1";
    $result_check = pg_query_params($conn, $stmt_check, array($email));
    $count = pg_fetch_result($result_check, 0, 0);
    if ($count > 0) {
        echo "<script>alert('البريد الإلكتروني مسجل بالفعل!');</script>";
    } else {
        $stmt = "INSERT INTO users (name, email, password, role) VALUES ($1, $2, $3, 'student')";
        $result = pg_query_params($conn, $stmt, array($name, $email, $password));
        if ($result) {
            echo "<script>alert('تم إضافة الطالب بنجاح!');</script>";
        }
    }
}

// إضافة كورس جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = htmlspecialchars($_POST['course_name']);
    $course_code = htmlspecialchars($_POST['course_code']); // تم استخدام course_code بدلاً من course_description

    $query = "INSERT INTO courses (course_name, course_code) VALUES ($1, $2)";
    $result = pg_query_params($conn, $query, array($course_name, $course_code));
    if ($result) {
        echo "<script>alert('تم إضافة الكورس بنجاح!');</script>";
    }
}

// تعديل بيانات الطالب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);

    $query = "UPDATE users SET name = $1, email = $2 WHERE id = $3";
    $result = pg_query_params($conn, $query, array($name, $email, $student_id));
    if ($result) {
        echo "<script>alert('تم تعديل بيانات الطالب بنجاح!');</script>";
    }
}

// إضافة نسبة الحضور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_attendance'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $attendance_percentage = $_POST['attendance_percentage'];

    // التحقق هل هناك سجل حضور سابق لهذا الطالب والكورس
    $query = "SELECT id FROM attendance WHERE user_id = $1 AND course_id = $2";
    $result = pg_query_params($conn, $query, array($student_id, $course_id));
    $attendance = pg_fetch_assoc($result);

    if ($attendance) {
        // إذا كان موجود، نحدثه
        $query = "UPDATE attendance SET attendance_percentage = $1 WHERE id = $2";
        pg_query_params($conn, $query, array($attendance_percentage, $attendance['id']));
    } else {
        // إذا لم يكن موجود، نضيف سجل جديد
        $query = "INSERT INTO attendance (user_id, course_id, attendance_percentage) VALUES ($1, $2, $3)";
        pg_query_params($conn, $query, array($student_id, $course_id, $attendance_percentage));
    }

    echo "<script>alert('تم إضافة/تحديث نسبة الحضور بنجاح!');</script>";
}

// حذف طالب
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $query = "DELETE FROM users WHERE id = $1";
    pg_query_params($conn, $query, array($delete_id));
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
