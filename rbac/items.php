<?php
return [
    'contacts' => [
        'type' => 2,
    ],
    'objects' => [
        'type' => 2,
    ],
    'actions' => [
        'type' => 2,
    ],
    'reports' => [
        'type' => 2,
    ],
    'receivables' => [
        'type' => 2,
    ],
    'calls' => [
        'type' => 2,
    ],
    'notifications' => [
        'type' => 2,
    ],
    'contracts' => [
        'type' => 2,
    ],
    'listen_call' => [
        'type' => 2,
    ],
    'delete_contact' => [
        'type' => 2,
    ],
    'show_payments' => [
        'type' => 2,
    ],
    'edit_comment' => [
        'type' => 2,
    ],
    'updateContact' => [
        'type' => 2,
    ],
    'updateOwnContact' => [
        'type' => 2,
        'ruleName' => 'isContactAuthor',
        'children' => [
            'updateContact',
        ],
    ],
    'tags' => [
        'type' => 2,
    ],
    'manager' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'objects',
            'actions',
            'calls',
            'notifications',
            'updateOwnContact',
        ],
    ],
    'supervisor' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'objects',
            'actions',
            'reports',
            'calls',
            'listen_call',
            'delete_contact',
            'show_payments',
            'edit_comment',
            'updateContact',
        ],
    ],
    'fin_dir' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contracts',
            'contacts',
            'objects',
            'actions',
            'reports',
            'receivables',
            'calls',
            'listen_call',
            'show_payments',
            'updateOwnContact',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'contacts',
            'objects',
            'actions',
            'reports',
            'calls',
            'listen_call',
            'delete_contact',
            'updateOwnContact',
            'tags',
        ],
    ],
];
