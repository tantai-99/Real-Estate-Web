<?php
namespace Library\Custom\Form\Element;

use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Wysiwyg as ValidWysiwyg;

class Wysiwyg extends Element {
    
    protected $_type = 'textarea';

	public function __construct($spec, $options = null) {
		parent::__construct($spec);
		
		$max = 20000;
		
		$stringLength = new StringLength(array('min' => null, 'max' => $max));
		$stringLength->setMessage('登録できる容量を超えています。', \App\Rules\StringLength::TOO_LONG);
		$this->addValidator($stringLength);

        // ATHOME_HP_DEV-5070
		$this->addValidator(new ValidWysiwyg());

		$this->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
			'rows' => 10,
		]);
		// $this->class = array('watch-input-count');
		// $this->maxlength = $max;
		// $this->rows = 10;
	}

	public function setValue($value) {
		if (preg_replace('/\r\n/', "\n", $value) !== '') {
			return parent::setValue(preg_replace('/\r\n/', "\n", $value));
		}
	}
}