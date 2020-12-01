<?php


namespace App\Models;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function list(array $select, array $conditions, array $orderBy = array('id', 'desc'), bool $paginate = true, int $page = 1, $pageSize = 10) : array
    {
        $result = self::where('id', '!=', 0);
        $result->select($select);
        $this->autoConditions($result, $conditions);
        $this->autoOrderBy($result, $orderBy);

        if ($paginate) {
            $requestPage = request('page');
            $currentPage = $requestPage ? $requestPage : $page;
            $res = $result->paginate($pageSize, ['*'], 'page', $currentPage);
//            $res = $result->toSql();dd($res);
        } else {
            $res = $result->get();
        }

        if (count($res)) {
            return resultToArray($res);
        } else {
            return [];
        }
    }

    public function add(array $fields)
    {
         return self::create($fields);
    }

    public function updateById(int $id, array $fields)
    {
        return self::where('id', $id)
            ->update($fields);
    }

    public function deleteById(int $id)
    {
        return self::where('id', $id)
            ->update(['delete_time' => time()]);
    }

    public function autoOrderBy($obj, array $orderBy) : void
    {
        $count = count($orderBy);
        $i = 0;
        while (($count - $i) >= 2) {
            $obj->orderBy($orderBy[$i], $orderBy[$i + 1]);
            $i += 2;
        }
    }

    public function autoConditions($obj, array $conditions) : void
    {
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $num = count($condition);
                if ($num != 2 && $num != 3) {
                    throw new \Exception('conditions传值异常');
                }
                if ($num == 3) {
                    switch (strtolower($condition[1])) {
                        case 'in':
                            $obj->whereIn($condition[0], $condition[2]);
                            break;
                        case 'notin':
                            $obj->whereNotIn($condition[0], $condition[2]);
                            break;
                        case 'like':
                            $obj->where($condition[0], $condition[1], $condition[2]);
                            break;
                        default:
                            $obj->where($condition[0], $condition[1], $condition[2]);
                    }
                } else {
                    $obj->where($condition[0], $condition[1]);
                }
            }
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
