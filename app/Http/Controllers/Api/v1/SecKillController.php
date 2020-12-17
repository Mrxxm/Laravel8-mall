<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Utils\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * mysql> CREATE TABLE `b_storage` (
    ->   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    ->   `number` int(11) DEFAULT NULL,
    ->   PRIMARY KEY (`id`)
    -> ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 * mysql> CREATE TABLE `b_order` (
    ->   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    ->   `number` int(11) DEFAULT NULL,
    ->   PRIMARY KEY (`id`)
    -> ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    Query OK, 0 rows affected (0.01 sec)
 */


class SecKillController
{
    const COUNT = 1;

    // storage 10
    // ab -n 20 -c 10 http://www.laravel8.com/api/v1/secKill/sharedLock
    // 非阻塞 不公平
    public function sharedLock(Request $request)
    {
        $storage = DB::table('b_storage')
            ->where('id', '=', 1)
            ->first();

        $quantity = $storage->number;

        $upd = [];
        $upd['number'] = $quantity - self::COUNT;

        $result = DB::table('b_storage')
            ->where('id', '=', 1)
            ->where('number', '>=', self::COUNT)
            ->update($upd);

        if ($result) {
            $insert = [];
            $insert['number'] = $quantity;
            DB::table('b_order')
                ->insert($insert);
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // storage 10
    // ab -n 20 -c 10 http://www.laravel8.com/api/v1/secKill/exclusiveLock
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
