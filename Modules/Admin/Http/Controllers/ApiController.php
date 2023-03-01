<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Library\Custom\Form\Element;
use App\Traits\JsonResponse;
use App\Rules\Regex;
use Library\Custom\Kaiin\Kaiin\KaiinParams;
use Library\Custom\Kaiin\Kaiin\Kaiin;
use Library\Custom\Kaiin\Tanto\TantoParams;
use Library\Custom\Kaiin\Tanto\GetTanto;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use Storage;

class ApiController extends Controller
{
	use JsonResponse;

	protected $companyRepository;
	protected $associatedCompanyRepository;
	protected $estateAssociatedCompanyRepository;

	public function init($request, $next)
	{
		$this->companyRepository = App::make(CompanyRepositoryInterface::class);
		$this->associatedCompanyRepository = App::make(AssociatedCompanyRepositoryInterface::class);
		$this->estateAssociatedCompanyRepository = App::make(EstateAssociatedCompanyRepositoryInterface::class);
		$response = $next($request);
		$accept = $request->server('HTTP_ACCEPT');
		if ($accept && strpos($accept, 'application/json') === false) {
			$response->header('json', 'Content-Type', 'text/html');
		}
		return $response;
	}

	/**
	 * デモ用ドメイン（サイト）作成する
	 */
	public function makeDemoAddUser(Request $request)
	{
		$config	= getConfigs('sales_demo');
		$apiKey		= $config->api->key;
		$domain		= $request->domain;
		$user		= $request->user;
		$pass		= $request->pass;
		$uri		= "http://api.apache.{$domain}/addUser.php?user={$user}&pass={$pass}&key={$apiKey}";
		$apiResult	= json_decode(file_get_contents($uri));
		return $this->success(['result' => $apiResult]);
	}

	/**
	 * 会員Noからグループへ設定できるかのチェックと情報を渡す（登録時用）
	 */
	public function getCompanyForMembernoCheck(Request $request)
	{
		$data['error'] = "";

		if (!$request->has("member_no") || $request->member_no == "") {
			$data['error'] = "会員Noを入力してください。 ";
			return $this->success($data);
		}

		if (!$request->has("company_id") || $request->company_id == "" || is_numeric($request->company_id == "")) {
			throw new \Exception("Error : No company_id.");
			exit;
		}
		//契約店の存在チェック
		$where = array(array("member_no", $request->member_no));
		$company = $this->companyRepository->fetchRow($where);
		if ($company == null) {
			$data['error'] = "契約者情報はありません。会員Noをお確かめください。 ";
			return $this->success($data);
		}

		//自分を登録しようとしたとき
		if ($company->id == $request->company_id) {
			$data['error'] = "自社を設定することは出来ません。 ";
			return $this->success($data);
		}

		//既に利用停止している場合はエラーにする（本契約で、利用停止日は過去）
		if ($company->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') && ($company->end_date != "" && $company->end_date <= date("Y-m-d H:i:s"))) {
			$data['error'] = "既に利用停止になってます。 ";
			return $this->success($data);
		}

		//既に登録しているかのチェック
		$asObj = $this->associatedCompanyRepository;
		$row = $asObj->getDataForCompanyIdSsubsidiaryId($request->company_id, $company->id);
		if ($row != null) {
			$data['error'] = "既にグループへ設定済みです。 ";
			return $this->success($data);
		}

		$datas = array();
		$datas["id"]            = $company->id;
		$datas["contract_type"] = $company->contract_type;
		$datas["member_no"]     = $company->member_no;
		$datas["company_name"]  = $company->company_name;
		$datas["member_name"]   = $company->member_name;
		$datas["location"]      = $company->location;
		$data['company'] = $datas;
		return $this->success($data);
	}

	/**
	 * 会員Noからグループへ設定できているかをチェックし情報を渡す（削除用）
	 */
	public function getCompanyForMemberno(Request $request)
	{
		$data['error'] = "";

		if (!$request->has("member_no") || $request->member_no == "") {
			$data['error'] = "会員Noを入力してください。 ";
			return $this->success($data);
		}

		if (!$request->has("company_id") || $request->company_id == "" || is_numeric($request->company_id == "")) {
			throw new \Exception("Error : No company_id.");
			exit;
		}

		//契約店の存在チェック
		$companyObj = $this->companyRepository;
		$where = array(array("member_no", $request->member_no));
		$company = $companyObj->fetchRow($where);
		if ($company == null) {
			$data['error'] = "契約者情報はありません。会員Noをお確かめください。 ";
			return $this->success($data);
		}

		//既に登録しているかのチェック
		$asObj = $this->associatedCompanyRepository;
		$row = $asObj->getDataForCompanyIdSsubsidiaryId($request->company_id, $company->id);
		if ($row == null) {
			$data['error'] = "グループへ設定がされていません。";
			return $this->success($data);
		}

		$datas = array();
		$datas["associated_company_id"] = $row->id;   //associated_companyテーブルのID
		$datas["subsidiary_company_id"] = $company->id;  //子会社のcompanyID
		$datas["member_no"]             = $company->member_no;
		$datas["company_name"]          = $company->company_name;
		$datas["member_name"]           = $company->member_name;
		$datas["location"]              = $company->location;
		$data['company'] = $datas;
		return $this->success($data);
	}


