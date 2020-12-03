<?php


namespace App\Models;


class GoodsModel extends BaseModel
{
    protected $table = 'goods';

    // 使用create方法添加时，需判断
    protected $fillable = ['title', 'category_id', 'category_path_id', 'goods_unit', 'keywords', 'stock', 'price', 'cost_price', 'sku_id', 'is_show_stock', 'production_time', 'goods_specs_type', 'description', 'goods_specs_data', 'status'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}
