<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\OrderServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController
{
    public function add(Request $request)
    {
        $data = $request->only('address_id', 'sku_ids');

        $validator = Validator::make($data, [
            'address_id'         => 'required|integer',
            'sku_ids'            => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new OrderServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function addSingle(Request $request)
    {
        $data = $request->only('address_id', 'sku_id', 'num');

        $validator = Validator::make($data, [
            'address_id'         => 'required|integer',
            'sku_id'             => 'required|integer',
            'num'                => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new OrderServiceImpl();

        try {
            $service->addSingle($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function checkOrderStatus()
    {
        (new OrderServiceImpl())->checkOrderStatus();
    }
}
