<?php


namespace App\Models;


class OrderGoodsModel extends BaseModel
{
    protected $table = 'order_goods';

    // 使用create方法添加时，需判断
    protected $fillable = ['order_no', 'sku_id', 'goods_id', 'num', 'title', 'sku', 'price', 'total_price', 'image'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
