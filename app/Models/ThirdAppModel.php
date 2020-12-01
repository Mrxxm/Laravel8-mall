<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ThirdAppModel extends BaseModel
{
    protected $table = 'third_app';

    // 默认true
    public $timestamps = true;

    // 默认datetime U-时间戳
    protected $dateFormat = '';

    protected $fillable = ['app_id', 'app_secret', 'app_description', 'scope', 'scope_description'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public static function check(string $username, string $password)
    {
        $user = self::where('app_id', '=', $username)
            ->where('app_secret', '=', md5(md5($password)))
            ->first();

        return resultToArray($user);
    }
}
