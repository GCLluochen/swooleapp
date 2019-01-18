<?php
namespace app\common\lib\task;

use app\common\lib\ali\Sms;
use app\common\lib\Redis;
use app\common\lib\redis\PHPRedis;
use Swoole\WebSocket\Server;

/**
 * 异步任务执行
 */
class Task
{
    /**
     * 异步发送 短信验证码
     *
     * @param array $data 
     * @return void
     */
    public function sendSms(array $data)
    {
        if (!isset($data['mobile']) || !isset($data['code'])) {
            return false;
        }
        try {
            //$resSms = Sms::sendSms($data['mobile'], $data['code']);
        } catch (\Exception $e) {
            //调用接口失败,返回错误
            return false;
        }
        if (1==1 || $resSms->Message == 'OK') {
            //将发送出的短信验证码存入Redis
            PHPRedis::getInstance()->set(Redis::smsKey($data['mobile']), $data['code'], Redis::$smsLifetime);
        } else {
            //发送失败,返回错误
            return false;
        }
        return true;
    }

    /**
     * 推送赛况数据
     *
     * @param array $pushData
     * @return void
     */
    public function pushLive(array $pushData, Server $server)
    {
        //从 redis 中获取已连接的 WebSocket fd,然后推送消息
        $clients = PHPRedis::getInstance()->sMembers(config('redis.live_socket_key'));
        //$clients = $server->ports[0]->connections;
        $socketData = json_encode($pushData, JSON_UNESCAPED_UNICODE);
        foreach ($clients as $fd) {
            $server->push($fd, $socketData);
        }
    }


    /**
     * 推送聊天数据
     *
     * @param array $chatData
     * @param Server $server
     * @return void
     */
    public function pushChat(array $chatData, Server $server)
    {
        //从 redis 中获取已连接的 WebSocket fd,然后推送消息
        $clients = PHPRedis::getInstance()->sMembers(config('redis.live_socket_key'));//$server->ports[1]->connections;//仅获取 聊天 ws 连接的fd
        $socketData = json_encode($chatData, JSON_UNESCAPED_UNICODE);
        foreach ($clients as $fd) {
            $server->push($fd, $socketData);
        }
    }
}