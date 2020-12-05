<?php


namespace App\Services\Impl;


use App\Models\GoodsSkuModel;
use App\Services\GoodsSkuService;
use App\Utils\ArrayUtil;
use App\Utils\Redis;

class GoodsSkuServiceImpl implements GoodsSkuService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new GoodsSkuModel();
    }

    // sku_id => sku表 -> goods_id => goods表 -> goods_id => sku表
    public function detailBySkuId(int $skuId): array
    {
        // with()查询：实际执行了两条sql语句
        // withJoin()查询：才是连表查询
        $conditions = [];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['delete_time', '=', 0];
        $skuWithGoods = $this->model->with('goods')->where($conditions)->find($skuId);
        $skuWithGoods = resultToArray($skuWithGoods);
        if (!$skuWithGoods || !$skuWithGoods['goods']) {
            throw new \Exception('商品已下架');
        }
        $select = ['id as sku_id', 'goods_id', 'specs_value_ids', 'price', 'cost_price', 'stock'];
        $conditions = [];
        $conditions[] = ['goods_id', '=', $skuWithGoods['goods_id']];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['delete_time', '=', 0];
        $orderBy = ['id', 'asc'];
        $skies = $this->model->list($select, $conditions, $orderBy, false);
        $svIdsToSkuId = array_column($skies, 'sku_id', 'specs_value_ids');

        // 商品规格 1统一 2多规格
        if($skuWithGoods['goods']['goods_specs_type'] == 1) {
            $sku = [];
        } else {
            $flagValue = '';
            foreach ($svIdsToSkuId as $key => $skuIdValue) {
                if ($skuIdValue == $skuId) {
                    $flagValue = $key;
                }
            }
            $sku = (new SpecsValueServiceImpl())->handleGoodsSkies($svIdsToSkuId, $flagValue);
        }

        $result = [
            "title"       => $skuWithGoods['goods']['title'],
            "price"       => $skuWithGoods['price'],
            "cost_price"  => $skuWithGoods['cost_price'],
            "sales_count" => 0,
            "stock"       => $skuWithGoods['stock'],
            "gids"        => $svIdsToSkuId,
            "sku"         => $sku,
            "detail" => [
                "d1" => [
                ],
                "d2" => ''
            ],
        ];

        Redis::getInstance()->incr('Laravel8:goods_' . $skuId . '_pv');

        return $result;
    }

    public function batchAdd(array $fields): array
    {
        $goodsId = $fields['goods_id'];
        unset($fields['goods_id']);

        foreach ($fields as $field) {
            $insert['goods_id']        = $goodsId;
            $insert['specs_value_ids'] = $field['specs_value_ids'];
            $insert['price']           = $field['price'];
            $insert['cost_price']      = $field['cost_price'];
            $insert['stock']           = $field['stock'];
            $sku[] = ($this->model->add($insert))->toArray();
        }

        // 排序-取出价格cost_price价格最低的回填到商品基础信息表
        $sku1 = ArrayUtil::arrsSortByKey($sku, 'cost_price', SORT_ASC);

        return $sku1;
    }

    public function add(array $fields): array
    {
        $goodsId = $fields['goods_id'];
        unset($fields['goods_id']);

        $insert['goods_id']        = $goodsId;
        $insert['specs_value_ids'] = '';
        $insert['price']           = $fields['price'];
        $insert['cost_price']      = $fields['cost_price'];
        $insert['stock']           = $fields['stock'];

        $sku = ($this->model->add($insert))->toArray();

        return $sku;
    }

    public function update(int $id, array $fields): void
    {
        $goodsSku = $this->model->find($id);
        if (!$goodsSku) {
            throw new \Exception('商品sku不存在');
        }
        if ($goodsSku->delete_time != 0) {
            throw new \Exception('商品sku已删除');
        }

        // 更新商品sku表
        $goodsId = $goodsSku->goods_id;
        $this->model->updateById($id, $fields);

        $select = ['id', 'price', 'cost_price', 'stock'];
        $conditions = [];
        $conditions[] = ['goods_id', '=', $goodsId];
        $conditions[] = ['delete_time', '=', 0];
        $orderBy = ['cost_price', 'asc']; // 按最低销售排序
        $skuResult = $this->model->list($select, $conditions, $orderBy, false);

        // 总库存
        $stock = array_sum(array_column($skuResult, "stock"));
        $goodsUpd = [
            'price'       => $skuResult[0]['price'],
            'cost_price'  => $skuResult[0]['cost_price'],
            'stock'       => $stock,
            'sku_id'      => $skuResult[0]['id'],
        ];
        // 更新商品表
        (new GoodsServiceImpl())->model->updateById($goodsId, $goodsUpd);
    }

    public function incrStock(int $id, int $num)
    {
        $goodsSku = $this->model->find($id);
        if (!$goodsSku) {
            throw new \Exception('商品sku不存在');
        }
        if ($goodsSku->delete_time != 0) {
            throw new \Exception('商品sku已删除');
        }

        $this->model->increment('stock', $num);
        (new GoodsServiceImpl())->model->increment('stock', $num);
    }

    public function decrStock(int $id, int $num)
    {
        $goodsSku = $this->model->find($id);
        if (!$goodsSku) {
            throw new \Exception('商品sku不存在');
        }
        if ($goodsSku->delete_time != 0) {
            throw new \Exception('商品sku已删除');
        }

        $this->model->decrement('stock', $num);
        (new GoodsServiceImpl())->model->decrement('stock', $num);
    }
}
