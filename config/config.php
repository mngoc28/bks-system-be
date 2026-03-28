<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'partner' => [
        1 => 'パートナー番号1',
        2 => 'パートナー番号2',
        3 => 'パートナー番号3',
        4 => 'パートナー番号4',
        5 => 'パートナー番号5',
    ],

    'typeImage' => [
        1 => 'メイン画像',
        2 => '間取図',
        3 => '建物',
        4 => '周辺',
        5 => 'キッチン',
        6 => '風呂・トイレ ',
        7 => '内観写真',
        8 => '家具家電',
        9 => 'その他',
    ],

    'propertyType' => [
        1 => 'マンション',
        2 => 'アパート',
        3 => '一戸建て',
        4 => 'その他',
    ],

    'buildingStructure' => [
        1 => '木造',
        2 => '鉄筋コンクリート構造',
        3 => '鉄骨鉄筋コンクリート構造',
        4 => '鉄骨',
        5 => '軽量気泡コンクリート造',
        6 => 'プレキャスト鉄筋コンクリート',
        7 => '軽量鉄骨',
        8 => '鉄骨ブロック',
        9 => 'その他',
    ],

    'parkingAvailability' => [
        1 => "空きあり",
        2 => "空きなし",
        3 => "要確認",
    ],
    'limit_faq' => 10
];
