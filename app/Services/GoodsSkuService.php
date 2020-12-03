<?php


namespace App\Services;


interface GoodsSkuService
{
    /*
     * 后台
     */
    public function BatchAdd(array $fields) : void;
}
