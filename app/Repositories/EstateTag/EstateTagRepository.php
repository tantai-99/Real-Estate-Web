<?php

namespace App\Repositories\EstateTag;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class EstateTagRepository extends BaseRepository implements EstateTagRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\EstateTag::class;
    }

    protected $_name = 'estate_tag';

    const ALL_TAGS_COL = 'all_tags';

    public function getTagDataForId($id)
    {


        $select = $this->model->select();
        $select->where('id', $id);
        $row = $select->first();

        // MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
        $this->_fillEachTags($row);

        return $row;
    }

    /**
     * 加盟店IDで取得
     */
    public function getDataForCompanyId($company_id)
    {

        $select = $this->model->select();
        $select->where('company_id', $company_id);
        $row = $select->first();

        // MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
        $this->_fillEachTags($row);

        return $row;
    }

    /**
     * all_tags をデコードし、各カラムに割り当てる
     */
    private function _fillEachTags(&$row)
    {
        if (empty($row) || empty($row->{self::ALL_TAGS_COL})) {
            return;
        }

        $all_tags = json_decode($row->{self::ALL_TAGS_COL});
        $cols = $this->getTableColumns();
        foreach (preg_grep("/_tag/", $cols) as $tag_col) {
            if ($tag_col == self::ALL_TAGS_COL) {
                continue;
            }
            if (isset($all_tags->{$tag_col})) {
                $row->{$tag_col} = $all_tags->{$tag_col};
            }
        }
    }

    /**
     * 更新する前に、all_tagsに集約する
     * MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
     */
    public function update($where, $data = [], $columns = []) 
    {
        $cols = $this->getTableColumns();

        // 必ず全タグが渡されないかもしれない対応
        $row = $this->fetchRow($where);

        if (!empty($row->{self::ALL_TAGS_COL})) {
            $all_tags = json_decode($row->{self::ALL_TAGS_COL}, true);
        } else {
            $all_tags = [];
        }

        foreach (preg_grep("/_tag/", $cols) as $tag_col) {
            if ($tag_col == self::ALL_TAGS_COL) {
                continue;
            }
            if (is_null($data[$tag_col])) {
                unset($all_tags[$tag_col]);
            }
            if (isset($data[$tag_col])) {
                $all_tags[$tag_col] = $data[$tag_col];
                $data[$tag_col] = null;
            }
        }

        if (!empty($all_tags)) {
            $data[self::ALL_TAGS_COL] = json_encode($all_tags, true);
        } else {
            $data[self::ALL_TAGS_COL] = null;
        }

        parent::update($where, $data);
    }

    /**
     * 新規登録する前に、all_tagsに集約する
     * MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
     */
    public function create($data = [])
    {
        $cols = $this->getTableColumns();
        $all_tags = [];
        foreach (preg_grep("/_tag/", $cols) as $tag_col) {
            if ($tag_col == self::ALL_TAGS_COL) {
                continue;
            }
            if (isset($data[$tag_col])) {
                $all_tags[$tag_col] = $data[$tag_col];
                $data[$tag_col] = null;
            }
        }
        if (!empty($all_tags)) {
            $data[self::ALL_TAGS_COL] = json_encode($all_tags, true);
        }

        return parent::create($data);
    }
}
