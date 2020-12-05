<?php


namespace App\Http\Controllers\Api\v1;


use App\Utils\Snowflake;

class DemoController
{
    public function index()
    {
        $randChar = getRandChar(32);
        return $randChar;
    }

    public function snowflake()
    {
        // workId 1 ~ 1023
        $NO = Snowflake::getInstance()->setWorkId(1)->id();
        return $NO;
    }
}
