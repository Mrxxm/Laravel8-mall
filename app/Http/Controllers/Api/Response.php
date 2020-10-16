<?php


namespace App\Http\Controllers\Api;


class Response
{
    // 成功
    const SUCCESS_CODE   = 200;
    // Token错误
    const CODE_LOST      = 100; // 缺少code
    const TOKEN_LOST     = 101; // 缺少token
    const TOKEN_ERROR    = 102; // token校验失败
    // 通用错误
    const MISSING_PARAM  = 901; // 缺少参数
    const UNKNOWN_ERROR  = 999; // 未知错误


    //映射错误信息
    public static $returnMessage  = [

        self::SUCCESS_CODE   => '操作成功',

        self::CODE_LOST      => '缺少code',
        self::TOKEN_LOST     => '缺少token',
        self::TOKEN_ERROR    => 'token校验失败',

        self::MISSING_PARAM  => '缺少参数',
        self::UNKNOWN_ERROR  => '未知错误',

    ];

    //格式化返回
    public static function makeResponse(bool $is_success, int $code, array $data = [], string $msg = '')
    {
        $result = [];
        $result['time']    = time();
        $result['result']  = $is_success;
        $result['code']    = $code;
        $result['data']    = $data;
        $result['message'] = !empty($msg) ? $msg : static::$returnMessage[$code];

        return response()->json($result);
    }
}
