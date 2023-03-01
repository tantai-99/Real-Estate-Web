<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepositoryInterface;
use Library\Custom\Kaiin\Kameiten\KameitenParams;
use Library\Custom\Kaiin\Kameiten\GetKameiten;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Controller\Action\InitializedCompany;

class SecondEstateExclusionController extends InitializedCompany{
	protected $_controller = 'second-estate-exclusion';
	/**
	 * 一覧表示
	 */
	public function init($request, $next)
	{
		return parent::init($request, $next);
	}

	public function index(Request $request) {
		$user = getUser();
		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}
		$this->view->topicPath('2次広告自動公開設定', 'index', 'second-estate-search-setting');
		$this->view->topicPath('物件取込み除外会社設定');

		$this->view->params = $request->all();
		$company_id = $user->getProfile()->id;
		$hp_id = $user->getCurrentHp()->id;

		// 検索条件
		$seae = App::make(SecondEstateExclusionRepositoryInterface::class);
		// $select->from(array("seae" => "second_estate_exclusion"), array(new Zend_Db_Expr("SQL_CALC_FOUND_ROWS seae.*")));
		$where = [["company_id", $company_id],["hp_id", $hp_id]];
		$rows = $seae->fetchAll($where, ['name_kana']);

		$this->view->rows = $rows;

