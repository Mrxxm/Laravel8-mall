<?php


namespace App\Http\Middleware;


use App\Http\Controllers\Api\Response;
use App\Utils\Token;
use Closure;
use Illuminate\Support\Facades\Validator;

class checkAdminToken
{
    function handle($request, Closure $next, $guard = null)
    {
        $token = request()->header('token');

        $validator = Validator::make(['token' => $token], [
            'token'           => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::TOKEN_LOST);
        }

        $isValid = Token::verifyToken($token);

        if (!$isValid) {
            return Response::makeResponse(false, Response::TOKEN_ERROR);
        }

        try {
            $uId = Token::getCurrentUId();
            $request['uId'] = $uId;
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::TOKEN_ERROR);
        }

        return $next($request);
    }
}
