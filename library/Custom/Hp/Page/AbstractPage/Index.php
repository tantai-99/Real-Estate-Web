<?php
namespace Library\Custom\Hp\Page\AbstractPage;

use Library\Custom\Hp\Page;
use Library\Custom\Hp\Page\SectionParts;

class Index extends Page {

    protected $_commonMainParts = array();
    protected $_commonSideParts = array();

    public function initContents() {
        parent::initContents();
        $this->form->addSubForm(new SectionParts\Lists(array('hp' => $this->getHp(), 'page'=>$this->getRow())), 'list');
    }

    public function initMainContents() {
        // メインコンテンツなし
    }

    public function initSideContents() {
        // サイドコンテンツなし
    }

    protected function _load() {
        // ロードなし
    }

    protected function _setPreset() {
        // プリセットなし
    }

    /**
     * 保存前の処理
     */
    protected function beforeSave() {
        // パーツないので処理なし
    }

    /**
     * 保存の後処理
     */
    protected function afterSave() {
        // パーツないので処理なし
    }
}

?>