<?php


namespace App\Models;


class CategoryModel extends BaseModel
{
    protected $table = 'category';

    // 使用create方法添加时，需判断
    protected $fillable = ['name', 'pid', 'icon', 'path', 'sort'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';


}
