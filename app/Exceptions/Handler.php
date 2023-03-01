<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use App\Exceptions\UnAuthorizedForApi;
use Throwable;
use Illuminate\Support\Facades\DB;
use Modules\V1api\Exceptions;
use Modules\V1api\Services;
require_once(base_path()."/library/phpQuery-onefile.php");

class Handler extends ExceptionHandler
{
    const DISPLAY_MESSAGE = 'システムエラーが発生しました。';
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];
    
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e) {
        $status = 0;
        if (method_exists($e, 'getStatusCode')) {
            $status = (int)$e->getStatusCode();
        }
        if (method_exists($e, 'getCode') && $status == 0) {
            $status = (int)$e->getCode();
        }
        if ($status == 0) {
            $status = 200;
        }
        switch($status) {
            case 404:
                $error = 'Page not found';
                break;
            default:
                if ($e instanceof CustomException) {
                    $error = $e->getDisplayMessage();
                } else if ($e instanceof TokenMismatchException) {
                    throw new UnAuthorizedForApi();
                } else {
                    $error = self::DISPLAY_MESSAGE;
                }

        }

        $isMaintenance= false;
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            if (strpos($request->getRequestUri(), 'v1api/parts/koma') == false && 
                strpos($request->getRequestUri(), '/v1api') > -1) {
                $isMaintenance = true;
            }
            if (strpos($request->getRequestUri(), 'api/contact') == true) {
                $isMaintenance = false;
            }
            if (strpos($request->getRequestUri(), 'api/estate-request') == true) {
                $isMaintenance = false;
            }
            if (strpos($request->getRequestUri(), 'api/estate-contact') == true) {
                $isMaintenance = false;
            }
        }

        \Log::error('Request Parameters');
        if ($isMaintenance) {
    	    if ($request->media === 'pc') {
                $doc = $this->getTemplateMaintanace(Services\ServiceUtils::checkDateMaitain() . '.tpl');
            } else {
                $doc = $this->getTemplateMaintanace(Services\ServiceUtils::checkDateMaitain() . '.sp.tpl');
            }
            return response()->json([
                'success' => true,
                'content' => $doc->html()
            ]);
        }

        if (strpos($request->getRequestUri(), 'api') > -1) {
            if( strpos($request->getRequestUri(), '/v1api') > -1) {
                $data = [];
                if ($e instanceof Exceptions\NoData) {
                    $data['success'] = false;
                    $data['message'] = $e->getDisplayMessage();
                    $data['debug'] = $this->debug();
                    return response()->json($data, $status);
                } elseif ($e instanceof Exceptions\Expired) {
                    $data['success']  = true;
                    $data['message_id'] = $e->getMessageId();
                    $data['message'] = $e->getDisplayMessage();
                    $data['debug'] = $this->debug();
                    return response()->json($data, $status);
                } elseif ($e instanceof Exceptions\Retryable) {
                    $data['success'] = false;
                    $error_id = $e->getErrId();
                    $data['message'] = $e->getMessage();
                    $data['exception'] = array(
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                    );
                    $data['debug'] = $this->debug();
                    return response()->json($data, $status);
                }
            }
            return response()->json([
                'success' => false,
                'error' => $error,
                'exception' => array(
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ),
                'request' => $request->all()
            ], $status);
        }
        if(getControllerName() == 'publish' && getActionName() == 'progress') {
            echo 'message: '.$e->getMessage();
            echo '<br>';
            echo 'file: '.$e->getFile();
            echo '<br>';
            echo 'line: '.$e->getLine();
            die;
        }
        $module = '';
        if (strpos($request->getRequestUri(), '/admin/') !== false) {
            $module = 'admin';
        }
        $title = 'ホームページ作成ツール';
        if (empty($module)) {
            return response()->view("errors.error", ['exception' => $e, 'error' => $error, 'title' => $title]);
        }
        return response()->view($module."::errors.error", ['exception' => $e, 'error' => $error, 'title' => $title]);
    }

    protected function debug()
    {
    	$client = Services\BApi\Client::getInstance();
    	$bDebug = $client->debug();
    
    	$client = Services\KApi\Client::getInstance();
    	$kDebug = $client->debug();
    
    	foreach ($kDebug as $key => $value) {
    		$bDebug[] = [$value];
    	}
    
    	return $bDebug;
    }

    protected function getTemplateMaintanace($template_file) {
        $html = file_get_contents(base_path('modules/v1api/resources/templates/' . $template_file));
        return \phpQuery::newDocument($html);
    }
}
