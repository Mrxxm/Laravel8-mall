<?php


namespace App\Services\Impl;


use App\Services\CartService;
use App\Utils\ArrayUtil;
use App\Utils\Redis;

class CartServiceImpl implements CartService
{
    public function add(array $fields): void
    {
        $num = $fields['num'];
        $skuId = $fields['sku_id'];
        $userId = request('uId');
        $conditions = [];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['delete_time', '=', 0];
        $skuWithGoods = (new GoodsSkuServiceImpl())->model
            ->with('goods')
            ->where($conditions)
            ->find($skuId);
        $skuWithGoods = resultToArray($skuWithGoods);
        if (!$skuWithGoods || !$skuWithGoods['goods']) {
            throw new \Exception('商品已下架');
        }

        $data = [
            "title"       => $skuWithGoods['goods']['title'],
            "num"         => $num,
            "goods_id"    => $skuWithGoods['goods']['id'],
            "create_time" => time(),
        ];

        try {
            $key = 'cart_' . $userId;
            $get = (Redis::getInstance())->hGet($key, $skuId);
            if ($get) {
                $get = json_decode($get, true);
                $data['num'] += $get['num'];
            }

            $res = (Redis::getInstance())->hSet($key, $skuId, json_encode($data));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(int $skuId, array $fields): void
    {
        $userId = request('uId');
        $key = 'cart_' . $userId;

        try {
            $get = (Redis::getInstance())->hGet($key, $skuId);
            if ($get) {
                $get = json_decode($get, true);
                $get['num'] = $fields['num'];
            } else {
                throw new \Exception("不存在该购物车的商品，您更新没有任何意义");
            }
            $res = (Redis::getInstance())->hSet($key, $skuId, json_encode($get));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(string $skuIds): void
    {
        if(!is_array($skuIds)) {
            $ids = explode(",", $skuIds);
        }
        $userId = request('uId');
        $key = 'cart_' . $userId;
        try {
            // ... 是PHP提供一个特性 可变参数
            $res = (Redis::getInstance())->hDel($key, ...$ids);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function list(array $data): array
    {
        $userId = request('uId');
        $key = 'cart_' . $userId;

        $ids = $data['ids'] ?? '';
        try {
            if ($ids) {
                $ids = explode(",", $ids);
                $redisCart = (Redis::getInstance())->hMget($key, $ids);
                if(in_array(false, array_values($redisCart))) {
                    return [];
                }
            } else {
                $redisCart = (Redis::getInstance())->hGetAll($key);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if(!$redisCart) {
            return [];
        }

        $result = [];
        $skuIds = array_keys($redisCart);

        $select = ['id as sku_id', 'specs_value_ids', 'goods_id', 'price', 'cost_price', 'stock'];
        $conditions = [];
        $conditions[] = ['id', 'in', $skuIds];
        $orderBy = ['id', 'asc'];
        $skies = (new GoodsSkuServiceImpl())->model->list($select, $conditions, $orderBy, false);

        $skuIdStock = array_column($skies, "stock", "sku_id");
        $skuIdPrice = array_column($skies, "price", "sku_id");
        $svIdsToSkuId = array_column($skies, 'sku_id', 'specs_value_ids');
        $specsValues = (new SpecsValueServiceImpl())->handleSpecsValue($svIdsToSkuId);

        foreach ($redisCart as $skuId => $cartInfo) {
            $cartInfo = json_decode($cartInfo, true);
            if ($ids && isset($stocks[$skuId]) && $skuIdStock[$skuId] < $cartInfo['num']) {
                throw new \Exception($cartInfo['title']."的商品库存不足");
            }
            $price = $skuIdPrice[$skuId] ?? 0;
            $cartInfo['sku_id']      = $skuId;
            $cartInfo['price']       = $price;
            $cartInfo['total_price'] = $price * $cartInfo['num'];
            $cartInfo['sku']         = $specsValues[$skuId] ?? "暂无规则";
            $result[] = $cartInfo;
        }
        if (!empty($result)) {
            $result = ArrayUtil::arrsSortByKey($result, "create_time");
        }

        return $result;
    }

    /**
     * 获取购物车数据
     */
    public function getCount() {
        $userId = request('uId');
        $key = 'cart_' . $userId;
        try {
            $count = (Redis::getInstance())->hLen($key);
        }catch (\Exception $e) {
            return 0;
        }
        return intval($count);
    }
}
