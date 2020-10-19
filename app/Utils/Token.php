<?php


namespace App\Utils;


use Illuminate\Support\Facades\Cache;

class Token
{
    public static function generateToken()
    {
        // 32个字符组成一组随机字符串
        $randChar = getRandChar(32);

        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        // salt
        $salt = config('secure.token_salt');

        return md5($randChar . $timestamp . $salt);
    }

    // 检测令牌是否过期
    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if ($exist) {
            return true;
        } else {
            return false;
        }
    }

    public static function getCurrentUId()
    {
        try {
            return self::getCurrentTokenVar('uId');
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public static function getCurrentTokenVar($key)
    {
        $token = request()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new \Exception('102');
        } else {
            // 判断是否为数组
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }

            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new \Exception('尝试获取的Token变量不存在');
            }
        }
    }

    // 前置方法一 (用户，管理员)
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope >= ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // 前置方法二 (用户)
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope == ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // 检测传入的用户Id是否为当前登录用户Id
    public static function isValidOpera($checkedUId)
    {
        if (empty($checkedUId)) {
            throw new Exception('检查UId时必须传入被检测的UId');
        }

        $currentOperaUId = self::getCurrentUId();
        if ($checkedUId == $currentOperaUId) {
            return true;
        }

        return false;
    }

}
