<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\Admin\Http\Form\InformationSearch;
use Modules\Admin\Http\Form\InformationRegist;
use Modules\Admin\Http\Form\InformationRegistDetail;
use Modules\Admin\Http\Form\InformationRegistFile;
use Modules\Admin\Http\Form\InformationRegistUrl;
use App\Repositories\Information\InformationRepositoryInterface;
use App\Repositories\InformationFiles\InformationFilesRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Library\Custom\Model\Lists\InformationDisplayPageCode;
use Library\Custom\Model\Lists\InformationDisplayTypeCode;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;

class InformationController extends Controller
{
	protected $_controller = 'information';
	protected $informationRepository;
	protected $informationFilesRepository;

	public function init($request, $next)
	{
		$this->informationRepository = App::make(InformationRepositoryInterface::class);
		$this->informationFilesRepository = App::make(InformationFilesRepositoryInterface::class);

		return $next($request);
	}

	public function index(Request $request)
	{
		// topicPath
		$this->view->topicPath('お知らせ管理');

		$search_form = new InformationSearch();
		$this->view->search_form = $search_form;

		$list = new InformationDisplayPageCode();
		$this->view->display_page_codes = $list->getAll();
		$list = new InformationDisplayTypeCode();
		$this->view->display_type_codes = $list->getAll();

		//パラメータ取得
		$params = $request->all();
		$search_form->setData($params);

		$rows = $this->informationRepository->searchData($request, $search_form, $params);

		$this->view->information = $rows;

		$search_arr = array();
		foreach ($search_form->getElements() as $key => $val) {
			$search_arr[$key] = ($val->getValue() == null) ? "" :  $val->getValue();
		}

		$this->view->search_param = $search_arr;
		return view('admin::information.index');
	}

	/**
	 * 登録
	 */
	public function edit(Request $request)
	{
		$this->view->topicPath('お知らせ管理', "index", $this->_controller);
		$this->view->topicPath("お知らせ作成・変更");

		//フォーム設定
		$this->view->form = $form = new Form();
		$form->addSubForm(new InformationRegist(), 'basic');
		$form->addSubForm(new InformationRegistUrl(), 'designation');
		$form->addSubForm(new InformationRegistDetail(), 'detail');
		$form->addSubForm(new InformationRegistFile(), 'one_file');

		//パラメータ取得
		$params = $request->all();
		$infoObj = $this->informationRepository;
		$infoFileObj = $this->informationFilesRepository;

		//登録ボタン押下時
		if ($request->has("submit") && $request->submit != "") {
			$form->setData($params);
			//必須切り替え
			if (isset($params['basic']['display_type_code'])) {
				switch ($params['basic']['display_type_code']) {
					case "1":
						$form->getSubForm('detail')->getElement('contents')->setRequired(false);
						$form->getSubForm('one_file')->getElement('name')->setRequired(false);
						break;
					case "2":
						$form->getSubForm('designation')->getElement('url')->setRequired(false);
						$form->getSubForm('designation')->setEmptyForm();
						$form->getSubForm('one_file')->getElement('name')->setRequired(false);

						break;
					case "3":
						$form->getSubForm('designation')->getElement('url')->setRequired(false);
						$form->getSubForm('designation')->setEmptyForm();
						$form->getSubForm('detail')->getElement('contents')->setRequired(false);
						break;
				}
			}
			//バリデーション
			if ($form->isValid($params)) {
				$error_flg = false;
				//個別チェック
				switch ($params['basic']['display_type_code']) {
						//詳細
					case "2":

						//ファイル名
						$element = new Element\Text('name');
						$element->addValidator(new StringLength(array('max' => 100)));

						$errors = array();

						foreach ($params['name'] as $key => $val) {

							if ($params['file_id'][$key] == '') {

								if ($val != "" && $params['tmp_file'][$key] == "") {
									$errors['file'][$key] = "ファイルを選択してください。";
									$error_flg = true;
								} else if ($val == "" && $params['tmp_file'][$key] != "") {
									$errors['file'][$key] = "ファイル名を設定してください。";
									$error_flg = true;
								}
							}
							$element->setValue($val);
							//桁数チェック
							if (!$element->isValid()) {
								$errors['name'][$key] = reset($element->getMessages());
								$error_flg = true;
							}
						}
						$this->view->error_details = $errors;

						break;

						//ファイル
					case "3":
						if ($params['one_file']['file_id'] == "" && $params['one_file']['tmp_file'] == "") {
							$form->getSubForm('one_file')->getElement('up_file_name')->setMessages(array("ファイルは必須です。"));
							$error_flg = true;
						}
						break;
				}

				if (!$error_flg) {
					//見た目戻す
					$request->input("back", "");
					$request->input("submit", "");
					$this->view->params = $params;
					return view('admin::information.conf');
				}
			}
			$form->getSubForm('detail')->getElement('contents')->setRequired(true);
			$form->getSubForm('designation')->getElement('url')->setRequired(true);
			$form->getSubForm('one_file')->getElement('name')->setRequired(true);
			//戻るボタン押下時
		} else if ($request->has("back") && $request->back != "") {
			unset($params['back']);
			$form->setData($params);

			//初期データ取得時
		} else if ($request->has("id") && $request->id != "") {

			$row = $infoObj->getDataForId($request->id);
			if ($row == null) {
				throw new Exception("No Information Data. ");
				exit;
			}

			//見た目の整頓
			$row->start_date = substr($row->start_date, 0, 10);
			if ($row->end_date == "0000-00-00 00:00:00") $row->end_date = "";
			$row->end_date = substr($row->end_date, 0, 10);
			$form->setData($row->toArray());

			//ファイルが存在する場合も考慮
			//詳細
			if ($row->display_type_code == config('constants.information_display_type_code.DETAIL_PAGE')) {
				$rows = $infoFileObj->getDataForInformationId($request->id);
				$fdata =  array();
				foreach ($rows as $key => $val) {
					$fdata["file_id"][$key] = $val->id;
					$fdata["name"][$key]    = $val->name;
					$fdata["up_file_name"][$key] = $val->name . "." . $val->extension;
				}
				$params = array_merge($params, $fdata);

				//ファイル
			} else if ($row->display_type_code == config('constants.information_display_type_code.FILE_LINK')) {
				$rows = $infoFileObj->getDataForInformationId($request->id);
				if (isset($rows[0])) {
					$datas = $rows[0]->toArray();
					$fdata = array();
					$fdata["file_id"] = $datas["id"];
					$fdata["name"]    = $datas["name"];
					$fdata["up_file_name"] = $datas["name"] . "." . $datas["extension"];
					$form->setData($fdata);
				}
			}
		} else {
			$form->getSubForm('basic')->getElement('display_type_code')->setValue(1);
		}

		$this->view->params = $params;
		return view('admin::information.edit');
	}

