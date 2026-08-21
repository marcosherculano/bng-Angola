<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'dir' => env('BACKUP_DIR', 'backups'),

    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'mysql_path' => env('MYSQL_PATH', 'mysql'),

    'zip_enabled' => env('BACKUP_ZIP_ENABLED', true),
];
