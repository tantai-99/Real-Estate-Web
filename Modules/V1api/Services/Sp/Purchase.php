<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class Purchase extends Services\AbstractElementService
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
		$this->header = "<h1 class='tx-explain'>売買物件から物件種目の選択</h1>";
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
		$head->title = "売買物件から探す｜{$pageSetting->getSiteName()}";
		$head->keywords = "売買物件,選択,{$pageSetting->getKeyword()}";
		$head->description = "【{$pageSetting->getCompanyName()}】売買物件選択画面。{$pageSetting->getDescription()}";
		return $head->html();
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
		$doc = $this->getTemplateDoc("/purchase/content.sp.tpl");
	
		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
	
        // パンくず作成
        // ホーム＞売買から探す
        $levels = [
            // URL = NAME
            '' => '売買から探す'
        ];
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        // 見出し処理
        $doc['h2']->text('売買物件から探す');
        $doc['.heading-lv1-1column']->next()->remove();

        $doc['h3']->text('お探しの売買物件をお選びください');

        $lead_sentence = 'まずは、お探しの売買物件種目をお選びください。'.
            "売買の不動産情報をお探しなら、${comName}におまかせください。" .
            'あなたのご希望に合った売買物件がきっと見つかります。';
        $doc["div.tx-lead-keyword p"]->text($lead_sentence);


        // 検索条件設定の種目のみ表示
        $settingShumoku = $searchCond->getShumoku();

        $doc["section.elemnet-kind-buy li"]->remove();

        $typeList = Estate\TypeList::getInstance();

        foreach ($settingShumoku as $cd) {
            // 種目情報
            $class = $typeList->getClassByType($cd);
            $name  = $typeList->get($cd);
            $url   = $typeList->getUrl($cd);

            $liElem = "<li><a href='/${url}/'>${name}</a></li>"; // TODO constant
            // 賃貸 は何もしない
            if ($class == Estate\ClassList::CLASS_CHINTAI_KYOJU ||
                $class == Estate\ClassList::CLASS_CHINTAI_JIGYO)
            {
            } else
            { // 売買
                $doc["section.elemnet-kind-buy ul"]->append($liElem);
            }
        }

        // 子要素がなければ404エラー
        if ($doc["section.elemnet-kind-buy li"]->size() == 0)
        {
        	throw new \Exception('売買が設定されていない', 404);
        }

        return $doc->html();
    }
}