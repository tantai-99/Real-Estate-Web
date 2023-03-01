<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\Parts\Element as PartsElement;
use App\Rules\StringLength;
use App\Repositories\HpPage\HpPageRepository;

class Original extends SubParts {

    protected $_has_heading = false;
    protected $_is_unique = true;

    protected $_columnMap = array(
        'article_elem_title'		=> 'attr_1',
    );
    protected $_hp;
    protected $_page;

    public function init() {
        parent::init();

        $max = 100;
        $element = new Element\Text('article_elem_title', array('disableLoadDefaultDecorators'=>true));
        $element->setRequired(true);
        $element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
        $element->setAttributes(array('class'=>'watch-input-count','maxlength' => $max));
        $this->add($element);

    }

    protected $_presetTypes = array(
        'image_text',
        'text',
        'image',
    );

    protected $_freeTypes = array(
    );

    protected function _createPartsElement($type) {
        $element = null;
        switch ($type) {
            case 'image_text':
                $element = new PartsElement\ArticlesImageText(['page' => $this->getPage(), 'hp' => $this->getHp()]);
                $element->setTitle('画像+テキスト');
                break;
            case 'text':
                $element = new PartsElement\ArticlesText();
                $element->setTitle('テキスト');
                break;
            case 'image':
                $element = new PartsElement\ArticlesImage(['page' => $this->getPage(), 'hp' => $this->getHp()]);
                $element->setTitle('画像');
                break;
            default:
                break;
        }
        return $element;
    }

    public function getTypeArcticle() {
        // $type = $this->getPage()->page_type_code;
        // switch ($type) {
        // 	case HpPageRepository::TYPE_BAIKAI_KEIYAKU:
        // 	case HpPageRepository::TYPE_BAIKYAKU_COST:
        // 		return 'origin_a';
        // 		break;
        // 	case HpPageRepository::TYPE_SENZOKU_SENNIN:
        // 		return 'origin_b';
        // 		break;
        // 	case HpPageRepository::TYPE_SHINSEI_SHORUI:
        // 		return 'origin_c';
        // 		break;
        // 	case HpPageRepository::TYPE_NOUCHI:
        // 		return 'origin_d';
        // 		break;
        // 	default:
        // 		return 'origin';
        // 		break;
        // }
        return 'origin';
    }
}