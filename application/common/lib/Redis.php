<?php
namespace app\common\lib;

class Redis
{
    /**
     * 短信验证码缓存前缀
     *
     * @var string
     */
    public static $smsPrefix = 'sms_';
    /**
     * 用户信息缓存 key 前缀
     *
     * @var string
     */
    public static $userPrefix = 'user_';
    //用户缓存有效期(秒)
    public static $userLifeTime = 3600;
    //短信验证码缓存有效时间
    public static $smsLifetime = 120;

    /**
     * 获取指定手机号码对应的缓存key
     *
     * @param string $mobile
     * @return void
     */
    public static function smsKey(string $mobile)
    {
        return self::$smsPrefix . $mobile;
    }

    /**
     * 获取用户信息缓存key
     *
     * @param string $mobile
     * @return void
     */
    public static function userKey(string $mobile)
    {
        return self::$userPrefix . $mobile;
    }

}