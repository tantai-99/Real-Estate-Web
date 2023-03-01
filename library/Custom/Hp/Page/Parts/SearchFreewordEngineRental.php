<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\EmbeeSearchER;
use App\Rules\AthomeFreeWordBukkenSrc;

class SearchFreewordEngineRental extends PartsAbstract {

    protected $_title    = 'フリーワード検索パーツ（検索エンジンレンタル用）';
    protected $_template = 'search-freeword-engine-rental';
    protected $_has_heading = false;
    protected $_is_unique = true;

	protected $_columnMap = array(
        'path'        => 'attr_1',
        'heading'     => 'attr_2',
	);


    public function init() {
        parent::init();
        $max = 30;
        $element = new Element\Text('heading', array('disableLoadDefaultDecorators'=>true));
        $element->setLabel('見出し');
        $element->addValidator(new StringLength(['min' => null, 'max' =>$max]));
        // $element->class = array('watch-input-count');
        // $element->maxlength = $max;
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' =>  $max
        ]);
        $this->add($element);
        $element = new Element\Text('path');
        $element->setLabel('埋込みタグ');
        // $element->addValidator(new EmbeeSearchER());
        $element->addValidator(new AthomeFreeWordBukkenSrc());
        $element->setValidRequired(true);
        $this->add($element);
    }
}