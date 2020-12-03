<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\GoodsServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GoodsController
{
    public function list(Request $request)
    {
        $data = $request->only('keyword');

        $validator = Validator::make($data, [
            'keyword'          => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function add(Request $request)
    {
        $data = $request->only('title', 'category_id', 'category_path_id', 'goods_unit', 'keywords', 'stock', 'price', 'cost_price', 'is_show_stock', 'production_time', 'goods_specs_type', 'description', 'status', 'sku');

        $validator = Validator::make($data, [
            'title'               => 'required|string',
            'category_id'         => 'required|integer',
            'category_path_id'    => 'required|string',
            'goods_unit'          => 'required|string',
            'keywords'            => 'required|string',
            'is_show_stock'       => 'required|integer|in:0,1',
            'production_time'     => 'required|date',
            'goods_specs_type'    => 'required|integer|in:1,2',
            'description'         => 'required|string',
            'status'              => 'integer|0,1',
            'stock'               => Rule::requiredIf(function () use ($data) {
                if (isset($data['goods_specs_type'])) {
                    return $data['goods_specs_type'] == 1 ? true : false;
                }
                return false;
            }),
            'price'               => Rule::requiredIf(function () use ($data) {
                if (isset($data['goods_specs_type'])) {
                    return $data['goods_specs_type'] == 1 ? true : false;
                }
                return false;
            }),
            'cost_price'          => Rule::requiredIf(function () use ($data) {
                if (isset($data['goods_specs_type'])) {
                    return $data['goods_specs_type'] == 1 ? true : false;
                }
                return false;
            }),
            'sku' => Rule::requiredIf(function () use ($data) {
                if (isset($data['goods_specs_type'])) {
                    return $data['goods_specs_type'] == 2 ? true : false;
                }
                return false;
            }),
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        // 验证sku中字段-多规格
        if ($data['goods_specs_type'] == 2) {
            // 判断是数组并且不是一维数组
            if (!is_array($data['sku']) || (count($data['sku']) == count($data['sku'], 1))) {
                return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], 'sku格式异常');
            }
            foreach ($data['sku'] as $sku) {
                $validator = Validator::make($sku, [
                    'specs_value_ids'     => 'required|string',
                    'price'               => 'required',
                    'cost_price'          => 'required',
                    'stock'               => 'required|integer',
                ]);

                if ($validator->fails()) {
                    return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
                }
            }
        }

        $service = new GoodsServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // TODO
    public function update(Request $request)
    {
        $data = $request->only('id', 'title', 'category_id', 'category_path_id', 'goods_unit', 'keywords', 'stock', 'price', 'cost_price', 'is_show_stock', 'production_time', 'goods_specs_type', 'description', 'goods_specs_data', 'status');

        $validator = Validator::make($data, [
            'id'               => 'required|integer',
            'status'           => 'integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsServiceImpl();

        try {
            $service->update($data['id'], $data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // TODO
    public function delete(Request $request)
    {
        $data = $request->only('id');

        $validator = Validator::make($data, [
            'id'             => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new GoodsServiceImpl();

        try {
            $service->delete($data['id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
