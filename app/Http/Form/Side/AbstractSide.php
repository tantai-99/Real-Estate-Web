<?php
namespace App\Http\Form\Side;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Repositories\HpSideElements\HpSideElementsRepository;
use App\Repositories\HpSideParts\HpSidePartsRepository;

    abstract class AbstractSide extends Form {

        /**
         * 初期化
         *
         * @throws Zend_Form_Exception
         */
        public function init() {

            parent::init();

            $this->setName('side');

            $element = new Element('text');
            $element->setLabel('テキスト');
            $this->add($element);

            $element = new Element('link');
            $element->setLabel('リンク');
            $this->add($element);

            $element = new Element('image');
            $element->setLabel('画像');
            $this->add($element);

            $element = new Element('qr');
            $element->setLabel('QRコード');
            $this->add($element);

            $element = new Element('line_at_fr_qr');
            $element->setLabel('LINE「友だち追加」QRコード');
            $this->add($element);

            $element = new Element('line_at_fr_btn');
            $element->setLabel('LINE「友だち追加」ボタン');
            $this->add($element);

            $element = new Element('fb');
            $element->setLabel('Facebookタイムライン');
            $this->add($element);

            $element = new Element('tw');
            $element->setLabel('Twitterタイムライン');
            $this->add($element);

            $this->useViewScript('side.phtml');
        }

        private $sort; //並び順
        private $type; //パーツタイプ

        /**
         * バリデーション
         *
         * @param array $params
         *
         * @return bool
         */
        public function isValid($params = array(),$checkErrors = true) {

            parent::isValid($params);

            $validateFlg = true;

            if (!isset($params['side']) || count($params['side']) < 1) {
                return $validateFlg;
            }

            foreach ($params['side'] as $sort => $values) {

                $this->sort = $sort;
                $this->type = key($values);

                $methodName = 'validate'.ucfirst(key($values));
                if (!$this->$methodName($values[key($values)])) {
                    $validateFlg = false;
                };

            }
            return $validateFlg;
        }

        /**
         * テキストのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateText($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateHeadingElem($values['heading'])) {
                $validateFlg = false;
            }

            if (!$this->validateTextareaElem($values['body'])) {
                $validateFlg = false;
            }

            return $validateFlg;
        }

        /**
         * リンクのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateLink($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateHeadingElem($values['heading'])) {
                $validateFlg = false;
            }

            foreach ($values as $subSort => $value) {
                if (is_numeric($subSort) && !$this->validateLinkElem($value, $subSort)) {
                    $validateFlg = false;
                }
            }

            return $validateFlg;
        }

        /**
         * 画像のバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateImage($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateHeadingElem($values['heading'])) {
                $validateFlg = false;
            }

            foreach ($values as $subSort => $value) {

                if (is_numeric($subSort) && !$this->validateImageElem($value, $subSort)
                ) {
                    $validateFlg = false;
                }

                if (is_numeric($subSort) && !$this->validateLinkElem($value, $subSort)
                ) {
                    $validateFlg = false;
                }
            }

            return $validateFlg;
        }

        /**
         * QRコードのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateQr($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateHeadingElem($values['heading'])) {
                $validateFlg = false;
            }
            return $validateFlg;
        }


        /**
         * Facebookのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateFb($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            return $validateFlg;
        }

        /**
         * Twitterのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateTw($values) {
            $validateFlg = true;

            if (!$this->validateDisplayElem($values['display'])) {
                $validateFlg = false;
            }

            return $validateFlg;
        }

        private function validateDisplayElem($str) {

            if ($str != 'visible' && $str != 'non_display') {
                return false;
            }
            return true;
        }

        /**
         * 見出し要素のバリデーション
         *
         * @param $str
         *
         * @return bool
         */
        private function validateHeadingElem($str) {

            $lengthValidetor = new StringLength(array('max' => 50,'min' =>0));

            if (!$lengthValidetor->isValid($str)) {
                $message = '見出しは50文字以内で入力してください';
                $this->setErrorMsg('heading', $message);
                return false;
            };
            return true;
        }

        /**
         * テキストエリア要素のバリデーション
         *
         * @param $str
         *
         * @return bool
         */
        private function validateTextareaElem($str) {

            $lengthValidetor = new StringLength(array('max' => 250,'min' =>1));

            if (!$lengthValidetor->isValid($str)) {
                $message = '本文は250文字以内で入力してください';
                $this->setErrorMsg('body', $message);
                return false;
            };
            return true;
        }

        /**
         * 画像要素のバリデーション
         *
         * @param $array
         * @param $subSort
         *
         * @return bool
         */
        private function validateImageElem($array, $subSort) {

            $validateFlag = true;

            if (!is_numeric($array['image_id'])) {

                $validateFlag = false;
                $message = '画像を選択してください';
                $this->setErrorMsg('image_id', $message, $subSort);
            }

            $validator = new StringLength(array('max' => 50,'min' =>1));
            if (!$validator->isValid($array['image_title'])) {
                $validateFlag = false;
                $message = 'タイトルを50文字以内で入力してください';
                $this->setErrorMsg('image_title', $message, $subSort);
            }

            return $validateFlag;
        }

        /**
         * リンク要素のバリデーション
         *
         * @param $array
         * @param $subSort
         *
         * @return bool
         */
        private function validateLinkElem($array, $subSort) {

            if (!array_key_exists('link_type', $array)) {

                $message = 'リンクを選択してください';
                $this->setErrorMsg('link_type', $message, $subSort);

                return false;
            }

            switch ($array['link_type']) {

                case 'own_page':
                    if (!is_numeric($array['page_id'])) {

                        $message = 'ページを選択してください';
                        $this->setErrorMsg('page_id', $message, $subSort);
                        return false;

                    }
                    return true;

                case 'url':
                    $lengthValidetor = new StringLength(array('max' => 100,'min' =>1));
                    if (!$lengthValidetor->isValid($array['url'])) {
                        $message = '100文字以内で入力してください';
                        $this->setErrorMsg('url', $message, $subSort);
                        return false;
                    }
                    return true;

                default:
                    return false;
            }
        }

        public $_error = array();

        /**
         * エラーメッセージをセット
         *
         * @param      $name
         * @param      $message
         * @param null $subSort
         */
        private function setErrorMsg($name, $message, $subSort = NULL) {

            if (is_numeric($subSort)) {
                $this->_error['side-'.$this->sort.'-'.$this->type.'-'.$subSort.'-'.$name] = array($message);
                return;
            }
            $this->_error['side-'.$this->sort.'-'.$this->type.'-'.$name] = array($message);
            return;

        }

        /**
         * エラーメッセージを取得
         * @return array
         */
        protected function getErrorMsg() {
            return $this->_error;
        }


        public function setLink($row, $elemsRows) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpSidePartsRepository::COL_LINK_HEADING;
            $res['heading'] = $row->$column;

            foreach ($elemsRows as $key => $elem) {

                if ($elem->side_parts_id == $row->id) {

                    $column = HpSideElementsRepository::COL_LINK_TYPE;
                    $res[$elem->sort]['link_type'] = $elem->$column == HpSideElementsRepository::OWN_PAGE ? 'own_page' : 'url';

                    $column = HpSideElementsRepository::COL_LINK_PAGE_ID;
                    $res[$elem->sort]['page_id'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_LINK_URL;
                    $res[$elem->sort]['url'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_LINK_OPEN;
                    $res[$elem->sort]['open'] = $elem->$column;
                }
            }
            return $res;
        }

        public function setImage($row, $elemsRows) {

            $res = array();

            $res['display'] = $row->display_flg;
            $column = HpSidePartsRepository::COL_IMAGE_HEADING;

            $res['heading'] = $row->$column;

            foreach ($elemsRows as $key => $elem) {
                if ($elem->side_parts_id == $row->id) {

                    $column = HpSideElementsRepository::COL_IMAGE_ID;
                    $res[$elem->sort]['image_id'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_IMAGE_TITLE;
                    $res[$elem->sort]['image_title'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_LINK_TYPE;
                    $res[$elem->sort]['link_type'] = $elem->$column == HpSideElementsRepository::OWN_PAGE ? 'own_page' : 'url';

                    $column = HpSideElementsRepository::COL_LINK_PAGE_ID;
                    $res[$elem->sort]['page_id'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_LINK_URL;
                    $res[$elem->sort]['url'] = $elem->$column;

                    $column = HpSideElementsRepository::COL_LINK_OPEN;
                    $res[$elem->sort]['open'] = $elem->$column;
                }
            }
            return $res;
        }

        public function setText($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpSidePartsRepository::COL_TEXT_HEADING;
            $res['heading'] = $row->$column;

            $column = HpSidePartsRepository::COL_TEXT_BODY;
            $res['body'] = $row->$column;

            return $res;
        }

        public function setQr($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpSidePartsRepository::COL_QR_HEADING;
            $res['heading'] = $row->$column;

            return $res;
        }

        public function setFb($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            return $res;
        }

        public function setTw($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            return $res;
        }

    }