<?php


namespace App\Http\Controllers\Api\v1;


class DemoController
{
    public function index()
    {
        $randChar = getRandChar(32);
        return $randChar;
    }
}
