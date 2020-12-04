<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\GoodsSkuServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodsController
{
    public function detail(Request $request)
    {
        $data = $request->only('sku_id');

        $validator = Validator::make($data, [
            'sku_id'             => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsSkuServiceImpl();

        try {
            $result = $service->detailBySkuId($data['sku_id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }
}
