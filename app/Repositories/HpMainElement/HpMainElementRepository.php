<?php

namespace App\Repositories\HpMainElement;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpMainElementRepository extends BaseRepository implements HpMainElementRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpMainElement::class;
    }

    /* カラム定義 */
    const COL_LIST_LIST         = 'attr_1';
    const COL_LIST_LINK_TYPE    = 'attr_3';
    const COL_LIST_LINK_PAGE_ID = 'attr_5';
    const COL_LIST_LINK_URL     = 'attr_2';
    const COL_LIST_LINK_OPEN    = 'attr_4';

    const COL_TABLE_TH = 'attr_1';
    const COL_TABLE_TD = 'attr_2';

    const COL_FILE_ID = 'attr_2';

    // リンクタイプ
    const OWN_PAGE = 1;
    const URL      = 2;

    /**
     * エレメント一覧を取得（ページ内）
     *
     * @param $pageId
     *
     */
    public function getElement($pageId) {

        $select = $this->model->select();
        $select->where('page_id', $pageId);
        $select->orderBy('sort');
        return $select->get();
    }

    private $_hpId;
    private $_pageId;
    private $_areaId;
    private $_partsId;
    private $_sort;

    /**
     * 保存
     *
     * @param       $sort
     * @param array $val
     * @param       $hpId
     * @param       $pageId
     *
     * @return mixed
     */
    public function save($areaRow, $partsRow, $subSort, $partsName, $elem) {

        $this->_hpId = $areaRow->hp_id;
        $this->_pageId = $areaRow->page_id;
        $this->_areaId = $areaRow->id;
        $this->_partsId = $partsRow->id;
        $this->_sort = $subSort;

        $methodName = 'save'.ucfirst($partsName);
        return $this->$methodName($elem);
    }

    private function saveList($elem) {

        $data = array(
            'sort'                      => $this->_sort,
            'parts_id'                  => $this->_partsId,
            'area_id'                   => $this->_areaId,
            'page_id'                   => $this->_pageId,
            'hp_id'                     => $this->_hpId,
            self::COL_LIST_LIST         => $elem['list'],
            self::COL_LIST_LINK_TYPE    => $elem['link_type'] == 'own_page' ? self::OWN_PAGE : self::URL,
            self::COL_LIST_LINK_PAGE_ID => $elem['link_type'] == 'own_page' ? $elem['page_id'] : '',
            self::COL_LIST_LINK_URL     => $elem['url'] == 'own_page' ? $elem['url'] : '',
            self::COL_LIST_LINK_OPEN    => $elem['open'] == '_blank' ? 1 : 0,
        );
        return $this->insertRow($data);
    }

    private function saveTable($elem) {

        $data = array(
            'sort'             => $this->_sort,
            'parts_id'         => $this->_partsId,
            'area_id'          => $this->_areaId,
            'page_id'          => $this->_pageId,
            'hp_id'            => $this->_hpId,
            self::COL_TABLE_TH => $elem['th'],
            self::COL_TABLE_TD => $elem['td'],
        );
        return $this->insertRow($data);
    }

    /**
     * レコードを作成
     *
     * @param $data
     */
    private function insertRow($data) {

        $newRow = $this->create($data);

        $newRow->save();
        return $newRow;
    }

    /**
     * ページ使用ファイルのID一覧を取得する
     * @param int   $hpId
     * @param int   $hpPageId
     * @return array
     */
    public function usedFileIdsInPage( $hpId, $hpPageId ) {
        $select = $this->model->select();
        $select->groupBy(self::COL_FILE_ID);
        $select->where('type', 'file');
        $select->where('hp_id', $hpId);
        $select->where('page_id', $hpPageId);
        $rows = $select->get();
        $res = [];
        if ($rows) {
            foreach ($rows as $row) {
                $res[] = $row->{self::COL_FILE_ID};
            }
        }
        return $res;
    }
}
