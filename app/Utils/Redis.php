<?php


namespace App\Utils;


class Redis
{
    private static $instance ;
    private $redis;

    private function __construct()
    {
        $this->redis =  new \Redis();
        $this->redis->connect('127.0.0.1');
        $this->redis->select(8);
    }
    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    public function setnx($key, $expTime)
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
        return $this->redis->del($key);
    }

    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     *
     * @return string The value, if the command executed successfully BOOL FALSE in case of failure
     *
     * @link    https://redis.io/commands/hget
     */
    public function hGet($key, $hashKey)
    {
        return $this->redis->hGet($key, $hashKey);
    }

    /**
     * Adds a value to the hash stored at key. If this value is already in the hash, FALSE is returned.
     *
     * @param string $key
     * @param string $hashKey
     * @param string $value
     *
     * @return int|bool
     * - 1 if value didn't exist and was added successfully,
     * - 0 if the value was already present and was replaced, FALSE if there was an error.
     *
     * @link    https://redis.io/commands/hset
     * @example
     * <pre>
     * $redis->del('h')
     * $redis->hSet('h', 'key1', 'hello');  // 1, 'key1' => 'hello' in the hash at "h"
     * $redis->hGet('h', 'key1');           // returns "hello"
     *
     * $redis->hSet('h', 'key1', 'plop');   // 0, value was replaced.
     * $redis->hGet('h', 'key1');           // returns "plop"
     * </pre>
     */
    public function hSet($key, $hashKey, $value)
    {
        return $this->redis->hSet($key, $hashKey, $value);
    }

    public function hDel($key, $hashKey1, ...$otherHashKeys)
    {
        return $this->redis->hDel($key, $hashKey1, ...$otherHashKeys);
    }

    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    public function hMGet($key, $hashKeys)
    {
        return $this->redis->hMGet($key, $hashKeys);
    }

    public function zAdd($key, $options, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
    {
        return $this->redis->zAdd($key, $options, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null);
    }

    public function zRangeByScore($key, $start, $end, array $options = array())
    {
        return $this->redis->zRangeByScore($key, $start, $end, $options);
    }

    public function zRem($key, $member1, ...$otherMembers)
    {
        return $this->redis->zRem($key, $member1, ...$otherMembers);
    }

}
