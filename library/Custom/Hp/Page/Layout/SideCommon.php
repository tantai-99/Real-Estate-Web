<?php
namespace Library\Custom\Hp\Page\Layout;
use Library\Custom\Form;
use Library\Custom\Form\Element;

class SideCommon extends Section {

    public $sideLayout = [];

    public function init(){
        $element = new Element\Hidden('title');
        
        $this->add($element);
        $element = new Element\Hidden('article_title');
		$this->add($element);
    }
    public function getSortedSideLayout() {
        $layouts = [];
        foreach ($this->sideLayout as $id => $layout) {
            $layout['id'] = $id;
            $layouts[] = $layout;
        }
        usort($layouts, function ($a, $b) {
            return $a['sort'] - $b['sort'];
        });
        return $layouts;
    }

    public function setValidSideLayout($layouts) {
        if (!is_array($layouts)) {
            return;
        }
        // 存在するデータを上書き
        foreach ($layouts as $id => $layout) {
            if (!isset($this->sideLayout[$id])) {
                continue;
            }

            if (isset($layout['sort']) && is_numeric($layout['sort'])) {
                $this->sideLayout[$id]['sort'] = (int) $layout['sort'];
            }

            if (isset($layout['display']) && in_array((int) $layout['display'], [0, 1], true)) {
                $this->sideLayout[$id]['display'] = (int) $layout['display'];
            }
            if (isset($layout['title']) && $id == \App\Models\Hp::SIDELAYOUT_OTHER_LINK) {
                $this->sideLayout[$id]['title'] = $layout['title'];
            }
            if (isset($layout['title']) && $id == \App\Models\Hp::SIDELAYOUT_ARTICLE_LINK) {
                $this->sideLayout[$id]['title'] = $layout['title'];
            }
        }
    }

    public function getTemplate() {
        return '_forms.hp-page.layout.side_common';
    }

    public function isValid($data, $checkError = true) {
        $isValid = parent::isValid($data);
        if (isset($data['title']) || (array_key_exists('title', $data) && $data['title'] == null)) {
            $count = mb_strlen($data['title']);
            if($data['title'] !== '' && $data['title'] !== null
                && empty(trim($data['title'])) || empty($data['title'])) {
                $this->getElement('title')->setMessages(["見出しを入力してください。"]);
                $isValid = false;
            }elseif($count > 20){
                $this->getElement('title')->setMessages("20 文字以内で入力してください。");
                $isValid = false;
            }
        }
        if (isset($data['article_title']) ||(array_key_exists('article_title', $data) && $data['article_title'] == null)) {
            $count = mb_strlen($data['article_title']);

            if($data['article_title'] !== '' && $data['article_title'] !== null
                && empty(trim($data['article_title'])) || empty($data['article_title'])){
                $this->getElement('article_title')->setMessages("見出しを入力してください。");
                $isValid = false;
            }elseif($count > 20){
                $this->getElement('article_title')->setMessages("20 文字以内で入力してください。");
                $isValid = false;
            }
        }
        return $isValid;
    }

    public function save($hp, $page) {
        parent::save($hp, $page);
        $hp->side_layout = json_encode($this->sideLayout, JSON_FORCE_OBJECT);
        $hp->save();
    }
}