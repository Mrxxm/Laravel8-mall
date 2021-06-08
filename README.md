## About Laravel8 Market

1.引用Laravel8框架完成商城项目

2.yum安装php方式[php7.2](https://www.cnblogs.com/itwlp/p/12004150.html)

3.nginx配置

```
server {
        ssl on;

        listen 443;

        ssl_certificate tool.kenrou.cn.pem;
        ssl_certificate_key tool.kenrou.cn.key;
        ssl_session_timeout 5m;
        ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;

        server_name  tool.kenrou.cn ;
        set $root /var/www/laravel8/public;
	root /var/www/laravel8/public;

    	error_log /var/log/nginx/laravel_8.error.log;
    	access_log /var/log/nginx/laravel_8.access.log;

    	#location /static {
   	 #   	try_files $uri $uri/ =404;
    	#}

    location / {
        #autoindex on;
        #autoindex_exact_size on;
        #autoindex_localtime on;
        if ( !-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
            break;
        }
    }

    location ~ .+\.php($|/) {
        fastcgi_pass 127.0.0.1:9001;
        fastcgi_index index.php;
        fastcgi_split_path_info ^((?U).+.php)(/?.+)$;
	fastcgi_param HTTPS on;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $root$fastcgi_script_name;
        include fastcgi_params;
    }


    location ~ .*\.(jpg|jpeg|gif|png|ico|swf)$  {
        expires 3y;
        gzip off;
    }
}
```

4.数据库备份[数据库](http://blog.kenrou.cn/Laravel8-mall.sql)

5.Document

[接口文档](https://www.showdoc.com.cn/kenrou?page_id=5597339566627627)

6.关系图

![](https://img9.doubanio.com/view/photo/l/public/p2629005626.jpg)

7.Explanatory Chart

* 商城系统

* 前台

1.小程序登录  
2.用户相关  
3.分类相关  
4.商品相关  
5.购物车相关  
6.用户地址  
7.订单相关  

* 后台

1.后台登录  
2.前台用户管理  
3.后台用户管理  
4.分类管理  
5.规格管理  
6.规格属性管理  
7.商品管理  

![](https://img3.doubanio.com/view/photo/l/public/p2627783720.jpg)














## 秒杀的三种方式

```
class SecKillController
{
    const COUNT = 1;

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/sharedLock
    // 非阻塞 不公平
    public function sharedLock(Request $request)
    {
        $storage = DB::table('storage')
            ->where('id', '=', 1)
            ->first();

        $quantity = $storage->number;

        $upd = [];
        $upd['number'] = $quantity - self::COUNT;

        $result = DB::table('storage')
            ->where('id', '=', 1)
            ->where('number', '>=', self::COUNT)
            ->update($upd);

        if ($result) {
            $insert = [];
            $insert['number'] = $quantity;
            DB::table('order')
                ->insert($insert);
        }

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/exclusiveLock
    // 阻塞 公平
    public function exclusiveLock(Request $request)
    {
        DB::beginTransaction();

        $storage = DB::table('storage')
            ->where('id', '=', 1)
            ->lockForUpdate()
            ->first();

        $quantity = $storage->number;

        $upd = [];
        $upd['number'] = $quantity - self::COUNT;

        $result = DB::table('storage')
            ->where('id', '=', 1)
            ->where('number', '>=', self::COUNT)
            ->update($upd);

        if ($result) {
            $insert = [];
            $insert['number'] = $quantity;
            DB::table('order')
                ->insert($insert);
        }

        DB::commit();

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }

    // storage 10
    // ab -n 20 -c 10 http://www.tool.com/api/v1/secKill/redisLock
    // 非阻塞 不公平
    public function redisLock(Request $request)
    {
        $redis = Redis::getInstance();
        $setRes = $redis->setnx('storage', 5);
        if ($setRes) {

            $storage = DB::table('storage')
                ->where('id', '=', 1)
                ->first();

            $quantity = $storage->number;

            $upd = [];
            $upd['number'] = $quantity - self::COUNT;

            $result = DB::table('storage')
                ->where('id', '=', 1)
                ->where('number', '>=', self::COUNT)
                ->update($upd);

            if ($result) {
                $insert = [];
                $insert['number'] = $quantity;
                DB::table('order')
                    ->insert($insert);
            }
        }
        $redis->del('storage');

        return Response::makeResponse(true, Response::SUCCESS_CODE);
    }
}
```
#### 发红包限制

```
2.88元 200个
6.66元 150个
8.88元 48个
```

```$xslt
<?php


namespace App\Http\Controllers\Dsc\v1;



use App\Services\Dsc\RedisOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Config\Definition\Exception\Exception;

class RedPacketController
{
    private $redPacketRuleTable = 'red_packet_rule';
    private $redPacketLogs = 'red_packet_logs';
    const COUNT = 1;

    // 共享锁方案
    // 非阻塞 不公平
    public function shareLockGet(Request $request)
    {
        $activityId = request('activity_id') ?? 1;
        $userId = request('userid') ?? rand(1000, 9999);

        $isExist = DB::table($this->redPacketLogs)
            ->where('activity_id', '=', $activityId)
            ->where('user_id', '=', $userId)
            ->count();
        if ($isExist) {
            return '已经领取';
        }

        $redPacketRule = DB::table($this->redPacketRuleTable)
            ->select('id as rule_id', 'activity_id', 'amount', 'num', 'handle_num')
            ->where('activity_id', '=', $activityId)
            ->where('is_del', '=', 0)
            ->where('is_show', '=', 1)
            ->where('handle_num', '>', 0)
            ->get()
            ->toArray();

        if (empty($redPacketRule)) {
            return "已领完！";
        }
        $ruleIdArr = array_column($redPacketRule, 'rule_id', 'amount');
        $redPacketRuleArr = array_column($redPacketRule, 'handle_num', 'amount');

        $redPacketRuleKey = array_rand($redPacketRuleArr);
        $ruleId = $ruleIdArr[$redPacketRuleKey];
        $num = $redPacketRuleArr[$redPacketRuleKey];

        $upd = [];
        $upd['handle_num'] = $num - self::COUNT;
        $result = DB::table($this->redPacketRuleTable)
            ->where('id', '=', $ruleId)
            ->where('handle_num', '>=', self::COUNT)
            ->update($upd);
        if ($result) {
            $fields = [];
            $fields['activity_id'] = $activityId;
            $fields['rule_id']     = $ruleId;
            $fields['user_id']     = $userId;
            DB::table($this->redPacketLogs)
                ->insert($fields);

            return "领取成功！" . $redPacketRuleKey;
        } else {
            return "领取失败！";
        }
    }

    // 悲观锁方案
    // 阻塞 公平
    public function exclusiveLockGet(Request $request)
    {
        $activityId = request('activity_id') ?? 1;
        $userId = request('userid') ?? rand(1000, 9999);

        $isExist = DB::table($this->redPacketLogs)
            ->where('activity_id', '=', $activityId)
            ->where('user_id', '=', $userId)
            ->count();
        if ($isExist) {
            return '已经领取';
        }

        DB::beginTransaction();

        try {
            $redPacketRule = DB::table($this->redPacketRuleTable)
                ->select('id as rule_id', 'activity_id', 'amount', 'num', 'handle_num')
                ->where('activity_id', '=', $activityId)
                ->where('is_del', '=', 0)
                ->where('is_show', '=', 1)
                ->where('handle_num', '>', 0)
                ->lockForUpdate()
                ->get()
                ->toArray();

            if (empty($redPacketRule)) {
                return "已领完！";
            }
            $ruleIdArr = array_column($redPacketRule, 'rule_id', 'amount');
            $redPacketRuleArr = array_column($redPacketRule, 'handle_num', 'amount');

            $redPacketRuleKey = array_rand($redPacketRuleArr);
            $ruleId = $ruleIdArr[$redPacketRuleKey];
            $num = $redPacketRuleArr[$redPacketRuleKey];

            $upd = [];
            $upd['handle_num'] = $num - self::COUNT;
            $result = DB::table($this->redPacketRuleTable)
                ->where('id', '=', $ruleId)
                ->update($upd);
            if ($result) {
                $fields = [];
                $fields['activity_id'] = $activityId;
                $fields['rule_id']     = $ruleId;
                $fields['user_id']     = $userId;
                DB::table($this->redPacketLogs)
                    ->insert($fields);
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }
        DB::commit();

        return "领取成功！" . $redPacketRuleKey;
    }

    // redis锁方案
    // 非阻塞 不公平
    public function redisLockGet(Request $request)
    {
        $activityId = request('activity_id') ?? 1;
        $userId = request('userid') ?? rand(1000, 9999);

        $isExist = DB::table($this->redPacketLogs)
            ->where('activity_id', '=', $activityId)
            ->where('user_id', '=', $userId)
            ->count();
        if ($isExist) {
            return '已经领取';
        }

        $redis = new RedisOperation();
        $setRes = $redis->setnx($activityId, 1);
        if ($setRes) {
            $redPacketRule = DB::table($this->redPacketRuleTable)
                ->select('id as rule_id', 'activity_id', 'amount', 'num', 'handle_num')
                ->where('activity_id', '=', $activityId)
                ->where('is_del', '=', 0)
                ->where('is_show', '=', 1)
                ->where('handle_num', '>', 0)
                ->get()
                ->toArray();

            if (empty($redPacketRule)) {
                $redis->del($activityId);

                return "已领完！";
            }
            $ruleIdArr = array_column($redPacketRule, 'rule_id', 'amount');
            $redPacketRuleArr = array_column($redPacketRule, 'handle_num', 'amount');

            $redPacketRuleKey = array_rand($redPacketRuleArr);
            $ruleId = $ruleIdArr[$redPacketRuleKey];
            $num = $redPacketRuleArr[$redPacketRuleKey];

            $upd = [];
            $upd['handle_num'] = $num - self::COUNT;
            $result = DB::table($this->redPacketRuleTable)
                ->where('id', '=', $ruleId)
                ->update($upd);
            if ($result) {
                $fields = [];
                $fields['activity_id'] = $activityId;
                $fields['rule_id']     = $ruleId;
                $fields['user_id']     = $userId;
                DB::table($this->redPacketLogs)
                    ->insert($fields);
            }
            $redis->del($activityId);
        } else {
            return "领取失败！";
        }

        return "领取成功！" . $redPacketRuleKey;
    }
}
```

#### 抽奖

```
// 抽奖
// 悲观锁方案
// 阻塞 公平
public function exclusiveLockLottery(Request $request)
{
    $activityId = request('activity_id') ?? 2;
    $userId = request('userid') ?? rand(1000, 9999);

    $isExist = DB::table($this->redPacketLogs)
        ->where('activity_id', '=', $activityId)
        ->where('user_id', '=', $userId)
        ->count();
    if ($isExist) {
        return '已经领取';
    }

    DB::beginTransaction();

    try {
        $redPacketRule = DB::table($this->redPacketRuleTable)
            ->select('id as rule_id', 'activity_id', 'amount', 'num', 'handle_num', 'weight')
            ->where('activity_id', '=', $activityId)
            ->where('is_del', '=', 0)
            ->where('is_show', '=', 1)
            ->where('handle_num', '>', 0)
            ->lockForUpdate()
            ->get()
            ->toArray();

        if (empty($redPacketRule)) {
            return "已领完！";
        }
        $ruleIdArr = array_column($redPacketRule, 'rule_id', 'amount');
        $redPacketRuleArr = array_column($redPacketRule, 'handle_num', 'amount');
        $weightsArr = array_column($redPacketRule, 'weight', 'amount');
        $weightValueArr = array_values($weightsArr);
        $sumWeight = array_sum($weightValueArr);

        //概率数组循环
        $redPacketRuleKey = 0;
        foreach ($weightsArr as $amount => $weight) {
            $random = mt_rand(1, $sumWeight);
            if ($random <= $weight) {
                $redPacketRuleKey = $amount;
                break;
            } else {
                $sumWeight -= $weight;
            }
        }

        $ruleId = $ruleIdArr[$redPacketRuleKey];
        $num = $redPacketRuleArr[$redPacketRuleKey];

        $upd = [];
        $upd['handle_num'] = $num - self::COUNT;
        $result = DB::table($this->redPacketRuleTable)
            ->where('id', '=', $ruleId)
            ->update($upd);
        if ($result) {
            $fields = [];
            $fields['activity_id'] = $activityId;
            $fields['rule_id']     = $ruleId;
            $fields['user_id']     = $userId;
            DB::table($this->redPacketLogs)
                ->insert($fields);
        }
    } catch (Exception $exception) {
        echo $exception->getMessage();
        DB::rollBack();
    }
    DB::commit();

    return "领取成功！" . $redPacketRuleKey;
}
```

