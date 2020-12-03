<?php


namespace App\Utils;


class ArrayUtil
{
    /**
     * 分类树， 支持无限极分类
     * $items
     *
     * array:3 [
        1 => array:3 [
            "category_id" => 1
            "name" => "鞋子"
            "pid" => 0
            ]
        2 => array:3 [
            "category_id" => 2
            "name" => "nike"
            "pid" => 1
            ]
        3 => array:3 [
            "category_id" => 3
            "name" => "aj"
            "pid" => 2
            ]
        ]
     *
     * $tree
     * array:1 [
        0 => array:4 [
            "category_id" => 1
            "name" => "鞋子"
            "pid" => 0
            "list" => array:1 [
                0 => array:4 [
                "category_id" => 2
                "name" => "nike"
                "pid" => 1
                "list" => array:1 [
                    0 => array:3 [
                        "category_id" => 3
                        "name" => "aj"
                        "pid" => 2
                        ]
                    ]
                ]
            ]
        ]
     ]
     */
    public static function getTree(array $data)
    {
        $items = [];
        foreach ($data as $v) {
            $items[$v['category_id']] = $v;
        }

        $tree = [];
        foreach ($items as $id => $item) {
            if (isset($items[$item['pid']])) {
                $items[$item['pid']]['list'][] = &$items[$id];
            } else {
                $tree[] = &$items[$id];
            }
        }
        return $tree;

    }

    /**
     * 控制分类树每一层的显示条数
     *
     * @param $data
     * @param int $firstCount
     * @param int $secondCount
     * @param int $threeCount
     * @return array
     */
    public static function sliceTreeArr($data, $firstCount = 5, $secondCount = 3, $threeCount = 5)
    {
        $data = array_slice($data, 0, $firstCount);
        foreach($data as $k => $v) {
            if(!empty($v['list'])) {
                $data[$k]['list'] = array_slice($v['list'], 0, $secondCount);
                foreach($v['list'] as $kk => $vv) {
                    if(!empty($vv['list'])) {
                        $data[$k]['list'][$kk]['list'] = array_slice($vv['list'], 0, $threeCount);
                    }
                }
            }
        }

        return $data;
    }

    // 排序(默认：从高到低)
    public static function arrsSortByKey($result, $key, $sort = SORT_DESC)
    {
        if(!is_array($result) || !$key) {
            return [];
        }
        array_multisort(array_column($result, $key), $sort, $result);
        return $result;
    }
}
