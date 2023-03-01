<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Library\Custom\Model\Lists\InformationDisplayPageCode;
use App\Repositories\Information\InformationRepositoryInterface;
use App\Repositories\InformationFiles\InformationFilesRepositoryInterface;

class InformationController extends Controller
{

	public function init($request, $next)
	{
		return $next($request);
	}


	public function index(Request $request)
	{
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
		$rows = $infoObj->pagination();
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
		return view('information.index');
	}

	/**
	 * 詳細
	 */
	public function detail(Request $request)
	{
		if (!$request->has("id") || $request->id == "" || !is_numeric($request->id)) {
			throw new \Exception("No Information ID");
			exit;
		}

		$this->view->topicPath('アットホームからのお知らせ', '');

		//パラメータ取得
		$params = $request->all();

		$infoObj = App::make(InformationRepositoryInterface::class);
		$infoFileObj = App::make(InformationFilesRepositoryInterface::class);

		//お知らせ情報の取得

		$row = $infoObj->getDataForId($request->id);
		if ($row == null) {
			throw new \Exception("No Information Data. ");
			exit;
		}

		$this->view->topicPath($row->title);

		//ファイル情報取得
		//詳細ページ
		if ($row->display_type_code == config('constants.information_display_type_code.DETAIL_PAGE')) {
			$rows = $infoFileObj->getDataForInformationId($request->id);
			$fdata =  array();
			foreach ($rows as $key => $val) {

				$fdata["file_id"][$key] = $val->id;
				$fdata["name"][$key]    = $val->name;
				$fdata["file_name"][$key] = $val->name . "." . $val->extension;
				$fdata["extension_check"][$key] = substr($val->extension, 0, 3);
			}
			$params = $fdata;

			//ファイルリンク
		} else if ($row->display_type_code == config('constants.information_display_type_code.FILE_LINK')) {

			$rows = $infoFileObj->getDataForInformationId($request->id);
			$fdata = $rows[0]->toArray();
			$fdata["file_id"] = $fdata["id"];
			$fdata["file_name"] = $fdata["name"] . "." . $fdata["extension"];
			$fdata["file_contents"] = $fdata["contents"];
			$fdata["extension_check"] = substr($fdata["extension"], 0, 3);
			$params = $fdata;
		}

		$this->view->information = $row;
		$this->view->params = $params;

		return view('information.detail');
	}

	/**
	 * ファイルダウンロード
	 */
	public function download(Request $request)
	{

		$infoFileObj = App::make(InformationFilesRepositoryInterface::class);

		//ファイルリンク
		if ($request->has("id") && $request->id != "") {
			$rows = $infoFileObj->getDataForInformationId($request->id);

			$fdata = $rows[0]->toArray();
			$fdata["file_id"] = $fdata["id"];
			$fdata["file_name"] = $fdata["name"] . "." . $fdata["extension"];
			$fdata["file_contents"] = $fdata["contents"];
			$fdata["extension_check"] = substr($fdata["extension"], 0, 3);
		}

		//詳細
		if ($request->has("file_id") && $request->file_id != "") {
			$rows = $infoFileObj->getDataForId($request->file_id);

			$fdata = array();
			$fdata["file_id"] = $rows->id;
			$fdata["file_name"] = $rows->name . "." . $rows->extension;
			$fdata["contents"] = $rows->contents;
			$fdata["extension_check"] = substr($rows->extension, 0, 3);
		}

		//ダウンロード
		//headerの設定（PDF,word,excel,pp)
		if ($fdata["extension_check"] = 'xls') {
			header("Content-Type: application/msexcel';");
		} else if ($fdata["extension_check"] = 'doc') {
			header("Content-Type: application/msword';");
		} else if ($fdata["extension_check"] = 'ppt') {
			header("Content-Type: application/mspowerpoint';");
		} else if ($fdata["extension_check"] = 'pdf') {
			header("Content-Type: application/pdf';");
		}

		$filename = $fdata['file_name'];
		$ua = $_SERVER['HTTP_USER_AGENT'];
		if (strstr($ua, 'Trident') || strstr($ua, 'MSIE')) $filename = mb_convert_encoding($fdata['file_name'], 'sjis-win', 'UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo $fdata["contents"];
		exit();
	}
}
