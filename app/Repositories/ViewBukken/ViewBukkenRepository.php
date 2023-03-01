<?php
namespace App\Repositories\ViewBukken;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class ViewBukkenRepository extends BaseRepository implements ViewBukkenRepositoryInterface
{
    use MySoftDeletes;
    protected $_name = 'view_bukken';
    const MAX_HISTORY = 50;

    public function getModel()
    {
        return \App\Models\ViewBukken::class;
    }

    public function updateOperationHistory($userId, $bukkenNos) {
        try {
            DB::beginTransaction();

            foreach ($bukkenNos as $bukkenNo) {
                // 同じデータがあればupdate（update_date更新、削除されたデータなら復活）、なければinsert
                $data = ['user_id' => $userId, 'bukken_no' => $bukkenNo, 'delete_flg' => 0];
                if ($this->update($data, ['user_id' => $userId, 'bukken_no' => $bukkenNo]) == 0) {
                    $this->create($data);
                }
            }

            // 制限を超えた場合、update_date→idが古い順に削除
            $overCount = $this->countRows(['user_id' => $userId, 'delete_flg' => 0], 'id') - self::MAX_HISTORY;
            if ($overCount > 0) {
                $sql = "";
                $sql.= "id IN (SELECT id FROM(";
                $sql.= "  SELECT id FROM ".$this->_name;
                $sql.= "    WHERE user_id = ".DB::quote($userId)." AND delete_flg = 0";
                $sql.= "    ORDER BY update_date, id";
                $sql.= "    LIMIT ".$overCount;
                $sql.= "  ) AS oldest";
                $sql.= ")";
                $this->delete([(int)'id' => new Zend_Db_Expr($sql)]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
