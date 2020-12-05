<?php


namespace App\Services\Impl;


use App\Models\OrderGoodsModel;
use App\Services\OrderGoodsService;

class OrderGoodsServiceImpl implements OrderGoodsService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new OrderGoodsModel();
    }
}
