<?php
namespace app\index\controller;

use think\Controller;
//use Swoole\Http\Response;
use app\common\lib\Util;
use app\common\lib\Redis;
use app\common\lib\redis\PHPRedis;

class Login extends Controller
{
    public function index()
    {
        //$mobile = addslashes(trim($this->request->param("mobile")));
        $mobile = addslashes(trim($_GET['mobile']));
        if (empty($mobile)) {
            return Util::show(config('code.error'), '手机号码不能为空');
        }
        //$verifyCode = addslashes(trim($this->request->param("code")));
        $verifyCode = addslashes(trim($_GET['code']));
        if (empty($verifyCode)) {
            return Util::show(config('code.error'), '验证码不能为空');
        }

        try {
            //根据手机号码查找 redis 缓存中的值
            $cacheVerifyCode = PHPRedis::getInstance()->get(Redis::smsKey($mobile));
        } catch (\Exception $e){
            return $e->getMessage();
        }
        if ($verifyCode == $cacheVerifyCode) {
            //将已通过验证的 验证码设置为失效状态
            PHPRedis::getInstance()->set(Redis::smsKey($mobile), $cacheVerifyCode, -1);
            //保存用户登录信息到 redis 中
            $userData = [
                'mobile' => $mobile,
                'login_time' => time(),
                'srcKey' => MD5(Redis::userKey($mobile)),
            ];
            $saveUser = PHPRedis::getInstance()->set(Redis::userKey($mobile), $userData, Redis::$userLifeTime);
            if (!$saveUser) {
                return Util::show(config('code.error'), "登录失败");
            }
            return Util::show(config('code.success'), "登录成功");
        } else {
            return Util::show(config('code.error'), "验证码错误");
        }
    
    }
}
