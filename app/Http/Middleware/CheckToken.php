<?php


namespace App\Http\Middleware;

use App\Http\Controllers\Api\Response;
use App\Utils\Token;
use Closure;
use Illuminate\Support\Facades\Validator;

class CheckToken
{
    function handle($request, Closure $next, $guard = null)
    {
        $token = request()->header('token');

        $validator = Validator::make([$token], [
            'token'           => 'required|string'
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::TOKEN_LOST);
        }

        $isValid = Token::verifyToken($token);

        if (!$isValid) {
            return Response::makeResponse(false, Response::TOKEN_ERROR);
        }

        return $next($request);
    }
}
