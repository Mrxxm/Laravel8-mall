<?php


namespace App\Http\Controllers\Api\v1;


use App\Services\UserService;
use App\Utils\Snowflake;
use Illuminate\Support\Facades\DB;
use AUService;
use App\Facades\AdminUserServiceFacade;

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

    /**
     * @return UserService
     */
    protected function getUserService(String $service = 'UserService')
    {
        app()->singleton($service, sprintf(config('app.service_path'), $service));

        return app()->get($service);
    }

    public function facadeService()
    {
        $result1 = AUService::list([]);
        $reeult2 = AdminUserServiceFacade::list([]);
        dd($result1, $reeult2);
    }
}
