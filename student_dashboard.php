<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['user_id'];

// جلب بيانات الطالب
$query_student = "SELECT * FROM users WHERE id = $1";
$result_student = pg_query_params($conn, $query_student, array($id));
$student = pg_fetch_assoc($result_student);

// جلب الدرجات
$query_grades = "SELECT courses.course_name, grades.grade, courses.course_code 
                 FROM grades 
                 JOIN courses ON grades.course_id = courses.id
                 WHERE grades.user_id = $1";
$result_grades = pg_query_params($conn, $query_grades, array($id));
$grades = pg_fetch_all($result_grades);

// جلب الحضور
$query_attendance = "SELECT courses.course_name, attendance.attendance_percentage, courses.course_code 
                     FROM attendance 
                     JOIN courses ON attendance.course_id = courses.id
                     WHERE attendance.user_id = $1";
$result_attendance = pg_query_params($conn, $query_attendance, array($id));
$attendance = pg_fetch_all($result_attendance);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الطالب</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1 class="display-5 text-primary mb-4 text-center">مرحباً، <?php echo htmlspecialchars($student['name']); ?></h1>

        <!-- الدرجات -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">الدرجات</div>
            <div class="card-body">
                <?php if (count($grades) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم المادة</th>
                                    <th>رمز المادة</th>
                                    <th>الدرجة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info text-center" role="alert">
                        لا توجد درجات لهذا الطالب.
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- نسبة الحضور -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">نسبة الحضور</div>
            <div class="card-body">
                <?php if (count($attendance) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم المادة</th>
                                    <th>رمز المادة</th>
                                    <th>نسبة الحضور</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance as $attend) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attend['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($attend['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($attend['attendance_percentage']); ?>%</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info text-center" role="alert">
                        لا توجد بيانات حضور لهذا الطالب.
                    </div>
                <?php } ?>
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
