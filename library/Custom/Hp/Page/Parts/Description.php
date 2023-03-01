<?php
namespace Library\Custom\Hp\Page\Parts;

class Description extends Text {

	protected $_title = '説明';
	protected $_template = 'text';
	
	protected $_has_heading = false;
	protected $_is_unique = true;

	protected $_columnMap = array(
			'value'	=> 'attr_1',
	);
}