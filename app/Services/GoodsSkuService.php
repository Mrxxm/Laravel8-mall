<?php


namespace App\Services;


interface GoodsSkuService
{
    /*
     * 后台
     */
    public function batchAdd(array $fields) : array;
}
