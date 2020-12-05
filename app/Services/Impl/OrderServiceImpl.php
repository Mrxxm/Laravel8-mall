<?php


namespace App\Services\Impl;


use App\Models\OrderModel;
use App\Services\OrderService;
use App\Utils\Snowflake;
use Illuminate\Support\Facades\DB;

class OrderServiceImpl implements OrderService
{
    public $model = null;
    public $orderGoodsService = null;

    public function __construct()
    {
        $this->model = new OrderModel();
        $this->orderGoodsService = new OrderGoodsServiceImpl();
    }

    public function addSingle(array $fields): void
    {
        $userId            = request('uId');
        $fields['user_id'] = $userId;
        $skuId             = $fields['sku_id'];
        $num               = $fields['num'];
        unset($fields['sku_id']);
        unset($fields['num']);

        // 1.生成订单号
        $orderNo = static::generateOrderNo();
        // 2.获取单页面数据
        try {
            $cart = (new CartServiceImpl())->single(['sku_id' => $skuId, 'num' => $num]);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
        if (!$cart) {
            throw new \Exception('商品不存在');
        }

        // 3.准备order_goods数据
        $orderGoods = array_map(function ($v) use ($orderNo) {
            $v['order_no'] = $orderNo;
            unset($v['sku']);
            unset($v['create_time']);
            return $v;
        }, $cart);

        // 4.准备order数据
        $fields['order_no']    = $orderNo;
        $fields['total_price'] = array_sum(array_column($orderGoods, "total_price"));
        $fields['total_num']   = array_sum(array_column($orderGoods, "num"));

        DB::beginTransaction();
        try {
            // 5.插入order
            $order = $this->model->add($fields);
            if (!$order->id) {
                throw new \Exception('插入order失败!');
            }
            // 6.插入order_goods
            foreach ($orderGoods as $orderGood) {
                $og = $this->orderGoodsService->model->add($orderGood);
                if (!$og->id) {
                    throw new \Exception('插入order_goods失败!');
                }
                $ogs[] = resultToArray($og);
            }
            // 7.减库存
            foreach ($ogs as $og) {
                (new GoodsSkuServiceImpl())->decrStock($og['sku_id'], $og['num']);
            }
            // 8.删除购物车里商品

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();
    }

    public function add(array $fields): void
    {
        $userId            = request('uId');
        $fields['user_id'] = $userId;
        $skuIdsStr         = $fields['sku_ids'];
        unset($fields['sku_ids']);

        // 1.生成订单号
        $orderNo = static::generateOrderNo();
        // 2.获取购物车数据
        try {
            $cart = (new CartServiceImpl())->list(['sku_ids' => $skuIdsStr]);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
        if (!$cart) {
            throw new \Exception('商品不存在');
        }
        // 3.准备order_goods数据
        $orderGoods = array_map(function ($v) use ($orderNo) {
            $v['order_no'] = $orderNo;
            unset($v['sku']);
            unset($v['create_time']);
            return $v;
        }, $cart);
//        dd($cart, $fields, $orderGoods);

        // 4.准备order数据
        $fields['order_no']    = $orderNo;
        $fields['total_price'] = array_sum(array_column($orderGoods, "total_price"));
        $fields['total_num']   = array_sum(array_column($orderGoods, "num"));
//        dd($cart, $fields, $orderGoods, $fields);

        DB::beginTransaction();
        try {
            // 5.插入order
            $order = $this->model->add($fields);
            if (!$order->id) {
                throw new \Exception('插入order失败!');
            }
            // 6.插入order_goods
            foreach ($orderGoods as $orderGood) {
                $og = $this->orderGoodsService->model->add($orderGood);
                if (!$og->id) {
                    throw new \Exception('插入order_goods失败!');
                }
                $ogs[] = resultToArray($og);
            }
            // 7.减库存
            foreach ($ogs as $og) {
                (new GoodsSkuServiceImpl())->decrStock($og['sku_id'], $og['num']);
            }
            // 8.删除购物车里商品
            (new CartServiceImpl())->delete($skuIdsStr);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();

    }

    private static function generateOrderNo()
    {
        $workId = rand(1, 1023);
        $orderNo = Snowflake::getInstance()->setWorkId($workId)->id();

        return $orderNo;
    }
}
