<?php
namespace Library\Custom\Hp\Page\SectionParts;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use Illuminate\Support\Facades\App;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\InformationMainImageSlideShow;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class MainImage extends SectionPartsAbstract {

	protected $_max_elements_count = 5;

	protected $_usedImages = array();
	protected $_usedFile2s = array();
	
	public function init() {
        parent::init();
        $options = array('hp' => $this->getHp(), 'page'=>$this->getPage());
        $topImageObj = App::make(HpTopImageRepositoryInterface::class);
        for ($i = 0; $i < $this->_max_elements_count; $i++) {
            $data = $topImageObj->fetchRow([['hp_id', $this->_hp->id],['sort', $i]]);
            if (!$data) {
                $data = $this->_rowImageDefault($i);
            }
            if (!is_array($data)) {
                $data = $data->toArray();
            }
            $form = new \Library\Custom\Hp\Page\SectionParts\Element\MainImage($options, $data);
            $form->setData($data);
			$this->addSubForm($form, 'main_image['.$i.']');
		}
        $countImage=$topImageObj->countRows([['hp_id', $this->_hp->id]]);

        $company    = App::make(CompanyRepositoryInterface::class);
        $companyRow = $company->fetchRowByHpId($this->_hp->id);
        if ($companyRow && ($companyRow->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE'))) {
            $flagOptions = array('1'=>'有効', '0'=>'無効');
            $timeSlideShow = InformationMainImageSlideShow::getNamesSpeed();
            $navSlideShow = InformationMainImageSlideShow::getNamesNavigation();
            $slide_show_flg = 0;
            $typeSlideShow = 1;
            $timeSlide = 2;
            $navSlide = 2;
            $arrowSlide = 0;
            $hpSlide = json_decode($this->_hp->slide_show);
            if(!$hpSlide && $countImage > 1) {
                //code here change
                $slide_show_flg = 1;
                $hpSlide = $this->_updatedSlideDefault();
            }
            if($hpSlide){
                $slide_show_flg = $hpSlide->slide_show_flg;
                $typeSlideShow = $hpSlide->slideshow;
                $timeSlide = $hpSlide->effect_slideshow[0];
                $navSlide = $hpSlide->effect_slideshow[1];
                $arrowSlide = $hpSlide->effect_slideshow[2];
            }

            $element = new Element\Radio('slide_show_flg');
            $element->setLabel('スライドショー機能');
            $element->setValueOptions($flagOptions);
            $element->setValue($slide_show_flg);
            $this->add($element);

            $element = new Element\Hidden('type_slideshow');
            $element->setLabel('スライドショー機能');
            $element->setValue($typeSlideShow);
            $this->add($element);

            $element = new Element\Select('time_slideshow');
            $element->setLabel('スライドショー機能');
            $element->setValueOptions($timeSlideShow);
            $element->setValue($timeSlide);
            $this->add($element);

            $element = new Element\Select('nav_slideshow');
            $element->setLabel('スライドショー機能');
            $element->setValueOptions($navSlideShow);
            $element->setValue($navSlide);
            $this->add($element);

            $element = new Element\Checkbox('arrow_slideshow');
            $element->setLabel('');
            $element->setValue($arrowSlide);
            $this->add($element);

            $element = new Element\Hidden('count_slide');
            $element->setLabel('');
            $this->add($element);
        }
	}
	
	public function isValid($data, $checkError = true) {
		$isValid = parent::isValid($data);
        $imageTitle = false;
        $slideShowFlg = true;
		$this->_subForms = $this->getSubForms();

		if ($isValid) {
			usort($this->_subForms, array($this, '_sortImages'));
			
            $countError = 0;
			foreach ($this->getSubForms() as $form) {
                $useImage       = $form->getElement('use_image')->getValue();
                $type           = $form->getElement('link_type')->getValue();
                $pageId         = $form->getElement('link_page_id')->getValue();
                $linkURL        = $form->getElement('link_url')->getValue();
                $file2          = $form->getElement('file2')->getValue();
                if (!$this->isLite()) {
                    $linkHouse      = $form->getElement('link_house')->getValue();
                }
                // $linkHouseTitle = $form->link_house_title->getValue()     ;

				if (!isEmpty($form->getElement('image')->getValue())) {

                        if ($useImage == 1) {
                            switch ($type) {
                                case 1: if ($pageId == '') {
                                    $form->getElement('link_page_id')->setMessages('ページを選択してください。');
                                    $countError++;
                                }
                                break;

                                case 2: if ($linkURL == '') {
                                    $form->getElement('link_url')->setMessages('URLを入力してください。');
                                    $countError++;
                                }
                                break;

                                case 3: if ($file2 == '') {
                                        $form->getElement('file2')->setMessages('ファイルを追加してください。');
                                        $countError++;
                                }
                                break;

                                case 4: if (isset($linkHouse) && $linkHouse == '') {
                                    $form->getElement('link_house')->setMessages('物件を選択してください。');
                                    $countError++;
                            }
                                break;
                            }
                        }
                    
					$imageTitle = true;
				}
			}

            if ($countError > 0) {
                return false;
            }
		}

        if (isset($data['slide_show_flg']) || isset($data['count_slide'])) {
            $data['slide_show_flg'] = (!isset($data['slide_show_flg'])) ? 0 : $data['slide_show_flg'];
            if ($data['slide_show_flg'] == 0) {
                foreach ($data as $key=>$images) {
                    if(is_numeric($key)){
                        if ($images['sort'] == '0' && $images['image'] == '') {
                            $slideShowFlg = false;
                        }
                    }
                }
            }
        }
        if ($imageTitle && $slideShowFlg) {
            return true;
        } else {
            if (!$imageTitle) {
                $firstImageNo = 0;
                for ($i = 0; $i < $this->_max_elements_count; $i++) {
                    if($data[ $i ]['sort'] == 0) {
                        $firstImageNo = $i + 1;
                    }
                }
                $this->getSubForm('main_image['.$this->_max_elements_count - $firstImageNo.']')->getElement('image')->setMessages('値は必須です。');
            } else {
                if (!$slideShowFlg) {
                    $this->getElement('slide_show_flg')->setMessages('一番左に画像を登録してください。');
                }
            }
        }
        $isValid = false;
		
		return $isValid;
	}
	
	protected function _sortImages($a, $b) {
		return ((int) $a->getElement('sort')->getValue()) - ((int) $b->getElement('sort')->getValue());
	}

	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Hp\Page\SectionParts\SectionPartsAbstract::save()
	 */
	public function save($hp, $page) {
        $data = $this->getValues();

		$table = App::make(HpTopImageRepositoryInterface::class);
		$table->delete(array(['hp_id', $this->_hp->id], ['page_id', $this->_page->id]), true);
        
        $this->_updatedSlide($data);
        $settingSlider = array('slide_show_flg', 'count_slide', 'type_slideshow', 'time_slideshow', 'nav_slideshow', 'arrow_slideshow');
        foreach ($settingSlider as $value) {
            unset($data[$value]);
        }

        if (isset($data['main_image'])) {
            $data = array_merge($data, $data['main_image']);
            unset($data['main_image']);
        }
        
		foreach ($data as $values) {
			// 画像がないものは保存しない
			if (!$values['image']) {
				continue;
			}

            if (isset($values['use_image'])) {
                if (!$values['use_image']) {
                    $values['link_type'] = HpMainPartsRepository::OWN_PAGE;
                }
                unset($values['use_image']);
            }

			$values['hp_id'] = $this->_hp->id;
			$values['page_id'] = $this->_page->id;

			$this->_usedImages[] = $values['image'];
			if ( $values[ 'file2' ] && ( $values[ 'link_type'] == HpMainPartsRepository::FILE ) )
			{		// ファイルが指定されていたら
				$this->_usedFile2s[] = $values[ 'file2' ] ;
			}
			else
			{
                $values[ 'file2' ] = null ;
                $values[ 'file2_title' ] = null;
            }
            
            if ($values['link_type'] != HpMainPartsRepository::HOUSE ) {
                $values['link_house'] = null;
            } else {
                $url = $this->isJson($values['link_house']) ? $this->isJson($values['link_house']) : $values['link_house'];
                $linkHouse = array(
                    'url' => $url,
                    'search_type' => $values['search_type'] ? $values['search_type'] : 0,
                    'house_no' => isset($values['house_no']) ? $values['house_no'] : null,
                    'house_type' => isset($values['link_house_type']) ? explode(',', $values['link_house_type']) : null,
                );
                $values['link_house'] = json_encode($linkHouse);  
            }
            unset($values['search_type']);
            unset($values['house_no']);
            unset($values['link_house_type']);

			$table->create($values);
		}
	}

	public function getUsedImages() {
		return $this->_usedImages;
	}
	
	public function getUsedFile2s()
	{
		return $this->_usedFile2s	;
	}
    
    private function _updatedSlideDefault(){
        $tableHp = App::make(HpRepositoryInterface::class);
        $effect = array(
            'slide_show_flg'        => 1,
            'slideshow'             => InformationMainImageSlideShow::EFFECT_HORIZONTAL,
            'effect_slideshow'      => [
                InformationMainImageSlideShow::SPEED_NORMAL,
                InformationMainImageSlideShow::NAVIGATION_CIRCLE,
                0,
            ],
        );
        $data = json_encode($effect);
        $tableHp->update(array(['id', $this->_hp->id]), array('slide_show' => $data));
        return json_decode($data);
    }
    private function _updatedSlide($data) {
        if (isset($data['slide_show_flg']) || isset($data['count_slide'])) {
            $tableHp = App::make(HpRepositoryInterface::class);
            $effect = array(
                'slide_show_flg'        => $data['slide_show_flg'],
                'slideshow'             => $data['type_slideshow'],
                'effect_slideshow'      => [
                    $data['time_slideshow'],
                    $data['nav_slideshow'],
                    $data['arrow_slideshow'],
                ],
            );
            $data = json_encode($effect);
            $tableHp->update(array(['id', $this->_hp->id]), array('slide_show' => $data));
        }
    }
    private function _rowImageDefault($sort) {
       return array(
            'id' => '',
            'image' => '',
            'image_title' => '',
            'link_type' => 1,
            'link_url' => '',
            'link_page_id' => '',
            'file2' => '',
            'file2_title' => '',
            'link_target_blank' => '1',
            'link_house' => '',
            // 'link_house_title' => '',
            'sort' => $sort,
            'page_id' => '',
            'hp_id' => '',
            'delete_flg' => '',
            'create_id' => '',
            'create_date' => '',
            'update_id' => '',
            'update_date' => '',
       );
    }
}