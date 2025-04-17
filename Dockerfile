FROM php:8.2-apache

# تثبيت الامتدادات المطلوبة
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# نسخ ملفات المشروع إلى مجلد الويب في السيرفر
COPY . /var/www/html/

# إعطاء صلاحيات للمجلد
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
