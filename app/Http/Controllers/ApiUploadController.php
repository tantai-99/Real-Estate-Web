<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpFile2ContentLength\HpFile2ContentLengthRepositoryInterface;
use Library\Custom\Form\Element;
use Library\Custom\Image;
use App\Traits\JsonResponse;
use App\Rules\ImageSize;
use App\Exceptions;
use App\Repositories\HpFileContent\HpFileContentRepositoryInterface;
use App\Repositories\HpFileContentLength\HpFileContentLengthRepositoryInterface;


class ApiUploadController extends Controller
{
	use JsonResponse;

	public function init($request, $next)
	{
		//既に容量が超えている場合はエラーとする
		$hp = getUser()->getCurrentHp();
		if ($hp->capacityCalculation() > config('constants.hp.SITE_OBER_CAPASITY_DATAMAX')) {
			return $this->success([
				'errors' => [
					'over_capacity' => [
						"data_max" => "容量が" . config('constants.hp.SITE_OBER_CAPASITY_DATAMAX')  . "MBを超えています。不要な画像などを削除してください。"
					]
				]
			]);
		}
		// $response = $next($request);
		$accept = $request->server('HTTP_ACCEPT');
		if ($accept && strpos($accept, 'application/json') === false) {
			$request->header('json', 'Content-Type', 'text/html');
		}
		return $next($request);
	}

