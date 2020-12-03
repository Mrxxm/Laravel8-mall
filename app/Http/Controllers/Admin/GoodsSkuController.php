<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\GoodsSkuServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodsSkuController
{
    public function list(Request $request)
    {
        $data = $request->only('goods_id');

        $validator = Validator::make($data, [
            'goods_id'          => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsSkuServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function update(Request $request)
    {
        $data = $request->only('id', 'stock', 'price', 'cost_price', 'status');

        $validator = Validator::make($data, [
            'id'                  => 'required|integer',
            'stock'               => 'integer',
            'price'               => '',
            'cost_price'          => '',
            'status'              => 'integer|0,1',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsSkuServiceImpl();

        try {
            $service->update($data['id'], $data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
