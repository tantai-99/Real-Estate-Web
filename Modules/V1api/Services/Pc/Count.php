<?php
namespace Modules\V1api\Services\Pc;
use Modules\V1api\Services\AbstractElementService;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;

class Count extends AbstractElementService
{
	public $count;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->count = $this->count($params, $settings, $datas);
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
	}
	
	private function count(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        $bukkenList = $datas->getBukkenList();
        $facetJson = $datas->getFacetJson();
        return [
            'total'  => $bukkenList['total_count'],
            'facets' => $facetJson,
        ];
	}
}