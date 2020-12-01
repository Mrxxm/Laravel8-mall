<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'user';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public static function getByOpenId(string $openid)
    {
        $user = self::where('openid', '=', $openid)
            ->first();

        return resultToArray($user);
    }

    public function add(string $openid)
    {
        $field = [];
        $field['openid'] = $openid;

        return self::insertGetId($field);
    }
}
