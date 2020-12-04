<?php


namespace App\Models;


class GoodsSkuModel extends BaseModel
{
    protected $table = 'goods_sku';

    // 使用create方法添加时，需判断
    protected $fillable = ['goods_id', 'specs_value_ids', 'price', 'cost_price', 'stock', 'status'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function goods()
    {
        /**
         * hasOne
            1、外键保存在关联表中;
            2、保存时自动更新关联表的记录;
            3、删除主表记录时自动删除关联记录;
           belongsTo
            1、外键放置在主表中;
            2、保存时不会自动更新关联表的记录;
            3、删除时也不会更新关联表的记录;
         */
        return $this->hasOne('App\Models\GoodsModel', 'id', 'goods_id');
//        return $this->belongsTo('App\Models\GoodsModel', 'goods_id', 'id');
    }

    public function updateByGoodsId(int $goodsId, array $fields)
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
