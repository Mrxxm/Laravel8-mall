<?php


namespace App\Models;


class GoodsSkuModel extends BaseModel
{
    protected $table = 'goods_sku';

    // 使用create方法添加时，需判断
    protected $fillable = ['goods_id', 'specs_value_ids', 'price', 'cost_price', 'stock', 'status'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function updateByGoodsId(int $goodsId, array $fields) : void
    {
        return self::where('goods_id', $goodsId)
            ->update($fields);
    }

    public function deleteByGoodsId(int $goodsId)
    {
        return self::where('goods_id', $goodsId)
            ->update(['delete_time' => time()]);
    }

}
