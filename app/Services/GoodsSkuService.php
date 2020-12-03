<?php


namespace App\Services;


interface GoodsSkuService
{
    /*
     * 后台
     */
    public function batchAdd(array $fields) : array;

    public function add(array $fields) : array;

    public function update(int $id, array $fields) : void;
}
