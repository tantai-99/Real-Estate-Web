<?php

namespace Modules\V1api\Services\Cms;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;

class HouseList extends Services\AbstractElementService
{
	public $content;
	public $info;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $this->content = $this->content($params, $settings, $datas);
        $this->info = $this->info($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}

	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
        /*
         * 物件一覧
         */
        // 物件要素の生成モジュール
        $bukkenMaker = new Element\HouseList;
        $bukkenList = $datas->getBukkenList();
        $pageInitialSettings = $settings->page;
        if ($params->getIsTitle()) {
            
            if (!$bukkenList['total_count']) {
                return [
                    'success' => false,
                    'bukken_no' => '',
                    'title' => '',
                    'url' => '',
                    'domain' => '',
                    'house_type' => [],
                ];
            }
            $bukken = $bukkenList['bukkens'][0];
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
            $searchSettings = array();
            $settingRow = null;
            $houseType = array();
            foreach($dispModel->csite_bukken_shumoku_cd as $shumoku) {
                $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
                if (!$settingRow) {
                    $settingRow = $settings->search->getSearchSettingRowByTypeCt($type_ct);
                }
                $houseType[] = Estate\TypeList::getInstance()->getByShumokuCode($shumoku);
            }
            $shumokuList = explode(",", $settingRow->enabled_estate_type);
            foreach($shumokuList as $shumoku_cd) {
                $searchSettings[] = Estate\TypeList::getShumokuCode(trim($shumoku_cd));
            }
            $detailUrl = Services\ServiceUtils::getDetailURL($dispModel, $dataModel,$searchSettings);
            $domain = 'https://www.'.$pageInitialSettings->getCompany()->domain;
            return [
                'success' => true,
                'bukken_no' => $dispModel->bukken_no,
                'title' => Services\ServiceUtils::replaceSsiteBukkenTitle($dispModel->csite_bukken_title),
                'url' => $detailUrl,
                'domain' => $domain,
                'house_type' => $houseType,
            ];
        }
        $doc = $this->getTemplateDoc("/houselist/content.tpl");
        
