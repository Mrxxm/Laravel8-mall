<?php


namespace App\Services\Impl;


use App\Models\UserModel;
use App\Services\UserTokenService;
use App\Utils\ScopeEnum;
use App\Utils\Token;
use Illuminate\Support\Facades\Cache;

class UserTokenServiceImpl implements UserTokenService
{
    protected $code;

    protected $appId;

    protected $appSecret;

    protected $loginUrl;

    function __construct($code)
    {
        $this->code      = $code;
        $this->appId     = config('wx.app_id');
        $this->appSecret = config('wx.app_secret');
        $this->loginUrl  = sprintf(config('wx.login_url'), $this->appId, $this->appSecret, $this->code);
    }

    public function get(): string
    {
        try {
            $result = api($this->loginUrl);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        $wxResult = resultToArray($result);

        if (empty($wxResult)) {

            throw new \Exception('获取open_id，session_key异常，微信内部错误');

        } else {

            $loginFail = array_key_exists('errorcode', $wxResult);

            if ($loginFail) {
                throw new \Exception('msg:' . $wxResult['errmsg'] . ' errorCode:' . $wxResult['errcode']);
            } else {
                try {
                    // 授权令牌
                    return $this->grantToken($wxResult);
                } catch (\Exception $exception) {
                    throw new \Exception($exception->getMessage());
                }
            }
        }
    }

    // 颁发令牌
    private function grantToken($wxResult)
    {
        // 拿到open_id
        $openid = $wxResult['openid'];
        // 查询数据库，判断open_id是否存在，不存在则新增一条记录，存在则不处理
        $user = UserModel::getByOpenId($openid);
        if ($user) {
            $uId = $user['id'];
        } else {
            $uId = $this->newUser($openid);
        }
        // 1)准备缓存数据 2)生成令牌  3)写入缓存
        $cacheValue = $this->prepareCacheValue($wxResult, $uId);
        // 把令牌返回到客户端
        try {
            $token = $this->saveToCache($cacheValue);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $token;
    }

    private function prepareCacheValue($wxResult, $uId)
    {
        $cacheValue = $wxResult;
        $cacheValue['uId'] = $uId;
        // scope=16 代表APP用户权限数值
        $cacheValue['scope'] = ScopeEnum::User;
        // scope=32 代表CMS管理员权限数值
        // $cacheValue['scope'] = ScopeEnum::Super;
        return $cacheValue;
    }

    private function saveToCache($cacheValue)
    {
        $key = Token::generateToken();
        $value = json($cacheValue);
        $expire_in = config('secure.token_expire_in');

        // 存入缓存(默认使用文件缓存系统)
        $request = Cache::set($key, $value, $expire_in);
        if (!$request) {
            throw new \Exception('服务器缓存异常');
        }

        return $key;
    }

    private function newUser($openid)
    {
        $user = UserModel::insert([
            'openid' => $openid
        ]);

        return $user->id;
    }
}
