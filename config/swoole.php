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
// | Swoole设置 php think swoole命令行下有效
// +----------------------------------------------------------------------
return [
    // 扩展自身配置
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 9999, // 监听端口
    'mode'                  => '', // 运行模式 默认为SWOOLE_PROCESS
    'sock_type'             => '', // sock type 默认为SWOOLE_SOCK_TCP
    'server_type'           => 'http', // 服务类型 支持 http websocket
    'app_path'              => '', // 应用地址 如果开启了 'daemonize'=>true 必须设置（使用绝对路径）
    'file_monitor'          => false, // 是否开启PHP文件更改监控（调试模式下自动开启）
    'file_monitor_interval' => 2, // 文件变化监控检测时间间隔（秒）
    'file_monitor_path'     => [], // 文件监控目录 默认监控application和config目录

    // 可以支持swoole的所有配置参数
    'pid_file'              => Env::get('runtime_path') . 'swoole.pid',
    'log_file'              => Env::get('runtime_path') . 'swoole.log',
    'document_root'         => Env::get('root_path') . 'public/static',
    'enable_static_handler' => true,
    'timer'                 => true,//是否开启系统定时器
    'interval'              => 500,//系统定时器 时间间隔
    'task_worker_num'       => 1,//swoole 任务工作进程数量

    'daemonize'             => false,
    'WorkerStart'           => function ($server, $worker_id) {
        define("APP_PATH", __DIR__ . "/../application");
        // 加载基础文件
        require_once __DIR__ . '/../thinkphp/base.php';
    },
    'Request'               => function ($request, $response) {
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

        $content = '';
        ob_start();
        think\Container::get('app')->run()->send();
        $content = ob_get_contents();
        ob_end_clean();

        $response->end($content);
    },
    //异步任务
    'Task'                  => function ($server, $task_id, $worker_id, $data) {
        return json_encode($data);
    },
];
