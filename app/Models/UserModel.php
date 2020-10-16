<?php


namespace App\Models;


class UserModel
{
    protected $table = 'user';

    public $timestamps = true;

    protected $dateFormat = 'U';

    const CREATED_AT = 'add_time';

    const UPDATED_AT = 'update_time';

    public static function getByOpenId(string $openid)
    {
        $user = self::where('openid', '=', $openid)
            ->first();

        return resultToArray($user);
    }
}
