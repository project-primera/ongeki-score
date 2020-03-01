<?php
return [
    'application-version' => env('APPLICATION_VERSION'),
    'mix-bookmarklet-version' => env('MIX_BOOKMARKLET_VERSION'),

    'ongeki-version' => env('ONGEKI_VERSION'),
    'ongeki-version-date' => env('ONGEKE_VERSION_DATE'),

    'is-maintenance-api-user-update' => env('IS_MAINTENANCE_API_USER_UPDATE'),

    'twitter-consumer-key' => env('TWITTER_CONSUMER_KEY'),
    'twitter-consumer-secret' => env('TWITTER_CONSUMER_SECRET'),
    'twitter-admin-account-access-token' => env('TWITTER_ADMIN_ACCOUNT_ACCESS_TOKEN'),
    'twitter-admin-account-access-token-secret' => env('TWITTER_ADMIN_ACCOUNT_ACCESS_TOKEN_SECRET'),

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