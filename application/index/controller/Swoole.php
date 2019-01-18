<?php
namespace app\index\controller;

use Swoole\Http\Request;
use Swoole\Http\Response;
use think\swoole\Server;

class Swoole11 extends Server
{
    protected $port = 9999;
    protected $serverType = 'http';
    protected $option = [
        'work_num' => 4,
        'daemonize' => false,
        'backlog' => 128,
    ];

    public function onRequest(Request $req, Response $rep)
    {
        $rep->end("<meta charset='utf-8' /><h3>来自 TP 的 Swoole 服务器</h3>");
    }

}