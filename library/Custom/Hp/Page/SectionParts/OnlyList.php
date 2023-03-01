<?php
namespace Library\Custom\Hp\Page\SectionParts;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Date;
use App\Rules\StringLengthCKEditor;
use Library\Custom\Model\Lists\LinkType;
use Library\Custom\Model\Master\PageList;
use App\Rules\Url;

class OnlyList extends SectionPartsAbstract {

    protected $_usedFile2s = array();

    public function init() {
        $data = array();
        $table = \App::make(HpInfoDetailLinkRepositoryInterface::class);
        if ($this->_page->id) {
            $data = $table->fetchRow([['page_id', $this->_page->id]]);
            if (!$data) {
                $data = $this->_rowLinkDetailDefault();
            }
        } else {
            $data = $this->_rowLinkDetailDefault();
        }

        $element = new Element\Text('title');
        $element->setAttributes([
            'class' => 'i-s-tooltip watch-input-count'
        ]);
        $element->setDescription($this->_page->title);
        $element->setValue($this->_page->title);
        $this->add($element);

        $element = new Element\Text('date');
        $element->setLabel('日付');
        $element->setRequired(true);
        $element->setAttribute('class', 'datepicker');
        $element->addValidator(new Date());

        if ($time = strtotime($this->_page->date)) {
            $element->setValue(date('Y年m月d日', $time));
        }
        $this->add($element);

        $max = 200;
        $element = new Element\Wysiwyg('list_title', array('disableLoadDefaultDecorators'=>true));
        $element->setLabel('一覧タイトル');
        $element->setRequired(true);
        $element->setAttributes([
            'class' => 'watch-input-count has-detail-list',
            'data-maxlength' => $max,
        ]);
        $element->addValidator(new StringLengthCKEditor(['min' => 0, 'max' =>$max]));
        $element->setValue($this->_page->list_title);
        $this->add($element);

        $element = new Element\Radio('link_type', array('disableLoadDefaultDecorators'=>true));
        $element->setValueOptions(LinkType::getInstance()->getAll());
        $element->setAttribute('class', 'input-link_file');
        $element->setRequired(true);
        $element->setSeparator("\n");
        $element->setValue($data['link_type']);
        $this->add($element);

        $element = new Element\Select('link_page_id', array('disableLoadDefaultDecorators'=>true));
        $element->setValueOptions(array(''=>'選択してください') + PageList::init(array('hp_id'=>$this->getHp()->id, 'current_id'=>$this->getPage()->id))->getOptions());
        $element->setValue($data['link_page_id']);
        $this->add($element);

        $max = 2000;
        $element = new Element\Text('link_url', array('disableLoadDefaultDecorators'=>true));
        $element->addValidator(new StringLength(['min' => null, 'max' => $max]));
        $element->addValidator(new Url());
        $element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
        $element->setValue($data['link_url']);
        $this->add($element);
        
        $element = new Element\Hidden(	'file2'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
        $element->setValue($data['file2']);
        $this->add( $element ) ;
        
        $notChecked = 0;
        $element = new Element\Checkbox('link_target_blank', array('disableLoadDefaultDecorators'=>true));
        $element->setLabel('別窓で開く');
        $element->setAttribute('class', 'ml-link-target-blank');
        $element->setValue($data['link_target_blank']);
        if ($data['link_target_blank'] !== $notChecked) {
            $element->setChecked(true);
        }
        $this->add($element);

        $element = new Element\Checkbox('use_image');
        $element->setAttribute('class', 'use-image-link');
        $element->setLabel('リンクを利用する');
        $this->add($element);

        $company = $this->_hp->fetchCompanyRow()->checkTopOriginal();
        $pagetype = $this->_page->page_type_code == HpPageRepository::TYPE_INFO_DETAIL;
        $category = $this->_page->fetchNewsCategories();
        $listCategory = $this->genCategory($category);
        if ($company && $pagetype) {
            $element = new Element\Select('notification_class');
            $element->setLabel('カテゴリー');
            $element->setValueOptions($listCategory);
            if (count($category) == 0) {
                $element->setAttribute('disabled', 'disabled');
            }
            $this->add($element);
        }

        if (!$this->isLite()) {
            $element = new Element\Hidden(	'link_house'			, array( 'disableLoadDefaultDecorators'	=> true ) ) ;
            $element->setValue($data['link_house']);
            $this->add( $element ) ;

            $element = new Element\Radio('search_type', array('disableLoadDefaultDecorators'=>true));
            $element->setValueOptions(array('0' => '条件で探す', '1' => '物件番号で探す'));
            $element->setAttribute('class', 'search-method');
            $this->add($element);
            
            $element = new Element\Text('house_no');
            $element->setAttributes([
                'class' => 'input-house-no',
                'placeholder' => '物件番号（8・10・11桁）を入力してください',
                'id' => 'house_no',
            ]);
            $this->add($element);

            $element = new Element\Hidden('link_house_type');
            $this->add($element);
        }

    }

    protected function _beforeSave($data) {
        $data['date'] = date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $data['date'])));

        return $data;
    }

    public function isValid($data, $checkError = true) {
        $isValid = parent::isValid($data);
        $_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());

        if ($_data[ 'use_image' ] && $isValid) {
            if ( isset( $_data[ 'link_type' ] ) == true ) {
                switch ( $_data['link_type'] ) {
                    case 1: if ($_data['link_page_id'] == '') {
                        $this->getElement('link_page_id')->setMessages('ページを選択してください。');
                        $isValid = false;
                    }
                    break;
                    case 2: if ($_data['link_url'] == '') {
                        $this->getElement('link_url')->setMessages('URLを入力してください。');
                        $isValid = false;
                    }
                    break;
                    case 3: if ($_data['file2'] == '') {
                            $this->getElement('file2')->setMessages('ファイルを追加してください。');
                            $isValid = false;
                    }
                    break;
                    case 4: if (isset($_data['link_house']) && $_data['link_house'] == '') {
                        $this->getElement('link_house')->setMessages('物件を選択してください。');
                        $isValid = false;
                }
                    break;
                }
            }
        }
        
        return $isValid;
    }

    /**
     * @param App\Models\Hp $hp
     * @param App\Models\HpPage $page
     */
    public function save($hp, $page) {
        $data = $this->getValues(true);
        $table = \App::make(HpInfoDetailLinkRepositoryInterface::class);

        $pages = array();
        $data = $this->_beforeSave($data);
        if (isset($data['title'])) {
            $pages['title'] = $data['title'];
            unset($data['title']);
        }
        if (isset($data['date'])) {
            $pages['date'] = $data['date'];
            unset($data['date']);
        }
        if (isset($data['list_title'])) {
            $pages['list_title'] = $data['list_title'];
            unset($data['list_title']);
        }
        if (isset($data['use_image'])) {
            if (!$data[ 'use_image']) {
                $data[ 'link_type'] = HpMainPartsRepository::OWN_PAGE;
                $data[ 'link_page_id' ] = null ;
                $data[ 'link_url' ] = null ;
                $data[ 'file2' ] = null ;
                $data[ 'link_house' ] = null ;
            }
            unset($data['use_image']);
        }
        $pageTable = \App::make(HpPageRepositoryInterface::class);
        $pageTable->update(array(['hp_id', $hp->id], ['id', $page->id]), $pages);

        if (isset($data['notification_class'])) {
            $class = $data['notification_class'];
            unset($data['notification_class']);
            $assocHpPage = \App::make(AssociatedHpPageAttributeRepositoryInterface::class);
            $row = $assocHpPage->fetchRowByHpId($page->link_id,$page->hp_id);
            if (!$row) {
                $assocHpPage->save($page->link_id, $class, $hp->id);
            } else {
                $assocHpPage->update(array(['hp_id',$hp->id], ['hp_page_id',$page->link_id]),array('hp_main_parts_id' => $class));
            }
        }
        if (array_key_exists('notification_class', $data)) {
            unset($data['notification_class']);
        }

        $data['hp_id'] = $this->_hp->id;
        $data['page_id'] = $this->_page->id;

        if ( $data[ 'file2' ] && ( $data[ 'link_type'] == HpMainPartsRepository::FILE ) )
        {		// ファイルが指定されていたら
            $this->_usedFile2s[] = $data[ 'file2' ] ;
        }
        else
        {
            $data[ 'file2' ] = null ;
            $data[ 'file2_title' ] = null;
        }
        if ($data[ 'link_type'] != HpMainPartsRepository::HOUSE ) {
            $data[ 'link_house' ] = null;
        } else {
            $url = $this->isJson($data['link_house']) ? $this->isJson($data['link_house']) : $data['link_house'];
            $linkHouse = array(
                'url' => $data['link_house'],
                'search_type' => $data['search_type'] ? $data['search_type'] : 0,
                'house_no' => isset($data['house_no']) ? $data['house_no'] : null,
                'house_type' => isset($data['link_house_type']) ? explode(',', $data['link_house_type']) : null,
            );
            $data['link_house'] = json_encode($linkHouse); 
        }
        unset($data['search_type']);
        unset($data['house_no']);
        unset($data['link_house_type']);
        //ATHOME_HP_DEV-5145 お知らせ（一覧のみリンクあり）のリンクが外れる原因を調査する
        $where = array(['hp_id', $this->_hp->id], ['page_id', $this->_page->id]);
        $infoDetail = $table->fetchRow($where);
        if ($infoDetail) {
            $table->update($where, $data);
        } else {
            $data['hp_id'] = $this->_hp->id;
            $data['page_id'] = $this->_page->id;
            if ($this->isCheckUseLink($data)) {
                $table->create($data);
            }
        }

        if ($infoDetail && !$this->isCheckUseLink($data)) {
            \App::make(HpInfoDetailLinkRepositoryInterface::class)->delete($where, true);
        }
    }

    public function getUsedFile2s()
    {
        return $this->_usedFile2s;
    }

    public function getSaveTable() {
        return \App::make(HpInfoDetailLinkRepositoryInterface::class);
    }

    private function _rowLinkDetailDefault() {
        return array(
             'id' => '',
             'link_type' => 1,
             'link_url' => '',
             'link_page_id' => '',
             'file2' => '',
             'file2_title' => '',
             'link_target_blank' => '',
             'link_house' => '',
             'page_id' => '',
             'hp_id' => '',
             'delete_flg' => '',
             'create_id' => '',
             'create_date' => '',
             'update_id' => '',
             'update_date' => '',
        );
     }

    /**
     * is a check in Use link
     * @param $data Form Data
     * @return bool
     */
     private function isCheckUseLink($data) {
         if (!$data['link_url'] && !$data['link_page_id'] && !$data['file2'] && !$data['link_house']) {
             return false;
         }
         return true;
     }
}