<?php
namespace Library\Custom\Controller\Action;
use App\Http\Controllers\Controller;

class InitializedCompany extends Controller {
	
	public function init($request, $next) {
		
		// 診断のみの場合は初期化チェックなし
		if (getUser()->getProfile()->isAnalyze()) {
			return $next($request);
		}
		
		// 初期設定未完了の場合は状況ごとに表示先振り分け
		$hp = getUser()->getCurrentHp();
		if (!$hp || !$hp->isInitialized()) {
				return $this->_thenNotInitialized($request, $next, $hp);
			}
		return parent::init($request, $next);
	}
	
	protected function _thenNotInitialized($request, $next, $hp) {
		$initializeAction = 'index';
		if ($hp) {
			switch ($hp->getInitializeStatus()) {
				case config('constants.hp.INITIAL_SETTING_STATUS_INIT'):
					$initializeAction = 'design';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_DESIGN'):
					$initializeAction = 'top-page';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_TOPPAGE'):
					$initializeAction = 'company-profile';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_COMPANYPROFILE'):
					$initializeAction = 'privacy-policy';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_PRIVACYPOLICY'):
					$initializeAction = 'site-policy';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_SITEPOLICY'):
					$initializeAction = 'contact';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_CONTACT'):
					$initializeAction = 'complete';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE'):
					$initializeAction = 'complete';
					break;
				case config('constants.hp.INITIAL_SETTING_STATUS_NEW'):
				default:
					break;
			}
		}
		return $this->_redirectSimple($initializeAction, 'initialize');
	}
}