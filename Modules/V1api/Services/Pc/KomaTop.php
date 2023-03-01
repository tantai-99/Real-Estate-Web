<?php

namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class KomaTop extends Services\AbstractElementService
{
	public $title;
	public $content;
	
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
        $bukkenList = $datas->getBukkenList(); 

        $komaMaker = new Element\KomaTop();
		// ATHOME_HP_DEV-4841 : ��4�����Ƃ��āAPageInitialSettings ��ǉ�
		return $komaMaker->createKoma($bukkenList, $params, $settings->special, $settings->page);

    }
}