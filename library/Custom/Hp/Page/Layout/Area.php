<?php
namespace Library\Custom\Hp\Page\Layout;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\InArray;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use Illuminate\Support\Facades\App;

class Area extends Form {

	public function init() {
		$element = new Element\Hidden('column_type_code');
		$element->setRequired(true);
		$element->setAttribute('class', 'column-type-code');
		$element->addValidator(new InArray(array_keys(App::make(HpAreaRepositoryInterface::class)->getColumnTypes())));
		$this->add($element);

		$element = new Element\Hidden('sort');
		$element->setRequired(true);
		$element->setAttribute('class', 'sort-value');
		$this->add($element);

		$this->addSubForm(new Form(), 'parts');
	}

	public function getColumnCount() {
		return (int) $this->getElement('column_type_code')->getValue();
	}

	public function setColumnCount($count) {
		$this->getElement('column_type_code')->setValue($count);
	}

	public function isMultiColumns() {
		return $this->getColumnCount() > 1;
	}

	public function getPartsByColumn() {
		$colsCount = $this->getColumnCount();
		$cols = array();
		for ($i=1;$i<=$colsCount;$i++) {
			$cols[$i] = array();
		}
		foreach ($this->getSubForm('parts')->getSubForms() as $parts) {
			$col = $parts->getColumn();
			if ($col > $colsCount) {
				continue;
			}

			$cols[$col][] = $parts;
		}
		return $cols;
	}

	/**
	 * 行毎にグルーピングしたPartsの配列を返す
	 *
	 * @return array
	 */
	public function getPartsByRow()
	{
		$rows = array();

		$row_index = 0;
		$prev_column = 0;
		foreach ($this->getSubForm('parts')->getSubForms() as $part) {
			if ($part->getColumn() <= $prev_column){
				$row_index++;
			}
			$prev_column = $part->getColumn();

			if (!isset($rows[$row_index])){
				$rows[$row_index] = array();
			}

			$rows[$row_index][] = $part;
		}

		return $rows;
	}

	public function addParts($parts, $partsNo) {
		if ($this->isMultiColumns()) {
			$parts->removeHeadingType();
		}
		$this->getSubForm('parts')->addSubForm($parts, $partsNo);
		return $this;
	}

	public function isValid($data, $checkError = true) {
		// 一旦パーツをはずす
		// $parts = $this->getSubForm('parts');

		// $this->clearSubForms();

		$isValid = parent::isValid($data, false);

		// $this->addSubForm($parts, 'parts');
		$subForms = $this->getSubForm('parts')->getSubForms();
		foreach ($subForms as $name => $form) {
			$isValid = $form->isValid(isset($data['parts'][$name])?$data['parts'][$name]:array(), false) && $isValid;
		}

		return $isValid;
	}

	public function save($hp, $page) {
		$data = array();
		foreach ($this->getElements() as $name => $element) {
			$value = $element->getValue();
			if (!isEmpty($value)) {
				$data[$name] = $value;
			}
		}

		$data['hp_id'] = $hp->id;
		$data['page_id'] = $page->id;

		$table = App::make(HpAreaRepositoryInterface::class);
		$id = $table->create($data);

		$subForms = $this->getSubForm('parts')->getSubForms();
		foreach ($subForms as $name => $form) {
			$form->save($hp, $page, $id->id);
		}
	}

	public function getUsedImages() {
		$images = array();
		$subForms = $this->getSubForm('parts')->getSubForms();
		foreach ($subForms as $name => $form) {
			if ($_images = $form->getUsedImages()) {
				$images = array_merge($images, $_images);
			}
		}
		return $images;
	}
	
	public function getUsedFile2s()
	{
		$file2s		= array();
		$subForms	= $this->getSubForm('parts')->getSubForms() ;
		foreach ($subForms as $name => $form) {
			if ( $_file2s = $form->getUsedFile2s() )
			{
				$file2s = array_merge( $file2s, $_file2s ) ;
			}
		}
		return $file2s;
	}
}