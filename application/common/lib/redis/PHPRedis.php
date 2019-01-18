<?php
namespace app\common\lib\redis;

class PHPRedis
{
    public $redis;
    private static $_instance;

    private function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect(config("redis.host"), config("redis.port"), config("redis.timeout"));
        $this->redis->auth(config("redis.auth"));
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 设置 redis 缓存
     *
     * @param [type] $key 缓存key
     * @param [type] $value 缓存值
     * @param integer $timeout 有效期
     * @return void
     */
    public function set($key, $value, $timeout = 0)
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if ((int)$timeout == 0) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $timeout, $value);
    }

    /**
     * 获取redis缓存值
     *
     * @param string $key 缓存key
     * @return void
     */
    public function get(string $key)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * 设置集合元素
     *
     * @param string $key
     * @param [type] $val
     * @return void
     */
    public function sAdd(string $key, $val)
    {
        if (self::check($key, $val)) {
            return $this->redis->sAdd($key, $val);
        }
        return false;
    }

    /**
     * 从集合中移除指定元素
     *
     * @param string $key
     * @param [type] $val
     * @return void
     */
    public function sRem(string $key, $val)
    {
        if (self::check($key, $val)) {
            return $this->redis->sRem($key, $val);
        }
        return false;
    }

    /**
     * 获取指定 key 的集合元素
     *
     * @param string $key
     * @return void
     */
    public function sMembers(string $key)
    {
        if (self::check($key)) {
            return $this->redis->sMembers($key);
        }
        return false;
    }

    /**
     * 删除指定的 key 所保存的值
     *
     * @param string $key
     * @return void
     */
    public function del(string $key)
    {
        if (!self::check($key)) {
            return false;
        }
        return $this->redis->del($key);
    }

    /**
     * redis 操作前的参数检查
     *
     * @param string $key
     * @param [type] $val
     * @param integer $lifeTime
     * @return void
     */
    protected static function check(string $key, $val = '', $lifeTime = 0)
    {
        if (!$key) {
            return false;
        }
        return true;
    }


    /**
     * 调用本类中不存在的方法时执行的回调
     *
     * @param [type] $method 方法名称
     * @param [type] $arguments 传递给调用方法的参数
     * @return void
     */
    public function __call($method, $arguments)
    {
        if (count($arguments) < 2) {
            return false;
        }
        return $this->redis->$method($arguments[0], $arguments[1]);
    }

}