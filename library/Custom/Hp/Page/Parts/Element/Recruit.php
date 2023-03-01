<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Hp\Page\Parts\AbstractParts\SubParts;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use Library\Custom\Model\Lists\Recruit as ListRecruit;
use Library\Custom\Hp\Page\Parts\Element as PartsElement;

class Recruit extends SubParts {

	protected $_has_heading = false;

	protected $_columnMap = array(
		'industry'	=> 'attr_1',
	);

	public function init() {
		parent::init();

		$max = 100;
		$element = new Element\Text('industry', array('disableLoadDefaultDecorators'=>true));
		$element->setValidRequired(true);
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

	}

	protected $_presetTypes = array(
			'job_description',
			'application_qualification',
			'emolument',
			'revision_of_wage_rate',
			'benefits',
			'location',
			'office_hours',
			'holiday',
			'vacation',
			'welfare',
			'training',
			'reference',
			'application_method',
			'pr',
			'image',
	);

	protected $_freeTypes = array(
			'free',
	);

	protected function _createPartsElement($type) {

        $titles = ListRecruit::getInstance()->getAll();
		$element = null;
		switch ($type) {
			case 'job_description':
				$element = new PartsElement\Text();
				$element->setTitle($titles[1]);
				break;
			case 'application_qualification':
				$element = new PartsElement\Text();
				$element->setTitle($titles[2]);
				break;
			case 'emolument':
				$element = new PartsElement\Text();
				$element->setTitle($titles[3]);
				break;
			case 'revision_of_wage_rate':
				$element = new PartsElement\Text();
				$element->setTitle($titles[4]);
				break;
			case 'benefits':
				$element = new PartsElement\Text();
				$element->setTitle($titles[5]);
				break;
			case 'location':
				$element = new PartsElement\Text();
				$element->setTitle($titles[6]);
				break;
			case 'office_hours':
				$element = new PartsElement\Text();
				$element->setTitle($titles[7]);
				break;
			case 'holiday':
				$element = new PartsElement\Text();
				$element->setTitle($titles[8]);
				break;
			case 'vacation':
				$element = new PartsElement\Text();
				$element->setTitle($titles[9]);
				break;
			case 'welfare':
				$element = new PartsElement\Text();
				$element->setTitle($titles[10]);
				break;
			case 'training':
				$element = new PartsElement\Text();
				$element->setTitle($titles[11]);
				break;
			case 'reference':
				$element = new PartsElement\Text();
				$element->setTitle($titles[12]);
				break;
			case 'application_method':
				$element = new PartsElement\Textarea();
				$element->setTitle($titles[13]);
				break;
			case 'pr':
				$element = new PartsElement\Textarea();
				$element->setTitle($titles[14]);
				break;
			case 'image':
				$element = new PartsElement\Image2();
				$element->setTitle('画像');
				$element->useImageTitle();
				break;
			case 'free':
				$element = new PartsElement\TextFree();
				$element->setTitle('フリーテキスト');
				break;
			default:
				break;
		}
		
		if ($element && $type != 'free') {
			$element->setIsUnique(true);
		}

		return $element;
	}

	/**
	 * @return array
	 */
	public function getFieldsForHTMLTable()
	{
		$ret = [];

		foreach ($this->getSubForm('elements')->getSubForms() as $element) {
			if ($element->getValue('type') !== 'image'){
				$ret[] = $element;
			}
		}

		return $ret;
	}

	/**
	 * @return array
	 */
	public function getImages()
	{
		$ret = [];
		foreach ($this->getSubForm('elements')->getSubForms() as $element) {
			if ($element->getValue('type') !== 'image') {
				continue;
			}

			$values = $element->getValues();
			if ($values['image1'] && $values['image1_title']){
				$ret[] = ['id' => $values['image1'], 'title' => $values['image1_title']];
			}
			if ($values['image2'] && $values['image2_title']){
				$ret[] = ['id' => $values['image2'], 'title' => $values['image2_title']];
			}
		}

		return $ret;
	}

    public function getUsedImages()
    {
        $ret = [];
        foreach ($this->getSubForm('elements')->getSubForms() as $element) {
            if ($element->getValue('type') !== 'image') {
                continue;
            }

            $values = $element->getValues();
            if ($values['image1'] && $values['image1_title']){
                $ret[] = $values['image1'];
            }
            if ($values['image2'] && $values['image2_title']){
                $ret[] = $values['image2'];
            }
        }
        return $ret;
    }
}