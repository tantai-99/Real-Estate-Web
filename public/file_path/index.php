<?php
ini_set( 'display_errors', 1 );
date_default_timezone_set('Asia/Tokyo');

log_write("start");

//返却用JSON
$data = array();
$data["success"] = false;
$data["error"]  = "";
$data["data"]  = array();

//パラメータの取得
$param = $_REQUEST;

//ファイルチェック
if(!isset($_FILES["file"]['tmp_name']) || $_FILES["file"]['tmp_name'] == "" ) {
	$data["error"] = "No File Error";
	log_write($data["error"]);
	json_view($data);
	exit;
}

//ディレクトリ存在チェック、なければ作る
$dgObj = new DirectoryGenerate();

//adminフォルダ作成
if(!$dgObj->createAdminDir()) {
	$data["error"] = "Admin Dir Error";
	log_write($data["error"]);
	json_view($data);
	exit;
}

switch($param['type']) {

	case "google" :

		if(!isset($param['company_id']) || $param['company_id'] == "" || !is_numeric($param['company_id'])) {
			$data["error"] = "No CompanyId Error";
			log_write($data["error"]);
			json_view($data);
			exit;
		}

		//各契約店のIDでフォルダ作成
		if(!$dgObj->createCompanyDir($param['company_id'])) {
			$data["error"] = "CompanyDir Error";
			log_write($data["error"]);
			json_view($data);
			exit;
		}

		if(!$dgObj->createGoogleDir()) {
			$data["error"] = "GoogleDir Error";
			log_write($data["error"]);
			json_view($data);
			exit;
		}
		break;

	case "tmp" :

		if(!$dgObj->createTmpDir()) {
			$data["error"] = "tmpDir Error";
			log_write($data["error"]);
			json_view($data);
			exit;
		}
		break;
}

//ファイルをコピーする
$tmpfile  = $_FILES["file"]['tmp_name'];
$filename = $_FILES["file"]['name'];
$file_copy = $dgObj->getGenerationDir() ."/". $filename;
if(!@copy($_FILES["file"]['tmp_name'], $file_copy)) {
	$data["error"] = "Copy File Error";
	log_write($data["error"]);
	json_view($data);
	exit;
}

//保存した内容を設定
$data["data"]['file_path'] = $file_copy;

//結果をOKに設定
$data["success"] = true;

//返却
json_view($data);

log_write("end");

/**
* 結果を返却
*/
function json_view($data) {
    header("Content-Type: application/json; charset=utf-8");
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-App-Client');
    header("X-Content-Type-Options: nosniff");
    echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}


/**
* ログの吐き出し
*/
function log_write($message) {

	$message = "[". date("Y-m-d H:i:s") ."] ". $message. "\n";
	// error_log($message, 3, "./logs/error.log");
}


/**
* 各フォルダを作成用のクラス
*/
class DirectoryGenerate {

	private $root_dir    = "";
	private $add_path_dir = "";

	public function __construct() {
	}

	public function createAdminDir() {
		$this->root_dir = realpath("./");
		//ディレクトリ存在チェック、なければ作る
		$this->add_path_dir = $this->root_dir . "/admin/";
		return $this->makeDir($this->add_path_dir);
	}

	public function createCompanyDir($company_id) {
		//ディレクトリ存在チェック、なければ作る
		$this->add_path_dir = $this->add_path_dir .$company_id ."/";
		return $this->makeDir($this->add_path_dir);
	}

	public function createGoogleDir() {
		//ディレクトリ存在チェック、なければ作る
		$this->add_path_dir = $this->add_path_dir ."google/";
		return $this->makeDir($this->add_path_dir);
	}

	public function createTmpDir() {
		//ディレクトリ存在チェック、なければ作る
		$this->add_path_dir = $this->add_path_dir ."tmp/";
		return $this->makeDir($this->add_path_dir);
	}

	public function getGenerationDir() {
		return $this->add_path_dir;
	}

	/**
	 * 実際に作成
	 */
	private function makeDir($path) {

		//ディレクトリ存在チェック、なければ作る
		if(!is_dir($path)) {
			if(!@mkdir($path, 0777)) {
				return false;
			}

			if(!@chmod($path, 0777)) {
				return false;
			}
		}
		return true;
	}
}

?>