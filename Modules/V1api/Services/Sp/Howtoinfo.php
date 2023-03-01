<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Modules\V1api\Models\EnsenEki;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class Howtoinfo extends Services\AbstractElementService
{
	public $head;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
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
		$this_page = '情報の見方';
		$head->title = "${this_page}｜${siteName}";
		$head->keywords = "${this_page},${keyword}";
		$head->description = "${this_page}ページ。${description}";
	
		return $head->html();
	}
}