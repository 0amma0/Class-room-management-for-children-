<?php

return [
    'dump_binary' => env('DB_DUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('DB_CLIENT_BINARY', 'mysql'),
    'timeout' => (int) env('DB_BACKUP_TIMEOUT', 900),
];
