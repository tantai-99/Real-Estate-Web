<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Modules\Admin\Http\Form\AccountSearch;
use App\Repositories\Manager\ManagerRepositoryInterface;
use Modules\Admin\Http\Form\AccountRegist;
use Library\Custom\User\Admin;
use Exception;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
	protected $_controller = 'account';

	protected $managerRepository;

	public function init($request, $next)
	{
		$this->managerRepository = App::make(ManagerRepositoryInterface::class);

		//権限チェック
		$profile = getInstanceUser('admin')->getProfile();
		if ($profile) {
			if ($profile->privilege_manage_flg != "1") {
				if ($profile->privilege_edit_flg == "1") {
					return redirect('/admin/company/');
				} else {
					return redirect('/admin/password/');
				}
			}
		}

		return $next($request);
	}

	public function index(Request $request)
	{
		// topicPath
		$this->view->topicPath('アカウント管理');

		$search_form = new AccountSearch();
		$this->view->search_form = $search_form;

		//パラメータ取得
		$params = $request->all();
		$search_form->setData($params);
		
		$rows = $this->managerRepository->searchData($request);
		$this->view->managers = $rows;

		$search_arr = array();
		foreach ($search_form->getElements() as $key => $val) {
			$search_arr[$key] = ($val->getValue() == null) ? "" :  $val->getValue();
		}

		$this->view->search_param = $search_arr;
		return view('admin::account.index');
	}

	public function edit(Request $request)
	{
		$this->view->topicPath('アカウント管理', "index", $this->_controller);
		$this->view->topicPath("アカウント作成・変更");

		//フォーム設定
		$form = new AccountRegist();

		$this->view->form = $form;

		//パラメータ取得
		$params = $request->all();

		$managerObj = $this->managerRepository;

		//登録ボタン押下時
		if ($request->has("submit") && $request->submit != "") {

			//バリデーション
			$form->setData($params);
			if ($form->isValid($params)) {

				$error_flg = false;

				//ログインIDの重複チェック
				$rows = $managerObj->getDataForLoginId($request->login_id, $request->id);
				if ($rows->count() > 0) {
					$form->getElement('login_id')->setMessages(array("既にログインIDが使用されています。"));
					$error_flg = true;
				}

				if (!$error_flg) {
					$form->setData($params);
					$form->getMessages();
					$request->input("back", "");
					$request->input("submit", "");
					$this->view->params = $params;
					return view('admin::account.conf');
				}
			}

			$form->setData($params);
			$this->view->params = $params;
			return view('admin::account.edit');

			//戻るボタン押下時
		} else if ($request->has("back") && $request->back != "") {
			unset($params['back']);
			$form->setData($params);

			//初期データ取得時
		} else if ($request->has("id") && $request->id != "") {

			$row = $managerObj->getDataForId($request->id);
			if ($row == null) {
				throw new Exception("No Manager Data. ");
				exit;
			}

			$privilege = array();
			if ($row->privilege_edit_flg == 1)   $privilege["privilege_flg"][] = config('constants.manager_account_authority.PRIVILEGE_EDIT');
			if ($row->privilege_manage_flg == 1) $privilege["privilege_flg"][] = config('constants.manager_account_authority.PRIVILEGE_MANAGE');
			if ($row->privilege_create_flg == 1) $privilege["privilege_flg"][] = config('constants.manager_account_authority.PRIVILEGE_CREATE');
			if ($row->privilege_open_flg == 1)   $privilege["privilege_flg"][] = config('constants.manager_account_authority.PRIVILEGE_OPEN');
			$form->setData($privilege);
			$form->setData($row->toArray());
		}

		$this->view->params = $params;
		return view('admin::account.edit');
	}

	public function conf(Request $request)
	{
		$this->view->topicPath('アカウント管理', "index", $this->_controller);
		$this->view->topicPath("アカウント作成・編集");

		$managerObj = $this->managerRepository;

		//パラメータ取得
		$params = $request->all();

		//元に戻るボタン押下時
		if ($request->has("back") && $request->back != "") {
			$form = new AccountRegist();
			$this->view->form = $form;
			$form->setData($params);
			$this->view->params = $params;
			return view('admin::account.edit');

			//登録ボタン押下時
		} else if ($request->has("submit") && $request->submit != "") {

			// ATHOME_HP_DEV-5105 CSRFチェック
			// $this->_helper->csrfToken();

			getUser()->clearCsrfToken();

			//フォーム設定
			$form = new AccountRegist();
			$form->setData($params);

			//バリデーション
			if (!$form->isValid($params)) {

				$error_flg = false;

				//ログインIDの重複チェック
				$rows = $managerObj->getDataForLoginId($request->login_id, $request->id);
				if ($rows->count() > 0) {
					$form->getElement('login_id')->setMessages(array("既にログインIDが使用されています。"));
					$error_flg = true;
				}

				if ($error_flg) {
					$form->setData($params);
					$form->getMessages();
					$request->input("back", "");
					$request->input("submit", "");
					$this->view->params = $params;
					return view('admin::account.edit');
				}
			}

			try {
				DB::beginTransaction();

				//新規
				if (!isset($params["id"]) || $params["id"] == "") {
					//アカウント登録
					$row = $managerObj->create();
					//更新
				} else {
					//アカウント更新
					$row = $managerObj->getDataForId($params["id"]);
					if ($row == null) {
						throw new Exception("No Manager Data.");
						return;
					}
				}

				$row->name      = $params["name"];
				// パスワードが更新されていたら、契約管理・制作代行のログイン試行回数を0にしロックを解除する
				if ($row->password !== $params["password"]) {
					$row->login_failed_count = 0;
					$row->locked_date = NULL;
					$row->creator_login_failed_count = 0;
					$row->creator_locked_date = NULL;
				}
				$row->login_id  = $params["login_id"];
				$row->password  = $params["password"];
				$row->privilege_edit_flg    = 0;
				$row->privilege_manage_flg  = 0;
				$row->privilege_create_flg  = 0;
				$row->privilege_open_flg    = 0;

				foreach ($params["privilege_flg"] as $val) {
					switch ($val) {
						case config('constants.manager_account_authority.PRIVILEGE_EDIT'):
							$row->privilege_edit_flg   = 1;
							break;
						case config('constants.manager_account_authority.PRIVILEGE_MANAGE'):
							$row->privilege_manage_flg = 1;
							break;
						case config('constants.manager_account_authority.PRIVILEGE_CREATE'):
							$row->privilege_create_flg = 1;
							break;
						case config('constants.manager_account_authority.PRIVILEGE_OPEN'):
							$row->privilege_open_flg   = 1;
							break;
					}
				}

				$row->save();
				DB::commit();

				return redirect('/admin/account/comp');
			} catch (Exception $e) {
				DB::rollback();
				throw $e;
			}
		}
		$this->view->params = $params;

		// ATHOME_HP_DEV-5105 CSRFトークン生成
		getUser()->regenerateCsrfToken();
		return view('admin::account.conf');
	}

	public function comp(Request $request)
	{
		$this->view->topicPath('アカウント管理', "index", $this->_controller);
		$this->view->topicPath("アカウント作成・編集");

		//パラメータ取得
		$params = $request->all();
		$this->view->params = $params;
		return view('admin::account.comp');
	}
}
