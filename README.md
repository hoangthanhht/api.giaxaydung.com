
Các bước khi cài đặt project
1. trên server tạo 1 tài khoăn người dùng 
2. kiểm tra phiên bản php trên serve khớp với bản php 7.4 của project nếu chưa có thì cài đúng phiên bản
3. cài đăt mysql cho serve
4. cài đặt composer nếu serve chưa có
5. sau đó tạo 1 folder để git project về : git clone đường dẫn git project tên thư mục muốn dư án tải về
6. chạy composer install để cài đặt các gói của project
7. cấu hình file env cho dự án
8. chạy php artisan migrate để tạo bảng
9. chạy php artisan key generate để tạo key 
10. chạy php artisan passport:install để cài đặt oanh
11. php artisan config:cache để cho nhận cấu hình mới thay đổi
## 1 số lưu ý.
1. cần dùng tài khoản user để clone project không dùng root
2. sau khi cài đặt xong pro thì cần phân full quyền cho thư mục storage cảu laravel cho user www-data
3. trong file config của pro thì cần chú ý đường dẫn đến phiên bản php mà project chạy
4. cấu hình đúng tên miền và tên thư mục root trỏ đến public của larave chứa index.php
5. cấu hình các file mặc định khi load đầu tiên là index.php hoặc index.html, htm
6. khởi động lại nginx
7. khi serve có cài cả apache thì cần tắt bỏ apache

