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
?>
