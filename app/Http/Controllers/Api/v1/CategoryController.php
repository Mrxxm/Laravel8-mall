<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\CategoryServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController
{
    public function listAll(Request $request)
    {
        $data = $request->only();

        $validator = Validator::make($data, [
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CategoryServiceImpl();

        try {
            $result = $service->listAll();
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }
}
