<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Env;

// +----------------------------------------------------------------------
// | Swoole设置 php think swoole:server 命令行下有效
// +----------------------------------------------------------------------
return [
    // 扩展自身配置
    'host'         => '0.0.0.0', // 监听地址
    'port'         => 9999, // 监听端口
    'type'         => 'socket', // 服务类型 支持 socket http server
    'mode'         => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type'    => '', // sock type 默认为SWOOLE_SOCK_TCP
    'swoole_class' => 'app\http\Swoole', // 自定义服务类名称
    

    // 可以支持swoole的所有配置参数
    'daemonize'    => false,
    'pid_file'     => Env::get('runtime_path') . 'swoole_server.pid',
    'log_file'     => Env::get('runtime_path') . 'swoole_server.log',
    'document_root'         => Env::get('root_path') . 'public/static',
    'enable_static_handler' => true,
    'daemonize'             => false,
    'onWorkerStart'           => function ($server, $worker_id) {
        define("APP_PATH", __DIR__ . "/../application");
        // 加载基础文件
        require_once __DIR__ . '/../thinkphp/base.php';
    },

    // 事件回调定义
    // websocket 连接回调
    'onOpen'               => function ($server, $req) {
        echo "HandShake Success with {$req->fd}\n";
    },
    // websocket 接收消息回调
    'onMessage'               => function ($server, $frame) {
        echo "Receive from {$frame->fd}: {$frame->data}, opcode: {$frame->opcode}, fin: {$frame->finish}\n";
        $server->push($frame->fd, "We have received your message\n");
        //$serv->task($frame->data);
        $server->after(5000, function () use ($server, $frame) {
            $server->push($frame->fd, "5秒后的信息");
        });
    },

    'onRequest' => function ($request, $response){
        $_SERVER = [];
        if (isset($request->server)) {
            foreach ($request->server as $k=>$val) {
                $_SERVER[trim($k)] = $val;
                $_SERVER[strtoupper(trim($k))] = $val;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k=>$val) {
                $_SERVER[strtoupper(trim($k))] = $val;
            }
        }
        $_GET = $_POST = [];
        if (isset($request->get)) {
            foreach ($request->get as $k=>$val) {
                $_GET[trim($k)] = $val;
            }
        }
        if (isset($request->post)) {
            foreach ($request->post as $k=>$val) {
                $_POST[trim($k)] = $val;
            }
        }
        //$_POST['http_server'] = $server;
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k=>$val) {
                $_FILES[trim($k)] = $val;
            }
        }

        $content = '';
        ob_start();
        think\Container::get('app')->run()->send();
        $content = ob_get_contents();
        ob_end_clean();

        $response->end($content);
    },

    'onClose' => function ($ser, $fd) {
        echo "client {$fd} closed\n";
    },

    
];
