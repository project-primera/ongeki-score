<?php
return [
    'application-version' => env('APPLICATION_VERSION'),
    'commit-hash' => env('COMMIT_HASH'),
    'github-repo-url' => env('GITHUB_REPO_URL'),

    'ongeki-version' => env('ONGEKI_VERSION'),
    'ongeki-version-date' => env('ONGEKE_VERSION_DATE'),

    'is-maintenance-api-user-update' => env('IS_MAINTENANCE_API_USER_UPDATE'),


    'slack-webhook-url-debug' => env('SLACK_WEBHOOK_URL_DEBUG'),
    'slack-webhook-url-info' => env('SLACK_WEBHOOK_URL_INFO'),
    'slack-webhook-url-notice' => env('SLACK_WEBHOOK_URL_NOTICE'),
    'slack-webhook-url-warning' => env('SLACK_WEBHOOK_URL_WARNING'),
    'slack-webhook-url-error' => env('SLACK_WEBHOOK_URL_ERROR'),
    'slack-webhook-url-critical' => env('SLACK_WEBHOOK_URL_CRITICAL'),
    'slack-webhook-url-alert' => env('SLACK_WEBHOOK_URL_ALERT'),
    'slack-webhook-url-emergency' => env('SLACK_WEBHOOK_URL_EMERGENCY'),
    'slack-webhook-url-default' => env('SLACK_WEBHOOK_URL_DEFAULT'),
];
