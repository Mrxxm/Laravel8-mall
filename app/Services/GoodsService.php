<?php


namespace App\Services;


interface GoodsService
{
    /*
     * 前台
     */

    /*
     * 后台
     */
    public function detail(int $id) : array ;

    public function list(array $data) : array ;

    public function add(array $fields) : void;

    public function update(int $id, array $fields) : void;

    public function delete(int $id) : void;
}
