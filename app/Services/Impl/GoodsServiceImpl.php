<?php


namespace App\Services\Impl;


use App\Models\GoodsModel;
use App\Services\GoodsService;
use Illuminate\Support\Facades\DB;

class GoodsServiceImpl implements GoodsService
{
    protected $model = null;
    protected $goodsSkuService = null;

    public function __construct()
    {
        $this->model = new GoodsModel();
        $this->goodsSkuService = new GoodsSkuServiceImpl();
    }

    public function list(array $data): array
    {
        // TODO: Implement list() method.
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
                DB::commit();
                return ;
            }

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

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
        DB::commit();

        return ;
    }

    public function update(int $id, array $fields): void
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): void
    {
        // TODO: Implement delete() method.
    }
}
