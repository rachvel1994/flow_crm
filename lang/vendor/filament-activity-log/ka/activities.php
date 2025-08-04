<?php

return [
    'title' => 'ქმედებების ისტორია',

    'date_format' => 'j F, Y',
    'time_format' => 'H:i l',

    'filters' => [
        'date' => 'თარიღი',
        'causer' => 'დამწყები',
        'subject_type' => 'ობიექტის ტიპი',
        'subject_id' => 'ობიექტის ID',
        'event' => 'ქმედება',
    ],
    'table' => [
        'field' => 'ველის სახელი',
        'old' => 'ძველი მნიშვნელობა',
        'new' => 'ახალი მნიშვნელობა',
        'value' => 'მნიშვნელობა',
        'no_records_yet' => 'ჩანაწერები ჯერ არ არის',
    ],
    'events' => [
        'created' => [
            'title' => 'შექმნა',
            'description' => 'ჩანაწერი შეიქმნა',
        ],
        'updated' => [
            'title' => 'განახლება',
            'description' => 'ჩანაწერი განახლდა',
        ],
        'deleted' => [
            'title' => 'წაშლა',
            'description' => 'ჩანაწერი წაიშალა',
        ],
        'restored' => [
            'title' => 'აღდგენა',
            'description' => 'ჩანაწერი აღდგა',
        ],
        'attached' => [
            'title' => 'მიმაგრება',
            'description' => 'ჩანაწერი მიმაგრდა',
        ],
        'detached' => [
            'title' => 'მოხსნა',
            'description' => 'ჩანაწერი მოცილდა',
        ],
    ],
    'boolean' => [
        'true' => 'კი',
        'false' => 'არა',
    ],
];
