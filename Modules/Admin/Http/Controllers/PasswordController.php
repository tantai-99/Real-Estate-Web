<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Repositories\Manager\ManagerRepositoryInterface;

use Library\Custom\Form;
use Modules\Admin\Http\Form\RePassword;

use Library\Custom\Crypt\Password as CryptPassword;

class PasswordController extends Controller {
	
	public function index(Request $request) {

		$this->view->topicPath("パスワード変更");

		//パラメータ取得
		$params = $request->all();

		//フォーム設定
		$form = new RePassword();
		$this->view->form = $form;
		// $form->populate($params);

		//情報取得
		$user = getInstanceUser('cms')->getProfile();
		$managerObj = App::make(ManagerRepositoryInterface::class);
		$row = $managerObj->getDataForId($user->id);
		if($row == null) {
			throw Exception("No Manager Data. ");
		}
		//登録ボタン押下時
		if($request->has("change") && $request->change != "") {
			//バリデーション
			$form->setData($params);
			if($form->isValid($params)) {
				$error_flg = false;
				$request->current_password = $request->current_password;
                //現在のパスワードの同一チェック\
                if($request->current_password != $row->password) {
                    $form->getElement('current_password')->setMessages( array("現在のパスワードが間違っています。") );
                    $error_flg = true;
                }

				//同一チェック
				if($request->password != $request->re_password) {
					$form->getElement('re_password')->setMessages( array("新パスワードと確認用パスワードが一致しません。") );
					$error_flg = true;
				}

				if(!$error_flg) {
					//アカウント更新
					$row = $managerObj->getDataForId($user->id);
					if($row == null) {
						throw new Exception("No Manager Data.");
						return;
					}	
						$row->password = $params['password'];				
					try {

			            DB::beginTransaction();
					    $row->save();

			            DB::commit();
		            } catch (Exception $e) {
			            DB::rollback();
			            throw $e;
		            }
					return redirect('/admin/password?regist_flg=true');
				}
			}
		}

		$this->view->manager = $row;
		// $this->view->assign("params", $params);

		// ATHOME_HP_DEV-5105 CSRFトークン生成
		// $this->view->assign("token", $this->getUser()->regenerateCsrfToken());
		return view('admin::password.index');
	}
}

