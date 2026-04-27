<?php

return [
    'fire_theft' => [
        'background' => 'templates/fire_theft_page1.png',
        'fields' => [
            // Header Info
            'policy_no'         => ['x' => 160, 'y' => 35, 'font_size' => 12],
            'issue_date'        => ['x' => 160, 'y' => 45, 'font_size' => 10],

            // Client Info
            'client_name'       => ['x' => 40, 'y' => 135, 'font_size' => 11],
            'trade_name'        => ['x' => 130, 'y' => 135, 'font_size' => 11],
            'permanent_address' => ['x' => 40, 'y' => 145, 'font_size' => 10],
            'occupation'        => ['x' => 120, 'y' => 145, 'font_size' => 10],
            'phone'             => ['x' => 170, 'y' => 145, 'font_size' => 10],

            // Duration
            'issue_date'        => ['x' => 160, 'y' => 160, 'font_size' => 10],
            'expiry_date'       => ['x' => 90, 'y' => 160, 'font_size' => 10],

            // Detailed Location (Question 1)
            'district'          => ['x' => 40, 'y' => 185],
            'mahalla'           => ['x' => 100, 'y' => 185],
            'zuqaq'             => ['x' => 130, 'y' => 185],
            'dar'               => ['x' => 160, 'y' => 185],
            'shop_no'           => ['x' => 190, 'y' => 185],
            'street_region'     => ['x' => 40, 'y' => 195],
            'shop_phone'        => ['x' => 130, 'y' => 195],
        ],
        'checkboxes' => [
            'fireTheftDetails.is_owner'               => ['x' => 180, 'y' => 205],
            'fireTheftDetails.has_accounting_records' => ['x' => 180, 'y' => 215],
            'fireTheftDetails.peril_explosion'        => ['x' => 25,  'y' => 265],
            'fireTheftDetails.peril_flood'            => ['x' => 50,  'y' => 265],
            'fireTheftDetails.peril_storm'            => ['x' => 75,  'y' => 265],
            'fireTheftDetails.peril_riot'             => ['x' => 100, 'y' => 265],
            'fireTheftDetails.peril_earthquake'       => ['x' => 125, 'y' => 265],
        ],
        'signatures' => [
            'client'   => ['x' => 40,  'y' => 275, 'width' => 30],
            'delegate' => ['x' => 150, 'y' => 275, 'width' => 30],
        ],
    ],
    'life' => [
        'background' => 'templates/life_form_page1.png',
        'fields' => [
            'client_name' => ['x' => 50, 'y' => 80],
            'lifeDetails.marital_status' => ['x' => 50, 'y' => 95],
            'lifeDetails.id_card_no'     => ['x' => 130, 'y' => 95],
        ],
        'signatures' => [
            'delegate' => ['x' => 140, 'y' => 260, 'width' => 30],
        ],
    ],
];
