<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Services\Sp\Element;
class Favorite extends Services\AbstractElementService
{
	public $head;
	public $header;
    public $content;
    public $breadCrumb;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
		$this->content = $this->content($params, $settings, $datas);
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}
	
	private function head(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pageInitialSettings = $settings->page;
		$siteName = $pageInitialSettings->getSiteName();
		$keyword = $pageInitialSettings->getKeyword();
		$comName = $pageInitialSettings->getCompanyName();
		$description = $pageInitialSettings->getDescription();
	
		$head = new Services\Head();
		$head->title = "お気に入り｜${siteName}";
		$head->keywords = "お気に入り,${keyword}";
		$head->description = "お気に入りページ。${description}";
	
		return $head->html();
	}
	
	private function header(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		return "<h1 class='tx-explain'>お気に入りの物件一覧</h1>";
	}
	
	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".sp.tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/favorite/content.sp.tpl");
	
		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
	
		$pNames = $datas->getParamNames();
	
		// 会社名
		$comName = $settings->page->getCompanyName();

        /*
         * パンくず作成
         * 検索タイプによって、作成するパンくずは異なる。
         */
        $top = $this->getSearchTopFileName($searchCond);
        $levels = [
            // URL = NAME
            "" => "お気に入り"
        ];
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        // お気に入り物件一覧
        if($params->getParam('sort') == 'asc') {
            $bukkenListBuf = [];
            foreach ($datas->getBukkenList()['bukkens'] as $bukken) {
                $id = $bukken['display_model']['id'];
                $bukkenListBuf[ $id ] = $bukken;
            }
            $bukkenList['bukkens'] = [];

            foreach (explode(",", $params->getParam('bukken_id')) as $id) {
                if(isset($bukkenListBuf[$id])) {
                    $bukkenList['bukkens'][] = $bukkenListBuf[$id];
                }
            }
        } else {
        $bukkenList = $datas->getBukkenList();
        }
        
        // 物件一覧の生成　１２種目
        // 必要要素の初期化とテンプレ化
        $bukkenMaker = new Element\BukkenList();

        // 各件数        
        $cnt = [
            'rent' => 0,
            'office' => 0,
            'parking' => 0,
            'others' => 0,
            'mansion' => 0,
            'house' => 0,
            'land' => 0,
            'business' => 0,
        ];
        $wrapperElemRent = $doc['div.list-fav.rent'];
        $wrapperElemParking = $doc['div.list-fav.parking'];
        $wrapperElemOffice = $doc['div.list-fav.office'];
        $wrapperElemOthers = $doc['div.list-fav.others'];
        $wrapperElemMansion = $doc['div.list-fav.mansion'];
        $wrapperElemHouse = $doc['div.list-fav.house'];
        $wrapperElemLand = $doc['div.list-fav.land'];
        $wrapperElemBusiness = $doc['div.list-fav.business'];

        foreach ($bukkenList['bukkens'] as $bukken)
        {
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            // 物件種目の判別
            // ATHOME_HP_DEV-4841 : 第3引数として 利用中の種目一覧を追加
            $shumoku = Services\ServiceUtils::getShumokuFromBukkenModel($dispModel, $dataModel, $settings->page->searchSetting);
            if(!in_array($shumoku, $settings->page->searchSetting)) {
                continue;
            }

            // $bukkenElemに物件API情報を設定
            $bukkenElem = $bukkenMaker->createElementHachi($shumoku, $dispModel, $dataModel, $params, false, $dispModel->niji_kokoku_jido_kokai_fl, $settings->page, $searchCond);
            switch ($shumoku)
            {
                case Services\ServiceUtils::TYPE_CHINTAI:
                    $wrapperElemRent->append($bukkenElem);
                    $cnt['rent'] = $cnt['rent'] +1;
                    break;
                case Services\ServiceUtils::TYPE_KASI_TENPO:
                case Services\ServiceUtils::TYPE_KASI_OFFICE:
                    $wrapperElemOffice->append($bukkenElem);
                    $cnt['office'] = $cnt['office'] +1;
                    break;
                case Services\ServiceUtils::TYPE_PARKING:
                    $wrapperElemParking->append($bukkenElem);
                    $cnt['parking'] = $cnt['parking'] +1;
                    break;
                case Services\ServiceUtils::TYPE_KASI_TOCHI:
                case Services\ServiceUtils::TYPE_KASI_OTHER:
                    $wrapperElemOthers->append($bukkenElem);
                    $cnt['others'] = $cnt['others'] +1;
                    break;
                case Services\ServiceUtils::TYPE_MANSION:
                    $wrapperElemMansion->append($bukkenElem);
                    $cnt['mansion'] = $cnt['mansion'] +1;
                    break;
                case Services\ServiceUtils::TYPE_KODATE:
                    $wrapperElemHouse->append($bukkenElem);
                    $cnt['house'] = $cnt['house'] +1;
                    break;
                case Services\ServiceUtils::TYPE_URI_TOCHI:
                    $wrapperElemLand->append($bukkenElem);
                    $cnt['land'] = $cnt['land'] +1;
                    break;
                case Services\ServiceUtils::TYPE_URI_TENPO:
                case Services\ServiceUtils::TYPE_URI_OFFICE:
                case Services\ServiceUtils::TYPE_URI_OTHER:
                    $wrapperElemBusiness->append($bukkenElem);
                    $cnt['business'] = $cnt['business'] +1;
                    break;
                default:
                    throw new \Exception('Illegal Argument.');
                    break;
            }
        }

        $tab = new Element\Tab();
        $deleted = $tab->getDeleteTabList($settings);

        // 賃貸・売買の総件数を取得
        $chintai_total = $baibai_total = 0;
        foreach (['rent', 'parking', 'office', 'others'] as $name) {
            if (!in_array($name, $deleted)) {
                $chintai_total += $cnt[$name];
            }
        }
        foreach (['mansion', 'house', 'land', 'business'] as $name) {
            if (!in_array($name, $deleted)) {
                $baibai_total += $cnt[$name];
            }
        }

        // 各物件の件数をタブに設定
        $doc['div.element-search-tab4.chintai li.rent a']->text("賃貸（". $cnt['rent'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_CHINTAI));
        $doc['div.element-search-tab4.chintai li.parking a']->text("駐車場（". $cnt['parking'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_PARKING));
        $doc['div.element-search-tab4.chintai li.office a']->text("店舗・事務所（". $cnt['office'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_KASI_OFFICE));
        $doc['div.element-search-tab4.chintai li.others a']->text("土地・その他（". $cnt['others'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_KASI_OTHER));

        $doc['div.element-search-tab4.baibai li.mansion a']->text("マンション（". $cnt['mansion'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_MANSION));
        $doc['div.element-search-tab4.baibai li.house a']->text("一戸建て（". $cnt['house'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_KODATE));
        $doc['div.element-search-tab4.baibai li.land a']->text("土地（". $cnt['land'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_URI_TOCHI));
        $doc['div.element-search-tab4.baibai li.business a']->text("事業用（". $cnt['business'] ."件）")
            ->attr('href', Services\ServiceUtils::getInquiryURL(Services\ServiceUtils::TYPE_URI_OTHER));

        // 
        $searchTab = $params->getParam('searchtab');
        $checklistTab = $params->getParam('checklisttab');
        $sort = $params->getParam('sort');

        $sort_support = true;
        if(is_null($sort)) {
            $sort_support = false;
            $sort = 'asc';
        }
        // ソート設定
        $sortSelectElem = $doc['p.sort-select'];
        $sortElem = $sortSelectElem['select:last']->empty();
        $sortElem->attr('cursort', $sort);

        if($sort_support) {
            if(is_null($checklistTab) || empty($checklistTab)) {
                if($settings->search->isPurchaseShumokuOnly()) {
                    $checklistTab = 'baibai';
                } elseif ($settings->search->isRentShumokuOnly()) {
                    $checklistTab = 'chintai';
                } else {
                    if($chintai_total == 0 && $baibai_total > 0) {
                        $checklistTab = 'baibai';
                    } else {
                        $checklistTab = 'chintai';
                    }
                }
            }
            if(is_null($searchTab) || empty($searchTab)) {
                $searchTab = null;
                foreach ($doc['div.element-search-tab4.' . $checklistTab . ' li'] as $searchLiDom) {
                    $classList = explode(" ", $searchLiDom->getAttribute('class'));
                    $searchTabTmp = $classList[0];
                    if(!in_array($searchTabTmp, array_keys($cnt))) {	// activeの可能性あり
                        $searchTabTmp = $classList[1];
                        if(!in_array($searchTabTmp, array_keys($cnt))) {
                            // dead-route
                            continue;
                        }
                    }

                    // 非表示の種目ならスキップ
                    if(in_array($searchTabTmp, $deleted)) {
                        continue;
                    }

                    if(is_null($searchTab)) {
                        // とりあえず最初の種目を設定する(default)
                        $searchTab = $searchTabTmp;
                    }
                    if($cnt[ $searchTabTmp ] > 0) {
                        // 件数があれば上書きし種目取得処理終了
                        $searchTab = $searchTabTmp;
                        break;
                    }
                }
            }

            $doc['div.element-search-tab4']->attr('style', 'display:none');
            $doc['div.element-search-tab4.' . $checklistTab]->attr('style', 'display:block');

            $doc['div.list-fav']->attr('style', 'display:none');
            $doc['div.list-fav.' . $searchTab]->attr('style', 'display:block');

            $sortOptions = [];
            $sortOptions[] = ['value' => 'asc', 'name' => '保存した順' ];

            $sortVals = $this->getPersonalSortVals();

            if($checklistTab == 'baibai') {
                    $doc['div.element-tab-search.chintai']->attr('style', 'display:none');
                    $doc['div.element-tab-search.baibai']->attr('style', 'display:block');
            }

            switch($searchTab) {
                case 'rent':
                    $sortOptions[] = [ 'name' => '賃料が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '賃料が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '間取り順', 'value' => $sortVals['no06'] ];
                    $sortOptions[] = [ 'name' => '面積が広い順', 'value' => $sortVals['no07'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no09'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'parking':
                    $sortOptions[] = [ 'name' => '賃料が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '賃料が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'office':
                    $sortOptions[] = [ 'name' => '賃料が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '賃料が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '面積が広い順', 'value' => $sortVals['no07'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no09'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'others':
                    $sortOptions[] = [ 'name' => '賃料が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '賃料が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no09'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'mansion':
                    $sortOptions[] = [ 'name' => '価格が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '価格が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '間取り順', 'value' => $sortVals['no06'] ];
                    $sortOptions[] = [ 'name' => '建物面積が広い順', 'value' => $sortVals['no07'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no09'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'house':
                    $sortOptions[] = [ 'name' => '価格が安い順', 'value' => $sortVals['no12'] ];
                    $sortOptions[] = [ 'name' => '価格が高い順', 'value' => $sortVals['no13'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no14'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no15'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no16'] ];
                    $sortOptions[] = [ 'name' => '間取り順', 'value' => $sortVals['no17'] ];
                    $sortOptions[] = [ 'name' => '建物面積が広い順', 'value' => $sortVals['no18'] ];
                    $sortOptions[] = [ 'name' => '面積が広い順', 'value' => $sortVals['no19'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no20'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no21'] ];
                    break;
                case 'land':
                    $sortOptions[] = [ 'name' => '価格が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '価格が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '面積が広い順', 'value' => $sortVals['no08'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                case 'business':
                    $sortOptions[] = [ 'name' => '価格が安い順', 'value' => $sortVals['no01'] ];
                    $sortOptions[] = [ 'name' => '価格が高い順', 'value' => $sortVals['no02'] ];
                    $sortOptions[] = [ 'name' => '駅順', 'value' => $sortVals['no03'] ];
                    $sortOptions[] = [ 'name' => '住所順', 'value' => $sortVals['no04'] ];
                    $sortOptions[] = [ 'name' => '駅から近い順', 'value' => $sortVals['no05'] ];
                    $sortOptions[] = [ 'name' => '建物面積が広い順', 'value' => $sortVals['no07'] ];
                    $sortOptions[] = [ 'name' => '築年月が浅い順', 'value' => $sortVals['no09'] ];
                    $sortOptions[] = [ 'name' => '新着順', 'value' => $sortVals['no10'] ];
                    break;
                default:
                    break;
            }

            foreach ($sortOptions as $sortOption) {
                $sortElem->append( sprintf("<option value='%s' class='%s'>%s</option>", $sortOption['value'], $sortOption['value'], $sortOption['name']) );
            }

            if ($sortElem["option[value='${sort}']"]->size() == 0) {
                $sortElem["option[value='']"]->attr('selected', 'selected');
            } else {
                $sortElem["option[value='${sort}']"]->attr('selected', 'selected');
            }
        } else {
            $sortElem->append("<option class='desc'>保存した降順</option>");
            $sortElem->append("<option class='asc'>保存した順</option>");
            $sortElem["option[value='asc']"]->attr('selected', 'selected');
        }

        // 0件メッセージ処理        
        if ($cnt['rent'] != 0) $wrapperElemRent['div:first']->remove();
        if ($cnt['parking'] != 0) $wrapperElemParking['div:first']->remove();
        if ($cnt['office'] != 0) $wrapperElemOffice['div:first']->remove();
        if ($cnt['others'] != 0) $wrapperElemOthers['div:first']->remove();
        if ($cnt['mansion'] != 0) $wrapperElemMansion['div:first']->remove();
        if ($cnt['house'] != 0) $wrapperElemHouse['div:first']->remove();
        if ($cnt['land'] != 0) $wrapperElemLand['div:first']->remove();
        if ($cnt['business'] != 0) $wrapperElemBusiness['div:first']->remove();

        $doc = $tab->create($doc, $settings, $searchTab, $checklistTab);

        return $doc->html();
    }
}
