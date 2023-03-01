<?php
namespace App\Http\Form\TDK;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\HpPageFileName;
use App\Rules\Keyword;
use App\Repositories\HpPage\HpPageRepositoryInterface;
    abstract class AbstractTDK extends Form {

        public function init() {

            $element = new Element\Text('title');
            $element->setAttributes([
                'class' => array('watch-input-count','i-s-tooltip'),
                'maxlength' => '30',
            ]);
            $element->setDescription($this->_hp->title);
            $element->setRequired(true);
            $element->addValidator(new StringLength(array('max' => 30,'min' =>0)));
            $this->add($element);

            $element = new Element\Textarea('description');
            $element->setDescription($this->_hp->description);
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '250',
                'rows' => '4',
            ]);
            $element->addValidator(new StringLength(array('max' => 300,'min' =>0)));
            $this->add($element);

            for ($i = 1; $i <= 3; $i++) {
                $element = new Element\Text('keyword'.$i);
                $element->setDescription($this->_hp->keywords);
                $element->setAttributes([
                    'class' => 'watch-input-count',
                    'maxlength' => '30',
                ]);
                $element->addValidator(new StringLength(array('max' => 30,'min' =>0)));
                $element->addValidator(new Keyword());
                $this->add($element);
            }

            $element = new Element\Text('filename');
            $element->addValidator(new StringLength(array('max' => 250,'min' =>0)));
            $element->setRequired(true);
            $element->setAttributes([
                'class' => 'watch-input-count',
                'maxlength' => '30',
            ]);
            $element->addValidator(new HpPageFileName(\App::make(HpPageRepositoryInterface::class), $this->_hp->id, $this->pageId));
            $this->add($element);

            $this->useViewScript('tdk.phtml');
        }

        // protected $_pageType;
        protected $_hp;

        public function setHp($hp) {
            $this->_hp = $hp;
        }

        protected $pageId;

        public function setPageId($pageId) {
            $this->pageId = $pageId;
        }

        public $_error;

        /**
         * エラーメッセージをセット
         * $key = 要素のID属性と一致
         *
         * @param      $name
         * @param      $message
         * @param null $subSort
         */
        private function setErrorMsg($message, $area, $input) {

            $key = $area;
            $key .= '-';
            $key .= $input;

            $this->_error[$key] = array($message);

            return;
        }

        /**
         * エラーメッセージを取得
         * @return array
         */
        protected function getErrorMsg() {
            return $this->_error;
        }
    }