		return view('second-estate-exclusion.index');
	}
	
	/**
	 * 検索
	 */
	public function search(Request $request) {
        // 加盟店の二次広告設定を取得
		$user = getUser();
		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}

		//パンクズ系
		$this->view->topicPath('2次広告自動公開設定', 'index', "second-estate-search-setting");
		$this->view->topicPath('物件取込み除外会社設定', 'index', $this->_controller);
		$this->view->topicPath('物件取込み除外会社設定（除外選択）');

		//セッションの設定
		$session_exclusion = $request->session()->get('cms-second-estate-exclusion');
		//ページング初期化
		if(!$request->cnt || $request->cnt == "" || !is_numeric($request->cnt)) {
			$request['cnt'] = 50 ; 
		}
		if(!$request->page || $request->page == "" || !is_numeric($request->page)) {
			$request['page'] = 1 ;
			$session_exclusion['data'] = array();
		 }
		$this->view->params = $request->all();
		//検索開始
		$rows = array();
		$search_arr = array();
		if(($request->has('search') || $request->search != "")) {
			//すでに設定されている会員を取得
			$company_id = $user->getProfile()->id;
			$hp_id = $user->getCurrentHp()->id;
			$seae = App::make(SecondEstateExclusionRepositoryInterface::class);
			$where = [["company_id", $company_id], ["hp_id", $hp_id]] ;
			//var_dump($select->__toString());
			$exclusion = $seae->fetchAll($where);
			$asd = array();
			foreach ($exclusion as $key => $value) {
				$asd[$value['member_no']] = $value;
			}
			$this->view->exclusion = $asd;
			$search_arr["search"] = "search";
			$apiParam = new KameitenParams();
			$apiParam->setPage($request->page);
			$apiParam->setperpage($request->cnt);
			$apiObj = new GetKameiten();
			//商号検索
			$request['BukkenShogo'] = trim($request->BukkenShogo);
			$request['DaihyoTel'] = trim($request->DaihyoTel);
			try {
				if(($request->has("submit_name") && $request->submit_name != "") &&
					($request->has("BukkenShogo") && $request->BukkenShogo != "") )  {
					$this->view->params['DaihyoTel'] = "";
                    $search_arr["submit_name"] = "submit_name";
					$apiParam->setBukkenShogo($request->BukkenShogo);	
					$rows = $apiObj->get($apiParam, '２次広告除外設定用会員取得');
				//電話検索
				}else if(($request->has("submit_phone") && $request->submit_phone) &&
						($request->has("DaihyoTel") && $request->DaihyoTel != "") ) {
					$this->view->params['BukkenShogo'] = "";
                    $search_arr["submit_phone"] = "submit_name";
					$apiParam->setDaihyoTel($request->DaihyoTel);
					$rows = $apiObj->get($apiParam, '２次広告除外設定用会員取得');
				}
			} catch (Exception $e) {
				$rows = array();
				$this->logger = Registry::get('logger');
				$this->logger->error(print_r($e->getMessage(), true));
			}
			if(!isset($rows) || count($rows) == 0) $rows = array();
			foreach ($rows as $key => $val) {
				$data = array();
				$data['kaiinNo']      = $val['kaiinNo'];
				$data['bukkenShogo']  = $val['bukkenShogo'];
				$data['bukkenShogoKana']  = $val['bukkenShogoKana'];
				$address = "";
				$address .= (isset($val['todofukenName']) && $val['todofukenName'] != "") ? $val['todofukenName'] : "";
				$address .= (isset($val['cityName']) && $val['cityName'] != "") ? $val['cityName'] : "";;
				$address .= (isset($val['townName']) && $val['townName'] != "") ? $val['townName'] : "";;
				$address .= (isset($val['banchi']) && $val['banchi'] != "") ? $val['banchi'] : "";;
				$address .= (isset($val['buildingName']) && $val['buildingName'] != "") ? $val['buildingName'] : "";;
				$data['address']      = $address;
				$data['railLineName'] = (isset($val['railLineName'])) ? $val['railLineName'] : "";
				$data['stationName']  = (isset($val['stationName'])) ? $val['stationName'] : "";
				$data['daihyoTel']    = (isset($val['daihyoTel'])) ? $val['daihyoTel'] : "";
				//Sessionに入れる
				if(!isset($session_exclusion->data[$val['kaiinNo']])){

					$session_exclusion['data'][$val['kaiinNo']] = $data;
				}

			}
			// Paginatorのセットアップ
			$this->view->total_count = 0;
			if($rows) {
				$this->view->total_count = $apiObj->getPagination()["totalCnt"];
				$now_count_first = ( $request->page - 1 ) * $request->cnt + 1	;			
				$now_count_last = $request->page * $request->cnt;
				if($now_count_last > $this->view->total_count) {
					$now_count_last = $this->view->total_count;
				}
				$this->view->now_count_first = $now_count_first;
				$this->view->now_count_last = $now_count_last;
				$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $this->view->total_count,10, $request->page);
				$rows = $paginator->items();
            	$paginator->setPath('/second-estate-exclusion/search');
				$this->view->paginator = $paginator;
			}
		}
		//検索内容
		$this->view->search_param = $search_arr;
		$this->view->rows = $rows;
		$request->session()->put('cms-second-estate-exclusion', $session_exclusion);
		return view('second-estate-exclusion.search');
	}

	/**
	 * 確認画面
	 */
	public function detail(Request $request)
    {	
		$user = getUser();
		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}

		$this->view->params = $request->all();

		//パンクズ系
		$this->view->topicPath('2次広告自動公開設定', 'index', "second-estate-search-setting");
		$this->view->topicPath('物件取込み除外会社設定', 'index', $this->_controller);
		$this->view->topicPath('物件取込み除外会社設定（除外選択）');
		// $this->view->messages = $this->_helper->flashMessenger->getMessages();

		if(!$request->has("kaiin_no") || $request->kaiin_no == "" || !is_array($request->kaiin_no)) {
			throw new Exception("No ID. ");
			exit;
		}
        // 全値取得
        $kaiin_no = $request->kaiin_no;	
		$session_exclusion = $request->session()->get('cms-second-estate-exclusion');
		$view_datas = array();
        foreach ($kaiin_no as $key => $value) {
			if(isset($session_exclusion['data'][$value])) {
        		$data = array();
				$data['kaiinNo']      = $session_exclusion['data'][$value]['kaiinNo'];
				$data['bukkenShogo']  = $session_exclusion['data'][$value]['bukkenShogo'];
				$data['address']      = $session_exclusion['data'][$value]['address'];
				$data['daihyoTel']    = $session_exclusion['data'][$value]['daihyoTel'];
				$data['railLineName'] = (isset($session_exclusion['data'][$value]['railLineName'])) ? $session_exclusion['data'][$value]['railLineName'] ." " : "";
				$data['stationName']  = (isset($session_exclusion['data'][$value]['stationName'])) ? $session_exclusion['data'][$value]['stationName'] : "";
				$view_datas[] = $data;
			}
        }
        $this->view->rows = $view_datas;
        return view('second-estate-exclusion.detail');
    }
	/**
	 * 保存する
	 */
	public function regist(Request $request)
    {
		$user = getUser();
		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}

		$this->view->params = $request->all();
		if($request->has('back') && $request->back != "") {
			$request['back'] = "";
			return app(SecondEstateExclusionController::class)->search($request);
			// return redirect()->route('default.secondestateexclusion.search.post');
		}

		if(!$request->has("kaiin_no") || $request->kaiin_no == "" || !is_array($request->kaiin_no)) {
			throw new Exception("No ID. ");
			exit;
		}

        // 全値取得
        $kaiin_no = $request->kaiin_no;
		$session_exclusion = $request->session()->get('cms-second-estate-exclusion');

		$seae = App::make(SecondEstateExclusionRepositoryInterface::class);
		$company_id = $user->getProfile()->id;
		$hp_id = $user->getCurrentHp()->id;

		try {
	        foreach ($kaiin_no as $key => $value) {
				if(isset($session_exclusion['data'][$value])) {
					$row = $seae->getDataForMemberNoCompanyId($session_exclusion['data'][$value]['kaiinNo'], $company_id, $hp_id);
					if(!$row) {
		        		$data = array();
						$data['company_id'] = $company_id;
						$data['hp_id']      = $hp_id;
						$data['name']       = $session_exclusion['data'][$value]['bukkenShogo'];
						$data['name_kana']  = $session_exclusion['data'][$value]['bukkenShogoKana'];
						$data['address']    = $session_exclusion['data'][$value]['address'];
						$nearest_station = "";
						$nearest_station .= (isset($session_exclusion['data'][$value]['railLineName'])) ? $session_exclusion['data'][$value]['railLineName'] ." " : "";
						$nearest_station .= (isset($session_exclusion['data'][$value]['stationName'])) ? $session_exclusion['data'][$value]['stationName'] : "";
						$data['nearest_station'] = $nearest_station;
						$data['tel']        = $session_exclusion['data'][$value]['daihyoTel'];
						$data['member_no']  = $session_exclusion['data'][$value]['kaiinNo'];
					    $seae->create($data);
					}else{
						$row->name       = $session_exclusion['data'][$value]['bukkenShogo'];
						$row->name_kana  = $session_exclusion['data'][$value]['bukkenShogoKana'];
						$row->address    = $session_exclusion['data'][$value]['address'];
						$row->tel        = $session_exclusion['data'][$value]['daihyoTel'];
						$nearest_station = "";
						$nearest_station .= (isset($session_exclusion['data'][$value]['railLineName'])) ? $session_exclusion['data'][$value]['railLineName'] ." " : "";
						$nearest_station .= (isset($session_exclusion['data'][$value]['stationName'])) ? $session_exclusion['data'][$value]['stationName'] : "";
						$row->nearest_station = $nearest_station;
						$row->save();
					}
				}
	        }

	        // 更新
            CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.SECOND_SETTING_EXCLUSION_UPDATE'));
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		return redirect("second-estate-exclusion/search?r=true");
		exit();
	}

	/**
	 * 削除
	 */
	public function delete(Request $request)
    {
		$user = getUser();
		if (!$user->isAvailableSecondEstate()) {
			$this->_forward404();
		}

		if(!$request->has("delete_id") || $request->delete_id == "" || !is_array($request->delete_id)) {
			throw new Exception("No ID. ");
			exit;
		}

		$seae =  App::make(SecondEstateExclusionRepositoryInterface::class);
		$company_id = $user->getProfile()->id;
		$hp_id = $user->getCurrentHp()->id;

		$deletes = $request->delete_id;
		try {

			DB::beginTransaction();

			foreach ($deletes as $key => $value) {
				$row = $seae->getDataForId($value);
				if(!$row) {
					throw new Exception("No Data. ");
					exit;
				}

				$data = array();
			    $data['delete_flg'] = 1;
			    $where = [["id", $value], ["company_id", $company_id], ["hp_id", $hp_id]];
			    $seae->update($where, $data);			}

            CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.SECOND_SETTING_EXCLUSION_DELETE'));

            DB::commit();

		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		return redirect("second-estate-exclusion?r=true");
		exit;
    }
}