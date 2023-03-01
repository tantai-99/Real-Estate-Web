<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Library\Custom\User\Cms as UserCms;
use Library\Custom\User\loginAgency;
use Library\Custom\Crypt\Password;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use Illuminate\Support\Facades\App;
use App\Repositories\Manager\ManagerRepositoryInterface;
use App\Http\Form\Auth;
use App\Http\Form\LoginByMemberNo;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Library\Custom\Model\Lists\LogEditType;
use App\Traits\JsonResponse;
use Exception;
use Library\Custom\Model\Lists\Original;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Publish;
use Library\Custom\Ftp;
use App\Repositories\Company\CompanyRepositoryInterface;

class CreatorController extends Controller {
	use JsonResponse;
    const LOCK_TIME = 600;

	public function init($request, $next) {

		$actionName = getActionName();
		if (!getUser()->getAdminProfile() && $actionName != 'login') {

			return redirect('creator/login?r=1');
		}

        if (getUser()->getAdminProfile() && $actionName == 'index') {
            return $this->_redirectSimple('select-company', 'creator');
        }
        return $next($request);
	}

	public function index(Request $request) {
		
	}
	
	public function login(Request $request) {
		$form = new Auth();

		$this->view->form = $form;
		if ($request->isMethod('post')) {

			$form->setData($request->all());

			if ($form->isValid($request->all())) {
			
				$crypt = new Password();
				$table = App::make(ManagerRepositoryInterface::class);

		        $creatorRow = $table->fetchRow([['login_id', $form->getElement('login_id')->getValue()]]);
				$row = $table->fetchLoginProfile(
						$form->getElement('login_id')->getValue(),
						$crypt->encrypt($form->getElement('password')->getValue())
					);
		        $message = $form->getElement('login_id')->getLabel() . 'または' . $form->getElement('password')->getLabel() . 'に誤りがあります';
		        // アカウントロックチェック
		        if(isset($creatorRow)){
			        if (count($creatorRow->toArray()) > 0 && !empty($creatorRow->creator_locked_date)) {
			            $lock_time_diff = strtotime('now') - strtotime($creatorRow->creator_locked_date);
			            // アカウントロック中
			            if ($lock_time_diff < self::LOCK_TIME) {
			                $form->getElement('password')->setMessages($message);
			                $form->getMessages();
							$form->checkErrors();
			                return false;
			            } else {
			                $table->creatorUnlockLoginAccount($creatorRow);
			            }
			        }
			    }
				if ((!$row) || (!$row->privilege_create_flg && !$row->privilege_open_flg)) {
		            $table->creatorLoginFailed($creatorRow);
					$form->getElement('password')->setMessages([$message]);
					$form->getMessages();
					$form->checkErrors();
				}else{
			        $table->creatorUnlockLoginAccount($creatorRow);
					getUser()->setAdminProfile($row);
			        $request->session()->regenerate();
					return redirect('creator\select-company');
				}
			}
		}
		$form->getElement('password')->setValue('');
		return view('creator.login');
	}
	
	public function selectCompany(Request $request) {

		$form = new LoginByMemberNo();
		$this->view->form = $form;
		if ($request->isMethod('post')) {

			$form->setData($request->all());

			if ($form->isValid($request->all())) {	
				$user = getUser();
				if (!$user->loginAgency($form->getElement('member_no')->getValue())) {
					$message = $form->getElement('member_no')->getLabel() . 'に誤りがあります';
					$form->getElement('member_no')->setMessages($message);
					$form->getElement('member_no')->getMessages();
				}else{
					return redirect()->route('default.index.index'); 
				}
			}
		}
		return view('creator.select-company');
	}
	
	public function reSelectCompany() {
		getUser()->logoutCompanyForCreator();
		
		return redirect('creator\select-company');
	}
	
	public function deleteHp() {

		$this->view->topicPath('制作代行サイト削除');
		return view('creator.delete-hp');
	}
	