	/**
	 * 確認
	 */
	public function conf(Request $request)
	{
		$this->view->topicPath('お知らせ管理', "index", $this->_controller);
		$this->view->topicPath("お知らせ作成・変更");

		$infoObj = $this->informationRepository;
		$infoFileObj = $this->informationFilesRepository;

		//パラメータ取得
		$params = $request->all();

		//フォーム設定
		$this->view->form = $form = new Form();
		$form->addSubForm(new InformationRegist(), 'basic');
		$form->addSubForm(new InformationRegistUrl(), 'designation');
		$form->addSubForm(new InformationRegistDetail(), 'detail');
		$form->addSubForm(new InformationRegistFile(), 'one_file');
		$form->setData($params);

		//元に戻るボタン押下時
		if ($request->has("back") && $request->back != "") {
			$form->setData($params);
			$this->view->params = $params;
			return view('admin::information.edit');

			//登録ボタン押下時
		} else if ($request->has("submit") && $request->submit != "") {

			DB::beginTransaction();
			//新規
			if (!isset($params["basic"]["id"]) || $params["basic"]["id"] == "") {
				//アカウント登録
				$row = $infoObj->create();
				//更新
			} else {

				//アカウント更新
				$row = $infoObj->getDataForId($params["basic"]["id"]);
				if ($row == null) {
					throw new Exception("No Information Data.");
					return;
				}
			}

			unset($row->delete_flg);
			unset($row->create_id);
			unset($row->create_date);
			unset($row->update_date);
			//設定
			$row->title             = $params['basic']["title"];
			$row->start_date        = ($params['basic']["start_date"] != "") ? $params['basic']["start_date"] : null;
			$row->end_date          = ($params['basic']["end_date"] != "") ? $params['basic']["end_date"] : null;
			$row->display_page_code = $params['basic']["display_page_code"];
			$row->important_flg     = $params['basic']["important_flg"][0];
			// $row->new_flg           = $params['basic']["new_flg"][0]; //Zend comments in the form
			$row->display_type_code = $params['basic']["display_type_code"];

			if (!isset($params['designation']["url"])) $params['designation']["url"] = "";
			$row->url               = $params['designation']["url"];

			if (!isset($params['detail']["contents"])) $params['detail']["contents"] = "";
			$row->contents          = $params['detail']["contents"];

			$row->save();

			$id = $row->id;

			if (isset($params["id"]) && $params["id"] > 0) $id = $params["id"];

			//URLとかを取得
			$conf = getConfigs('admin.FileUploadServer');
			//ファイル系の登録
			switch ($params['basic']['display_type_code']) {
				case config('constants.information_display_type_code.URL'):

					//URLの設定のみなので、ファイルを削除する
					$data = array();
					$data['delete_flg'] = 1;
					$where = array(array("information_id" , $id));
					$infoFileObj->delete($where);
					break;

				case config('constants.information_display_type_code.DETAIL_PAGE'):

					//ファイルIDをチェックしてなければ、一旦全部消す

					$no_flie = true;
					if (isset($params['file_id'])) {
						foreach ($params['file_id'] as  $val) {
							if (isset($val) && $val != "" && $val > 0) $no_flie = false;
						}
					}
					if ($no_flie == true) {
						$where = array(array("information_id" , $id));
						$infoFileObj->delete($where);
					}

					//登録する
					if (isset($params['name'])) {
						foreach ($params['name'] as $key => $val) {
							$item = array();
							$item['information_id'] = $id;
							$item['name']           = $val;
	
							//画像設定
							if ($val != "" &&  $params["tmp_file"][$key] != "") {
								//拡張子
								$parts = explode('.', $params["tmp_file"][$key]);
								$ext = array_pop($parts);
								if ($ext == "") {
									DB::rollback();
									throw new Exception("No Extension Error");
									exit;
								}
								$item['extension'] = $ext;
								$data = @file_get_contents($conf->upload->admin_url . "tmp/" . $params["tmp_file"][$key]);
								if ($data === false) {
									DB::rollback();
									throw new Exception("No File Error");
									exit;
								}
	
	
								$item['contents'] = $data;
	
								//ファイル名がない場合はファイル達も消す
							} else if ($val == "") {
								$item['contents']  = null;
								$item['extension'] = null;
							}
	
							if (isset($params['file_id'][$key]) && $params['file_id'][$key] != "" && $params['file_id'][$key] > 0) {
								$infoFileObj->update($params['file_id'][$key], $item);
							} else {
								$infoFileObj->create($item);
							}
						}
					}

					break;

				case config('constants.information_display_type_code.FILE_LINK'):

					$item = array();
					$item['information_id'] = $id;
					$item['name']           = $params['one_file']["name"];

					//画像設定
					if ($params['one_file']["tmp_file"] != "") {
						//拡張子
						$parts = explode('.', $params['one_file']["tmp_file"]);
						$ext = array_pop($parts);
						$item['extension'] = $ext;
						$data = @file_get_contents($conf->upload->admin_url . "tmp/" . $params['one_file']["tmp_file"]);
						if ($data === false) {
							DB::rollback();
							throw new Exception("No File Error");
							exit;
						}
						$item['contents'] = $data;
					}

					if (isset($params['one_file']['file_id']) && $params['one_file']['file_id'] != "" && $params['one_file']['file_id'] > 0) {
						$infoFileObj->update($params['one_file']['file_id'], $item);
					} else {

						//IDが無ければファイルを削除する
						$where = array(["information_id", $id]);
						$infoFileObj->delete($where);

						//入れる
						$infoFileObj->create($item);
					}
					break;
			}
			DB::commit();

			return redirect('/admin/information/comp');
		}

		$this->view->params = $params;
		return view('admin::information.conf');
	}

	/**
	 * 完了
	 */
	public function comp(Request $request)
	{
		$this->view->topicPath('お知らせ管理', "index", $this->_controller);
		$this->view->topicPath("お知らせ作成・変更");

		//パラメータ取得
		$params = $request->all();
		$this->view->params = $params;
		return view('admin::information.comp');
	}
}
