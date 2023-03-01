<?php
namespace App\Http\Form\Main;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainElement\HpMainElementRepository;

    abstract class AbstractMain extends Form {

        public function init() {

            $this->setName('main');

            $element = new Element('text');
            $element->setLabel('テキスト');
            $this->add($element);

            $element = new Element('list');
            $element->setLabel('リスト');
            $this->add($element);

            $element = new Element('table');
            $element->setLabel('表');
            $this->add($element);

            $element = new Element('image');
            $element->setLabel('画像');
            $this->add($element);

            $element = new Element('map');
            $element->setLabel('地図');
            $this->add($element);

            // @todo
            // 物件フォーム->ph2?
            //
            // $element = new Element('structure');
            // $this->add($element);

            // @todo
            // YouTubeフォーム->仕様未定
            //
            // $element = new Element('youtube');
            // $this->add($element);

            $this->useViewScript('main.phtml');
        }


        private $area;
        private $column;
        private $sort;
        private $type;

        public function isValid($params = array(),$checkErrors = true) {

            parent::isValid($params);

            $validateFlg = true;

            if (count($params['main']) < 1) {
                return true;
            }

            foreach ($params['main'] as $area => $temp1) {
                foreach ($temp1 as $column => $temp2) {
                    foreach ($temp2 as $sort => $values) {

                        $this->area = $area;
                        $this->column = $column;
                        $this->sort = $sort;
                        $this->type = key($values);

                        $methodName = 'validate'.ucfirst($this->type);

                        if (!$this->$methodName($values[$this->type])) {
                            $validateFlg = false;
                        };
                    }
                }
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

            if (!$this->validateElemDisplay($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeadingType($values['heading_type'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeading($values['heading'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemTextarea($values['body'])) {
                $validateFlg = false;
            }

            return $validateFlg;
        }

        /**
         * リストのバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateList($values) {

            $validateFlg = true;

            if (!$this->validateElemDisplay($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeadingType($values['heading_type'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeading($values['heading'])) {
                $validateFlg = false;
            }

            foreach ($values as $subSort => $value) {

                if (is_numeric($subSort) && !$this->validateElemList($value['list'], $subSort)) {
                    $validateFlg = false;
                }

                if (is_numeric($subSort) && !$this->validateElemLink($value, $subSort)) {
                    $validateFlg = false;
                }
            }

            return $validateFlg;
        }

        private function validateTable($values) {

            $validateFlg = true;

            if (!$this->validateElemDisplay($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeadingType($values['heading_type'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeading($values['heading'])) {
                $validateFlg = false;
            }

            foreach ($values as $subSort => $value) {

                if (is_numeric($subSort) && !$this->validateElemTh($value['th'], $subSort)) {
                    $validateFlg = false;
                }

                if (is_numeric($subSort) && !$this->validateElemTd($value['td'], $subSort)) {
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

            if (!$this->validateElemDisplay($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeadingType($values['heading_type'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeading($values['heading'])) {
                $validateFlg = false;
            }


            if (!$this->validateElemImage($values)) {
                $validateFlg = false;
            }

            if (!$this->validateElemLink($values)) {
                $validateFlg = false;
            }

            return $validateFlg;
        }

        /**
         * 地図のバリデーション
         *
         * @param $values
         *
         * @return bool
         */
        private function validateMap($values) {

            $validateFlg = true;

            if (!$this->validateElemDisplay($values['display'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeadingType($values['heading_type'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemHeading($values['heading'])) {
                $validateFlg = false;
            }

            if (!$this->validateElemLatLng((float)$values['lat'], (float)$values['lng'])) {
                $validateFlg = false;
            }


            return $validateFlg;
        }

        private function validateElemLatLng($lat, $lng) {

            $validator = new Zend_Validate_Float();
            if (!$validator->isValid($lat) || !$validator->isValid($lng)) {
                $message = 'ピンを配置してください';
                $this->setErrorMsg('lat', $message);
                return false;
            };
            return true;
        }


        private function validateElemTh($str, $subSort) {

            $lengthValidetor = new StringLength(array('max' => 50,'min' =>1));

            if (!$lengthValidetor->isValid($str)) {
                $message = '50文字以内で入力してください';
                $this->setErrorMsg('th', $message, $subSort);
                return false;
            };
            return true;
        }

        private function validateElemTd($str, $subSort) {

            $lengthValidetor = new StringLength(array('max' => 50,'min' =>1));

            if (!$lengthValidetor->isValid($str)) {
                $message = '50文字以内で入力してください';
                $this->setErrorMsg('td', $message, $subSort);
                return false;
            };
            return true;
        }

        private function validateElemDisplay($str) {

            if ($str != 'visible' && $str != 'non_display') {
                return false;
            }
            return true;
        }

        /**
         * 見出しタイプのバリデーション
         *
         * @param $str
         *
         * @return bool
         */
        private function validateElemHeadingType($str) {

            $array = array(
                'h1', 'h2', 'h3'
            );

            if (is_numeric(array_search($str, $array))) {
                return true;
            }
            $message = '見出しを選択してください';
            $this->setErrorMsg('heading_type', $message);
            return false;
        }

        /**
         * 見出し要素のバリデーション
         *
         * @param $str
         *
         * @return bool
         */
        private function validateElemHeading($str) {

            $lengthValidetor = new StringLength(array('max' => 50,'min' =>1));

            if (!$lengthValidetor->isValid($str)) {
                $message = '見出しは50文字以内で入力してください';
                $this->setErrorMsg('heading', $message);
                return false;
            };
            return true;
        }

        private function validateElemList($str, $subSort) {

            $lengthValidetor = new StringLength(array('max' => 50,'min' =>1));

            if (!$lengthValidetor->isValid($str)) {
                $message = 'リストは50文字以内で入力してください';
                $this->setErrorMsg('list', $message, $subSort);
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
        private function validateElemTextarea($str) {

            $lengthValidetor = new  StringLength(array('max' => 250,'min' =>1));

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
        private function validateElemImage($array) {

            $validateFlag = true;

            if (!is_numeric($array['image_id'])) {

                $validateFlag = false;
                $message = '画像を選択してください';
                $this->setErrorMsg('image_id', $message);
            }

            $validator = new StringLength(array('max' => 50,'min' =>1));
            if (!$validator->isValid($array['title'])) {
                $validateFlag = false;
                $message = 'タイトルを50文字以内で入力してください';
                $this->setErrorMsg('title', $message);
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
        private function validateElemLink($array, $subSort = NULL) {

            // @todo
            // リンク登録js実装後にバリデーション作る
            return true;

            if (!array_key_exists('link_type', $array)) {

                return true;
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
                    $lengthValidetor = new  StringLength(array('max' => 100,'min' =>1));
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
                $this->_error['main-'.$this->area.'-'.$this->column.'-'.$this->sort.'-'.$this->type.'-'.$subSort.'-'.$name] = array($message);
                return;
            }
            $this->_error['main-'.$this->area.'-'.$this->column.'-'.$this->sort.'-'.$this->type.'-'.$name] = array($message);
        }

        /**
         * エラーメッセージを取得
         * @return array
         */
        protected function getErrorMsg() {
            return $this->_error;
        }

        public function setText($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpMainPartsRepository::COL_TEXT_HEADING_TYPE;
            $res['heading_type'] = $row->$column;

            $column = HpMainPartsRepository::COL_TEXT_HEADING;
            $res['heading'] = $row->$column;

            $column = HpMainPartsRepository::COL_TEXT_BODY;
            $res['body'] = $row->$column;

            return $res;
        }

        public function setMap($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpMainPartsRepository::COL_MAP_HEADING_TYPE;
            $res['heading_type'] = 'h'.$row->$column;

            $column = HpMainPartsRepository::COL_MAP_HEADING;
            $res['heading'] = $row->$column;

            $column = HpMainPartsRepository::COL_MAP_LAT;
            $res['lat'] = $row->$column;

            $column = HpMainPartsRepository::COL_MAP_LNG;
            $res['lng'] = $row->$column;

            return $res;
        }

        public function setImage($row) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING_TYPE;
            $res['heading_type'] = 'h'.$row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING;
            $res['heading'] = $row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_ID;
            $res['image_id'] = $row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_TITLE;
            $res['title'] = $row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_LINK_TYPE;
            $res['link_type'] = $row->$column == HpMainPartsRepository::OWN_PAGE ? 'own_page' : 'url';

            $column = HpMainPartsRepository::COL_IMAGE_LINK_TYPE;
            $res['page_id'] = $row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_LINK_URL;
            $res['url'] = $row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_LINK_OPEN;
            $res['open'] = $row->$column == HpMainPartsRepository::BLANK ? true : false;

            return $res;

        }

        public function setList($row, $elemsRows) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING_TYPE;
            $res['heading_type'] = 'h'.$row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING;
            $res['heading'] = $row->$column;

            foreach ($elemsRows as $elemRow) {
                if ($elemRow->parts_id == $row->id) {
                    $column = HpMainElementRepository::COL_LIST_LIST;
                    $res[$elemRow->sort]['list'] = $elemRow->$column;
                };

                // @todo リンク部分実装
            }
            return $res;
        }


        public function setTable($row, $elemsRows) {

            $res = array();

            $res['display'] = $row->display_flg;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING_TYPE;
            $res['heading_type'] = 'h'.$row->$column;

            $column = HpMainPartsRepository::COL_IMAGE_HEADING;
            $res['heading'] = $row->$column;

            foreach ($elemsRows as $elemRow) {
                if ($elemRow->parts_id == $row->id) {

                    $column = HpMainElementRepository::COL_TABLE_TH;
                    $res[$elemRow->sort]['th'] = $elemRow->$column;

                    $column = HpMainElementRepository::COL_TABLE_TD;
                    $res[$elemRow->sort]['td'] = $elemRow->$column;
                };
            }

            return $res;
        }

        /**
         * メインコンテンツの共通パーツをプリセットに設定
         */
        public function setDefaultCommonParts() {

        }

        /**
         * メインコンテンツの共通パーツをプリセットに設定
         *
         * @param $col
         * @param $types = array(
         *               colNum => array(partsType),
         *               colNum => array(partsType, partsType, partsType, ),
         *               colNum => array(partsType, partsType, ),
         *               )
         * @return string
         */
        protected function setDefaultMainCommonParts($col, $types) {

            $html = '';

            $html .= '<div class="columnNum" data-column="'.$col.'">';
            for ($i = 1; $i <= $col; $i++) {
                $html .= '<div class="column">';
                foreach ($types[$col] as $type) {
                    $html .= '<span data-type="'.$type.'"></span>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';

            return $html;
        }

    }