	public function apiDeleteHp() {

		$user = getUser();
        $isAgency = $user->isAgency();
		$table = App::make(AssociatedCompanyHpRepositoryInterface::class);
		
		if ($hp = $user->getCurrentHp()) {
			DB::beginTransaction();
			
			// 代行作成データを削除
			$hp->deleteAll(true);
			if ($backupHp = $user->getBackupHp()) {
				// ロールバック用データがある場合は削除
				$backupHp->deleteAll(true);
			}

			// 代行データ紐付けを削除
			$table->deleteCreatorHp($user->getProfile()->id);
			
			//全て終わったら「substitute」フォルダ内部を削除する
			$config 		= getConfigs('sales_demo');

			$companyObj = App::make(CompanyRepositoryInterface::class);
			$companyRow = $companyObj->getDataForId($user->getProfile()->id);
			$cftp = new Ftp( "ftp.{$config->demo->domain}" ) ;
			//ログインする
			$cftp->login( $companyRow->member_no, $companyRow->ftp_password ) ;
			//パッシブモードの設定
			if($companyRow->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE')) $cftp->pasv(true);
			//設定したディレクトリ以下を削除
			$cftp->deleteFolderBelow( "substitute.{$companyRow->member_no}.{$config->demo->domain}" ) ;

			DB::commit();

           CmsOperation::getInstance()->creatorLog(LogEditType::CREATOR_DATA_DELETE);
		}
		$data['cms_plan'] = $user->getProfile()->cms_plan;
		$user->logoutCompanyForCreator();
		
        $data['redirectTo'] = '/creator/select-company';
		
		return $this->success($data);
	}
	
	public function copyToCompany() {
		if ($this->view->hasBackupData()) {
			return redirect()->route('default.index.index');
		}
		$this->view->topicPath('代行更新');
		return view('creator.copy-to-company');
	}
	
	public function apiCopyToCompany(Request $request) {
		
		$user = getUser();
		$hp = $user->getCurrentHp();

		if ($user->getProfile()->getCurrentHp()->hasReserve()) {
			throw new Exception('has reserve');
		}
		
		if (!$user->getBackupHp()) {

			$profile = $user->getProfile();

            /** @var App\Models\Company $profile */
            $isTop = $profile->checkTopOriginal();
			/*
			 * ATHOME_HP_DEV-4866
			 * 代行更新のタイミングでトップオリジナルファイルのバックアップを行う
			 */
			if($isTop) {
				// Topオリジナルファイルフォルダ
				$topSrcPath = Original::getOriginalImportPath($profile->id);
				if(preg_match("/^(.*)\/" . $profile->id . "\/$/", $topSrcPath, $match)) {
					$topSrcPath = $match[1] . $ds . $profile->id;
				}
				$pubTopSrcPath = $topSrcPath . "_published";

				if(is_dir($topSrcPath)) {
					$command = 'aws s3 sync '. $topSrcPath . "/ " . $pubTopSrcPath . "/" . ' --delete';
					$output = [];
					exec($command, $output, $status);
					if($status != 0) die;
				}
			}
			// ここからpollingしつつ処理開始
            // ATHOME_HP_DEV-4426: Change clone data
			// header("Content-type: application/json; charset=utf-8");

			// 代行作成データのコピーを作成
			$newHp = $hp->copyAllForCreatorToCompany($isTop);

			$table = App::make(AssociatedCompanyHpRepositoryInterface::class);

			try {
				DB::beginTransaction();

				$currentHp = $profile->getCurrentHp();
				// ロールバック用データとして現データをとっておく
				$table->updateBackupHp($profile->id, $currentHp->id);
				// 現データと代行作成データのコピーを入れ替え
				$table->updateCurrentHp($profile->id, $newHp->id);

				CmsOperation::getInstance()->creatorLog(config('constants.log_edit_type.CREATOR_UPDATE'));
				
				//全て終わったら「substitute」フォルダ内部を削除する

				$pFtp				= new Publish\Ftp( $hp->id,config('constants.publish_type.TYPE_SUBSTITUTE')) ;

				$companyRow			= $pFtp->getCompany()				;	// 少し無理があるけど再利用
				$ftp_server_name	= $companyRow->ftp_server_name		;
				$ftp_user_id		= $companyRow->ftp_user_id			;
				$ftp_password		= $companyRow->ftp_password			;
				$domain				= $companyRow->domain				;

				$cftp = new Ftp($ftp_server_name) 						;

				//ログインする
				$cftp->login( $ftp_user_id, $ftp_password			) ;
				//パッシブモードの設定
				if($companyRow->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE')) $cftp->pasv(true);
				//設定したディレクトリ以下を削除
				$cftp->deleteFolderBelow( "substitute.". $domain	) ; 

				DB::commit();
				echo '<script>parent.cloneDataReload();</script>';
				exit;
			} catch (Exception $e) {
				DB::rollback();
				throw $e;
			}
		}
		$data['redirectTo'] = '/';
		return $this->success($data);
	}
	
	public function rollback() {
		if (!getInstanceUser('cms')->hasBackupData()) {
			return redirect()->route('default.index.index');
		}
		
		$this->view->topicPath('ロールバック');

		return view('creator.rollback');
	}
	
	public function apiRollback() {
		$user = getUser();
		$cms_plan = $user->getProfile()->cms_plan;
		if ($hp = $user->getBackupHp()) {
			$profile = $user->getProfile();
			$currentHp = $profile->getCurrentHp();
			
			$table = App::make(AssociatedCompanyHpRepositoryInterface::class);
			try {
				DB::beginTransaction();
			
				$table->updateCurrentHpForRollback($profile->id, $hp->id);
				// バックアップ（加盟店作成）と現データ（代行で作成）を入れ替え
				// App::make(HpRepositoryInterface::class)->update(array('all_upload_flg' => 1), array('id = ?' => $hp->id));
				// ATHOME_HP_DEV-3126
				$hpRow = App::make(HpRepositoryInterface::class)->fetchRow([['id' , $hp->id ]]);
				$hpRow->all_upload_flg = 1;
				$hpRow->setAllUploadParts('ALL', 1);
				$hpRow->save();

				// ATHOME_HP_DEV-5198
				// CMS:物件検索設定のupdate_date を現在時間にする
				$cmsSetting = App::make(HpEstateSettingRepositoryInterface::class)->getSetting($hp->id);
				if(!empty($cmsSetting) && is_numeric($cmsSetting->id)) {
					// setting.IDがある=物件検索設定済みの時のみ処理
					$updateDate = date('Y-m-d H:i:s');
					foreach(App::make(EstateClassSearchRepositoryInterface::class)->getSettingAll($hp->id, $cmsSetting->id) as $ecsRow) {
						$ecsRow->update_date = $updateDate;
						$ecsRow->save();
					}
				}

				// 現データ（代行で作成）削除
				$currentHp->deleteAll(true);
				
				DB::commit();

				CmsOperation::getInstance()->creatorLog(LogEditType::CREATOR_ROLLBACK);
			} catch (Exception $e) {
				DB::rollback();
				throw $e;
			}
			
		}
		
		$data['redirectTo'] = '/';
		$data['cms_plan'] = $cms_plan;

		return $this->success($data);
	}

	public function publish(){

		return redirect('publish/simple');
	}
}
