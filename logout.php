<?php
session_start();

// إزالة جميع المتغيرات في الجلسة
session_unset();

// تدمير الجلسة
session_destroy();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header('Location: login.html');
exit;
?>
