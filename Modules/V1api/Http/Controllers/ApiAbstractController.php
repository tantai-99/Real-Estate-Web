<?php
namespace Modules\V1api\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use Modules\V1api\Models;
use Modules\V1api\Services\ServiceUtils;

class ApiAbstractController extends Controller {
    protected $_response = [];
    public function init($request, $next)
	{
        $this->_response = new \stdClass();
        // Zend_Registry::set('V1api_Request', $this->getRequest());
        
		// $contextSwitch = $this->_helper->getHelper('contextSwitch');
		// $contextSwitch->setActionContext($this->getRequest()->getActionName(), array('json'))
		// 	->initContext('json');
		/*
         * 認証
         */
        $params = new Models\Params((object) $this->_request->all());

        if (! $this->authApi($params->getComId(), $params->getApiKey()) &&
                \App::environment() != "local") {
            throw new \Exception('Unauthorized api key.');
        }
        ServiceUtils::resetRequsest();
        $this->preDispatch();
        return $next($request);
    }
    
    public function preDispatch() {

    }
	
	/** 
	 * API認証
	 */
	private function authApi($company_id, $api_key){ 

        $model = \App::make(CompanyAccountRepositoryInterface::class);
		$row  = $model->getDataRowForCompanyId($company_id);

		if ($row->api_key == $api_key){
			return true;
		}
		return false;
	}
    

	public function postDispatch()
	{
        if (\App::environment() != "production" && \App::environment() != "staging") {
        	$this->debug();
        }
	}

}