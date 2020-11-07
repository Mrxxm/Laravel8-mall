<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecKillController
{
    const COUNT = 1;

    // ab -n 10 -c 5 http://www.tool.com/api/v1/secKill/sharedLock
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

    // ab -n 10 -c 5 http://www.tool.com/api/v1/secKill/exclusiveLock
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

    public function redisLock(Request $request)
    {

    }
}
