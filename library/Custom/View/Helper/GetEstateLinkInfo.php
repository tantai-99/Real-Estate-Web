<?php
namespace Library\Custom\View\Helper;

use Library\Custom\Model\Estate\TypeList;

/**
 * サイドパーツのリンク生成に必要な情報を収集
 * - 物件検索、特集専用
 *
 * Class Library\Custom\View\Helper\GetEstateLinkInfo
 */
class GetEstateLinkInfo extends  HelperAbstract {

    /**
     * @param                          $estate_link_id string 文字列を含んだままのid
     * @param  $hp
     * @return array
     */
    public function getEstateLinkInfo($estate_link_id, $hp) {

        $estateSetting = $hp->getEstateSetting();
        if (!$estateSetting) {
            return $this->res();
        }

        // 物件検索TOPへのリンク追加
        if (preg_match("/^estate_top/", $estate_link_id)) {
            return $this->res('/shumoku.html', $estateSetting->getTitle('物件検索トップ','shumoku'));
        }

        // 賃貸物件検索TOPへのリンク追加
        if (preg_match("/^estate_rent/", $estate_link_id)) {
            return $this->res('/rent.html', $estateSetting->getTitle('賃貸物件検索トップ','rent'));
        }

        // 物件検索TOPへのリンク追加
        if (preg_match("/^estate_purchase/", $estate_link_id)) {
            return $this->res('/purchase.html', $estateSetting->getTitle('売買物件検索トップ','purchase'));
        }

        // 物件検索種目へのリンク追加
        if (preg_match("/^estate_type_/", $estate_link_id)) {
            $searchSettings = $estateSetting->getSearchSettingAll();
            foreach ($searchSettings as $searchSetting) {
                foreach ($searchSetting->getLinkIdList() as $linkId => $label) {
                    if ($estate_link_id === $linkId) {
                        $url = '/'.TypeList::getInstance()->getUrl(str_replace('estate_type_', '', $estate_link_id)).'/';;
                        return $this->res($url, $label);
                    }
                }
            }
        }

        // 物件特集へのリンク追加
        if (preg_match("/^estate_special_/", $estate_link_id)) {
            $specials = $estateSetting->getSpecialAll();
            foreach ($specials as $special) {
                if ($estate_link_id === $special->getLinkId()) {
                    return $this->res("/{$special->filename}/", $special->title);
                }
            }
        }

        return $this->res();
    }

    private function res($url = '/', $title = '') {

        return [
            'url'   => $url,
            'title' => $title,
        ];
    }
}