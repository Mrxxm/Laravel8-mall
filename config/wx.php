<?php


return [
    'app_id'           => 'wxd30c4976700557ff',
    'app_secret'       => '8eb1e7ba91318e572a4e57b86316921a',
    'login_url'        => "https://api.weixin.qq.com/sns/jscode2session?" . "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s"
];
