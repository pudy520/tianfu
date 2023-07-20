


## 扩展包要求

-   PHP >= 7.0

## 安装命令

```shell
$ composer require "pdy/tianfu" -vvv
```

$wexin = new  TfPay();
$params = [
'total_fee' => 1, //单位分
'out_trade_no' =>  time(),
'body' => 'subject-测试',
'notify_url' => '回调地址',
];
return   $wexin->unifiedOrder($params);
```

## 常用方法

```
```