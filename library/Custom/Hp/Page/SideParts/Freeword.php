<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Form\Element;
use Library\Custom\Model\Estate\ClassList;
use App\Rules\NotInArray;

class Freeword extends SidePartsAbstract {

	protected $_is_unique = true;
	protected $_has_heading = false;

	protected $_title = 'フリーワード検索';
	protected $_template = 'freeword';
	protected $_columnMap = [
        'living_lease' => 'attr_1',
        'office_lease' => 'attr_2',
        'living_buy' => 'attr_3',
        'office_buy' => 'attr_4',
    ];

	public function init()
    {
        parent::init();
        
		// 物件検索設定が実施済みかを確認
        if(empty($this->getHp()->getEstateSetting(1))) {
            return;
        }
        
		$estateSettngRows = $this->getHp()->getEstateSetting(1)->getSearchSettingAllForFreeword();
        $options = [null => '非表示', '1' => '1', '2' => '2', '3' => '3', '4' => '4'];
        foreach ($estateSettngRows as $estateSettngRow) {
            $estateClassType = $estateSettngRow->estate_class;
            switch ($estateClassType) {
                case ClassList::CLASS_CHINTAI_KYOJU:
                    $element = new Element\Select('living_lease');
                    $element->setValueOptions($options);
                    $element->setLabel('居住用賃貸');
                    $this->add($element);
                    break;
                case ClassList::CLASS_CHINTAI_JIGYO:
                    $element = new Element\Select('office_lease');
                    $element->setValueOptions($options);
                    $element->setLabel('事業用賃貸');
                    $this->add($element);
                    break;
                case ClassList::CLASS_BAIBAI_KYOJU:
                    $element = new Element\Select('living_buy');
                    $element->setValueOptions($options);
                    $element->setLabel('居住用売買');
                    $this->add($element);
                    break;
                case ClassList::CLASS_BAIBAI_JIGYO:
                    $element = new Element\Select('office_buy');
                    $element->setValueOptions($options);
                    $element->setLabel('事業用売買');
                    $this->add($element);
                    break;
            }
        }

		// 表示チェックのサマリ用 formを末尾に追加
		$validator = new NotInArray();
        $validator->setValues([0]);
        $validator->setMessage('内容を入力してください。項目自体不要な場合は×で削除してください。');
		$element = new Element\Checkbox('display_any');
        $element->setAttributes(['class' => 'display_any_cb']);
        $element->addValidator($validator);
        $this->add($element);
    }

	protected function _beforeSave( $data )
	{
		// display_anyは DB格納不要であるため保存前にunsetする
		unset($data[ 'display_any' ]);
		return $data ;
    }
}