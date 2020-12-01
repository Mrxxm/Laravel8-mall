<?php

if (!function_exists('json')) {
    /**
     * 数组返回
     * @param $res
     * @return string
     */
    function json($res)
    {
        if (empty($res)) {
            return '';
        }

        return json_encode($res);
    }
}

if (!function_exists('resultToArray')) {
    /**
     * 数组返回
     * @param $res
     * @return array
     */
    function resultToArray($res)
    {
        if (empty($res)) {
            return [];
        }

        $res = json_decode(json_encode($res), true);

        if (isset($res['links'])) {
            unset($res['links']);
        }

        return $res;
    }
}

if (!function_exists('getRandChar')) {
    /**
     * 获取随机字符串
     * @param $length
     * @return string
     */
    function getRandChar(int $length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }

        return $str;
    }
}

if (!function_exists('api')) {
    /**
     * @param string $url
     * @param string $params
     * @param string $method
     * @param array $header
     * @throws \Exception
     * @return
     */
    function api(string $url, string $params = '', string $method = 'GET', array $header = array())
    {
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => FALSE
        );
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url.'?';
                if (!empty($params)) {
                    $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                }
                break;
            case 'POST':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = TRUE;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error){
            throw new Exception('curl执行出错');
        }

        return $result;
    }
}

if (!function_exists('DateFormat')) {
    /**
     * 数组返回
     * @param $time
     * @return string
     */
    function DateFormat($time = '')
    {
        if (empty($time) ) {
            if ($time == 0) {
                return '';
            }
            return Date('Y-m-d H:i:s', time());
        }

        return Date('Y-m-d H:i:s', $time);
    }
}


