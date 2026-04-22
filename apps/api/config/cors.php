<?php

return [
    /*
     * フロントエンド (Vercel) からの API リクエストを許可する。
     * 本番では FRONTEND_URL / ADMIN_URL 環境変数で URL を指定する。
     */
    'paths' => ['api/*', 'health'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('ADMIN_URL',    'http://localhost:3001'),
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
