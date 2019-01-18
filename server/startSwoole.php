<?php
define("APP_PATH", __DIR__ . '/../application');
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

app\common\lib\redis\PHPRedis::getInstance()->lPush('wl', 'shanling');
app\common\lib\redis\PHPRedis::getInstance()->lPush('wl', 'mojo');