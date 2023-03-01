<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use Modules\V1api\Services\Pc\Element;

class Rent extends Services\AbstractElementService
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
		$this->head = $this->head($params, $settings);
		$this->header = "<h1 class='tx-explain'>賃貸物件から物件種目の選択</h1>";
		$this->content = $this->content($params, $settings, $datas);
	}

	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{}
	
	private function head(
			Params $params,
			Settings $settings)
	{		
		$pageSetting = $settings->page;
		$head = new Services\Head();
		$head->title = "賃貸物件から探す｜{$pageSetting->getSiteName()}";
		$head->keywords = "賃貸物件,選択,{$pageSetting->getKeyword()}";
		$head->description = "【{$pageSetting->getCompanyName()}】賃貸物件選択画面。{$pageSetting->getDescription()}";
		return $head->html();
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
		$doc = $this->getTemplateDoc("/rent/content.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
		
        // パンくず作成
        // ホーム＞賃貸から探す
        $levels = [
            // URL = NAME
            '' => '賃貸から探す'
        ];
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);


        // 見出し処理
        $doc['h2']->text('賃貸物件から探す');
        $doc['.heading-lv1-1column']->next()->remove();

        $doc['h3.heading-lv2-1column']->text('お探しの賃貸物件をお選びください');

        $lead_sentence = 'まずは、お探しの賃貸物件種目をお選びください。'.
            "賃貸の不動産情報をお探しなら、${comName}におまかせください。" .
            'あなたのご希望に合った賃貸物件がきっと見つかります。';
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);


        // 検索条件設定の種目のみ表示
        $settingShumoku = $searchCond->getShumoku();

        $doc["div.elemnet-kind-rent li"]->remove();

        $typeList = Estate\TypeList::getInstance();

        foreach ($settingShumoku as $cd) {
            // 種目情報
            $class = $typeList->getClassByType($cd);
            $name  = $typeList->get($cd);
            $url   = $typeList->getUrl($cd);

            $liElem = "<li><a href='/${url}/'>${name}</a></li>";
            // 賃貸
            if ($class == Estate\ClassList::CLASS_CHINTAI_KYOJU ||
                $class == Estate\ClassList::CLASS_CHINTAI_JIGYO)
            {
                $doc["div.elemnet-kind-rent ul"]->append($liElem);
            } else
            { // 売買　なにもしない
            }
        }

        // 子要素がなければ404エラー
        if ($doc["div.elemnet-kind-rent li"]->size() == 0)
        {
        	throw new \Exception('賃貸が設定されていない', 404);
        }

        // コンテンツ下部要素の作成
        $SEOMaker = new Element\SEOLinks();
        $SEOMaker->rent(
            $doc, $params, $settings, $datas);

        return $doc->html();
    }
}