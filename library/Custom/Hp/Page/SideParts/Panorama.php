<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Form\Element;
use App\Rules\PanoramaCode;

class Panorama extends SidePartsAbstract {

	protected $_title = 'VR内見・パノラマ';
	protected $_template = 'panorama';

    protected $_columnMap = array(
            'heading'       => 'attr_1',
            'value'         => 'attr_2',
    );

    public function init() {
        parent::init();

        $element = new Element\Text('value', array('disableLoadDefaultDecorators'=>true));
        // $element->setRequired(true);
        $element->setValidRequired(true);
        $element->addValidator(new PanoramaCode());
        $this->add($element);
    }
}