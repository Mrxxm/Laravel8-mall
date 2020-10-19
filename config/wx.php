<?php


return [
    'app_id'           => 'wx275f9dea54e952ee',
    'app_secret'       => '88592069faf27f2630e8218c5003b274',
    'login_url'        => "https://api.weixin.qq.com/sns/jscode2session?" . "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s"
];