	public function favicon()
	{
		$user = getInstanceUser('cms');
		$cms_plan = $user->getProfile()->cms_plan;
		$hp = $user->getCurrentHp();
		if (!$hp) {
			throw new \Exception('ホームページが存在しません。');
		}

		$element = new Element\File('file');
		$element->setValue(app('request')->file);
		$element->addValidator('max:10240');
		if (!$element->isValid(false) || is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success([
				'errors' => ['ファイルサイズが10MBの制限を超えています。']
				]);
		}
		$element->addValidator('file_extension:jpg,jpeg,gif,png,ico', '登録できる拡張子は jpg,jpeg,gif,png,ico です。');
		$element->addValidator('mimes:jpg,jpeg,gif,png,ico', '登録できる拡張子は jpg,jpeg,gif,png,ico です。');
		if (!$element->isValid(false)) {
			return $this->success(['errors' => $element->getMessages()]);
		}

		$filename = $_FILES['file']['tmp_name'];
		$icon = null;
		try {
			$image = new Image($filename);
		} catch (\Exception $e) {
			$info = @getimagesize($filename);
			if ($info && isset($info[2]) && $info[2] === IMAGETYPE_ICO) {
				$icon = file_get_contents($filename);
			} else {
				throw $e;
			}
		}
		if ($icon === null) {
			$icon = $image->imageIco();
		}


		$table = App::make(HpSiteImageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data['id'] = $table->create(array(
				'type' => config('constants.hp_site_image.TYPE_FAVICON'),
				'extension' => 'ico',
				'content' => $icon,
				'hp_id' => $hp->id,
				'create_id' => $user->getProfile()->id,
			));

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_image['id'] = $data['id']->id;
		$data_image['cms_plan'] = $cms_plan;

		return $this->success($data_image);
	}

	public function siteLogoPc()
	{
		return $this->_siteLogo(config('constants.hp_site_image.TYPE_SITELOGO_PC'));
	}

	public function siteLogoSp()
	{
		return $this->_siteLogo(config('constants.hp_site_image.TYPE_SITELOGO_SP'));
	}

	protected function _siteLogo($type)
	{
		$user = getInstanceUser('cms');
		$cms_plan = $user->getProfile()->cms_plan;
		$hp = $user->getCurrentHp();
		if (!$hp) {
			throw new \Exception('ホームページが存在しません。');
		}

		$size = array();
		$size[config('constants.hp_site_image.TYPE_SITELOGO_PC')] = array(280, 60);
		$size[config('constants.hp_site_image.TYPE_SITELOGO_SP')] = array(200, 200);

		list($width, $height) = $size[$type];

        $element = new Element\File('file');
        $element->setValue(app('request')->file);
		$element->addValidator('max:10240');
		if (!$element->isValid(false) || is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success([
				'errors' => ['ファイルサイズが10MBの制限を超えています。']
				]);
		}
        $element->addValidator('file_extension:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
        $element->addValidator('mimes:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
        if (!$element->isValid(false)) {
            return $this->success(['errors' => $element->getMessages()]);
        }

		$filename = $_FILES['file']['tmp_name'];
		$parts = explode('.', $_FILES['file']['name']);
		$ext = array_pop($parts);
		$image = new Image($filename);
		$content = $image->fit($width, $height)->getContent();

		$table = App::make(HpSiteImageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data['id'] = $table->create(array(
				'type' => $type,
				'extension' => $ext,
				'content' => $content,
				'hp_id' => $hp->id,
				'create_id' => $user->getProfile()->id,
			));
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_image['id'] = $data['id']->id;
		$data_image['cms_plan'] = $cms_plan;

		return $this->success($data_image);
	}

	protected function webclip()
	{
		$user = getInstanceUser('cms');
		$cms_plan = $user->getProfile()->cms_plan;
		$hp = $user->getCurrentHp();
		if (!$hp) {
			throw new \Exception('ホームページが存在しません。');
		}
		$element = new Element\File('file');
        $element->setValue(app('request')->file);
		$element->addValidator('max:10240');
		if (!$element->isValid(false) || is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success([
				'errors' => ['ファイルサイズが10MBの制限を超えています。']
				]);
		}
        $element->addValidator('file_extension:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
        $element->addValidator('mimes:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
        if (!$element->isValid(false)) {
            return $this->success(['errors' => $element->getMessages()]);
        }

		$filename = $_FILES['file']['tmp_name'];
		$parts = explode('.', $_FILES['file']['name']);
		$ext = array_pop($parts);
		$image = new Image($filename);
		//		$content = $image->fit("144", "144")->getContent();
		$content = $image->fit("152", "152")->getContent();


		$table = App::make(HpSiteImageRepositoryInterface::class);
		try {
			DB::beginTransaction();

			$data['id'] = $table->create(array(
				'type' => config('constants.hp_site_image.TYPE_WEBCLIP'),
				'extension' => $ext,
				'content' => $content,
				'hp_id' => $hp->id,
				'create_id' => $user->getProfile()->id,
			));

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		$data_image['id'] = $data['id']->id;
		$data_image['cms_plan'] = $cms_plan;

		return $this->success($data_image);
	}

	public function hpImage(Request $request) {
		$hp = getInstanceUser('cms')->getCurrentHp();
		if (!$hp) {
			throw new Exceptions\CustomException('ホームページが存在しません。');
		}
        
        $element = new Element\File('file');
        $element->setValue(app('request')->file);
        if (is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success(['errors' => ['ファイルサイズが10MBの制限を超えています。']]);
		} else {
			$element->addValidator('file_extension:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
			$element->addValidator('mimes:jpg,jpeg,png,gif', '登録できる拡張子は jpg,jpeg,gif,png です。');
			if (!$element->isValid(false)) {
				return $this->success(['errors' => $element->getMessages()]);
			}

			$filename = $_FILES['file']['tmp_name'];
			$imageType = getimagesize($filename)[2]; 
			if ($imageType === IMAGETYPE_GIF) 
			{
				$element->addValidator('max:2048');
				if (!$element->isValid(false)) {
					return $this->success(['errors' => ['ファイルサイズが2MBの制限を超えています。']]);
				}
				$imageSize = new ImageSize(['max_width' => 1280, 'max_height' => 960], [ImageSize::WIDTH_TOO_BIG => '縦960ピクセル、横1280ピクセルの制限を超えています。', ImageSize::HEIGHT_TOO_BIG=> '縦960ピクセル、横1280ピクセルの制限を超えています。']);
				$element->addValidator($imageSize);
			}
			else
			{
				$element->addValidator('max:10240');
				if (!$element->isValid(false)) {
					return $this->success(['errors' => ['ファイルサイズが10MBの制限を超えています。']]);
				}
			}
		}

		$parts = explode('.', $_FILES['file']['name']);
		$ext = array_pop($parts);
		if ($imageType === IMAGETYPE_GIF) {
			if (false === ($content = @file_get_contents($filename))) {
				throw new \Exception('画像の読み込みに失敗しました。');
			}
		} else {
			$image = new Image($filename);
			$content = $image->fit("1280", "960")->getContent();
			if (empty($content)) {
				throw new \Exception('画像の読み込みに失敗しました。');
			}
		}
        // ATHOME_HP_DEV-2896: 画像の圧縮を行う(第3引数true)/回転反転不要(第4引数false)
		// Image::convertImage($content, $filename, true, false);
		$table = App::make(HpImageContentRepositoryInterface::class);
		try {
			DB::beginTransaction();
		    $row = $table->create(array(
				'extension' => $ext,
				'content' => $content,
				'hp_id' => $hp->id,
				'create_id' => getInstanceUser('cms')->getProfile()->id
		    ));
		   	$row->id = $row->aid;
            $row->save();
            DB::commit();
            return $this->success(['id' => $row->aid]);
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
	}
	
	public function hpFile2(Request $request) {
		$user	= getInstanceUser('cms')		;
		$hp		= $user->getCurrentHp()	;
		if ( !$hp ) {
			throw new \Exception( 'ホームページが存在しません。' ) ;
		}
		
		$element = new Element\File('file');
        $element->setValue(app('request')->file);
		if (is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success(['errors' => ['ファイルサイズが5MBの制限を超えています。']]);
		} else {
			$element->addValidator('file_extension:pdf,xls,xlsx,doc,docx,ppt,pptx');
			$element->addValidator('mimes:pdf,xls,xlsx,doc,docx,ppt,pptx');
			if (!$element->isValid(false)) {
				return $this->success(['errors' => ['登録できる拡張子は pdf,xls,xlsx,doc,docx,ppt,pptx です。']]);
			}
			$element->addValidator('max:5120');
			if (!$element->isValid(false) || is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
				return $this->success(['errors' => ['ファイルサイズが5MBの制限を超えています。']]);
			}
		}
		
		if (isset( $_FILES[ 'file' ][ 'name' ] ) )
		{
			$vowels = array('*','/','\\','#','(',')','\'','?','&','@','=',',','+','<','>','$','"','%',' ','　');
			foreach( $vowels as $val )
			{
				if(strpos( $_FILES[ 'file' ][ 'name' ], $val ) !== false )
				{	
					return $this->success([
						'errors' => [
							"ファイル名に使用できない文字列が含まれております。"							,
							"エラー文字：* / \ # ( ) ? & @ = , + < > $ \" % 半角スペース 全角スペース"	,
						]
					]);
				}
			}
		}

		$filename	=				$_FILES[ 'file' ][ 'tmp_name'	]	;
		$parts		= explode( '.', $_FILES[ 'file' ][ 'name'		] )	;
		$ext = array_pop( $parts ) ;
		if ( false === ( $content = @file_get_contents( $filename ) ) ) {
			throw new \Exception( 'ファイルの読み込みに失敗しました。' ) ;
		}
		$table		= App::make(HpFile2ContentRepositoryInterface::class) ;
		// contentsの容量格納テーブル
		$tablelen	= App::make(HpFile2ContentLengthRepositoryInterface::class) ;
		try {
			DB::beginTransaction() ;
			$row = $table->create( array(
				'filename'		=> mb_convert_encoding( $_FILES['file']['name'], 'UTF-8', 'auto' )	,
				'extension'		=> $ext																,
				'content'		=> $content															,
				'hp_id'			=> $hp->id															,
				'create_id'		=> $user->getProfile()->id											,
			)) ;
			$row->id = $row->aid;
			$row->save();
			
			$lid = $tablelen->create( array(
				'hp_file2_content_id'	=> $row->aid,
				'content_length'	=> strlen($content) 
			) ) ;
			
			DB::commit() ;
			return $this->success(['id' => $row->aid]);
		} catch ( \Exception $e ) {
			DB::rollback() ;
		}
	}

	public function hpFile(Request $request)
	{
		$user = getInstanceUser('cms');
		$hp = $user->getCurrentHp();
		if (!$hp) {
			throw new \Exception('ホームページが存在しません。');
		}

		$element = new Element\File('file');
		$element->setValue(app('request')->file);
		if (is_null(app('request')->file) || (isset($_FILES['file']) && $_FILES['file']['error'])) {
			return $this->success(['errors' => ['ファイルサイズが2MBの制限を超えています。']]);
		} else {
			$element->addValidator('file_extension:pdf,xls,xlsx,doc,docx,ppt,pptx');
			$element->addValidator('mimes:pdf,xls,xlsx,doc,docx,ppt,pptx');
			if (!$element->isValid(false)) {
				return $this->success(['errors' => ['登録できる拡張子は pdf,xls,xlsx,doc,docx,ppt,pptx です。']]);
			}
			$element->addValidator('max:2048');
			if (!$element->isValid(false)) {
				return $this->success([
					'errors' => ['ファイルサイズが2MBの制限を超えています。']
					]);
			}
		}

		if (isset($_FILES['file']['name'])) {
			$vowels = array('*', '/', '\\', '#', '(', ')', '\'', '?', '&', '@', '=', ',', '+', '<', '>', '$', '"', '%', ' ', '　');
			foreach ($vowels as $val) {
				if (strpos($_FILES['file']['name'], $val) !== false) {
					return $this->success([
						'errors' => [
							"ファイル名に使用できない文字列が含まれております。",
							"エラー文字：* / \ # ( ) ? & @ = , + < > $ \" % 半角スペース 全角スペース"
						]
					]);
				}
			}
		}

		$filename = $_FILES['file']['tmp_name'];
		$parts = explode('.', $_FILES['file']['name']);
		$ext = array_pop($parts);
		if (false === ($content = @file_get_contents($filename))) {
			throw new \Exception('ファイルの読み込みに失敗しました。');
		}

		$table = App::make(HpFileContentRepositoryInterface::class);

		// contentsの容量格納テーブル
		$tablelen = App::make(HpFileContentLengthRepositoryInterface::class);

		try {
			DB::beginTransaction() ;

			$row = $table->create(array(
				'extension' => $ext,
				'filename' => mb_convert_encoding($_FILES['file']['name'], 'UTF-8', 'auto'),
				'content' => $content,
				'hp_id' => $hp->id,
			));

			$row->id = $row->aid;
			$row->save();

			$lid = $tablelen->create(array(
				'hp_file_content_id' => $row->aid,
				'content_length' => strlen($content)
			));

			DB::commit();
			return $this->success(['id' => $row->aid]);
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	public function hpFileInfo(Request $request)
	{
		$user = getInstanceUser('cms');
		$hp = $user->getCurrentHp();
		if (!$hp) {
			throw new \Exception('ホームページが存在しません。');
		}

		$table = App::make(HpFileContentRepositoryInterface::class);
		if ($info = $table->fetchInfo($hp->id, (int)$request->id)) {
			$data = $info->toArray();
		}
		return $this->success([
			'info' => [
				'id' => $data['id'],
				'extension' => $data['extension'],
				'filename'	=> $data['filename'],
				],
			]);
	}
	
	public function hpFile2Info(Request $request)
	{ 
		$user	= getInstanceUser('cms')		;
		$hp		= $user->getCurrentHp()	;
		if ( !$hp ) {
			throw new \Exception( 'ホームページが存在しません。' ) ;
		}
		
		$table = App::make(HpFile2ContentRepositoryInterface::class) ;
		if ( $info = $table->fetchInfo( $hp->id, (int)$request->id ) ) {
			$data = $info->toArray() ;
		}
		return $this->success([
			'info' => [
				'id' => $data['id'],
				'extension' => $data['extension'],
				'filename'	=> $data['filename'],
				],
			]);
	}
}
