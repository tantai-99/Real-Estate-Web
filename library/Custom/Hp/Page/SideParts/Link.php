<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Hp\Page\SideParts\Element;

class Link extends HasElementAbstract {

	protected $_title = 'リンク';
	protected $_template = 'link';

	protected $_presetTypes = array(
			'link'
	);
	
	protected	$_element	;

	protected function _createPartsElement($type) {
		$this->_element = null;
		if ($type == 'link') {
			$this->_element = new Element\Link( array( 'hp' => $this->getHp(), 'page' => $this->getPage() ) ) ;
		}
		return $this->_element;
	}

	public function isValid($data, $checkError = true)
	{
		$isValid	= parent::isValid($data);
		if ( $isValid )
		{
			if ( $this->_element == null )
			{
				$this->getElement('heading')->setMessages(	'リンクを追加して下さい。'		)	;
				$isValid	= false		;
			}
		}
		return $isValid;
	}
}