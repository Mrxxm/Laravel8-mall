<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\AdminUserServiceImpl;
use App\Services\Impl\AppTokenServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserController
{
    public function list(Request $request)
    {
        $data = $request->only('keyword');

        $validator = Validator::make($data, [
            'keyword'             => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new AdminUserServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function add(Request $request)
    {
        $data = $request->only('app_id', 'app_secret', 'app_description', 'scope', 'scope_description');

        $validator = Validator::make($data, [
            'app_id'             => 'required|string',
            'app_secret'         => 'required|string',
            'app_description'    => 'string',
            'scope'              => 'required|string|in:32',
            'scope_description'  => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new AdminUserServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function update(Request $request)
    {
        $data = $request->only('id', 'app_secret', 'app_description', 'scope', 'scope_description');

        $validator = Validator::make($data, [
            'id'                 => 'required|integer',
            'app_secret'         => 'string',
            'app_description'    => 'string',
            'scope'              => 'string|in:32',
            'scope_description'  => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new AdminUserServiceImpl();

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
            'id'                 => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new AdminUserServiceImpl();

        try {
             $service->delete($data['id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
