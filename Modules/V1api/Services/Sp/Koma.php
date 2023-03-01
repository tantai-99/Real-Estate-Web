<?php
namespace Modules\V1api\Services\Sp;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class Koma extends Services\AbstractElementService
{
	public $title;
	public $content;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->content = $this->content($params, $settings, $datas);
		$this->title = $settings->special->getCurrentPagesSpecialRow()->title;
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
		$doc = $this->getTemplateDoc("/koma/content.sp.tpl");

        $bukkenList = $datas->getBukkenList();

        $komaMaker = new Element\Koma();
        // ATHOME_HP_DEV-4841 : 第4引数として、PageInitialSettings を追加
		$komaMaker->createKoma($doc, $bukkenList, $params, $settings->page);

        return $doc->html();
    }
}