<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Response;
use stdClass;
use Library\Custom\Model\Lists\Original;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\Information\InformationRepositoryInterface;
use Illuminate\Support\Facades\App;
use Exception;
use Library\Custom\View\TopOriginalLang;
use Illuminate\Http\Request;
use App\Repositories\Hp\HpRepositoryInterface;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $guard;
    protected $view;
    protected $timezone = 'Asia/Tokyo';
    protected $_request;

    public function __construct() {
        $this->_request = app('request');
        View::share('title', 'ホームページ作成ツール');

        $this->view = new \Library\Custom\View();
        $this->view->addHelperPath(array('library/Custom/View/Helper/'), '\Library\Custom\View\Helper\\');
        $topic = '<img alt="ホーム" src="/images/common/icon_home.png">';
        $this->view->topicPath()->clear();
        $this->view->topicPath($topic, 'index', 'index', array(), false);
        $this->middleware(function($request, $next) {
            $profile = getUser()->getProfile();
            if(getModuleName() == 'default' && $profile){
                $this->view->cms_plan = $profile->cms_plan;
            }
            return $this->init($request, $next);
        });
        View::share('view', $this->view);
        $this->text = new TopOriginalLang();
        View::share('text', $this->text);
        $this->clientS3 = \Aws\S3\S3Client::factory(array(
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest'
          ));
        $this->clientS3->registerStreamWrapper();
    }

    public function init($request, $next) {
        return $next($request);
    }

    public function login(Request $request) {
        $form = new $this->_formClass();
        $this->view->form = $form;
    
        //アットホームからのお知らせを取得
        $infoObj = App::make(InformationRepositoryInterface::class);
        $rows = $infoObj->getLoginbeforeData();
        $count = $infoObj->getFoundRow($infoObj->getLoginbeforeDataStatement());
        $this->view->count = $count;
        $infomations = $rows->toArray();
        $this->view->information = $infomations;

        if ($request->isMethod('post')) {
            $form->setData($request->all());
            if ($form->isValid($request->all())) {
                $user = getUser();
                if (!$user->login($form->getElement('login_id')->getValue(), $form->getElement('password')->getValue())) {
                    $message = $form->getElement('login_id')->getLabel() . 'または' . $form->getElement('password')->getLabel() . 'に誤りがあります';
                    $form->getElement('password')->setMessages($message);
                    $form->checkErrors();
                } else {
                    //ログイン日を設定する
                    $user->updateLoginDate();
                    $previous_link = $this->guard->getRequest()->session()->get('previous_link');
                    if(isset($previous_link)){
                        return redirect($previous_link);
                    }
                    return redirect('/');
                }
            }
        }
        $form->getElement('password')->setValue('');
        return view('auth.login');
    }

    public function _redirectSimple($action, $controller = null, $module = null) {
        if (!$module) {
            $module = getModuleName();
        }
        if (!$controller) {
            $controller = getControllerName();
        }
        return redirect(urlSimple($action, $controller, $module));
    }

    public function logout() {
        getUser()->logout();
        $this->guard->logout();
        session()->flush();
        return $this->_redirectSimple('index', 'index');
    }

    protected function _forward404() {
        abort(404, '404 not found.');
    }
    
    public function isApiRequest() {
        return strpos(getModuleName(), 'api') !== false ||
				strpos(getControllerName(), 'api') === 0 ||
				strpos(getActionName(), 'api') === 0;
    }

    /**
     * @param $companyId
     * @return null|App\Models\Company
     * @throws Exception
     */
    public function _checkCompanyTOP($companyId){
        $this->_checkValidCompanyPayload($companyId);
        /** @var App\Models\Company $row */
        $row = App::make(CompanyRepositoryInterface::class)->getDataForId($companyId);
        $this->_checkValidCompanyPayload($row, 'db');
        if ($row->checkTopOriginal()) {
            return $row;
        }
        $original = new Original;
        redirect($original->getScreenUrl(config('constants.original.ORIGINAL_EDIT_CMS'),$companyId));
    }

    /**
     * @param $data
     * @param string $type
     * @throws Exception
     */
    public function _checkValidCompanyPayload($data, $type = 'db'){
        switch($type){
            case 'request':
                if(!is_numeric($data) || $data < 1){
                    throw new Exception("No Company ID. ");
                }
                break;
            case 'db':
                if(!$data || is_null($data)){
                    throw new Exception("No Company ID. ");
                }
                break;
        }
    }

    /**
     * Generate breadcrumb for TOP Original Edit
     * @param $title
     * @param $companyId
     * @param array $data
     */
    public function breadcrumbTOPEdit($title, $companyId, array $data = []){
        $this->view->title = $title;
        $this->view->topicPath('契約管理', "index", $this->_controller);
        $this->view->topicPath("契約者詳細", "detail", $this->_controller, ['id'=>$companyId]);
        $this->view->topicPath("TOPオリジナル編集", "original-edit",$this->_controller,[
            'company_id' => $companyId
        ]);
        $this->view->topicPath($title);
    }

    public function checkDisplayFreeword($hpId) {
        $hpRow = App::make(HpRepositoryInterface::class)->fetchRow([['id', $hpId]]);
        $setting = $hpRow->getEstateSetting();
        $displayFreeword = true;
        if ($setting) {
            $settingList = $setting->getSearchSettingAll();
            foreach ($settingList as $estateClassSearchRow) {
                $searchSetting = $setting->getSearchSetting($estateClassSearchRow['estate_class']);
                if ($searchSetting['display_freeword'] == 1) {
                    $displayFreeword = false;
                }
            }
        }
        return $displayFreeword;
    }

    /**
	 * not isset action search
	 */
	protected function isActionSearch() {
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan ;
        if($cms_plan == config('constants.cms_plan.CMS_PLAN_LITE') ){
            return false;
        }
        return true;
	}

    public function getDateTime($time = 'now')
    {
        $timezone = new \DateTimeZone($this->timezone);
        $datetime = new \DateTime($time, $timezone);
        
        if (false === $datetime) {
            throw new Exception('Time input is not valid.');
            return;
        }
        
        return $datetime;
    }
}
