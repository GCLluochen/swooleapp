<?php
/**
 * redis 连接配置
 */
return [
    'host' => '127.0.0.1',
    'port' => '6379',
    'auth' => 'sujintest',
    'timeout' => 5, //redis 连接超时时间
    'live_socket_key' => 'socket_linker', //直播 socket 连接用户保存 redis key
];