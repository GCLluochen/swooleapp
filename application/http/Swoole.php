<?php
namespace app\http;

use think\swoole\Server;
use think\facade\Env;
use app\common\lib\task\Task;

class Swoole extends Server
{
    protected $ws = null;
    protected $host = '0.0.0.0';
    protected $port = 9999;
    protected $chatPort = 9998;
	protected $option = [
        'worker_num'=> 4,//工作进程
        'task_worker_num' => 2,//异步任务处理进程
		'daemonize'	=> false,
        'backlog'	=> 128,
        'enable_static_handler' => true,
    ];
    protected $mode  = SWOOLE_PROCESS; // 运行模式 默认为SWOOLE_PROCESS
    protected $sock_type = SWOOLE_SOCK_TCP; // sock type 默认为SWOOLE_SOCK_TCP

    public function __construct()
    {
        //删除 全部客户端连接
        \app\common\lib\redis\PHPRedis::getInstance()->del(config("redis.live_socket_key"));

        //parent::__construct();
        $this->option['document_root'] = Env::get('root_path') . 'public/static';
        $this->ws = new \Swoole\WebSocket\Server($this->host, $this->port);
        // 监听聊天数据端口
        //$chatServer = $this->ws->listen($this->host, $this->chatPort, SWOOLE_SOCK_TCP);
        $this->ws->set($this->option);
        // $chatServer->set([
        //     'open_http_protocol' => false,// 关闭聊天端口的 http 协议连接
        // ]);

        $this->ws->on("WorkerStart", [$this, "onWorkerStart"]);
        $this->ws->on("Receive", [$this, "onReceive"]);
        $this->ws->on("Open", [$this, "onOpen"]);
        $this->ws->on("Message", [$this, "onMessage"]);
        //$chatServer->on("Message", [$this, "onChatMessage"]);
        $this->ws->on("Request", [$this, "onRequest"]);
        $this->ws->on("Task", [$this, "onTask"]);
        $this->ws->on("Finish", [$this, "onFinish"]);
        $this->ws->on("Close", [$this, "onClose"]);

        $this->ws->start();
    }

	public function onReceive($server, $fd, $from_id, $data)
	{
		$server->send($fd, 'Swoole: '.$data);
    }
    
    public function onOpen($server, $req)
    {
        echo "HandShake Success with {$req->fd}\n";
        //将 websocket 连接 ID 存入redis
        // go(function () use ($server, $req) {
        //     $redis = new \Swoole\Coroutine\Redis();
        //     $redis->connect(config("redis.host"), config('redis.port'));
        //     $redis->auth(config('redis.auth'));
        //     $socketLinker = $redis->get(config('redis.live_socket_key'));
        //     if (!$socketLinker) {
        //         $socketLinker = [$req->fd];
        //     } else {
        //         $socketLinker = json_decode($socketLinker, true);
        //         array_push($socketLinker, $req->fd);
        //     }
        //     $redis->set(config('redis.live_socket_key'), json_encode($socketLinker, JSON_UNESCAPED_UNICODE), 7200);
        // });
        \app\common\lib\redis\PHPRedis::getInstance()->sAdd(config('redis.live_socket_key'), $req->fd);
    }

    public function onWorkerStart($server, $worker_id)
    {
        define("APP_PATH", __DIR__ . "/../application");
        // 加载基础文件
        require_once dirname(APP_PATH) . '/../thinkphp/base.php';
    }

    public function onMessage($server, $frame)
    {
        //echo "Receive from {$frame->fd}: {$frame->data}, opcode: {$frame->opcode}, fin: {$frame->finish}\n";
        // $server->push($frame->fd, "We have received your message\n");
        // //$serv->task($frame->data);
        // $server->after(5000, function () use ($server, $frame) {
        //     $server->push($frame->fd, "5秒后的信息");
        //     //$server->task("From WebSocket");
        // });

        //接收聊天数据
        $data = json_decode($frame->data, true);
        $chatTask = [
            'method' => 'pushChat',
            'data' => [
                'msg_type' => 'chat',
                'from_user' => $data['from_user'],
                'user_name' => $data['user_name'],
                'content' => $data['content'],
            ],
        ];
        //异步推送到全部连接 ( 包括消息来源用户 )
        $server->task($chatTask);

    }

    /**
     * WebSocket 接收聊天信息的回调处理
     *
     * @param [type] $server
     * @param [type] $frame
     * @return void
     */
    public function onChatMessage($server, $frame)
    {
        //接收聊天数据
        $data = json_decode($frame->data, true);
        $chatTask = [
            'method' => 'pushChat',
            'data' => [
                'msg_type' => 'chat',
                'from_user' => $data['from_user'],
                'user_name' => $data['user_name'],
                'content' => $data['content'],
            ],
        ];
        //异步推送到全部连接 ( 包括消息来源用户 )
        $server->task($chatTask);
    }

    public function onRequest($request, $response)
    {
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
        $_POST['http_server'] = $this->ws;
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k=>$val) {
                $_FILES[trim($k)] = $val;
            }
        }

        $content = '';
        ob_start();
        \think\Container::get('app')->run()->send();
        $content = ob_get_contents();
        ob_end_clean();

        $response->end($content);
    }

    /**
     * 异步任务事件回调
     *
     * @param [type] $serv
     * @param [type] $task_id
     * @param [type] $worker_id
     * @param [type] $data
     * @return void
     */
    public function onTask($serv, $task_id, $worker_id, $data)
    {
        echo "Start Async Task: \n";
        $taskObj = new Task();
        $taskMethod = trim($data['method']);
        if (method_exists($taskObj, $taskMethod)) {
            $taskObj->$taskMethod($data['data'], $serv);
        }
        $serv->finish("Task {$task_id} -> OK");
    }

    /**
     * 异步任务完成后的回调
     *
     * @param [type] $serv
     * @param [type] $task_id
     * @param [type] $data
     * @return void
     */
    public function onFinish($serv, $task_id, $data)
    {
        echo "Data: {$data}\n";
        echo "Task $task_id finished\n";
    }

    /**
     * WebSocket 连接关闭事件回调
     *
     * @param [type] $server WebSocket Server对象
     * @param [type] $fd 连接 ID
     * @return void
     */
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed\n";
        //从 redis 中移除 websocket 连接ID
        // go(function () use ($server, $fd) {
        //     $redis = new \Swoole\Coroutine\Redis();
        //     $redis->connect(config("redis.host"), config('redis.port'));
        //     $redis->auth(config('redis.auth'));
        //     $socketLinker = $redis->get(config('redis.live_socket_key'));
        //     if ($socketLinker) {
        //         $socketLinker = json_decode($socketLinker, true);
        //         $flipLinker = array_flip($socketLinker);
        //         unset($flipLinker[(int)$fd]);
        //         $redis->set(config('redis.live_socket_key'), json_encode(array_flip($flipLinker), JSON_UNESCAPED_UNICODE), 7200);   
        //     }
        // });

        //从 WebSocket 连接集合中移除当前关闭的连接 ID
        \app\common\lib\redis\PHPRedis::getInstance()->sRem(config('redis.live_socket_key'), $fd);
    }
}