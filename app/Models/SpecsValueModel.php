<?php


namespace App\Models;


class SpecsValueModel extends BaseModel
{
    protected $table = 'specs_value';

    // 使用create方法添加时，需判断
    protected $fillable = ['specs_id', 'name'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
