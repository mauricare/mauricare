<?php

return [
    'notice_version' => env('PRIVACY_NOTICE_VERSION', '2026-08-03'),
    'controller_name' => env('PRIVACY_CONTROLLER_NAME', 'Mauricare'),
    'controller_email' => env('PRIVACY_CONTROLLER_EMAIL', 'privacy@mauricare.mu'),
    'dpo_email' => env('PRIVACY_DPO_EMAIL', 'privacy@mauricare.mu'),
    'account_retention_days' => (int) env('PRIVACY_ACCOUNT_RETENTION_DAYS', 30),
    'audit_retention_days' => (int) env('PRIVACY_AUDIT_RETENTION_DAYS', 2190),
];
