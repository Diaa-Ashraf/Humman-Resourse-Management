<?php
return [
    'password_min_length'=>(int)env('HRMS_PASSWORD_MIN_LENGTH',12),
    'login_max_attempts'=>(int)env('HRMS_LOGIN_MAX_ATTEMPTS',5),
    'login_lockout_seconds'=>(int)env('HRMS_LOGIN_LOCKOUT_SECONDS',300),
    'mfa_challenge_minutes'=>(int)env('HRMS_MFA_CHALLENGE_MINUTES',5),
    'session_idle_minutes'=>(int)env('HRMS_SESSION_IDLE_MINUTES',120),
];
