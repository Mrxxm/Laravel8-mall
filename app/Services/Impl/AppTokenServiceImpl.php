<?php


namespace App\Services\Impl;


use App\Models\ThirdAppModel;
use App\Services\AppTokenService;
use App\Utils\Token;
use Illuminate\Support\Facades\Cache;

class AppTokenServiceImpl implements AppTokenService
{
    public function get(string $username, string $password): string
    {
        $app = ThirdAppModel::check($username, $password);
        if (!$app) {
            throw new \Exception('登录失败，用户不存在或密码错误!');
        }

        $values = [
            'scope' => $app['scope'],
            'uid' => $app['id'],
        ];
        try {
            $token = $this->saveToCache($values);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $token;
    }


    private function saveToCache($values)
    {
        $token = Token::generateToken();
        $expire_in = config('setting.token_expire_in');
        $result = Cache::set($token, json_encode($values), $expire_in);
        if (!$result) {
            throw new \Exception('服务器缓存异常');
        }

        return $token;
    }
}
