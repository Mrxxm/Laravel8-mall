<?php


namespace App\Services\Impl;


use App\Models\GoodsModel;
use App\Services\GoodsService;
use Illuminate\Support\Facades\DB;

class GoodsServiceImpl implements GoodsService
{
    public $model = null;
    protected $goodsSkuService = null;
    protected $categoryService = null;
    protected $specsService = null;
    protected $specsValueService = null;

    public function __construct()
    {
        $this->model = new GoodsModel();
        $this->goodsSkuService = new GoodsSkuServiceImpl();
        $this->categoryService = new CategoryServiceImpl();
        $this->specsService = new SpecsServiceImpl();
        $this->specsValueService = new SpecsValueServiceImpl();
    }

    // todo 重构
    public function detail(int $id): array
    {
        $conditions = [];
        $conditions[] = ['id', '=', $id];
        $conditions[] = ['delete_time', '=', 0];

        $goods = $this->model->where($conditions)->first();
        if (!$goods) {
            throw new \Exception('商品不存在或已删除');
        }
        $goodsId = $goods->id;

        $select = ['id as sku_id', 'goods_id', 'specs_value_ids', 'price', 'cost_price', 'stock', 'status'];
        $conditions = [];
        $conditions[] = ['goods_id', '=', $goodsId];
        $conditions[] = ['delete_time', '=', 0];
        $orderBy = ['id', 'asc'];
        $skies = $this->goodsSkuService->model->list($select, $conditions, $orderBy, false);

        $svIdsToSkuId = array_column($skies, 'sku_id', 'specs_value_ids');
        $specsValues = (new SpecsValueServiceImpl())->handleSpecsValue($svIdsToSkuId);

        if (count($skies)) {
            foreach ($skies as &$sku) {
                $sku['specs'] = $specsValues[$sku['sku_id']];
            }
        }
        $goods = resultToArray($goods);
        $goods['sku'] = $skies;

        return $goods;
    }

    public function list(array $data): array
    {
        $select = ['id', 'sort', 'category_id', 'title', 'stock', 'production_time', 'status', 'create_time', 'update_time', 'delete_time'];

        $categoryId = $data['category_id'] ?? '';
        $keyword = $data['keyword'] ?? '';

        $conditions = [];
        $conditions[] = ['delete_time', '=', 0];
        if (!empty($categoryId)) {
            $conditions[] = ['category_path_id', 'find_in_set', $categoryId];
        }
        if (!empty($keyword)) {
            $conditions[] = ['title', 'like', "%{$keyword}%"];
        }

        $orderBy = array('sort', 'asc');

        $result = $this->model->list($select, $conditions, $orderBy);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['delete_date'] = DateFormat($res['delete_time']);
                $res['category_name'] = ($this->categoryService->model->find($res['category_id']))->name;
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        DB::beginTransaction();
        try {

            if ($fields['goods_specs_type'] == 2) {
                $sku = $fields['sku'];
                unset($fields['sku']);
            }

            $goods = $this->model->add($fields);
            $goodsId = $goods->id;

            if ($fields['goods_specs_type'] == 1) {
                // 统一规格
                $fields['goods_id'] = $goodsId;
                $skuResult = $this->goodsSkuService->model->add($fields);
                $goodsUpd = [
                    'sku_id'      => $skuResult['id'],
                ];
                $this->model->updateById($goodsId, $goodsUpd);
            } else {
                // 多规格
                $sku['goods_id'] = $goodsId;
                $skuResult = $this->goodsSkuService->batchAdd($sku);
                // 总库存
                $stock = array_sum(array_column($skuResult, "stock"));
                $goodsUpd = [
                    'price'       => $skuResult[0]['price'],
                    'cost_price'  => $skuResult[0]['cost_price'],
                    'stock'       => $stock,
                    'sku_id'      => $skuResult[0]['id'],
                ];
                $this->model->updateById($goodsId, $goodsUpd);
            }

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
        DB::commit();

        return ;
    }

    public function update(int $id, array $fields): void
    {
        $goods = $this->model->find($id);
        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        if ($goods->delete_time != 0) {
            throw new \Exception('商品已删除');
        }
        $skuId     = $goods->sku_id;
        $specsType = $goods->goods_specs_type;
        $goodsId   = $goods->id;
        if ($specsType == 1) {
            // 统一规格
            $goodsSku = $this->goodsSkuService->model->find($skuId);
            if (!$goodsSku) {
                throw new \Exception('商品sku不存在');
            }
            if ($goodsSku->delete_time != 0) {
                throw new \Exception('商品sku已删除');
            }
            // 更新商品表
            $this->model->updateById($id, $fields);
            $goodsSkuUpd = [];
            if (isset($fields['price'])) {
                $goodsSkuUpd['price'] = $fields['price'];
            }
            if (isset($fields['cost_price'])) {
                $goodsSkuUpd['cost_price'] = $fields['cost_price'];
            }
            if (isset($fields['stock'])) {
                $goodsSkuUpd['stock'] = $fields['stock'];
            }
            if (isset($fields['status'])) {
                $goodsSkuUpd['status'] = $fields['status'];
            }
            if (!empty($goodsSkuUpd)) {
                // 更新商品sku表
                $this->goodsSkuService->model->updateById($skuId, $goodsSkuUpd);
            }

        } else {
            // 多规格
            // 防止修改这三个参数
            unset($fields['price']);
            unset($fields['cost_price']);
            unset($fields['stock']);
            // 更新商品表
            $this->model->updateById($id, $fields);
            $goodsSkuUpd = [];
            if (isset($fields['status'])) {
                $goodsSkuUpd['status'] = $fields['status'];
            }
            if (!empty($goodsSkuUpd)) {
                // 更新商品sku表
                $this->goodsSkuService->model->updateByGoodsId($goodsId, $goodsSkuUpd);
            }
        }

        return ;
    }

    public function delete(int $id): void
    {
        $goods = $this->model->find($id);
        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        if ($goods->delete_time != 0) {
            throw new \Exception('商品已删除');
        }
        $goodsId = $goods->id;
        $this->model->deleteById($id);
        $this->goodsSkuService->model->deleteByGoodsId($goodsId);
    }
}
