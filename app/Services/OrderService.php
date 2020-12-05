<?php


namespace App\Services;


interface OrderService
{
    /*
     * 前台
     */
    public function add(array $fields) : void;
}
