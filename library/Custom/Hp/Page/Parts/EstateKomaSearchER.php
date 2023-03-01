<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\AthomeKomaSrc;
use App\Rules\KomaSearchEr;

class EstateKomaSearchER extends PartsAbstract {

    protected $_title    = '特集コマ（検索エンジンレンタル用）';
    protected $_template = 'estate-koma-search-er';

    protected $_has_heading = false;

	protected $_columnMap = array(
        'heading'    => 'attr_1',
        'htmltagpc'  => 'attr_2',
        'htmltagsp'  => 'attr_3',
	);

    protected $_required_force = array(
        'htmltagpc', 'htmltagsp'
    );

    public function init() {
        parent::init();

        $max = 20;
        $element = new Element\Text('heading', array('disableLoadDefaultDecorators'=>true));
        $element->setLabel('見出し');
        $element->addValidator(new StringLength(['min' => null, 'max' => $max]));
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max
        ]);
        $this->add($element);

        $element = new Element\Text('htmltagpc');
        $element->setLabel('埋込みタグ（PC用）');
        $element->setValidRequired(true);
        $element->addValidator(new AthomeKomaSrc());
        $this->add($element);
        
        $element = new Element\Text('htmltagsp');
        $element->setLabel('埋込みタグ（スマホ用）');
        $element->setValidRequired(true);
        $element->addValidator(new AthomeKomaSrc());
        $this->add($element);
    }

    // ATHOME_HP_DEV-3367 Check valid htmltagpc and htmltagsp
    public function isValid($data, $checkError = true) {
        if (!$this->isRequired()) {
            $ignore = array_merge(array('page_type_code', 'sort', 'column_sort', 'display_flg'));
            foreach ($this->getElements() as $name => $element) {
                if (!in_array($name, $ignore) && ($element->isRequired() || $element->isValidRequired())) {
                    KomaSearchEr::addToKomaSearchEr($element);
                }
            }
        }
        return parent::isValid($data,false);
    }
}