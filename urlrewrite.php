<?php

$arUrlRewrite = [
    0 =>
        [
            'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
            'RULE' => 'componentName=$1',
            'ID' => null,
            'PATH' => '/bitrix/services/mobileapp/jn.php',
            'SORT' => 100,
        ],
    2 =>
        [
            'CONDITION' => '#^/bitrix/services/ymarket/#',
            'RULE' => '',
            'ID' => '',
            'PATH' => '/bitrix/services/ymarket/index.php',
            'SORT' => 100,
        ],
    1 =>
        [
            'CONDITION' => '#^/rest/#',
            'RULE' => '',
            'ID' => null,
            'PATH' => '/bitrix/services/rest/index.php',
            'SORT' => 100,
        ],
    3 => [
        'CONDITION' => '#^/catalog/#',
        'RULE' => '',
        'ID' => 'bitrix:catalog',
        'PATH' => '/catalog/index.php',
        'SORT' => 100,
    ],
];
