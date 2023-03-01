<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Form\RePassword;
use Illuminate\Support\Facades\App;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PasswordController extends Controller
{
	public function init($request, $next)
	{
		return $next($request);
	}

	public function index(Request $request)
	{
		$this->view->topicPath("パスワード変更");

		//パラメータ取得
		$params = $request->all();

		//フォーム設定
		$form = new RePassword();
		$this->view->form = $form;
		$this->view->params = $params;
		$form->setData($params);

		//情報取得
		$user = getInstanceUser('cms')->getProfile();
		$companyObj = App::make(CompanyAccountRepositoryInterface::class);

		$row = $companyObj->getDataRowForCompanyId($user->id);
		$this->view->plan	= $user->cms_plan;
		$this->view->start	= $user->reserve_start_date ? $user->reserve_start_date_view : $user->start_date_view;

		if ($row == null) {
			throw new \Exception("No Company Data. ");
		}

		//保存ボタン押下時
		if ($request->has("change") && $request->change != "") {
			$form->setData($params);
			//バリデーション
			if ($form->isValid($params)) {
				$error_flg = false;

				//現在のパスワードの同一チェック
				if ($request->password != $row->password) {
					$form->getElement('password')->setMessages(array("現在のパスワードが間違っています。"));
					$error_flg = true;
				}

				//新しいパスワードの同一チェック
				if ($request->new_password != $request->re_new_password) {
					$form->getElement('new_password')->setMessages(array("新パスワードと新しいパスワード（確認）が一致しません。"));
					$error_flg = true;
				}

				if (!$error_flg) {
					//アカウント更新
					$row = $companyObj->getDataRowForCompanyId($user->id);
					if ($row == null) {
						throw new \Exception("No Company Data.");
						return;
					}

					$row->password = $request->new_password;

					try {
						DB::beginTransaction();

						$row->save();

						DB::commit();
					} catch (\Exception $e) {
						DB::rollback();
						throw $e;
					}
					return redirect('/default/password?regist_flg=true');
				}
			}
		}

		return view('password.index');
	}
}
