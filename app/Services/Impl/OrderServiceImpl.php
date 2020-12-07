<?php


namespace App\Services\Impl;


use App\Models\OrderModel;
use App\Services\OrderService;
use App\Utils\Redis;
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

        // 9.将订单order_no加入消息队列
        /*
         * 把当前订单ID 放入延迟队列中， 定期检测订单是否已经支付 （因为订单有效期是20分钟，超过这个时间还没有支付的，
         * 我们需要把这个订单取消 ， 然后库存+操作）小伙伴需要举一反三，比如其他场景也可以用到延迟队列：发货提醒等
         * 学习就是要不断的提升自己，老师授的只是思路，我们需要举一反三，从而提升自己
         */
        try {
//            (Redis::getInstance())->zAdd("order_status", time() + 20 * 60, $orderNo);
        } catch (\Exception $e) {
            // 记录日志， 添加监控 ，异步根据监控内容处理。
        }
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
            (new CartServiceImpl())->delete($skuIdsStr);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();

        // 9.将订单order_no加入消息队列
        /*
         * 把当前订单ID 放入延迟队列中， 定期检测订单是否已经支付 （因为订单有效期是20分钟，超过这个时间还没有支付的，
         * 我们需要把这个订单取消 ， 然后库存+操作）小伙伴需要举一反三，比如其他场景也可以用到延迟队列：发货提醒等
         * 学习就是要不断的提升自己，老师授的只是思路，我们需要举一反三，从而提升自己
         */
        try {
            (Redis::getInstance())->zAdd("order_status", time() + 10, $orderNo);
        } catch (\Exception $e) {
            // 记录日志， 添加监控 ，异步根据监控内容处理。
        }
    }

    private static function generateOrderNo()
    {
        $workId = rand(1, 1023);
        $orderNo = Snowflake::getInstance()->setWorkId($workId)->id();

        return $orderNo;
    }

    // 定时任务消费延迟消息队列
    public function checkOrderStatus() {

        $result = (Redis::getInstance())->zRangeByScore("order_status", 0, time(), ['limit' => [0, 1]]);

        if(empty($result) || empty($result[0])) {
            return false;
        }

        try {
            $delRedis = (Redis::getInstance())->zRem("order_status", $result[0]);
        } catch (\Exception $e) {
            // 记录日志
            $delRedis = "";
        }
        if ($delRedis) {
            echo "订单id:{$result[0]}在规定时间内没有完成支付 我们判定为无效订单删除".PHP_EOL;
            /**
             * 第一步： 根据订单ID 去数据库order表里面获取当前这条订单数据 看下当前状态是否是待支付:status = 1
             *        如果是那么我们需要把状态更新为 已取消 status = 7， 否则不需要care
             *
             * 第二步： 如果第一步status修改7之后， 我们需要再查询order_goods表，
             *        拿到 sku_id num  把sku表数据库存增加num
             *        goods表总库存也需要修改。
             */

            $order = $this->model->where('order_no', '=', $result[0])->select('status')->first();
            if ($order->status == 1) {
                $this->model->where('order_no', '=', $result[0])->update(['status' => 7]);

                $select = ['id', 'sku_id', 'num'];
                $conditions = [];
                $conditions[] = ['order_no', '=', $result[0]];
                $goodsOrder = $this->orderGoodsService->model->list($select, $conditions, ['id', 'asc'], false);
                if (count($goodsOrder)) {
                    foreach ($goodsOrder as $goodOrder) {
                        (new GoodsSkuServiceImpl())->incrStock($goodOrder['sku_id'], $goodOrder['num']);
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }
}
