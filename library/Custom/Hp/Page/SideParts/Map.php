<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Form\Element;
use App\Rules\LatLng;

class Map extends SidePartsAbstract {

	protected $_title = '地図';
	protected $_template = 'map';

	protected $_columnMap = array(
			'heading'		=> 'attr_2',
			'pin_lat'		=> 'attr_3',
			'pin_lng'		=> 'attr_4',
			'center_lat'	=> 'attr_5',
			'center_lng'	=> 'attr_6',
			'zoom'			=> 'attr_7',
	);

	public function init() {
		parent::init();

		$mapConfig = new \Library\Custom\Hp\Map;
		$latlgn = $mapConfig->getSelfPref();

		$element = new Element\Hidden('pin_lat', array('disableLoadDefaultDecorators'=>true));
		$element->setValue($latlgn['lat']);
		$element->setAttribute('class', 'pin_lat');
		$element->addValidator(new LatLng());
		$this->add($element);

		$element = new Element\Hidden('pin_lng', array('disableLoadDefaultDecorators'=>true));
		$element->setValue($latlgn['lng']);;
		$element->setAttribute('class', 'pin_lng');
		$element->addValidator(new LatLng());
		$this->add($element);

		$element = new Element\Hidden('center_lat', array('disableLoadDefaultDecorators'=>true));
		$element->setValue($latlgn['lat']);
		$element->setAttribute('class', 'center_lat');
		$element->addValidator(new LatLng());
		$this->add($element);

		$element = new Element\Hidden('center_lng', array('disableLoadDefaultDecorators'=>true));
		$element->setValue($latlgn['lng']);
		$element->setAttribute('class', 'center_lng');
		$element->addValidator(new LatLng());
		$this->add($element);

		$element = new Element\Hidden('zoom', array('disableLoadDefaultDecorators'=>true));
		$element->setValue(15);
		$element->class = array('zoom');
		$element->addValidator(new LatLng());
		$this->add($element);

	}
}