<?php

return [
    'notify_mail' => (bool) env('HRMS_NOTIFY_MAIL', false),
    'backup_disk' => env('HRMS_BACKUP_DISK', 'local'),
    'backup_retention_days' => (int) env('HRMS_BACKUP_RETENTION_DAYS', 14),
];
