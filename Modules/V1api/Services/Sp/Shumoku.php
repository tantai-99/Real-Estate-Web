<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class Shumoku extends Services\AbstractElementService
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
		$this->header = "<h1 class='tx-explain'>物件種目の選択</h1>";
		$this->content = $this->content($params, $settings, $datas);
	}
	
    public function check(
    		Params $params,
    		Settings $settings,
    		Datas $datas)
	{
		$settingShumoku = $settings->search->getShumoku();
		if (is_null($settingShumoku) || count($settingShumoku) == 0) {
			throw new \Exception('売買・賃貸が設定されていない', 404);
		}
	}
	
    private function head(
        Params $params,
        Settings $settings)
    {
    	$pageSetting = $settings->page;
    	$head = new Services\Head();
    	$head->title = "物件種目から探す｜{$pageSetting->getSiteName()}";
    	$head->keywords = "物件種目,選択,{$pageSetting->getKeyword()}";
    	$head->description = "【{$pageSetting->getCompanyName()}】物件選択画面。{$pageSetting->getDescription()}";
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
        $doc = $this->getTemplateDoc("/shumoku/content.sp.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;
	
        // パンくず作成
        // ホーム＞種目を選択する
        $levels = [
            // URL = NAME
            '' => '種目を選択する'
        ];
        $this->breadCrumb = $this->createBreadCrumbSp($doc['div.breadcrumb'], $levels);

        // 見出し処理
        $doc['h2']->text('お探しの物件種目から探す');
        // 特集用の要素は削除
//        $doc['.heading-lv1-1column']->next()->remove();

        $doc['h3']->text('お探しの物件種目をお選びください');

        $lead_sentence = 'まずは、お探しの物件種目をお選びください。'.
            "不動産情報をお探しなら、${comName}におまかせください。" .
            'あなたのご希望に合った物件がきっと見つかります。';
        $doc[".heading-lv2-1column"]->next()->children()->text($lead_sentence);


        // 検索条件設定の種目のみ表示
        $settingShumoku = $searchCond->getShumoku();

        // 子要素がなければ削除
        if ($searchCond->isPurchaseShumokuOnly())
        {
            $doc["ul.element-search-tab-body.rent"]->remove();
            $doc["div.element-search-tab li.rent"]->remove();

            // 売買をアクティブに変更
            $doc["div.element-search-tab li.purchase"]->addClass('active');
            $doc["ul.element-search-tab-body.purchase"]->removeAttr('style');
        }
        else
        {
            $doc["ul.element-search-tab-body.rent li"]->remove();
        }

        if ($searchCond->isRentShumokuOnly())
        {
            $doc["ul.element-search-tab-body.purchase"]->remove();
            $doc["div.element-search-tab li.purchase"]->remove();
        }
        else
        {
            $doc["ul.element-search-tab-body.purchase li"]->remove();
        }

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
                $doc["ul.element-search-tab-body.rent"]->append($liElem);
            } else
            { // 売買
                $doc["ul.element-search-tab-body.purchase"]->append($liElem);
            }
        }

        return $doc->html();
    }
}