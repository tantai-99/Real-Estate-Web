<?php
namespace Modules\V1api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\V1api\Services;
use Modules\V1api\Exceptions;

class ErrorController extends Controller {

    public function error(Request $request)
    {
        $errors = $request->error_handler;
        // no data error
        if ($errors->exception instanceof Exceptions\NoData) {
    		$this->view->clearVars();
            $this->view->success = false;
            $this->view->message = $errors->exception->getDisplayMessage();
            $this->debug();
			return;
        } elseif ($errors->exception instanceof Exceptions\Expired) {
    		$this->view->clearVars();
            $this->view->success = true;
            $this->view->message_id = $errors->exception->getMessageId();
            $this->view->message = $errors->exception->getDisplayMessage();
            $this->debug();
            return;
        } elseif ($errors->exception instanceof Exceptions\Retryable) {
    		$this->view->clearVars();
            $this->view->success = false;
            $this->view->error_id = $errors->exception->getErrId();
            $this->view->message = $errors->exception->getMessage();
        	$exception = $errors->exception;
        	$this->view->exception = array(
        			'message' => $exception->getMessage(),
        			'file' => $exception->getFile(),
        			'line' => $exception->getLine(),
        			'trace' => $exception->getTraceAsString()
        	);
        	$this->debug();
			return;
        }
		parent::errorAction();
    }
    
    // このメソッドは本来ApiAbstractControllerのメソッド
    protected function debug()
    {
    	$client = Services\BApi\Client::getInstance();
    	$bDebug = $client->debug();
    
    	$client = Services\KApi\Client::getInstance();
    	$kDebug = $client->debug();
    
    	foreach ($kDebug as $key => $value) {
    		$bDebug[] = [$value];
    	}
    
    	$this->view->debug = $bDebug;
    }
}