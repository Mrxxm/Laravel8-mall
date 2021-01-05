<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Utils\Snowflake;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class DemoController
{
    public function index()
    {
        return $this->getUserService()->getById(2);
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

    protected function getUserService(String $service = 'UserService')
    {
        app()->singleton($service, sprintf(config('service_path'), $service));

        return app()->get($service);
    }
}
