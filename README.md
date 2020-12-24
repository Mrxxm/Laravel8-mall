## About Laravel8 Market

## 关系图

![](https://img9.doubanio.com/view/photo/l/public/p2629005626.jpg)

## Explanatory Chart

* 商城系统

* 前台

1.小程序登录  
2.用户相关  
3.分类相关  
4.商品相关  
5.购物车相关  
6.用户地址  
7.订单相关  

* 后台

1.后台登录  
2.前台用户管理  
3.后台用户管理  
4.分类管理  
5.规格管理  
6.规格属性管理  
7.商品管理  


![](https://img3.doubanio.com/view/photo/l/public/p2627783720.jpg)

## Document

[接口文档](https://www.showdoc.com.cn/kenrou?page_id=5597339566627627)

## 秒杀的三种方式

```
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
```

