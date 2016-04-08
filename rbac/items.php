<?php
return [
    'contacts' => [
        'type' => 2,
    ],
    'actions' => [
        'type' => 2,
    ],
    'reports' => [
        'type' => 2,
    ],
    'calls' => [
        'type' => 2,
    ],
    'notifications' => [
        'type' => 2,
    ],
    'listen_call' => [
        'type' => 2,
    ],
    'delete_contact' => [
        'type' => 2,
    ],
    'edit_comment' => [
        'type' => 2,
    ],
    'updateContact' => [
        'type' => 2,
    ],
    'tags' => [
        'type' => 2,
    ],
    'import' => [
        'type' => 2,
    ],
    'users' => [
        'type' => 2,
    ],
    'updateUser' => [
        'type' => 2,
    ],
    'delete_user' => [
        'type' => 2,
    ],
    'manager' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'actions',
            'calls',
            'notifications',
            'updateContact',
        ],
    ],
    'operator' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'actions',
            'calls',
            'listen_call',
            'delete_contact',
            'edit_comment',
            'updateContact',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'actions',
            'reports',
            'calls',
            'listen_call',
            'delete_contact',
            'updateContact',
            'tags',
            'import',
            'users',
            'updateUser',
            'delete_user',
        ],
    ],
];
