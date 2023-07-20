<?php

return [
    'partner'    => env('TIANFU_PARTNER'),  //商户号
    'key'        => env('TIANFU_KEY'),   //商户key
    'subpartner' => env('TIANFU_SUBPARTNER'), //
    'notify_url' => env('TIANFU_NOTIFY_URL'),  //异步回调地址
    'return_url' => env('TIANFU_RETURN_URL'),//同步回调地址
    'show_url'   => env('TIANFU_SHOW_URL'), //显示地址
    'fee_type'   => 1,
    'test'       => env('TIANFU_TEST', false), //是否开启测试
];
