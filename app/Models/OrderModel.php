<?php


namespace App\Models;


class OrderModel extends BaseModel
{
    protected $table = 'order';

    // 使用create方法添加时，需判断
    protected $fillable = ['user_id', 'order_no', 'total_price', 'total_num', 'pay_type', 'express', 'express_order_no', 'message', 'address_id'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
