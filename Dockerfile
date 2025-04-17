# استخدم صورة PHP الرسمية مع Apache
FROM php:8.2-apache

# انسخ ملفات مشروعك إلى مجلد السيرفر داخل الحاوية
COPY . /var/www/html/


# إعدادات PHP (اختياري)
# RUN docker-php-ext-install mysqli pdo pdo_mysql
