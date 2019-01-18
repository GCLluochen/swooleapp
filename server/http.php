<?php
use Swoole\Http\Server;

//ob_implicit_flush(false);

$serv = new Server('0.0.0.0', 9999);
$serv->set([
    'enable_static_handler' => true,
    'document_root' => '/home/www/swoole/swooleapp/public/static',
    'worker_num' => 4,
]);

$serv->on("WorkerStart", function(Server $worker, $worker_id){
    define("APP_PATH", __DIR__ . "/../application");
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';

    // 执行应用并响应
    //think\Container::get('app')->run()->send();
});

$serv->on("Request", function($request, $response){
    // $logCont = [
    //     'date:' => date("Y-m-d H:i:s"),
    //     'get:' => $request->get,
    //     'post:' => $request->post,
    //     'header:' => $request->header, 
    // ];
    // Swoole\Async::writeFile("./http.log", json_encode($logCont) . PHP_EOL, null, FILE_APPEND);
    //$response->cookie("aoteman", "saiwen", time() + 3600);

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

    //ob_implicit_flush(false);
    $content = '';
    ob_start();
    think\Container::get('app')->run()->send();
    $content = ob_get_contents();
    // //echo "哈哈哈";
    ob_end_clean();

    $response->end($content);
});

$serv->start();