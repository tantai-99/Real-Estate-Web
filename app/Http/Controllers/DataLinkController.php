<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use Library\Custom\Model\Lists\Original;
use App\Traits\JsonResponse;

class DataLinkController extends Controller
{
    use JsonResponse;
    private $timeOut = 20;
    private $pulling = 2;

//     /**
//      * @throws App\Exceptions\UnAuthorizedForApi
//      */
//     public function __construct()
// 	{
//         parent::__construct();

// 		session_write_close();
// //        ignore_user_abort(true);
// //        set_time_limit($this->timeOut);
//     }
    
    /**
     * function check OriginalSetting has change
     *
     */
    public function apiGetUpdateNavigation(Request $request)
    {        
        try {
            // time to response result to browser
            $time = $request->get('pulling') ? (int)$request->get('pulling') : $this->pulling;
            
            $now = $this->getDateTime();
            $user = getUser();
            $hp = $user->getCurrentHp();
            $hpId = $hp->id;

            $count = 0;
            while ($count < $this->timeOut) {

                // Did the connection fail?
                if(connection_aborted() != 0)
                {
                    return $this->success(['updated' => 'disconnected']);
                }

                $updated  = Original::checkGlobalNavigationChange($hpId, $now) ? true : false;

                if ($updated) {
                    $res  =  ['updated'=>$updated];
                    // exit($this->_helper->json($res));
                    return $this->success($res);
                }
                
                // to clear cache
                clearstatcache();

                $count += $time;

                // to sleep
                sleep($time);
            }
            return $this->success(['cms_plan' => getUser()->getProfile()->cms_plan]);
        } catch (Exception $e) {
            $res  =  ['errors' =>['code'=>$e->getCode(),'message'=>$e->getMessage()]];
            // exit($this->_helper->json($res));
            return $this->success($res);
        }
    }
    
    /**
     * function check OriginalSetting has change
     *
     */
    public function apiGetUpdatePageParts(Request $request)
    {        
        try {
            // time to response result to browser
            $time = $request->get('pulling') ? (int)$request->get('pulling') : $this->pulling;
            
            $now = $this->getDateTime();
            $table = \App::make(HpMainPartsRepositoryInterface::class);
            $hp_id = getUser()->getCurrentHp()->id;

            $count = 0;
            while ($count < $this->timeOut) {

                // Did the connection fail?
                if(connection_aborted() != 0)
                {
                    // exit($this->_helper->json(['updated' => 'disconnected']));
                    return $this->success(['updated' => 'disconnected']);
                }

                $updated  = $table->hasChangedParts($hp_id, $now) ? true : false;
                
                if ($updated) {
                    $res  =  ['updated'=>$updated];
                    // exit($this->_helper->json($res));
                    return $this->success($res);
                }
                
                // to clear cache
                clearstatcache();

                $count += $time;

                // to sleep
                sleep($time);
            }
            return $this->success(['cms_plan' => getUser()->getProfile()->cms_plan]);
        } catch (Exception $e) {
            $res  =  ['errors' =>['code'=>$e->getCode(),'message'=>$e->getMessage()]];
            // exit($this->_helper->json($res));
            return $this->success($res);
        }
    }
    
    /**
     * function check notification has change
     *
     */
    public function apiGetUpdateNotification(Request $request)
    {
        try {
            // time to response result to browser
            $time = $request->get('pulling') ? (int)$request->get('pulling') : $this->pulling;
            
            $now = $this->getDateTime();
            $table = \App::make(HpMainPartsRepositoryInterface::class);
            $hp_id = getUser()->getCurrentHp()->id;

            $count = 0;
            while ($count < $this->timeOut) {

                // Did the connection fail?
                if(connection_aborted() != 0)
                {
                    // exit($this->_helper->json(['updated' => 'disconnected']));
                    return $this->success(['updated' => 'disconnected']);
                }

                $updated  = $table->hasChangedNotification($hp_id, $now) ? true : false;
                
                if ($updated) {
                    $res  =  ['updated'=>$updated];
                    // exit($this->_helper->json($res));
                    return $this->success($res);
                }
                
                // to clear cache
                clearstatcache();

                $count += $time;

                // to sleep
                sleep($time);
            }
            return $this->success(['cms_plan' => getUser()->getProfile()->cms_plan]);
        } catch (Exception $e) {
            $res  =  ['errors' =>['code'=>$e->getCode(),'message'=>$e->getMessage()]];
            // exit($this->_helper->json($res));
            return $this->success($res);
        }
    }
    
    /**
     * function check HpMainParts estate koma has change
     *
     */
    public function apiGetUpdateEstateKoma(Request $request)
    {
        try {
            // time to response result to browser
            $time = $request->get('pulling') ? (int)$request->get('pulling') : $this->pulling;
            
            $now = $this->getDateTime();
            $table = \App::make(HpMainPartsRepositoryInterface::class);
            $hp_id = getUser()->getCurrentHp()->id;

            $count = 0;
            while ($count < $this->timeOut) {

                // Did the connection fail?
                if(connection_aborted() != 0)
                {
                    // exit($this->_helper->json(['updated' => 'disconnected']));
                    return $this->success(['updated' => 'disconnected']);
                }

                $updated  = $table->hasChangedEstateKoma($hp_id, $now) ? true : false;
                
                if ($updated) {
                    $res  =  ['updated'=>$updated];
                    // exit($this->_helper->json($res));
                    return $this->success($res);
                }
                
                // to clear cache
                clearstatcache();

                $count += $time;

                // to sleep
                sleep($time);
            }
            return $this->success(['cms_plan' => getUser()->getProfile()->cms_plan]);
        } catch (Exception $e) {
            $res  =  ['errors' =>['code'=>$e->getCode(),'message'=>$e->getMessage()]];
            // exit($this->_helper->json($res));
            return $this->success($res);
        }
    }
}