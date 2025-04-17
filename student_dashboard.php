<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب الدرجات
$stmt_grades = $conn->prepare("SELECT courses.course_name, grades.grade, courses.course_code 
                               FROM grades 
                               JOIN courses ON grades.course_id = courses.id
                               WHERE grades.user_id = ?");
$stmt_grades->execute([$id]);
$grades = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

// جلب الحضور
$stmt_attendance = $conn->prepare("SELECT courses.course_name, attendance.attendance_percentage, courses.course_code 
                                   FROM attendance 
                                   JOIN courses ON attendance.course_id = courses.id
                                   WHERE attendance.user_id = ?");
$stmt_attendance->execute([$id]);
$attendance = $stmt_attendance->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة الطالب</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>مرحباً، <?php echo htmlspecialchars($student['name']); ?></h1>

    <h2>الدرجات</h2>
    <?php if (count($grades) > 0) { ?>
        <table border="1">
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
    <?php } else { ?>
        <p>لا توجد درجات لهذا الطالب.</p>
    <?php } ?>

    <h2>نسبة الحضور</h2>
    <?php if (count($attendance) > 0) { ?>
        <table border="1">
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
    <?php } else { ?>
        <p>لا توجد بيانات حضور لهذا الطالب.</p>
    <?php } ?>

    <a href="logout.php">تسجيل خروج</a>
</body>
</html>
