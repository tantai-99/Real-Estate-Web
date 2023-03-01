<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class Suggest extends Services\AbstractElementService
{
	public $suggest;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->suggest = $this->suggest($params, $settings, $datas);
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}
	
	private function suggest(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        return $bukkenSuggest = $datas->getSuggestList();
	}
}