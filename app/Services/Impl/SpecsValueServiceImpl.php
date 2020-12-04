<?php


namespace App\Services\Impl;

use App\Models\SpecsValueModel;
use App\Services\SpecsValueService;

class SpecsValueServiceImpl implements SpecsValueService
{
    public $model = null;
    protected $specsService = null;

    public function __construct()
    {
        $this->model = new SpecsValueModel();
        $this->specsService = new SpecsServiceImpl();
    }

    public function search(array $data): array
    {
        $select = ['id', 'name'];

        $keyword = $data['keyword'] ?? '';
        $specsId = $data['specs_id'];

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['specs_id', '=', $specsId];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy, false);

        return $result;
    }

    public function list(array $data): array
    {
        $select = ['id', 'specs_id', 'name', 'status', 'create_time', 'update_time', 'delete_time'];

        $keyword = $data['keyword'] ?? '';
        $specsId = $data['specs_id'];

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        $conditions[] = ['specs_id', '=', $specsId];
        if (!empty($keyword)) {
            $conditions[] = ['name', 'like', "%{$keyword}%"];
        }

        $orderBy = array('id', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['specs_name'] = ($this->specsService->model->find($specsId))->name;
                $res['delete_date'] = DateFormat($res['delete_time']);
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        $specsId = $fields['specs_id'];
        $specs = $this->specsService->model->where('delete_time', 0)->find($specsId);
        if (!$specs) {
            throw new \Exception('规格不存在');
        }
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        $specsValue = $this->model->find($id);
        if (!$specsValue) {
            throw new \Exception('规格属性不存在');
        }
        if ($specsValue->delete_time != 0) {
            throw new \Exception('规格属性已删除');
        }
        unset($fields['id']);
        $this->model->updateById($id, $fields);
    }

    public function delete(int $id): void
    {
        $specsValue = $this->model->find($id);
        if (!$specsValue) {
            throw new \Exception('规格属性不存在');
        }
        if ($specsValue->delete_time != 0) {
            throw new \Exception('规格属性已删除');
        }
        $this->model->deleteById($id);
    }

    /*
     * 处理规格和规格属性用规格属性id指向
     */
    public function handleSWithSVBySVIds(array $specsValueIds) : array
    {
        $select = ['id as specs_value_id', 'specs_id', 'name'];
        $conditions = [];
        $conditions[] = ['id', 'in', $specsValueIds];
        $orderBy = ['id', 'desc'];
        $specsValues = $this->model->list($select, $conditions, $orderBy, false);

        $select = ['id', 'name'];
        $conditions = [];
        $conditions[] = ['status', '=', 1];
        $conditions[] = ['delete_time', '=', 0];
        $orderBy = ['id', 'desc'];
        $specs = $this->specsService->model->list($select, $conditions, $orderBy, false);
        $specs = array_column($specs, 'name', 'id');

        $res = [];
        foreach ($specsValues as $specsValue) {
            $res[$specsValue['specs_value_id']] = [
                'specs_value_name' => $specsValue['name'],
                'specs_name' => $specs[$specsValue['specs_id']] ?? '',
            ];
        }

        /**
         * $res
         * array:3 [
            14 => array:2 [
                "specs_value_name" => "38"
                "specs_name" => "鞋码"
                ]
            5 => array:2 [
                "specs_value_name" => "绿色"
                "specs_name" => "颜色"
                ]
            4 => array:2 [
                "specs_value_name" => "白色"
                "specs_name" => "颜色"
                ]
            ]
         */
        return $res;
    }

    /*
     * 购物车列表使用
     */
    public function handleSpecsValue(array $gIds) : array
    {
        $svKeys = array_keys($gIds);
        $specsValueIds = implode(",", $svKeys);
        $specsValueIds = array_unique(explode(",", $specsValueIds));

        $sWithSv = $this->handleSWithSVBySVIds($specsValueIds);

        $res = [];
        foreach ($gIds as $key => $skuId) {
            // 1,7
            $key = explode(",", $key);
            // $key [1,7]
            // 处理sku默认文案
            $skuStr = [];
            foreach ($key as $svId) {
                $skuStr[] = $sWithSv[$svId]['specs_name'].":".$sWithSv[$svId]['specs_value_name'];
            }
            $res[$skuId] = implode(" ", $skuStr);
        }

        /**
         * array:2 [
            12 => "颜色:白色 鞋码:38"
            13 => "颜色:绿色 鞋码:38"
            ]
         */

        return $res;
    }

    /*
     * 商品详情使用
     */
    public function handleGoodsSkies(array $gIds, string $flagValue = ''): array
    {
        $specsValueKeys = array_keys($gIds);
        foreach($specsValueKeys as $specsValueKey) {
            // 1,7
            // 2,7
            // 1,8
            // 2,8
            $specsValueKey = explode(",", $specsValueKey);
            // [1, 7]
            foreach($specsValueKey as $k => $v) {
                $svIdsSeparateBySequence[$k][] = $v; // 0 => [1,2,1,2] 1 => [7,7,8,8]
                $specsValueIds[] = $v; // 0 => [1,7,2,7,1,8,2,8]
            }
        }
        $specsValueIds = array_unique($specsValueIds);

        $sWithSv = $this->handleSWithSVBySVIds($specsValueIds);

        $flagValue = explode(",", $flagValue);
        $result = [];
        foreach($svIdsSeparateBySequence as $key => $svIds) {
            $svIds = array_unique($svIds);
            $list = [];
            foreach ($svIds as $svId) {
                $list[] = [
                    "id" => $svId,
                    "name" => $sWithSv[$svId]['specs_value_name'],
                    "flag" => in_array($svId, $flagValue) ? 1 : 0,
                ];
            }

            $result[$key] = [
                "name" => $sWithSv[$svIds[0]]['specs_name'],
                "list" => $list,
            ];
        }

        /**
         * $result
         * array:2 [
            0 => array:2 [
                "name" => "颜色"
                "list" => array:2 [
                    0 => array:3 [
                        "id" => "4"
                        "name" => "白色"
                        "flag" => 0
                    ]
                    1 => array:3 [
                        "id" => "5"
                        "name" => "绿色"
                        "flag" => 0
                    ]
                ]
            ]
            1 => array:2 [
                "name" => "鞋码"
                "list" => array:1 [
                    0 => array:3 [
                        "id" => "14"
                        "name" => "38"
                        "flag" => 0
                    ]
                ]
            ]
        ]
         */

        return $result;
    }

}