	/**
	 * 会員Noから物件グループへ設定できるかのチェックと情報を渡す（登録時用）
	 */
	public function getEstateGroupSubCompaniesByMemberNoForAdd(Request $request)
	{
		$data['error'] = "";
		if (!$request->has("member_no") || $request->member_no == "") {
			$data['error'] = "会員Noを入力してください。";
			return $this->success($data);
		}

		if (!$request->has("company_id") || $request->company_id == "" || is_numeric($request->company_id == "")) {
			throw new \Exception("Error : No company_id.");
			exit;
		}

		// 親会社の会社情報を取得する
		$mainCompanyId = $request->company_id;
		$companyObj = $this->companyRepository;
		$mainCompany = $companyObj->getDataForId($mainCompanyId);
		if ($mainCompany == null) {
			$data['error'] = "契約者情報はありません。会員Noをお確かめください。";
			return $this->success($data);
		}
		// 子会員の会員No
		$subMemberNo = $request->member_no;
		// 子会員に自分を設定するのはNG
		if ($subMemberNo == $mainCompany->member_no) {
			$data['error'] = "自会員を物件グループに登録することはできません。";
			return $this->success($data);
		}

		//既に登録しているかのチェック
		$estateAssosiateObj = $this->estateAssociatedCompanyRepository;
		$subRow = $estateAssosiateObj->getDataByCompanyIdSubsidiaryId($mainCompany->id, $subMemberNo);
		if ($subRow != null || !empty($subRow)) {
			$data['error'] = "すでに物件グループに登録されいています。";
			return $this->success($data);
		}
		// 会員APIから会員情報を取得する
		// 会員APIに接続して会員情報を取得
		$apiParam = new KaiinParams();
		$apiParam->setKaiinNo($subMemberNo);
		$apiObj = new Kaiin();
		$subMemberData = $apiObj->get($apiParam, '会員基本取得');
		if (is_null($subMemberData) || empty($subMemberData)) {
			$data['error'] = "会員情報はありません。会員Noをお確かめください。";
			return $this->success($data);
		}
		$subMemberData = (object)$subMemberData;

		// インターネットコードがない会員はグループ追加できない
		if (!property_exists($subMemberData, 'kaiinLinkNo') || empty($subMemberData->kaiinLinkNo)) {
			$data['error'] = "インターネットコードの設定が必須です。";
			return $this->success($data);
		}

		// 子会員がアドバンス登録されているか確認する
		$subCompanyRow = $companyObj->getDataForMemberNo($subMemberNo);
		$subCompanyName = "(アドバンスには登録されていない会員です)";
		if (!is_null($subCompanyRow) && count($subCompanyRow) > 0) {
			$subCompanyName = $subCompanyRow[0]->company_name;
		}

		// 子会社情報を出力する
		$datas = array();
		$datas["member_no"]     = $subMemberData->kaiinNo;
		$datas["link_no"]       = property_exists($subMemberData, 'kaiinLinkNo') ? $subMemberData->kaiinLinkNo : "";
		$datas["company_name"]  = $subCompanyName;
		$datas["member_name"]   = $subMemberData->seikiShogo['shogoName'];
		$datas["location"]      = $this->getLocation($subMemberData);
		$data['company'] = $datas;
		return $this->success($data);
	}

