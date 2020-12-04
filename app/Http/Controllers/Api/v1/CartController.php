<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\CartServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController
{
    public function add(Request $request)
    {
        $data = $request->only('sku_id', 'num');

        $validator = Validator::make($data, [
            'sku_id'           => 'required|integer',
            'num'              => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CartServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function update(Request $request)
    {
        $data = $request->only('sku_id', 'num');

        $validator = Validator::make($data, [
            'sku_id'              => 'required|integer',
            'num'                 => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CartServiceImpl();

        try {
            $service->update($data['sku_id'], $data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function delete(Request $request)
    {
        $data = $request->only('sku_ids');

        $validator = Validator::make($data, [
            'sku_ids'              => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CartServiceImpl();

        try {
            $service->delete($data['sku_ids']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
