<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Api\Response;
use App\Services\Impl\CategoryServiceImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController
{
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


}
