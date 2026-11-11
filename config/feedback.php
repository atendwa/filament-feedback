<?php

declare(strict_types=1);

return [
    'categories' => [
        'General Feedback', 'User Experience', 'Feature Request', 'Bug Report', 'Quality of Service', 'Compliment',
    ],

    'public_limit' => 5,

    'notify_email' => env('FEEDBACK_NOTIFY_EMAIL'),

    'path' => 'give-feedback',

    'tenant' => null,

    'user_model' => null,

    'public' => [
        'vite' => null,
        'head' => null,
        'logo' => null,
        'decoration' => null,
    ],

    'navigation' => [
        'cluster' => null,
        'group' => null,
        'sort' => 10,
        'mine_group' => null,
        'mine_sort' => 20,
    ],
];
