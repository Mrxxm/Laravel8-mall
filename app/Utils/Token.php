<?php


namespace App\Utils;


class Token
{
    public static function generateToken()
    {
        // 32个字符组成一组随机字符串
        $randChar = getRandChar(32);
        // 用三组字符串,进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        // salt
        $salt = config('secure.token_salt');

        return md5($randChar . $timestamp . $salt);
    }

    public static function getCurrentTokenVar($key)
    {
        // 从http的请求头中获取令牌
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            // 判断是否为数组
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }

            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量不存在');
            }

        }
    }

    public static function getCurrentUId()
    {
        return self::getCurrentTokenVar('uId');
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
}
