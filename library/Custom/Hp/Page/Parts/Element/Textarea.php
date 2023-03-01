<?php
namespace Library\Custom\Hp\Page\Parts\Element;

class Textarea extends Text {

	protected $_valueClass = 'Library\Custom\Form\Element\Wysiwyg';

	protected $_valueMaxLength = 20000;
}