<?php

namespace App\Repositories\HpSiteImage;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class HpSiteImageRepository extends BaseRepository implements HpSiteImageRepositoryInterface
{
    protected $_name = 'hp_site_image';
    public function getModel()
    {
        return \App\Models\HpSiteImage::class;
    }

    public function fetchFaviconRow($id, $hp_id)
    {
        return $this->fetchRowByType($id, $hp_id, config('constants.hp_site_image.TYPE_FAVICON'));
    }

    /**
     * サイトロゴ（PC）を取得する
     *
     * @param int $id
     * @param int $hp_id
     */
    public function fetchSiteLogoPCRow($id, $hp_id)
    {
        return $this->fetchRowByType($id, $hp_id, config('constants.hp_site_image.TYPE_SITELOGO_PC'));
    }

    /**
     * サイトロゴ（スマホ）を取得する
     *
     * @param int $id
     * @param int $hp_id
     */
    public function fetchSiteLogoSPRow($id, $hp_id)
    {
        return $this->fetchRowByType($id, $hp_id, config('constants.hp_site_image.TYPE_SITELOGO_SP'));
    }

    /**
     * ウェブクリップを取得する
     *
     * @param int $id
     * @param int $hp_id
     */
    public function fetchWebclipRow($id, $hp_id)
    {
        return $this->fetchRowByType($id, $hp_id, config('constants.hp_site_image.TYPE_WEBCLIP'));
    }

    public function fetchRowByType($id, $hp_id, $type)
    {
        return $this->fetchRow(array(
            ['id', $id],
            ['hp_id', $hp_id],
            ['type', $type]
        ));
    }

    /**
     * サイトイメージの容量を計算する
     *
     * @param int $hp_id
     * @param int $type
     */
    public function getCapacity($hp_id, $type = null)
    {
        $capacity = 0;
        if ($type == null) {
            foreach(config('constants.hp_site_image') as $type=>$value) {
                $capacity += $this->getCapacityByType($hp_id, $value);
            }
        } else {
            $capacity += $this->getCapacityByType($hp_id, $type);
        }
        return $capacity;
    }

    public function getCapacityByType($hp_id, $type) {
        $select = $this->model->from($this->_name." as i");
        $select->selectRaw("length(i.content) as blob_capacity");
        $select->join("hp", function($join) {
            $join->on("hp.id", "i.hp_id")
                 ->where("hp.delete_flg", 0);
        });
        $select->where("hp.favicon", "i.id");
        $select->where("i.type", $type);
        $select->where("i.hp_id", $hp_id);
        $select->where("i.delete_flg", 0);
        $row = $select->withoutGlobalScopes()->first();
        return ($row == null || $row->blob_capacity == null) ? 0 : $row->blob_capacity;
    }
}
