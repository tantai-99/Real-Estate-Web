<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Library\Custom\User\Cms as UserCms;
use Library\Custom\Model\Lists\InformationDisplayPageCode;
use App\Repositories\Information\InformationRepositoryInterface;
use App\Repositories\InformationFiles\InformationFilesRepositoryInterface;

class AuthController extends Controller {

    protected $guard;
    protected $_formClass = '\App\Http\Form\Auth';
 
    const BASE_URL = '/';

    public function __construct() {
        parent::__construct();

        $this->guard = Auth::guard('default');
    }

    public function login(Request $request){
        $session = $this->_getAgentLoginSession();
		// 代行ログイン用パラメータは、'TantoCD=xxx' または 'TantoCd=xxx'
		$tantoCD = $request->has($request->TantoCD) ? $request->has($request->TantoCD):'';
		if (isEmpty($tantoCD)) {
			$tantoCD = $request->has($request->TantoCd) ? $request->TantoCd : '';
		}
		if (!isEmpty($tantoCD)) {
			$ini = getConfigs('agent_login');
			$allowed = $ini->allowed;
			if (!$allowed) {
				$this->_agentLogin($tantoCD);
				return;
			}
			
			$referer = null;
			if ($this->getRequest()->isPost()) {
				$referer = $session->referer;
			}
			else if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
				$session->referer = $referer;
			}
			
			if (!isEmpty($referer)) {
				foreach ($allowed as $allowedReferer) {
					if (strpos($referer, $allowedReferer) === 0) {
						$this->_agentLogin($tantoCD);
						return;
					}
				}
			}
		}
		
