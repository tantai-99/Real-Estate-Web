<?php
namespace Library\Custom\Hp\Page\AbstractPage;

use Library\Custom\Hp;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use Library\Custom\Model\Lists\HpPagePlaceholderData;

class Detail extends Hp\Page {

	public function initContents() {
        if ($this->_row->notIsPageInfoDetail()) {
            $class_name = '\Library\Custom\Hp\Page\SectionParts\OnlyList';
            $tdk = new $class_name(array('hp' => $this->getHp(), 'page'=>$this->getRow()));
        } else {
            if ($this->_row->isMultiPageType()) {
                $class_name = '\Library\Custom\Hp\Page\SectionParts\TdkDate';
            }
            else {
                $class_name = '\Library\Custom\Hp\Page\SectionParts\Tdk';
            }
            $tdk = new $class_name(array('hp' => $this->getHp(), 'page'=>$this->getRow()));
            
            if (!$tdk->getElement('filename')->getValue()) {
                $tdk->getElement('filename')->setValue($this->_default_filename);
            }
            
            if (!$tdk->getElement('title')->getValue()) {
                $tdk->getElement('title')->setValue($this->getTypeNameJp());
            }
        }

        if ($this->getRow()->id && $this->isTopOriginal()) {
            $assocHpPageAttr = \App::make(AssociatedHpPageAttributeRepositoryInterface::class);
            $row = $assocHpPageAttr->fetchRowByHpId($this->getRow()->link_id,$this->getRow()->hp_id);
            if ($row) {
                $tdk->getElement('notification_class')->setValue($row->hp_main_parts_id);
            }
        }
		
		//プレースフォルダーを設定する
		$placeholder = new HpPagePlaceholderData();
		$data = $placeholder->get($this->_row->page_type_code);
        foreach($tdk->getElements() as $name => $element) {
            if(isset($data[$name])) $element->setAttribute('placeholder', $data[$name]);
        }
		
		$this->form->addSubForm($tdk, 'tdk');
	}
}