	/**
	 * 物件グループ情報を取得する（参照・削除用）
	 */
	public function getEstateGroupSubCompaniesByMemberNo(Request $request)
	{

		$data['error'] = "";

		if (!$request->has("member_no") || $request->member_no == "") {
			$data['error'] = "会員Noの入力がありません。";
			return $this->success($data);
		}

		if (!$request->has("company_id") || $request->company_id == "" || is_numeric($request->company_id == "")) {
			throw new \Exception("Error : No company_id.");
			exit;
		}

		// 親会社の会社情報を取得する
		$mainCompanyId = $request->company_id;
		$companyObj = $this->companyRepository;
		$mainCompany = $companyObj->getDataForId($mainCompanyId);
		if ($mainCompany == null) {
			$data['error'] = "契約者情報はありません。会員Noをお確かめください。";
			return $this->success($data);

		}

		// 子会員の会員No
		$subMemberNo = $request->member_no;

		//登録情報を取得する
		$estateAssosiateObj = $this->estateAssociatedCompanyRepository;

		$assosiateRow = $estateAssosiateObj->getDataByCompanyIdSubsidiaryId($mainCompany->id, $subMemberNo);
		if (empty($assosiateRow)) {
			$data['error'] = "物件グループへの登録情報がありません。";
			return $this->success($data);
		}


		// 会員APIに接続して会員情報を取得
		$apiParam = new KaiinParams();
		$apiParam->setKaiinNo($subMemberNo);
		$apiObj = new Kaiin();
		$subMemberData = $apiObj->get($apiParam, '会員基本取得');
		if (is_null($subMemberData)) {
			$data['error'] = "会員情報はありません。会員Noが変更されている可能生があります。";
			return $this->success($data);
		}
		$subMemberData = (object)$subMemberData;


		// 子会員がアドバンス登録されているか確認する
		$subCompanyRow = $companyObj->getDataForMemberNo($subMemberNo);
		$subCompanyName = "(アドバンスには登録されていない会員です)";
		if (!is_null($subCompanyRow) && count($subCompanyRow) > 0) {
			$subCompanyName = $subCompanyRow[0]->company_name;
		}

		// 子会社情報を出力する
		$datas = array();
		$datas["associate_id"]  = $assosiateRow->id;
		$datas["member_no"]     = $subMemberData->kaiinNo;
		$datas["link_no"]       = property_exists($subMemberData, 'kaiinLinkNo') ? $subMemberData->kaiinLinkNo : "";
		$datas["company_name"]  = $subCompanyName;
		$datas["member_name"]   = $subMemberData->seikiShogo['shogoName'];
		$datas["location"]      = $this->getLocation($subMemberData);
		$data['company'] = $datas;
		return $this->success($data);
	}


	/**
	 * ファイル仮保存用API
	 */
	public function setFileUpload(Request $request)
	{
		$validator = \Validator::make($request->all(), [
			'file' => [
				'required',
				'max:3072',
				'file_extension:jpg,png,gif,pdf,xlsx,xsl,ppt,pptx,docx,doc',
                'mimes:jpg,png,gif,pdf,xlsx,xsl,ppt,pptx,docx,doc',
			]
		]);

		if (count($validator->errors()->getMessages()) > 0) {
			return response()->json([
				'success' => false,
				'errors' => array("ファイルを確かめてください。")
			]);
		}

		//拡張子
		$parts = explode('.', $_FILES['file']['name']);
		$ext = array_pop($parts);
		if ($ext == "") {
			throw new \Exception("No Extension Error");
			exit;
		}

		//ファイル情報を集約
		$file_data = array();
		$file_data['tmpfile']       = $_FILES["file"]['tmp_name'];
		$file_data['file_name']     = $_FILES["file"]['name'];
		//ファイル名生成
		$profile = getInstanceUser('cms')->getProfile();
		$file_data['new_file_name'] = $profile->id . "_" . date("YmdHis") . "_" . mt_rand() . "." . $ext;

		//curlに送信する情報の設定
		$data = array(
			'type' => 'tmp',
			'file' => new \CURLFile($file_data['tmpfile'], $_FILES['file']['type'], $file_data['new_file_name'])
		);

		$data_file['file'] = array(
			"file_name"      => $file_data['new_file_name'],
			"moto_file_name" => $file_data['file_name']
		);
		return $this->updateFileToS3($data, $file_data);
	}

	/**
	 * ファイル仮保存用API
	 */
	public function setP12FileUpload(Request $request)
	{

		//パラメータ取得
		$params = $request->all();
		//オブジェクト取得
		$companyObj = App::make(CompanyRepositoryInterface::class);

		if (!isset($params['company_id']) || $params['company_id'] == "" || !is_numeric($params['company_id'])) {
			throw new \Exception("No ComapnyID Error");
			exit;
		} else {
			$row = $companyObj->find($params['company_id']);
			if ($row == null) {
				throw new \Exception("No Company Data. ");
				exit;
			}
		}

		//拡張子
		$parts = explode('.', $_FILES['google']['name']["google_p12"]);
		$ext = array_pop($parts);
		if ($ext == "") {
			throw new \Exception("No Extension Error");
			exit;
		} else if ($ext != "p12") {
			throw new \Exception("No Extension P12 File Error");
			exit;
		}

		//ファイル情報を集約
		$file_data = array();
		$file_data['tmpfile']       = $_FILES["google"]['tmp_name']["google_p12"];
		$file_data['file_name']     = $_FILES["google"]['name']["google_p12"];
		$file_data['new_file_name'] = "google_p12key_" . $params['company_id'] . "." . $ext;

		//curlに送信する情報の設定
		$data = array(
			'type' => 'google',
			'company_id' => $params['company_id'],
			'file' => new \CURLFile($file_data['tmpfile'], null, $file_data['new_file_name'])
		);
		return $this->updateFileToS3($data, $file_data);
	}