        // 必要要素の初期化とテンプレ化
        $wrapperElem = $doc['div.object-wrapper .tb-basic']->empty();
        $type_ct = (array) $params->getTypeCt();
        $settingRow = $settings->search->getSearchSettingRowPublishByTypeCt(@$type_ct[0]);
        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];
            if ($params->getLinkPage() && $params->getBukkenNo()) {
                foreach($dispModel->csite_bukken_shumoku_cd as $shumoku) {
                    $type_ct = Services\ServiceUtils::getShumokuCtByCd($shumoku);
                    $settingRow = $settings->search->getSearchSettingRowPublishByTypeCt($type_ct);
                    if ($settingRow) {
                        break;
                    }
                }
            }
            // $bukkenElemに物件APIから取得した情報を設定
            $bukkenElem = $bukkenMaker->createElement($dispModel, $dataModel, $params, $datas->getCodeList(), $settingRow,  $pageInitialSettings);
            $wrapperElem->append($bukkenElem);
        }
        if (is_null($params->getIsCondition())) {
            $doc['div.confirm-condition-search']->remove();
        }
        // sort-table処理
        $sortMaker = new Element\SortTable();
        $sortElem = $sortMaker->createElement($params);
        $doc['div.sort-table']->replaceWith($sortElem);
        /*
         * ページング処理
         */
        $page        = $bukkenList['current_page'];
        $total_page  = $bukkenList['total_pages'];
        $per_page    = $bukkenList['per_page'];
        $total_count = $bukkenList['total_count'];

        // カウント部分
        if ($total_count === 0)
        {
            $doc['div.section.house-list']->empty();
            $first = 0;
            $last = 0;
        } else {
            $first = $per_page * ($page - 1) + 1;
            $last  = $per_page * $page;
            if ($last > $total_count) $last = $total_count;
        }

        $countTxt = "<span>".number_format($total_count)."</span>件中　${first}件〜${last}件を表示";
        $doc['p.total-count']->empty()->append($countTxt);
        if ($total_count <= $per_page) {
            $doc['ul.paging']->remove();
        } else {
            $articlePagerElem      = $doc['ul.paging'];
            if ($params->getIsConfirm()) {
                $doc['ul.paging.paging-top']->remove();
            }
            $articlePagerElem      = $doc['ul.paging']->empty();
            $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
            $pager_prev  = "<li class='prev'><a href='javascript:;' data-page='${prev_page}'></a></li>";

            if ($page > 1) {
                $articlePagerElem->append($pager_prev);
            }
            if ($page <= 5) {
                $i = 1;
            } else  if (($total_page - $page) <= 2) {
                $i = $total_page -9;
            } else {
                $i = $page -4;
            }
            if ($i <= 0) {
                $i = 1;
            }
            $lastPage = $i +9;
            for ($i; $i <= $total_page && $i <= $lastPage; $i++)
            {
                $active = '';
                if ($i == $page) {
                    $active = 'class="is-active"';
                }
                $pager = "<li ${active}><a href='javascript:;' data-page='${i}'>${i}</a></li>";
                $articlePagerElem->append($pager);
            }
    
            if ($page != $total_page) {
                $next_page = $page +1;
                $pager_next = "<li class='next'><a href='javascript:;' data-page='${next_page}'></a></li>";
                $articlePagerElem->append($pager_next);
            }    
            // } else {
            //     $doc['div.article-pager']->remove();
            //     $articlePagerElem      = $doc['ul.article-pager-modal']->empty();
            //     $pager_first = "<li class='pager-first'><a href='javascript:;'>&lt;&lt;</a></li>";
            //     $prev_page = ($page == 0 || $page == 1) ? 1 : $page -1;
            //     $pager_prev  = "<li class='pager-prev'><a href='javascript:;' data-page='${prev_page}'>&lt;</a></li>";
            //     $pager_dot  = "<li class='pager-dot'><span class='dot'>...</span></li>";
        
            //     if ($page > 1) {
            //         $articlePagerElem->append($pager_first);
            //         $articlePagerElem->append($pager_prev);
            //     }
            //     if ($page > 3) {
            //         $articlePagerElem->append($pager_dot);
            //     }
            //     // if ($page >)
            //     // 上部ページャー　最大５
            //     if ($page <= 3) {
            //         $i = 1;
            //     } else  if (($total_page - $page) <= 2) {
            //         $i = $total_page -4;
            //     } else {
            //         $i = $page -2;
            //     }
            //     if ($i <= 0) {
            //         $i = 1;
            //     }
            //     $lastPage = $i +4;
            //     for ($i; $i <= $total_page && $i <= $lastPage; $i++)
            //     {
            //         if ($i === $page) {
            //             $pager = "<li><span>${i}</span></li>";
            //         } else {
            //             $pager = "<li><a href='javascript:;' data-page='${i}'>${i}</a></li>";
            //         }
            //         $articlePagerElem->append($pager);
            //     }
        
            //     $next_page = ($page == 0 || $page == $total_page) ? $total_page : $page +1;
            //     $pager_next = "<li class='pager-next'><a href='javascript:;' data-page='${next_page}'>&gt;</a></li>";
            //     $pager_last = "<li class='pager-last'><a href='javascript:;' data-page='${total_page}'>&gt;&gt;</a></li>";
            //     if ($page < ($total_page - 2)) {
            //         $articlePagerElem->append($pager_dot);
            //     }
            //     if ($page < ($total_page - 1)) {
            //         $articlePagerElem->append($pager_next);
            //         $articlePagerElem->append($pager_last);
            //     }
            // }
        }

        return $doc->html();
    }

    private function info(
			Params $params,
			Settings $settings,
			Datas $datas)
    {
        $bukkenList = $datas->getBukkenList();
    	return [
    			'current_page' => $bukkenList['current_page'],
    			'total_page' => $bukkenList['total_pages'],
    			'total_count' => $bukkenList['total_count']
    	];
    }
}