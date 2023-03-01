<?php
namespace Library\Custom\Hp\Page\SectionParts;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\NewMark;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Rules\StringLength;
use App\Rules\Keyword;
use App\Rules\RequiredSomeOne;
use App\Rules\HpPageFileName;
use Library\Custom\User\UserAbstract;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;

class Tdk extends SectionPartsAbstract {

    public function init() {
        parent::init();

        $max = 20;
        $element = new Element\Text('title');
        $element->setAttributes([
            'class' => 'i-s-tooltip watch-input-count',
            'data-maxlength' => $max,
        ]);
        $element->setDescription($this->_hp->title);
        $element->setRequired(true);
        $element->addValidator(new StringLength(['min' => 0, 'max' => $max]));
        $element->setValue($this->_page->title);
        $this->add($element);

        $max = 37;
        $element = new Element\Textarea('description');
        $element->setDescription($this->_hp->description);
        $element->setRequired(true);
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
            'rows' => '4'
        ]);
        $element->addValidator(new StringLength(['min' => 0, 'max' => $max]));
        $element->setValue($this->_page->description);
        $this->add($element);

        $max = 20;
        $keywords = explode(',',$this->_page->keywords);
        $names = array();
        for ($i = 1; $i <= 3; $i++) {
        	$names[] = 'keyword'.$i;
            $element = new Element\Text('keyword'.$i);
            // $element->setAllowEmpty(false);
            $element->setDescription($this->_hp->keywords);
            $element->setAttribute('class', 'watch-input-count');
            if($i == 1) $element->setAttribute('class', 'watch-input-count  first');
            $element->setAttribute('maxlength', $max);
            $element->addValidator(new StringLength(['min' => 0, 'max' => $max]));
            $element->addValidator(new Keyword());
            if (isset($keywords[$i - 1])) {
            	$element->setValue($keywords[$i - 1]);
            }
            $this->add($element);
        }
        $this->getElement('keyword1')->addValidator(new RequiredSomeOne(array('elementNames'=>$names)));

        $max = 20;
        $element = new Element\Text('filename');
        $element->addValidator(new StringLength(['min' => 0, 'max' => $max]));
        $element->setRequired(true);
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
        $element->addValidator(new HpPageFileName(\App::make(HpPageRepositoryInterface::class), $this->_hp->id, $this->_page->id, $this->_page->page_category_code, $this->_page->page_type_code));
        $element->setValue($this->_page->filename);
        $this->add($element);

        $pagetype = $this->_page->page_type_code == HpPageRepository::TYPE_INFO_INDEX;
        $newMark = new NewMark();
        if (is_null($this->_page->new_mark)) {
            $value = NewMark::NOT_USED;
        } else {
            $value = $this->_page->new_mark;
        }
        if ($pagetype) {
            $element = new Element\Radio('new_mark');
            $element->setLabel('NEWマーク');
            $element->setValueOptions($newMark->getAll());
            $element->setSeparator(' ');
            $element->setValue($value);
            $this->add($element);
        }
    }

    /**
     * @param App\Models\Hp $hp
     * @param App\Models\HpPage $page
     */
    public function save($hp, $page) {
    	$data = $this->getValues();

    	$company = $hp->fetchCompanyRow();
        $user = UserAbstract::getInstance();
        if(get_class($user) == 'Library\Custom\User\Cms' && $user->isNerfedTop()){
            $globalNav = $hp->getGlobalNavigation();
            $ids = array_map(function ($item) {
                return $item['id'];
            }, $globalNav->toArray());
            if (in_array($page->id, $ids)) {
                unset($data['title']);
                unset($data['filename']);
            }
        }

    	$data = $this->_beforeSave($data);
        if (isset($data['notification_class'])) {
            $class = $data['notification_class'];
            unset($data['notification_class']);
            $assocHpPage = \App::make(AssociatedHpPageAttributeRepositoryInterface::class);
            $row = $assocHpPage->fetchRowByHpId($page->link_id,$page->hp_id);
            if (!$row) {
                $assocHpPage->save($page->link_id, $class, $hp->id);
            } else {
                $assocHpPage->update(array(['hp_id', $hp->id], ['hp_page_id', $page->link_id]), array('hp_main_parts_id' => $class));
            }
        }
        if (array_key_exists('notification_class', $data)) {
            unset($data['notification_class']);
        }
    	$table = \App::make(HpPageRepositoryInterface::class);
    	$table->update(array(['hp_id', $hp->id], ['id', $page->id]), $data);
    }

    protected function _beforeSave($data) {
    	$keywords = array($data['keyword1'], $data['keyword2'], $data['keyword3']);
    	$keywords = implode(',', $keywords);
    	$data['keywords'] = $keywords;
    	unset($data['keyword1']);
    	unset($data['keyword2']);
    	unset($data['keyword3']);
    	return $data;
    }
}