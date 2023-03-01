<?php
namespace Modules\V1api\Http\Controllers;

use Modules\V1api\Models;
use Modules\V1api\Services;
use App\Traits\JsonResponse;

class InquiryController extends ApiAbstractController
{
	use JsonResponse;
    protected $params;
    protected $settings;
    
	public function preDispatch()
	{
		// パラメータ取得
		$params = (object) $this->_request->all();
		$this->params = new Models\Params($params);
		$this->settings = new Models\Settings($this->params);
	}
	
    /**
     * お問い合わせ画面API
     */
    public function edit()
    {
        $logic = new Models\Logic();
    	$datas = $logic->inqedit($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\InqEdit();
    	}
    	else
        {
    		$maker = new Services\Sp\InqEdit();
        }
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }
    
    public function confirm()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->inqconfirm($this->params, $this->settings);

    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\InqConfirm();
    	}
    	else
    	{
    		$maker = new Services\Sp\InqConfirm();
    	}
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }
    
    public function complete()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->inqcomplete($this->params, $this->settings);
    
    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\InqComplete();
    	}
    	else
    	{
    		$maker = new Services\Sp\InqComplete();
    	}
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }
    
    public function error()
    {
    	$logic = new Models\Logic();
    	$datas = $logic->inqerror($this->params, $this->settings);
    
    	if ($this->params->isPcMedia())
    	{
    		$maker = new Services\Pc\InqError();
    	}
    	else
    	{
    		$maker = new Services\Sp\InqError();
    	}
    	$elements = $maker->execute($this->params, $this->settings, $datas);
    	return $this->successV1api($elements);
    }
    
    private function createElement($serviceName, V1api_Service_iElementServiceManager $manager)
    {
        $service = $manager->getService($serviceName);
        $element = $service->createElement(
            $this->params, $this->pageInitialSetting, $this->searchSetting, $this->specialSetting);
        return $element;
    }
}