		$this->guard->getRequest()->session()->put('agent_login', null);
		return parent::login($request);
    }

    protected function _getAgentLoginSession() {

		return $this->guard->getRequest()->session()->get('agent_login');
    }
    
    protected function _agentLogin($tantoCD, Request $request) {
		
		$form = new LoginByMemberNo();
		$this->view->form = $form;
		$this->view->tantoCD = $tantoCD;
	
		if (!$this->getRequest()->isPost()) {
			return;
		}
	
		if ($request->isPost() && $form->isValid($request->all)) {
			$user = getUser();
            if ($user->loginAgent($form->getElement('member_no')->getValue(), $tantoCD)) {
                return redirect()->route('cms.index'); 
            } else {
                $message = $form->getElement('member_no')->getLabel() . 'に誤りがあります';
                $form->getElement('member_no')->setMessages($message);
                $form->checkErrors();
            }
        }
        
        return view('auth.agent-login');
		
		$this->_getAgentLoginSession()->unsetAll();
		
		$this->_redirectSimple('index', 'index');
	}

    public function logout(){

    	if(getUser()->getAdminProfile()){
    		$this->guard->logout();
    		session()->flush();
    		return redirect()->guest(self::BASE_URL . 'creator/login');
    	}
        $this->guard->logout();
        session()->flush();
        return redirect()->guest(self::BASE_URL . 'auth/login?r=1');
    }

	public function list(Request $request){
		// topicPath
		$this->view->topicPath('アットホームからのお知らせ');

		//後で削除
		$list = new InformationDisplayPageCode();
		$this->view->display_page_codes = $list->getAll();

		//パラメータ取得
		$params = $request->all();

		$infoObj = App::make(InformationRepositoryInterface::class);
		$infoFileObj = App::make(InformationFilesRepositoryInterface::class);

		//記事取得
		$rows = $infoObj->paginationBeforeLogin();
		$rows_arr = $rows->toArray();
		
		//ファイル取得
		$lists = array();
		foreach ($rows_arr['data'] as $row_key => $row_val) {
			$lists[$row_key] = $row_val;
			$lists[$row_key]['file_list'] = array();
			if ($row_val['display_page_code'] != config('constants.information_display_type_code.URL')) {
				$fileRows = $infoFileObj->getDataForInformationId($row_val['id']);
				if ($fileRows->count() > 0) {
					$datas = array();
					foreach ($fileRows as $key => $val) {
						$data = array();
						$data["file_id"]   = $val['id'];
						$data["name"]      = $val['name'];
						$data["extension"] = $val['extension'];
						$datas[] = $data;
					}
					$lists[$row_key]['file_list'] = $datas;
				}
			}
		}

		$this->view->paginator = $rows;
		$this->view->information = $lists;
		return view('auth.list');
	}

	/**
	 * 詳細
	 */
	public function detail(Request $request) {

		$this->view->topicPath('お知らせ詳細');


		if(!$request->has("id") || $request->id == "" || !is_numeric($request->id)) {
			throw new Exception("No Information ID");
			exit;
		}

		//パラメータ取得
		$params = null;
		$param = $request->all();
		// $this->view->assign("param", $params);

		$infoObj = App::make(InformationRepositoryInterface::class);
		$infoFileObj = App::make(InformationFilesRepositoryInterface::class);

		//お知らせ情報の取得
		$row = $infoObj->getDataForId($request->id);
		if($row == null) {
			throw new Exception("No Information Data. ");
			exit;

		}else if(!($row->display_page_code == config('constants.information_display_page_code.LOGIN_BEFORE_VIEW') || $row->display_page_code == config('constants.information_display_page_code.ALL_VIEW'))) {
			throw new Exception("No Information Display Page Code Error. ");
			exit;
		}

		//ファイル情報取得
		//詳細ページ
		if($row->display_type_code == config('constants.information_display_type_code.DETAIL_PAGE')) {
			$rows = $infoFileObj->getDataForInformationId($request->id);
			$fdata =  array();
			foreach($rows as $key => $val) {
			
				$fdata["file_id"][$key] = $val->id;
				$fdata["name"][$key]    = $val->name;
				$fdata["file_name"][$key] = $val->name .".". $val->extension;
				$fdata["extension_check"][$key] = substr($val->extension, 0, 3);
			}
			$params = $fdata;

		//ファイルリンク
		}else if($row->display_type_code == config('constants.information_display_type_code.FILE_LINK')) {

			$rows = $infoFileObj->getDataForInformationId($request->id);
			$fdata = $rows[0]->toArray();
			$fdata["file_id"] = $fdata["id"];
			$fdata["file_name"] = $fdata["name"] .".". $fdata["extension"];
			$fdata["file_contents"] = $fdata["contents"];
			$fdata["extension_check"] = substr($fdata["extension"],0, 3);
			$params = $fdata;
		}

		
		$this->view->information = $row;
		$this->view->param = $param;
		$this->view->params = $params;
		
		return view('auth.detail');
	}

	/**
	 * ファイルダウンロード
	 */
	public function download(Request $request) {

		$infoObj = App::make(InformationRepositoryInterface::class);
		$infoFileObj = App::make(InformationFilesRepositoryInterface::class);

		//ファイルリンク
		if($request->has("id") && $request->id != "") {
			$rows = $infoFileObj->getDataForInformationId($request->id);

			$fdata = $rows[0]->toArray();
			$fdata["file_id"] = $fdata["id"];
			$fdata["file_name"] = $fdata["name"] .".". $fdata["extension"];
			$fdata["file_contents"] = $fdata["contents"];
			$fdata["extension_check"] = substr($fdata["extension"],0, 3);

			$row = $infoObj->getDataForId($fdata["information_id"]);

		}

		//詳細
		if ($request->has("file_id") && $request->file_id != "") {
			$rows = $infoFileObj->getDataForId($request->file_id);

			$fdata = array();
			$fdata["file_id"] = $rows->id;
			$fdata["file_name"] = $rows->name .".". $rows->extension;
			$fdata["contents"] = $rows->contents;
			$fdata["extension_check"] = substr($rows->extension, 0, 3);

			$row = $infoObj->getDataForId($rows->information_id);
		}

		if(!($row->display_page_code == config('constants.information_display_page_code.LOGIN_BEFORE_VIEW') || $row->display_page_code == config('constants.information_display_page_code.ALL_VIEW'))) {
			throw new Exception("No Information Display Page Code Error. ");
			exit;
		}

		//ダウンロード
		//headerの設定（PDF,word,excel,pp)
		if($fdata["extension_check"] = 'xls'){
			header("Content-Type: application/msexcel';");
		
		}else if($fdata["extension_check"] = 'doc'){
			header("Content-Type: application/msword';");
		
		}else if($fdata["extension_check"] = 'ppt'){
			header("Content-Type: application/mspowerpoint';");
			
		}else if($fdata["extension_check"] = 'pdf'){
			header("Content-Type: application/pdf';");
		}
		$filename = $fdata['file_name'];
		$ua = $_SERVER['HTTP_USER_AGENT'];
		if (strstr($ua, 'Trident') || strstr($ua, 'MSIE')) $filename = mb_convert_encoding($fdata['file_name'], 'sjis-win', 'UTF-8');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		echo $fdata["contents"];
		exit();
	}
}