<?php

return [
    'query' => [
        'limit' => [
            'min' => 100,
            'max' => 500,
        ]
    ],
    'session' => [
        'earliest' => 7,
        'latest' => 9,
        'buffer' => [
            'hours' => 2,
            'mins' => 120
        ]
    ],
    'role' => [
        'admin' => 1,
        'vendor' => 2,
        'cleaner' => 3,
        'user' => 4,
    ],
    'booking' => [
        'status' => [
            'cancelled' => -1,
            'accepted' => 0,
            'paid' => 1,
            'delivering' => 2,
            'inProgress' => 3,
            'done' => 4,
            'rated' => 5
        ],
        'lastChance' => [
            'hours' => 72,
            'days' => 3
        ],
        'refund' => [
            'firstChance' => [
                'hours' => 72,
                'percentage' => 70
            ],
            'lastChance' => [
                'hours' => 24,
                'percentage' => 50
            ],
            '24' => 50,
            '72' => 70,
        ],
        'blocking' => [
            'seconds' => 600
        ],
        'allowSorting' => [
            'created_at',
            'booking_date',
        ]
    ],
    'payment' => [
        'booking' => 1,
        'topup' => 2,
        'method' => [
            'credit_card' => 1,
            'online_banking' => 2,
            'wallet' => 3
        ],
        'method_tostr' => [
            1 => 'Credit Card',
            2 => 'Online Banking',
            3 => 'Wallet Coint',
        ],
        'type_tostr' => [
            1 => 'Booking',
            2 => 'Top Up',
        ]
    ],
    'wallet' => [
        'action' => [
            'plus' => 1,
            'minus' => 2,
            'credit' => 1,
            'debit' => 2,
            'add' => 1,
            'deduct' => 2,
        ],
        'limit' => 10000
    ]
];
