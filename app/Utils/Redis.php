<?php


namespace App\Utils;


class Redis
{
    private static $instance ;
    private $redis;

    private function __construct()
    {
        $this->redis =  new \Redis();
        $this->redis ->connect('127.0.0.1');
    }
    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    public function set($key, $expTime)
    {
        // 初步加锁
        $isLock = $this->redis->setnx($key, time() + $expTime);
        if ($isLock) {
            return true;
        } else {
            // 加锁失败的情况下。判断锁是否已经存在，如果锁存在切已经过期，那么删除锁。进行重新加锁
            $val = $this->redis->get($key);
            if($val && $val < time()) {
                $this->del($key);
                return $this->redis->setnx($key, time() + $expTime);
            }
            return false;
        }
    }

    public function del($key)
    {
        $this->redis->del($key);
    }

}
