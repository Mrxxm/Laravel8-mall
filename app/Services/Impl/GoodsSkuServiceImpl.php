<?php


namespace App\Services\Impl;


use App\Models\GoodsSkuModel;
use App\Services\GoodsSkuService;
use App\Utils\ArrayUtil;

class GoodsSkuServiceImpl implements GoodsSkuService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new GoodsSkuModel();
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

        $this->model->updateById($id, $fields);
    }
}
