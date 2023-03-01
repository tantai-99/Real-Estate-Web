<?php
namespace Modules\V1api\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\V1api\Models;
use Modules\V1api\Services\Cms\HouseList;
use App\Traits\JsonResponse;

class HouseController extends ApiAbstractController
{   
    use JsonResponse;
    protected $params;
	protected $settings;
    public function preDispatch() {
        // パラメータ取得
		$params = (object) $this->_request->all();
		$this->params = new Models\Params($params);
		$this->settings = new Models\Settings($this->params);
    }

    public function houseAll() {
        $logic = new Models\Logic();
        $datas = $logic->searchHouse($this->params, $this->settings);
        if (is_null($this->params->getIsCount())) {
            $maker = new HouseList();
            $elements = $maker->execute($this->params, $this->settings, $datas);
            return $this->successV1api($elements);
        } else {
            $bukkenList = $datas->getBukkenList();
            return $this->successV1api(['total' => $bukkenList['total_count']]);
        }
    }
}