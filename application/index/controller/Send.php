<?php
namespace app\index\controller;

use think\Controller;
use app\common\lib\Util;
use app\common\lib\Redis;
use app\common\lib\ali\Sms;
use app\common\lib\redis\PHPRedis;

class Send extends Controller
{
    public function index()
    {
        $mobile = addslashes(trim($this->request->param("mobile")));
        if (empty($mobile)) {
            return Util::show(config('code.error'), '手机号码不能为空');
        }
        //生成随机 短信验证码
        $code = Util::generateRandomCode();

        $data = [
            'mobile' => $mobile,
            'code' => $code,
        ];
        $res = (new \app\common\lib\task\Task())->sendSms($data);

        /*
        //短信发送需要放入异步任务中执行
        //调用短信发送
        try {
            //$resSms = Sms::sendSms($mobile, $code);
        } catch (\Exception $e) {
            //return Util::show(config('code.error'), '调用短信接口失败');
        }
        if (1==1 || $resSms->Code == 'OK') {
            //将手机号 => 验证码存入 redis, 作为登录验证用
            //异步存入redis
            $redis = new \Swoole\Coroutine\Redis();
            $redis->connect(config("redis.host"), config('redis.port'));
            $redis->auth(config('redis.auth'));
            $redis->set(Redis::smsKey($mobile), $code, Redis::$smsLifetime);

            //同步存入redis
            //PHPRedis::getInstance()->set(Redis::smsKey($mobile), $code, Redis::$smsLifetime);

        } else {
            return Util::show(config('code.error'), '验证码发送失败');
        } */
        return Util::show(config('code.success'), "短信验证码发送成功");
    }
}
