<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Publish\Render;
use Library\Custom\Model\Estate\TypeList;
use App\Repositories\HpPage\HpPageRepository;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内リンクのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpLink extends  HelperAbstract
{

    public function hpLink($page_link_id = null)
    {
        if (is_null($page_link_id)) {
            return $this;
        }

        if (is_numeric($page_link_id)) {

            $page = $this->findPageByLinkId($page_link_id);

            if (getActionName() !== 'previewPage') {
                $domain = Render\AbstractRender::www($this->_view->mode).Render\Content::prefix($this->_view->mode).$this->_view->company->domain;
                $baseUrl = Render\AbstractRender::protocol($this->_view->mode).$domain;
                if (!is_null($page) && $page['new_path']) {
                    return $baseUrl.'/'.substr($page['new_path'], 0, -10); // -10 = "index.html".length
                }
                return "{$baseUrl}/404notFound/";
            }

        return urlSimple('preview-page', 'publish', 'default', array(
                'device' => app('request')->device,
            'id' => $page['id']
        ));
        }
        else {

            $domain  = Render\AbstractRender::www($this->_view->mode).Render\Content::prefix($this->_view->mode).$this->_view->company->domain;
            $baseUrl = Render\AbstractRender::protocol($this->_view->mode).$domain;

            $estateSetting = $this->_view->hp->getEstateSetting();
            if ($estateSetting) {
                // 物件検索TOPへのリンク追加
                if (preg_match("/^estate_top/", $page_link_id)) {
                    return $baseUrl.'/shumoku.html';
                }

                // 賃貸物件検索TOPへのリンク追加
                if (preg_match("/^estate_rent/", $page_link_id)) {
                    return $baseUrl.'/rent.html';
                }

                // 売買物件検索TOPへのリンク追加
                if (preg_match("/^estate_purchase/", $page_link_id)) {
                    return $baseUrl.'/purchase.html';
                }

                // 物件検索種目へのリンク追加
                if (preg_match("/^estate_type_/", $page_link_id)) {
                    $searchSettings = $estateSetting->getSearchSettingAll();
                    foreach ($searchSettings as $searchSetting) {
                        foreach ($searchSetting->getLinkIdList(true) as $linkId => $label) {
                            if ($page_link_id === $linkId) {
                                $url = '/'.TypeList::getInstance()->getUrl(str_replace('estate_type_', '', $page_link_id)).'/';
                                return $baseUrl.$url;
                            }
                        }
                    }
                    $url = '/'.TypeList::getInstance()->getUrl(str_replace('estate_type_', '', $page_link_id)).'/';
                    return  $baseUrl.$url;
                }

                // 物件特集へのリンク追加
                if (preg_match("/^estate_special_/", $page_link_id)) {
                    $specials = $estateSetting->getSpecialAll();
                    foreach ($specials as $special) {
                        if ($page_link_id === $special->getLinkId()) {
                            return "{$baseUrl}/{$special->filename}/";
                        }
                    }
                    return "{$baseUrl}/404notFound/";
                }
            }

            return $baseUrl;
        }
    }

    public function type($page_type_code)
    {
        $page = $this->findPageByType($page_type_code);
        if (!$page || !$page['link_id']) {
            return '';
        }

        return $this->hpLink($page['link_id']);
    }
    
    public function linkList($page_link_id)
    {
        $pages = $this->_view->pages;
        if (isset($this->_view->all_pages)){
            $pages = $this->_view->all_pages;
        }

        foreach ($pages as $page) {
            if ($page_link_id  == $page['id'] && HpPageRepository::TYPE_INFO_INDEX == $page['page_type_code']) {
                return $this->hpLink($page['link_id']);
            }
        }
        
        return '';
    }

    public function findPageByType($page_type)
    {
        foreach ($this->_view->all_pages as $page) {
            if ((int)$page['page_type_code'] === $page_type) {
                return $page;
            }
        }
        return null;
    }

    public function findPageByLinkId($link_id)
    {
        $pages = $this->_view->pages;
        if (isset($this->_view->all_pages)){
            $pages = $this->_view->all_pages;
        }

        foreach ($pages as $page) {
            if ($page['link_id'] == $link_id) {
                return $page;
            }
        }

        return null;
    }
}