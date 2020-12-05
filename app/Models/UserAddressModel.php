<?php


namespace App\Models;


class UserAddressModel extends BaseModel
{
    protected $table = 'user_address';

    protected $fillable = ['name', 'mobile', 'province', 'city', 'country', 'detail', 'user_id'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
