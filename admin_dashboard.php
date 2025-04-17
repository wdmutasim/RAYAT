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
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة المسؤول</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1 class="display-5 text-primary mb-4 text-center">لوحة تحكم المسؤول</h1>

        <!-- إضافة درجة -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">إضافة درجة</div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select name="student_id" class="form-select" required>
                                <?php foreach ($students as $student) { ?>
                                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="course_id" class="form-select" required>
                                <?php foreach ($courses as $course) { ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="grade" class="form-control" placeholder="الدرجة" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_grade" class="btn btn-primary w-100">إضافة</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- إضافة طالب جديد -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">إضافة طالب جديد</div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="الاسم" required>
                        </div>
                        <div class="col-md-4">
                            <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" required>
                        </div>
                        <div class="col-md-2">
                            <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_student" class="btn btn-primary w-100">إضافة الطالب</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- إضافة كورس جديد -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">إضافة كورس جديد</div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="course_name" class="form-control" placeholder="اسم الكورس" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="course_code" class="form-control" placeholder="كود الكورس" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_course" class="btn btn-primary w-100">إضافة الكورس</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- إضافة نسبة حضور -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">إضافة نسبة حضور</div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="student_id" class="form-label">اختر الطالب:</label>
                            <select name="student_id" class="form-select" required>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id']; ?>"><?= htmlspecialchars($student['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="course_id" class="form-label">اختر الكورس:</label>
                            <select name="course_id" class="form-select" required>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="attendance_percentage" class="form-label">نسبة الحضور (%):</label>
                            <input type="number" name="attendance_percentage" class="form-control" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_attendance" class="btn btn-primary w-100">إضافة الحضور</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- إدارة الطلاب -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">إدارة الطلاب</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
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
                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>">
                                        </td>
                                        <td>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>">
                                        </td>
                                        <td>
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="edit_student" class="btn btn-sm btn-success">تعديل</button>
                                            <a href="?delete_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">حذف</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- تسجيل الخروج -->
        <div class="text-center">
            <a href="logout.php" class="btn btn-outline-primary">تسجيل خروج</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