	/**
	 * ファイルサーバーへデータ送信用
	 *
	 * @param String $url
	 * @param Array  $data
	 *
	 */
	private function updateFileForCurl($url, $data, $file_data)
	{
		//接続開始
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res   = curl_exec($ch);
		$error = curl_errno($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		//チェック
		if ($error !== 0) {
			throw new \Exception("CURL Error");
			exit;
		} else if ($info['http_code'] !== 200) {
			throw new \Exception("CURL Error HTTP-CODE : " . $info['http_code']);
			exit;
		} else {
			$results = json_decode($res);

			if ($results == null) {
				throw new \Exception("Upload File Error : " . $res);
				exit;
			} else if ($results->success == true) {
				$data['file'] = array(
					"file_name"      => $file_data['new_file_name'],
					"moto_file_name" => $file_data['file_name']
				);
				return $this->success($data);
			} else {
				throw new \Exception("Upload File Error : " . $results->error);
				exit;
			}
		}
	}

	public function updateFileToS3($data, $file_data) {
		$path = 'file_path/admin/';
		switch($data['type']) {

			case "google" :
		
				if(!isset($data['company_id']) || $data['company_id'] == "" || !is_numeric($data['company_id'])) {
					return $this->success([
						'error' => "No CompanyId Error"
					]);
				}
				$path .= $data['company_id'];
				
				break;
				
		
			case "tmp" :
				$path .= $data['type'];
				break;
		}
		if (!Storage::disk('s3')->put($path.'/'.$file_data['new_file_name'], file_get_contents($file_data['tmpfile']))) {
			throw new \Exception("Upload File Error : " . $res);
			exit;
		}
		return $this->success([
			'file' => array(
				"file_name"      => $file_data['new_file_name'],
				"moto_file_name" => $file_data['file_name']
			)
		]);
	}


	/**
	 * 担当者情報を基幹システムの会員APIから取得し返却する
	 */
	public function getAtStaffForCd(Request $request)
	{
		
		//$this->data->error = "";
		$element = new Element\Text("cd");
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$element->setValue($request->cd);
		if (!$element->isValid(false) || trim($request->cd) === '') {
			return $this->success([]);
		}
		//会員APIに接続して担当者情報を取得
		$tantoApiParam = new TantoParams();
		$tantoApiParam->setTantoCd($request->cd);
		$tantoapiObj = new GetTanto();
		$tantouInfo = $tantoapiObj->get($tantoApiParam, '担当者取得');
		if (is_null($tantouInfo) || empty($tantouInfo)) {
			return $this->success([]);
		}
		$tantouInfo = (object)$tantouInfo;

		$data = array();
		$data['tantoName']      = $tantouInfo->tantoName;
		$data['shozokuName'] = $tantouInfo->tantoShozoku['mShozokuEigyoshoName'];

		return $this->success($data);
	}

	/**
	 * 会員を基幹システムの会員APIから取得し返却する
	 */
	public function getAtMemberForNo(Request $request)
	{

		$element = new Element\Text("no");
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$element->setValue($request->no);
		if (!$element->isValid(false)) {
			return $this->success([]);
		}

		// 会員APIに接続して会員情報を取得
		$apiParam = new KaiinParams();
		$apiParam->setKaiinNo($request->no);
		$apiObj = new Kaiin();
		$kaiinData = $apiObj->get($apiParam, '会員基本取得');
		if (is_null($kaiinData) || empty($kaiinData)) {
			return $this->success([]);
		}

		$kaiinData = (object)$kaiinData;
		$data = array();

		$data['seikiShogoName'] = $kaiinData->seikiShogo['shogoName'];
		$data['kaiinLinkNo']    = property_exists($kaiinData, 'kaiinLinkNo') ? $kaiinData->kaiinLinkNo : "";
		$data['location']       = $this->getLocation($kaiinData);

		return $this->success($data);
	}

	// 会員APIのレスポンスから住所情報を作成する
	private function getLocation($kaiinData)
	{

		$location = property_exists($kaiinData, 'todofukenName') ? $kaiinData->todofukenName : "";
		$location .= property_exists($kaiinData, 'cityName')     ? $kaiinData->cityName : "";
		$location .= property_exists($kaiinData, 'townName')     ? $kaiinData->townName : "";
		$location .= property_exists($kaiinData, 'banchi')       ? $kaiinData->banchi : "";
		$location .= property_exists($kaiinData, 'buildingName') ? $kaiinData->buildingName : "";

		return $location;
	}
}
