@echo off
cd /d "%~dp0"
php -d upload_max_filesize=100M -d post_max_size=512M -d max_file_uploads=50 -S 127.0.0.1:8000 server.php
