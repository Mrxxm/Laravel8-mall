<?php


namespace App\Http\Middleware;

use App\Http\Controllers\Api\Response;
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

        return $next($request);
    }
}
