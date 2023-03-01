<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;
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
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/favorite/content.tpl");
		
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
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);

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
        // 検索結果ゼロのテキスト削除
        $doc['p.tx-nohit']->remove();
        
        // 物件一覧の生成　１２種目
        // 必要要素の初期化とテンプレ化
        $bukkenMaker = new Services\Pc\Element\BukkenList();
                
        $wrapperElemRent = $doc['div.article-object-wrapper.rent']->empty();
        $wrapperElemParking = $doc['div.article-object-wrapper.parking']->empty();
        $wrapperElemOffice = $doc['div.article-object-wrapper.office']->empty();
        $wrapperElemOthers = $doc['div.article-object-wrapper.others']->empty();
        $wrapperElemMansion = $doc['div.article-object-wrapper.mansion']->empty();
        $wrapperElemHouse = $doc['div.article-object-wrapper.house']->empty();
        $wrapperElemLand = $doc['div.article-object-wrapper.land']->empty();
        $wrapperElemBusiness = $doc['div.article-object-wrapper.business']->empty();

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
            $bukkenElem = $bukkenMaker->createElementHachi($shumoku, $dispModel, $dataModel, $params, $dispModel->niji_kokoku_jido_kokai_fl, $settings->page, $searchCond);
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

        // 各物件のお気に入りに登録ボタンを削除
        $doc['.article-object .btn-fav']->remove();

        // 各物件にお気に入りから削除
        $doc['.article-object .btn-contact']->after('<li class="btn-delete"><a href="#">お気に入り削除</a></li>');

        // 各物件の件数をタブに設定
        $doc['div.element-tab-search li.rent a']->text("賃貸（". $cnt['rent'] ."件）");
        $doc['div.element-tab-search li.parking a']->text("駐車場（". $cnt['parking'] ."件）");
        $doc['div.element-tab-search li.office a']->text("店舗・事務所（". $cnt['office'] ."件）");
        $doc['div.element-tab-search li.others a']->text("土地・その他（". $cnt['others'] ."件）");
        $doc['div.element-tab-search li.mansion a']->text("マンション（". $cnt['mansion'] ."件）");
        $doc['div.element-tab-search li.house a']->text("一戸建て（". $cnt['house'] ."件）");
        $doc['div.element-tab-search li.land a']->text("土地（". $cnt['land'] ."件）");
        $doc['div.element-tab-search li.business a']->text("事業用（". $cnt['business'] ."件）");

        // 
        $searchTab = $params->getParam('searchtab');
        $checklistTab = $params->getParam('checklisttab');
        $sort = $params->getParam('sort');

        $sort_support = true;
        if(is_null($sort)) {
            $sort_support = false;
            $sort = 'asc';
        }

        // 設定されていない物件種目のタブを削除
        $tab = new Services\Pc\Element\Tab();
        $doc = $tab->delete($doc, $settings, $searchTab, $checklistTab);

        // 各物件の件数をチェックリストに設定
        $deleted = $tab->getDeleteTabList();

        $chintai_total = 0;
        foreach (['rent', 'parking', 'office', 'others'] as $name) {
            if (!in_array($name, $deleted)) {
                $chintai_total += $cnt[$name];
            }
        }
        $doc['div.checklist-tab li.chintai a']->text("賃貸（".$chintai_total."件）");

        $baibai_total = 0;
        foreach (['mansion', 'house', 'land', 'business'] as $name) {
            if (!in_array($name, $deleted)) {
                $baibai_total += $cnt[$name];
            }
        }
        $doc['div.checklist-tab li.baibai a']->text("売買（".$baibai_total."件）");

        $sortSelectElem = $doc['dl.sort-select'];
        $sortElem = $sortSelectElem['select:last']->empty();
        $sortElem->attr('cursort', $sort);

        if($sort_support) {
            if(is_null($checklistTab) || empty($checklistTab)) {
                // checklist-tabの数が複数(2)かつ、 賃貸が0かつ、売買が複数
                if(count($doc['div.checklist-tab li']) == 2 && $chintai_total == 0 && $baibai_total > 0) {
                    // 売買をactiveにする
                    $checklistTab = 'baibai';
                    $doc['div.checklist-tab li.chintai']->removeClass('active');
                    $doc['div.checklist-tab li.baibai']->addClass('active');
                } else {
                    if($settings->search->isPurchaseShumokuOnly()) {
                        $checklistTab = 'baibai';
                        $doc['div.checklist-tab li.chintai']->removeClass('active');
                        $doc['div.checklist-tab li.baibai']->addClass('active');
                    } else {
                        // 賃貸のみ、賃貸売買両方かつ、賃貸が複数もしくは、賃貸・売買ともに0
                        $checklistTab = 'chintai';
                    }
                }
            }
            if(is_null($searchTab) || empty($searchTab)) {
                // 種別のactiveを一括解除
                $doc['div.element-tab-search.chintai li']->removeClass('active');
                $doc['div.element-tab-search.baibai li']->removeClass('active');

                // 指定種目配下の種別より、最初に現れる件数0でない種別を選択
                foreach ($doc['div.element-tab-search.' . $checklistTab . ' li'] as $searchLiDom) {
                    $array = explode(" ", $searchLiDom->getAttribute('class'));
                    $searchTabTmp = array_shift($array);
                    if($cnt[ $searchTabTmp ] > 0) {
                        $searchTab = $searchTabTmp;
                        break;
                    }
            }
            if(is_null($searchTab) || empty($searchTab)) {
                    // 種別がすべて0なら、最初に現れる種別を選択
                $array = explode(" ", $doc['div.element-tab-search.' . $checklistTab . ' li:first']->attr('class'));
                $searchTab = array_shift($array);
            }
                // 対象種別をactiveにする
                $doc['div.element-tab-search.' . $checklistTab . ' .' . $searchTab ]->addClass('active');
            }
            $doc['div.article-object-wrapper']->attr('style', 'display:none');
            $doc['div.article-object-wrapper.' . $searchTab]->attr('style', 'display:block');

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
            $sortElem->append("<option value='asc' class='asc'>保存した順</option>");
            $sortElem->append("<option value='desc' class='desc'>保存した降順</option>");
            $sortElem["option[value='asc']"]->attr('selected', 'selected');
        }

        return $doc->html();
    }
}
