<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Utils\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecKillController
{
    const COUNT = 1;

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/sharedLock
    // 非阻塞 不公平
    public function sharedLock(Request $request)
    {
        $storage = DB::table('storage')
            ->where('id', '=', 1)
            ->first();

        $quantity = $storage->number;

        $upd = [];
        $upd['number'] = $quantity - self::COUNT;

        $result = DB::table('storage')
            ->where('id', '=', 1)
            ->where('number', '>=', self::COUNT)
            ->update($upd);

        if ($result) {
            $insert = [];
            $insert['number'] = $quantity;
            DB::table('order')
                ->insert($insert);
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/exclusiveLock
    // 阻塞 公平
    public function exclusiveLock(Request $request)
    {
        DB::beginTransaction();

        $storage = DB::table('storage')
            ->where('id', '=', 1)
            ->lockForUpdate()
            ->first();

        $quantity = $storage->number;

        $upd = [];
        $upd['number'] = $quantity - self::COUNT;

        $result = DB::table('storage')
            ->where('id', '=', 1)
            ->where('number', '>=', self::COUNT)
            ->update($upd);

        if ($result) {
            $insert = [];
            $insert['number'] = $quantity;
            DB::table('order')
                ->insert($insert);
        }

        DB::commit();

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/redisLock
    // 非阻塞 不公平
    public function redisLock(Request $request)
    {
        $redis = Redis::getInstance();
        $setRes = $redis->setnx('storage', 5);
        if ($setRes) {

            $storage = DB::table('storage')
                ->where('id', '=', 1)
                ->first();

            $quantity = $storage->number;

            $upd = [];
            $upd['number'] = $quantity - self::COUNT;

            $result = DB::table('storage')
                ->where('id', '=', 1)
                ->where('number', '>=', self::COUNT)
                ->update($upd);

            if ($result) {
                $insert = [];
                $insert['number'] = $quantity;
                DB::table('order')
                    ->insert($insert);
            }
        }
        $redis->del('storage');

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
