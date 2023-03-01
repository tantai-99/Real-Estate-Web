<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Modules\V1api\Services\Pc\Element;

class SpatialEstatelist extends Services\AbstractElementService
{
	public $content;
	public $aside;

	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->content = $this->content($params, $settings, $datas);
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
		$doc = $this->getTemplateDoc("/spatial/estate_list.tpl");

		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;

		$pNames = $datas->getParamNames();
		// 検索タイプ
		$s_type = $params->getSearchType();
		// 種目情報の取得
		$type_ct = $params->getTypeCt();
		$shumoku    = $pNames->getShumokuCd();
		$shumoku_nm = $pNames->getShumokuName();
		// 都道府県の取得
		$ken_ct = $params->getKenCt();
		$ken_cd  = $pNames->getKenCd();
		$ken_nm  = $pNames->getKenName();
		// 市区町村の取得（複数指定の場合は使用できない）
		$shikugun_ct = $params->getShikugunCt(); // 単数or複数
		$shikugun_cd = $pNames->getShikugunCd();
		$shikugun_nm = $pNames->getShikugunName();
		// 政令指定都市の取得（複数指定の場合は使用できない）
		$locate_ct = $params->getLocateCt(); // 単数or複数
		$locate_cd = $pNames->getLocateCd();
		$locate_nm = $pNames->getLocateName();

		// こだわり条件
		$searchFilter = $datas->getSearchFilter();
		/*
		 * 物件一覧
		 */
		$resultMaker = new Element\ResultMap();
		$bukkenList = $datas->getBukkenList();
		$resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, !!$params->getSpecialPath(), $settings->page);

		return $doc->html();
	}
}