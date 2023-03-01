<?php

namespace App\Repositories\HpSideElements;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\HpPage\HpPageRepository;

class HpSideElementsRepository extends BaseRepository implements HpSideElementsRepositoryInterface
{

    protected $_name = 'hp_side_element';

    private $partsName;
    private $elem;
    private $sort;
    private $hpId;
    private $pageId;
    private $partsId;
    private $_isCommon = false;

    /* カラム定義 */

    // リンク
    const COL_LINK_TYPE    = 'attr_5';
    const COL_LINK_PAGE_ID = 'attr_3';
    const COL_LINK_URL     = 'attr_1';
    const COL_LINK_OPEN    = 'attr_6';

    // 画像
    const COL_IMAGE_ID    = 'attr_4';
    const COL_IMAGE_TITLE = 'attr_2';


    /* その他定義 */

    // self::COL_LINK_TYPE
    const OWN_PAGE = 1;
    const URL      = 2;

    // self::COL_LINK_OPEN
    const BLANK = 1;
    const SELF  = 0;

    // 共通パーツプラグ
    const COMMON   = 1;
    const ORIGINAL = 0;



    public function getModel()
    {
        return \App\Models\HpSideElements::class;
    }

    /**
     * エレメント一覧を取得（ページ内）
     * @param $pageId
     *
     * @return App\Collections\CustomCollection
     */
    public function getElement($pageId) {

        $select = $this->model->select();
        $select->where('page_id', $pageId);
        $select->order('sort');
        return $select->get();
    }

    /**
     * 属性を指定してエレメントを取得
     * @param $hpId
     * @param $col
     * @param $attr
     * @return App\Models\Model
     */
    public function getAttr($hpId,$col,$attr) {

        $select = $this->model->select();
        $select->where('hp_id ', $hpId);
        $select->where($col, $attr);
        $select->order('sort');
        return $select->first();
    }

    /**
     * 保存
     *
     * @param $partsName
     * @param $elem
     * @param $sort
     * @param $hpId
     * @param $pageRow
     * @param $partsId
     */
    public function save($partsName, $elem, $sort, $hpId, $pageRow, $partsId) {

        $this->partsName = $partsName;
        $this->elem = $elem;
        $this->sort = $sort;
        $this->hpId = $hpId;
        $this->pageId = $pageRow->id;
        $this->partsId = $partsId;
        if (HpPageRepository::TYPE_TOP == $pageRow->page_type_code) {
            $this->_isCommon = true;
        };

        $methodName = 'save'.ucfirst($this->partsName);
        $this->$methodName();
    }

    private function saveLink() {

        $data = array(
            self::COL_LINK_TYPE    => $this->elem['link_type'] == 'own_page' ? self::OWN_PAGE : self::URL,
            self::COL_LINK_PAGE_ID => $this->elem['link_type'] == 'own_page' ? $this->elem['page_id'] : '',
            self::COL_LINK_URL     => $this->elem['link_type'] == 'url' ? $this->elem['url'] : '',
            self::COL_LINK_OPEN    => $this->elem['open'] == '_blank' ? 1 : 0,
            'sort'                 => $this->sort,
            'common_parts_flg'     => $this->_isCommon ? self::COMMON : self::ORIGINAL,
            'delete_flg'           => '0',
            'hp_id '               => $this->hpId,
            'page_id'              => $this->pageId,
            'side_parts_id'        => $this->partsId,
        );

        return $this->insertRow($data);
    }

    private function saveImage() {
        $data = array(
            self::COL_IMAGE_ID     => $this->elem['image_id'],
            self::COL_IMAGE_TITLE  => $this->elem['image_title'],
            self::COL_LINK_TYPE    => $this->elem['link_type'] == 'own_page' ? self::OWN_PAGE : self::URL,
            self::COL_LINK_PAGE_ID => $this->elem['link_type'] == 'own_page' ? $this->elem['page_id'] : '',
            self::COL_LINK_URL     => $this->elem['link_type'] == 'url' ? $this->elem['url'] : '',
            self::COL_LINK_OPEN    => $this->elem['open'] == '_blank' ? self::BLANK : self::SELF,
            'sort'                 => $this->sort,
            'common_parts_flg'     => $this->_isCommon ? self::COMMON : self::ORIGINAL,
            'delete_flg'           => '0',
            'hp_id '               => $this->hpId,
            'page_id'              => $this->pageId,
            'side_parts_id'        => $this->partsId,
        );

        return $this->insertRow($data);
    }

    /**
     * レコードの作成
     *
     * @param $data
     *
     * @return App\Models
     */
    private function insertRow($data) {

        $newRow = $this->create($data);

        $newRow->save();
        return $newRow;
    }
}
