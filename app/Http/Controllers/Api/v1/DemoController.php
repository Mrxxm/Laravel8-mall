<?php


namespace App\Http\Controllers\Api\v1;


use App\Utils\Snowflake;
use Illuminate\Support\Facades\DB;

class DemoController
{
    public function index()
    {
        $randChar = getRandChar(32);
        return $randChar;
    }

    /**
     * 分布式id生成器
     * @return int
     * @throws \Exception
     */
    public function snowflake()
    {
        // workId 0 ~ 1023
        $NO = Snowflake::getInstance()->setWorkId(0)->id();
        return $NO;
    }

    public function lock()
    {
//        $shareLock = DB::table('user')->where('id', '=', 2)->sharedLock()->dd();
//        return $shareLock;
        $sadLock = DB::table('user')->where('id', '=', 2)->lockForUpdate()->dd();
        return $sadLock;
    }
}
