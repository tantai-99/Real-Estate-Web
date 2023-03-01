<?php

namespace App\Http\Form\SiteMap;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Rules\ParentHpPageId;

class Page extends Form
{

	public function __construct()
	{
		parent::__construct();

		$hp = getInstanceUser('cms')->getCurrentHp();
		$hpPageTable = App::make(HpPageRepositoryInterface::class);
		
		$types = $hpPageTable->getTypeListJp();
		//  旧物件ページ
		unset($types[HpPageRepository::TYPE_STRUCTURE_INDEX]);
		unset($types[HpPageRepository::TYPE_STRUCTURE_DETAIL]);
		$element = new Element\Select('page_type_code');
		$element->setLabel('ページ種別');
		$element->setRequired(true);
		$element->setValueOptions($types);
		$this->add($element);

		$element = new Element\Text('parent_page_id');
		$element->setLabel('親ページID');
		$element->setAllowEmpty(true);
		$element->addValidator(new ParentHpPageId($hpPageTable, $hp->id));

		$this->add($element);

		$element = new Element('sort');
		$element->setLabel('並び順');
		// $element->addValidator(new Zend_Validate_Int());
		$this->add($element);
	}

	public function getMessages()
	{
		$messages = array();

		foreach ($this->getElements() as $name => $element) {
			if (!$element->hasErrors()) {
				continue;
			}

			$messages[$name] = $this->getGroupErrors(array($name));
		}

		return $messages;
	}
}
