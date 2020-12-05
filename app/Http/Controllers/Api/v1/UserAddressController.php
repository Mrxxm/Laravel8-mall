<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\UserAddressServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAddressController
{
    public function list(Request $request)
    {
        $data = $request->only('');

        $validator = Validator::make($data, [
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new UserAddressServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function add(Request $request)
    {
        $data = $request->only('name', 'mobile', 'province', 'city', 'country', 'detail', 'user_id');

        $validator = Validator::make($data, [
            'name'               => 'required|string',
            'mobile'             => 'required|string',
            'province'           => 'required|string',
            'city'               => 'required|string',
            'country'            => 'required|string',
            'detail'             => 'required|string',
            'user_id'            => 'integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new UserAddressServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function update(Request $request)
    {
        $data = $request->only('id', 'name', 'mobile', 'province', 'city', 'country', 'detail');

        $validator = Validator::make($data, [
            'id'                 => 'required|integer',
            'name'               => 'string',
            'mobile'             => 'string',
            'province'           => 'string',
            'city'               => 'string',
            'country'            => 'string',
            'detail'             => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new UserAddressServiceImpl();

        try {
            $service->update($data['id'], $data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function delete(Request $request)
    {
        $data = $request->only('id');

        $validator = Validator::make($data, [
            'id'             => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new UserAddressServiceImpl();

        try {
            $service->delete($data['id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
