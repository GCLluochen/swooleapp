<?php
namespace app\admin\controller;

use app\common\lib\Util;
use think\Facade\Request;
use app\common\lib\redis\PHPRedis;

/**
 * 赛况
 */
class Live
{
    public function index()
    {
        // $ws = $_POST['http_server'];
        // $ws->task("This is Async Task");
        // return "异步任务已投递";
        $name = $_GET['name'];//Request::param('name');
        return $name;
    }

    /**
     * 保存管理员发布的赛况数据,推送到前台用户连接
     *
     * @return void
     */
    public function push()
    {
        //需要保存的赛况数据
        $data = $_POST;
        $outsType = intval($data['type']);//比赛节数
        $teamId = intval($data['team_id']) ?? 0;
        $image = isset($data['image']) ? trim($data['image']) : '';// 赛况图片
        $content = addslashes(trim($data['content']));
        $scoreLeft = intval($data['score_left']);
        $scoreRight = intval($data['score_right']);

        $server = $_POST['http_server'];//Swoole server
        //保存赛况到数据库
        $this->addOuts($data, $server);

        //球队列表
        $teamList = [
            1 => [
                'team_id' => 1,
                'name' => '马刺',
                'logo' => '/live/imgs/team1.png',
            ],
            4 => [
                'team_id' => 4,
                'name' => '火箭',
                'logo' => '/live/imgs/team2.png',
            ],
        ];

        // 将赛况数据 push 到已连接的 websocket中
        $pushData = [
            'msg_type' => 'outs',
            'type' => $outsType,
            'time' => date("i:s", time()),
            'image' => isset($data['image']) ? $image : '',
            'content' => $content,
            'logo' => $teamId == 0 ? '' : $teamList[$teamId]['logo'],
            'team' => $teamId == 0 ? '解说员(luochen)' : $teamList[$teamId]['name'],
            'score_left' => $scoreLeft,
            'score_right' => $scoreRight,
        ];

        /**
         * 推送赛况到全部 webSocket 连接
         */
        // go(function () use ($server, $pushData) {
        //     // 获取保存在 redis 中的socket 连接ID
        //     $redis = new \Swoole\Coroutine\Redis();
        //     $redis->connect(config("redis.host"), config('redis.port'));
        //     $redis->auth(config('redis.auth'));
        //     $socketLinker = $redis->get(config('swoole_server.socket_link_key'));

        //     if ($socketLinker) {
        //         $socketData = $pushData;
        //         if (is_array($pushData)) {
        //             $socketData = json_encode($pushData, JSON_UNESCAPED_UNICODE);
        //         }
        //         $socketLinker = json_decode($socketLinker, true);
        //         foreach ($socketLinker as $fd) {
        //             $server->push($fd, $socketData);
        //         }
        //     }
        // });
        $taskData = [
            'method' => 'pushLive',
            'data' => $pushData,
        ];
        //异步推送
        $server->task($taskData);
        return Util::show(config("code.success"), '赛况已推送');
    }

    /**
     * 异步保存赛况数据
     *
     * @param [type] $server
     * @param array $data
     * @return void
     */
    protected function addOuts(array $data, $server)
    {
        go(function () use ($server, $data) {
            $swoole_mysql = new \Swoole\Coroutine\MySQL();
            $swoole_mysql->connect([
                'host' => config('database.hostname'),
                'port' => config('database.hostport'),
                'user' => config('database.username'),
                'password' => config('database.password'),
                'database' => config('database.database'),
            ]);
            $outsType = intval($data['type']);//比赛节数
            $teamId = intval($data['team_id']) ?? 0;
            $image = isset($data['image']) ? trim($data['image']) : '';// 赛况图片
            $content = addslashes(trim($data['content']));
            $scoreLeft = intval($data['score_left']);
            $scoreRight = intval($data['score_right']);

            $timeStamp = time();
            //此处需要根据 比赛ID 来获取到比赛的双方球队ID
            // 暂定为 [1, 4]
            $sqlAdd = "INSERT INTO `live_outs`(`game_id`, `team_id`, `team_score`, `image`, `content`, `type`, `status`, `create_time`, `update_time`) VALUES (1, ?, ?, ?, ?, ?, 2, ?, ?)";
            $stmt = $swoole_mysql->prepare($sqlAdd);
            if (!$stmt) {
                return false;
            }

            //选择了球队ID,则需要更新比赛情况
            if ($scoreLeft != 0) {
                //更新左边的球队比分
                $resAddLeft = $stmt->execute([1, $scoreLeft, $image, $content, $outsType, $timeStamp, $timeStamp]);
            }
            if ($scoreRight != 0) {
                //更新右边的球队比分
                $resAddRight = $stmt->execute([4, $scoreRight, $image, $content, $outsType, $timeStamp, $timeStamp]);
            }
            if ($scoreLeft == 0 && $scoreRight == 0) {
                //未设置比分时,仅添加一条无相关队伍的赛况内容
                $sqlAdd = "INSERT INTO `live_outs`(`game_id`,`image`, `content`, `type`, `status`, `create_time`, `update_time`) VALUES (1, ?, ?, ?, 2, ?, ?)";
                $stmt = $swoole_mysql->prepare($sqlAdd);
                //更新右边的球队比分
                $resUpdOuts = $stmt->execute([ $image, $content, $outsType, $timeStamp, $timeStamp]);
            }
            return true;
        });
    }

    /**
     * 保存聊天数据,并推送到其他用户界面上
     *
     * @return void
     */
    public function chart()
    {
        $data = $_POST;

    }
}