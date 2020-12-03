<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\SpecsValueServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecsValueController
{
    public function search(Request $request)
    {
        $data = $request->only('keyword', 'specs_id');

        $validator = Validator::make($data, [
            'specs_id'         => 'required|integer',
            'keyword'          => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new SpecsValueServiceImpl();

        try {
            $result = $service->search($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function list(Request $request)
    {
        $data = $request->only('keyword', 'specs_id');

        $validator = Validator::make($data, [
            'specs_id'         => 'required|integer',
            'keyword'          => 'string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new SpecsValueServiceImpl();

        try {
            $result = $service->list($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE, $result);
    }

    public function add(Request $request)
    {
        $data = $request->only('specs_id', 'name');

        $validator = Validator::make($data, [
            'specs_id'         => 'required|integer',
            'name'             => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new SpecsValueServiceImpl();

        try {
            $service->add($data);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    public function update(Request $request)
    {
        $data = $request->only('id', 'status');

        $validator = Validator::make($data, [
            'id'               => 'required|integer',
            'status'           => 'integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return Response::makeResponse(false, Response::MISSING_PARAM, [], $validator->errors()->first());
        }

        $service = new SpecsValueServiceImpl();

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

        $service = new SpecsValueServiceImpl();

        try {
            $service->delete($data['id']);
        } catch (\Exception $exception) {
            return Response::makeResponse(false, Response::UNKNOWN_ERROR, [], $exception->getMessage());
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
