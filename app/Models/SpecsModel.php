<?php


namespace App\Models;


class SpecsModel extends BaseModel
{
    protected $table = 'specs';

    // 使用create方法添加时，需判断
    protected $fillable = ['name'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
