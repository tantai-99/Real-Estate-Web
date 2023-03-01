<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Publish\Render;
use Library\Custom\Model\Estate\TypeList;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Illuminate\Support\Facades\App;

/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内リンクのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpAnchor extends  HelperAbstract
{

    /**
     * atagの中身を作成する
     */
    public function hpAnchor($page_link_id = null) {

        if (is_null($page_link_id)) {
            return $this;
        }

        $arr = array();
        $arr['href'] = "";
        $arr['title'] = isset($page['title']) ? $page['title'] : "";
        $arr['target'] = "_self";

        if (is_numeric($page_link_id)) {

            $page = $this->findPageByLinkId($page_link_id);
            if (in_array($page['page_type_code'], App::make(HpPageRepositoryInterface::class)->getCategoryMap()[HpPageRepository::CATEGORY_FORM])) {
                $arr['target'] = "_blank";
            }

            if (getActionName() === 'previewPage') {

                $arr['href'] = urlSimple('preview-page', 'publish', 'default', [
                    'device' => app('request')->device,
                    'id'     => $page['id'],
                ]);
            }
            else {
                if (!is_null($page) && $page['new_path']) {
                    $domain      = Render\AbstractRender::www($this->_view->mode).Render\Content::prefix($this->_view->mode).$this->_view->company->domain;
                    $arr['href'] = Render\AbstractRender::protocol($this->_view->mode).$domain.'/'.substr($page['new_path'], 0, -10); // -10 = "index.html".length
                }
            }
        }
        else {

            $domain  = Render\AbstractRender::www($this->_view->mode).Render\Content::prefix($this->_view->mode).self::$_company->domain;
            $baseUrl = Render\AbstractRender::protocol($this->_view->mode).$domain;

            $estateSetting = self::$_hp->getEstateSetting();

            if ($estateSetting) {
                // 物件検索TOPへのリンク追加
                if (preg_match("/^estate_top/", $page_link_id)) {
                    $arr['href'] = $baseUrl.'/shumoku.html';;
                    $arr['title']  = $estateSetting->getTitle('物件検索トップ','shumoku',true);
                } elseif (preg_match("/^estate_rent/", $page_link_id)) {
                    $arr['href'] = $baseUrl.'/rent.html';;
                    $arr['title']  = $estateSetting->getTitle('賃貸物件検索トップ','rent',true);
                } elseif (preg_match("/^estate_purchase/", $page_link_id)) {
                    $arr['href'] = $baseUrl.'/purchase.html';;
                    $arr['title']  = $estateSetting->getTitle('売買物件検索トップ','purchase',true);
                }
                // 物件検索種目へのリンク追加
                elseif (preg_match("/^estate_type_/", $page_link_id)) {
                    $searchSettings = $estateSetting->getSearchSettingAll();
                    foreach ($searchSettings as $searchSetting) {
                        foreach ($searchSetting->getLinkIdList(true) as $linkId => $label) {
                            if ($page_link_id === $linkId) {
                                $url = '/'.TypeList::getInstance()->getUrl(str_replace('estate_type_', '', $page_link_id)).'/';
                                $arr['href']   = $baseUrl.$url; // @todo
                                $arr['title']  = $label;
                            }
                        }
                    }
                }

                // 物件特集へのリンク追加
                elseif (preg_match("/^estate_special_/", $page_link_id)) {
                    $specials = $estateSetting->getSpecialAll();
                    foreach ($specials as $special) {
                        if ($page_link_id === $special->getLinkId()) {
                            $arr['href']   = "{$baseUrl}/{$special->filename}/";
                            $arr['title']  = $special->title;
                        }
                    }
                }
            }
        }

        return $arr;
    }

    public function type($page_type_code)
    {
        $page = $this->findPageByType($page_type_code);
        if (!$page || !$page['link_id']) {
            return '';
        }

        return $this->hpLink($page['link_id']);
    }

    public function findPageByType($page_type)
    {
        foreach (self::$_pages as $page) {
            if ((int)$page['page_type_code'] === $page_type) {
                return $page;
            }
        }
        return null;
    }

    public function findPageByLinkId($link_id)
    {
        foreach (self::$_pages as $page) {
            if ($page['link_id'] == $link_id) {
                return $page;
            }
        }

        return null;
    }

}