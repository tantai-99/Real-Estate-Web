<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class LineAtQr extends SidePartsAbstract {

	protected $_is_unique = true;
	//protected $_has_heading = false;

	protected $_title = 'LINE公式アカウント「友だち追加」QRコード';
	protected $_template = 'line-at-qr';

    protected $_columnMap = array(
        'heading'	=> 'attr_1',
        'comment'  	=> 'attr_2',
    );

    public function init(){
        parent::init();

        $this->getElement('heading')->setAttribute('data-maxlength', 20);
        $this->getElement('heading')->addValidator(new StringLength(['max' => 20]));

        $max = 50;
        $element = new Element\Text('comment', array('disableLoadDefaultDecorators'=>true));
        $element->addValidator(new StringLength(['min' => null,'max' => $max]));
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
        $this->add($element);

    }


}