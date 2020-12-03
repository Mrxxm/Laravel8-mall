<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\CategoryServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController
{
    public function search(Request $request)
    {
        $data = $request->only('pid');

        $validator = Validator::make($data, [
            'pid'              => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CategoryServiceImpl();

        try {
            $result = $service->search($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function list(Request $request)
    {
        $data = $request->only('keyword', 'pid');

        $validator = Validator::make($data, [
            'pid'              => 'required|integer',
            'keyword'          => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CategoryServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function add(Request $request)
    {
        $data = $request->only('name', 'pid', 'icon', 'sort');

        $validator = Validator::make($data, [
            'name'             => 'required|string',
            'pid'              => 'required|integer',
            'icon'             => 'string',
            'sort'             => 'integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CategoryServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function update(Request $request)
    {
        $data = $request->only('id', 'icon', 'status', 'sort');

        $validator = Validator::make($data, [
            'id'               => 'required|integer',
            'icon'             => 'string',
            'status'           => 'integer',
            'sort'             => 'integer',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new CategoryServiceImpl();

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

        $service = new CategoryServiceImpl();

        try {
            $service->delete($data['id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

}
