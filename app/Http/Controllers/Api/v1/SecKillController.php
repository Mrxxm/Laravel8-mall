<?php


namespace App\Http\Controllers\Api\v1;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecKillController
{
    const COUNT = 1;

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
    }

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
    }

    public function redisLock(Request $request)
    {

    }
}
