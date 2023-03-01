<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Http\Form\Site;
use App\Http\Form\Keyword;
use Illuminate\Http\Request;
use Library\Custom\Util;
use Illuminate\Support\Facades\DB;
use Library\Custom\Logger\CmsOperation;
use Library\Custom\Model\Lists\LogEditType;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Repositories\HpImageCategory\HpImageCategoryRepositoryInterface;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpFile2Category\HpFile2CategoryRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\MTheme\MThemeRepositoryInterface;
use App\Http\Form\Image;
use App\Http\Form\ImageCategory;
use App\Http\Form\File2;
use App\Http\Form\File2Category;
use Library\Custom\Form;
use Library\Custom\Form\add;
use Library\Custom\Form\Element;
use Library\Custom\Model\Lists\CmsPlan;
use App\Traits\JsonResponse;
use App\Http\Form\Design;
use App\Model\HpFile2Used;
use App\Model\HpFile2Content;
use Library\Custom\Controller\Action\InitializedCompany;
use App\Repositories\HankyoPlusLog\HankyoPlusLogRepositoryInterface;

class SiteSettingController extends InitializedCompany
{
	use JsonResponse;

	protected function _thenNotInitialized($request, $next, $hp) {
		if ($this->isApiRequest() && $hp) {
			return $next($request);
		}
		return parent::_thenNotInitialized($request, $next, $hp);
	}

	public function init($request, $next)
	{
		return parent::init($request, $next);
	}

	public function index()
	{
		$this->view->topicPath('基本設定：初期設定');
		// $this->view->messages = $this->_helper->flashMessenger->getMessages();

		$this->view->form = new Site();
		$this->view->form->addSubForm(new Keyword(), 'keywords');
		$hp = getInstanceUser('cms')->getCurrentHp();
		$this->view->form->setData($hp->toArray());
		$this->view->form->getSubForm('keywords')->setData(explode(",", $hp->keywords));
		$this->view->cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;

		return view('site-setting.index');
	}

	public function apiSaveIndex(Request $request)
	{
		$form = new Site();
		$cms_plan = getInstanceUser('cms')->getProfile()->cms_plan;
		$hp = getInstanceUser('cms')->getCurrentHp();

		if ($hp->hasReserve()) {
			throw new \Exception('has reserve');
		}

		$params = $request->all();

		$form->setData($params);

		if (!$form->isValid($params)) {
			return $this->success(['errors' => $form->getMessages()]);
		}
		// ATHOME_HP_DEV-5179 Query-Stringに'only_valid=1'があればエラーなしの結果を返すだけ
		if (!is_null($request->only_valid) && $request->only_valid == '1') {
			$data_hp = [];
			$data_hp['cms_plan'] = $cms_plan;

			return $this->success($data_hp);
		}

		$form->setData($hp->toArray());

		$data = $request->all();
		$data['keywords'] = implode(',', $data['keywords']);

		// 画像IDが空の場合nullをセット
		foreach (array('favicon', 'logo_pc', 'logo_sp', 'webclip') as $col) {
			if (Util::isEmptyKey($data, $col)) {
				$data[$col] = null;
			}
		}

		$flag_tw_widget_id = false;
		if ($hp->tw_username != $data['tw_username']) {
			$flag_tw_widget_id = true;
		} else {
			if ($hp->tw_widget_id != $data['tw_widget_id']) {
				$flag_tw_widget_id = true;
			}
		}

		// ATHOME_HP_DEV-3126 all_update_flg 設定条件変更

		/**
		 * ATHOME_HP_DEV-5157
		 * 差分チェックを実施しないように変更(一応コードは残しておく)
		 * 
		$change_flg = false;
		foreach(array_keys($data) as $key) {
			if($data[ $key ] != $hp->{$key}) {
				$change_flg = true;
				break;
			}
		} */

		$change_flg = true;

		if ($change_flg) {
			$hp->all_upload_flg = 1;
			$hp->setAllUploadParts('initial', 1); // 初期設定による全公開
		} else {
			throw new \App\Exceptions\NotModifiedException('初期設定の更新情報が確認できません。');
		}

		$hp->setFromArray($data);
		//$hp->all_upload_flg = 1;
		if ($flag_tw_widget_id) {
			$hp->tw_widget_id = "";
		}

		try {
			DB::beginTransaction();

			$hp->save();

			// 反響プラスログを保存する
			if (getInstanceUser('cms')->getProfile()->cms_plan != config('constants.cms_plan.CMS_PLAN_LITE')) {
				$company = $hp->fetchCompanyRow();
				$hankyoPlusLogTable = App::make(HankyoPlusLogRepositoryInterface::class);
				$hankyoPlusLogTable->saveOperation(
					$data['hankyo_plus_use_flg'],
					$hp->id,
					$company->id
				);
			}

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}

		// CMS操作ログ
		CmsOperation::getInstance()->cmsLog(LogEditType::SITESETTING_UPDATE);

		$data_hp = [];
		$data_hp['cms_plan'] = $cms_plan;

		return $this->success($data_hp);
	}

	public function design()
	{
        if (getInstanceUser('cms')->isNerfedTop()) {
           return Redirect::to('/site-setting');
        }
        $this->view->topicPath('基本設定：デザイン選択');
		
        $company_id =getInstanceUser('cms')->getProfile()->id						;
        $this->view->form= new Design( $company_id )	;
        $this->view->messages = $this->view->form->getMessages();
        // $cms_plan		= getInstanceUser('cms')->getProfile()->cms_plan;
        $hp = getInstanceUser('cms')->getCurrentHp();
        $this->view->form->setData($hp->toArray());
		return view('site-setting.design');
	}

	public function apiSaveDesign(Request $request)
	{
		$data=array();
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$company_id = $user->getProfile()->id;
		$form		= new Design($company_id);
		$hp = getUser()->getCurrentHp();
		if ($hp->hasReserve()) {
			throw new Exception('has reserve');
		}
		$form->setData($hp->toArray());
		//デザインパターン追加（カラー自由版）
		$theme_id = $request->theme_id;
		$model = App::make(MThemeRepositoryInterface::class);
		$where= [['id',$theme_id]];
		$row = $model->fetchRow($where);
		if ($row) {
			
			if (strpos($row->name, 'custom_color') === false) {
				// $form->removeElement("color_code");
				$form->getElement('color_code')->setAttributes([
					'required'=>false,
				]);
			} else {
				$form->removeElement("color_id");
				// $form->color_id->setRequired(false);
				$color_code = $request->color_code;
				$request->color_code=str_replace('#', '', $color_code);
			}
		}
		//デザインパターン追加（カラー自由版）
		if (!$form->isValid($request->all())) {
			$data['errors'] = $form->getMessages();
			return $this->success($data);
		}
		// ATHOME_HP_DEV-5272 Query-Stringに'only_valid=1'があればエラーなしの結果を返すだけ
		if (!is_null($request->only_valid) && $request->only_valid == '1') {
			return $this->success($data);
		}
		// $data = $form->getValue();
		$data=$request->all();
		//デザインパターン追加（カラー自由版）
		if (strpos($row->name, 'custom_color') === false) {
			$data['color_code'] = '';
		} else {
			$data['color_id'] = 0;
		}
		//デザインパターン追加（カラー自由版）

		// ATHOME_HP_DEV-3126 all_update_flg 設定条件変更

		/**
		 * ATHOME_HP_DEV-5157
		 * 差分チェックを実施しないように変更(一応コードは残しておく)
		 * 
        $change_flg = false;
		foreach(array_keys($data) as $key) {
			if($data[ $key ] != $hp->{$key}) {
				$change_flg = true;
				break;
			}
		}
		 */
		$change_flg = true;

		if ($change_flg) {
			$hp->all_upload_flg = 1;
			$hp->setAllUploadParts('design', 1);  // デザイン変更による全公開
		} else {
			throw new \App\Exceptions\NotModifiedException('デザイン情報の更新情報が確認できません。');
		}

		$hp->setFromArray($data);
		// $hp->all_upload_flg = 1;
		
		try {
			DB::beginTransaction();
			$hp->save();
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
		
		// CMS操作ログ
		
		CmsOperation::getInstance()->cmsLog(config('constants.LogEditType.DESIGN_UPDATE'));
		return $this->success([]);
	}

	public function image()
	{
		$this->view->topicPath('基本設定：画像フォルダ');
		$form = new Image();
		$hp = getInstanceUser('cms')->getCurrentHp();
		$hpImages = [];
		foreach ($hp->fetchImages() as $hpImage) {
			$hpImages[] = $hpImage->toResponseArray();
		}
		$rowset = $hp->fetchImageCategories();
		$options = array('0' => '選択してください');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->name;
		}
		$this->view->hpImages = $hpImages;
		$form->getElement('category_id')->setValueOptions($options);
		$this->view->form = $form ;
		return view('site-setting.image');
	}

	public function file2()
	{
		$this->view->topicPath('基本設定：ファイル管理');

		$form = new File2();

		$hp = getInstanceUser('cms')->getCurrentHp();

		$hpFile2s = [];
		foreach ($hp->fetchFile2s() as $hpFile2) {
			$hpFile2s[] = $hpFile2->toResponseArray();
		}
		$this->_setFile2extensions($hp->id, $hpFile2s);
		$this->_setFile2MemberOnly( $hp->id, $hpFile2s);
		$rowset = $hp->fetchFile2Categories();
		$options = array('0' => '選択してください');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->name;
		}
		$form->getElement('category_id')->setValueOptions($options);
		$this->view->hpFile2s = $hpFile2s;
		$this->view->form = $form;

		return view('site-setting.file2');
	}

	public function apiGetImages()
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$hp = getUser()->getCurrentHp();
		$data['images'] = [];
		foreach ($hp->fetchImages() as $key => $image) {
			$data['images'][] = $image->toResponseArray();
		}
		$results = array();
		$table = App::make(HpImageCategoryRepositoryInterface::class);
		$rowset = $table->fetchAll(array(['hp_id', $hp->id]));
		foreach ($rowset as $row) {
			$results[] = array(
				'id'   => $row->id,
				'name' => $row->name,
				'sort' => $row->sort,
			);
		}
		$data['categories'] = $results;
		return $this->success($data);
	}

	protected function _setFile2extensions($hp_id, &$targetArray)
	{	
		$model					= DB::table('hp_file2_content')->select('id', 'extension')
			->where('hp_id', $hp_id)
			->get();
		$rowset	= $model;
		$exts	= array();
		foreach ($rowset as $row) {
			$exts[$row->id] = $row->extension;
		}
		foreach ($targetArray as &$val) {
			$val['extension'] = $exts[$val['hp_file2_content_id']];
		}
	}

	protected function _setFile2MemberOnly($hp_id, &$targetArray)
	{	

		$hpPage	= App::make(HpPageRepositoryInterface::class);
		$model					= DB::table('hp_file2_used')->select('hp_page_id', 'hp_file2_id')
			->where('hp_id', $hp_id)
			->get();
		$rowset	= $model;
		$member	= array();
		$in_use	= array();
		foreach ($rowset as $row)		// 使用しているファイルのループ
			{
				$hp_page_id		= $row->hp_page_id;
				$hp_file2_id	= $row->hp_file2_id;
				$member[$hp_file2_id]	= $this->_isMemberOnly($hpPage, $hp_page_id);
				$in_use[$hp_file2_id]	= true;
			}
		if(isset($row)){
			foreach ($targetArray as &$val) {
				$hp_page_id		= $row->hp_page_id;
				$hp_file2_id	= $row->hp_file2_id;
				$member[$hp_file2_id]	= $this->_isMemberOnly($hpPage, $hp_page_id);
				$in_use[$hp_file2_id]	= true;
			}
		}
		foreach ($targetArray as &$val) {
			@$val['member'] = $member[$val['id']];
			@$val['in_use'] = $in_use[$val['id']];
		}
	}

	protected function _isMemberOnly(&$hpPage, $hp_page_id, $level = 1)
	{
		$result	= false;
		$row	= $hpPage->fetchRowById($hp_page_id);
		if ($row == null) {
			return false;
		}
		if ($row->member_only_flg && ($level > 1)) {
			return true;
		}
		if ($row->parent_page_id) {
			$result = $this->_isMemberOnly($hpPage, $row->parent_page_id, ++$level);
		}
		return $result;
	}

	public function apiGetFile2()
	{
		$hp = getUser()->getCurrentHp();
		$data['file2s'] = [];
		foreach ($hp->fetchFile2s() as $key => $file2s) {
			$data['file2s'][] = $file2s->toResponseArray();
		}

		$this->_setFile2extensions($hp->id, $data['file2s']);
		$this->_setFile2MemberOnly($hp->id, $data['file2s']);

		$results	= array();
		$table		= App::make(HpFile2CategoryRepositoryInterface::class);
		$rowset		= $table->fetchAll(array(['hp_id', $hp->id]));
		foreach ($rowset as $row) {
			$results[] = array(
				'id'   => $row->id,
				'name' => $row->name,
				'sort' => $row->sort,
			);
		}
        
		$data['categories'] = $results;
		return $this->success($data);
	}

	public function downloadImage(Request $request)
	{
		$id = (int) $request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();
		$table = App::make(HpImageRepositoryInterface::class);
		$imageRow = $table->fetchRow([['id', $id], ['hp_id', $hp->id]]);
		if (!$imageRow) {
			$this->_forward404();
		}
		$contentRow = $imageRow->getContent();
		if (!$contentRow) {
			$this->_forward404();
		}
		$filename = $imageRow->title;
		$filename .= '.' . $contentRow->extension;

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
			$filename = mb_convert_encoding($filename, 'SJIS-win');
		}
		switch ($contentRow->extension) {
			case 'jpg':
				$type = 'jpeg';
				break;
			default:
				$type = $contentRow->extension;
				break;
		}
		header('Cache-Control: public');
		header('Pragma: public');
		header('Content-Type: image/' . $type);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo $contentRow->content;
		exit();
	}

	public function downloadFile2(Request $request)
	{
		$id = (int) $request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();
		$table = App::make(HpFile2RepositoryInterface::class);
		$file2Row = $table->fetchRow([['id', $id], ['hp_id', $hp->id]]);
		if (!$file2Row) {
			$this->_forward404();
		}
		$contentRow = $file2Row->getContent();
		if (!$contentRow) {
			$this->_forward404();
		}
		$filename = $file2Row->title;
		$filename .= '.' . $contentRow->extension;

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
			$filename = mb_convert_encoding($filename, 'SJIS-win');
		}
		switch ($contentRow->extension) {
			case 'pdf':
				$type = 'pdf';
			case 'xls':
			case 'xlss':
				$type = 'vnd.ms-excel';
			case 'doc':
				$type = 'msword';
			case 'ppt':
			case 'pptx':
				$type = 'vnd.ms-powerpoint';
				break;
			default:
				$type = $contentRow->extension;
				break;
		}
		header('Cache-Control: public');
		header('Pragma: public');
		header('Content-Type: application/' . $type);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		echo $contentRow->content;
		exit();
	}

	public function apiSaveImage(Request $request)
	{
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$form = new Image();
		$hp = getInstanceUser('cms')->getCurrentHp();
		$rowset = $hp->fetchImageCategories();
		$options = array('0' => '選択してください');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->name;
		}
		$form->getElement('category_id')->setValueOptions($options);
		$form->setData($request->all());
		if (!$form->isValid($request->all())) {
			$data['errors']= $form->getMessages();
			return $this->success($data);
		}
		$table = App::make(HpImageRepositoryInterface::class);
		DB::beginTransaction();
		try {
            $form->setData($request->all());
            if ($table->fetchRow([['hp_image_content_id',$form->getElement('hp_image_content_id')->getValue()],['hp_id',$hp->id]])) {
                $data['errors'] = array(
                    'hp_image_content_id' => array(
                        'invalid' => '画像の登録に失敗しました。再度アップロードしてください。'
                    )
                );
                return $this->success($data);
            }
            $data = $form->getDatas();
            $data['hp_id'] = $hp->id;
            $row = $table->create($data);
            $row->id = $row->aid;
            $row->save();
            DB::commit();
            $data['id'] = $row->id;
            $data['item'] = $row->toResponseArray();
			return $this->success($data);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        } finally {
            //$query = "UNLOCK TABLES";
            //$adapter->getConnection()->query($query);
		}

		// CMS操作ログ
		CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.IMAGE_CREATE'));
	}

	public function apiSaveFile2(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$form	= new File2();

		$params					= $request->all();
		$file2Title				= $params['title'];
		$hp_file2_content_id	= $params['hp_file2_content_id'];
		$row					= DB::table('hp_file2_content')->select('extension')
																->where('id', $hp_file2_content_id )
																->first() ;
		if(!is_null($row)){
			$extension = $row->extension;
		}
		$hp		= getInstanceUser('cms')->getCurrentHp()	;
		$rowset	= $hp->fetchFile2Categories()		;
		$options = array( '0' => '選択してください' ) ;
		foreach ( $rowset as $row )
		{
			$options[ $row->id ] = $row->name ;
		}
		$form->getElement('category_id')->setValueOptions( $options ) ;
		$form->setData($params);
		
		if ( !$form->isValid( $request->all() ) ) {
			$data['errors'] = $form->getMessages() ;
			return $this->success($data);
		}
		$table = App::make(HpFile2RepositoryInterface::class);
		try {
			DB::beginTransaction() ;
			// 既に登録済みのファイルかチェックする
			if (!is_null($table->fetchRow([['hp_file2_content_id',$hp_file2_content_id], ['hp_id', $hp->id]] )))
			{
				$data['errors'] = array(
					'hp_file2_content_id' => array(
						'invalid' => 'ファイルの登録に失敗しました。再度アップロードしてください。'
					)
				);
				return $this->success($data);;
			}
			// ファイルタイトルに使用不可の文字があるかチェックする
			$vowels = array(" ", "*", "/", "\\", '#', "(", ")", "?", "&", "@", "=", ",", "+", "<", ">", "$", "　", '"', "%");
			foreach ($vowels as $val) {
				if (strpos($file2Title, $val) !== false) {
					$data['errors'] = array(
						'title' => array(
							'message'	=> 'ファイルタイトルに使用できない文字列が含まれております。',
							'invalid'	=> 'エラー文字：* / \ # ( ) ? & @ = , + < > $ " % 半角スペース 全角スペース',
						)
					);
					return $this->success($data);
				}
			}
			// 既に登録済みのファイルタイトルかチェックする
			if (!is_null($table->fetchRow( [['title', $file2Title], ['hp_id', $hp->id]] )))
			{	
				$data['errors'] = array(
					'title' => array(
						'invalid' => '既に同じタイトルのファイルが登録されています。'
					)
				);
				return $this->success($data);;
			}
			$form->setData($params);
			$data = $form->getDatas();
			$data['hp_id'] = $hp->id;
			$row = $table->create($data);
			$row->id = $row->aid;
			$row->save();
			DB::commit();
			return response()->json(
				[
					'success' => true,
					'data' =>
					[
						'item' =>
						[
							'id' => $row->aid,
							'title' => $row->title,
							'hp_file2_content_id' => $row->hp_file2_content_id,
							'category_id' => $row->category_id,
							'hp_id' => $row->hp_id,
							'extension' => $extension
						],
					],
					'cms_plan' => getInstanceUser('cms')->getProfile()->cms_plan,
				]
			);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		} finally {
		}

		// CMS操作ログ
		CmsOperation::getInstance()->cmsLog(config('constants.log_edit_type.CREATE_FILE2'));
	}

	public function apiEditImageCategory(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id = (int) $request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();
		$rowset = $hp->fetchImageCategories();
		$options = array('0' => '選択してください');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->name;
		}
		$form = new Form();
		$element = new Element\Select('category_id');
		$element->setLabel('画像カテゴリ');
		$element->setValueOptions(array(0 => '選択してください'));
		$form->add($element);
		$form->getElement('category_id')->setValueOptions($options);
		if (!$form->isValid($request->all())) {
			$data['errors'] = $form->getMessages();
			return $this->success($data);
		}
		$table = App::make(HpImageRepositoryInterface::class);
		$form->setData($request->all());
		$category_id = $form->getDatas();
		DB::beginTransaction();
		try {
			$where = [['id', $id], ['hp_id', $hp->id]];

			$table->update($where, $category_id);
			DB::commit();
			$data = '';
			return $this->success($data);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	public function apiEditFile2Category(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id = (int) $request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();
		$rowset = $hp->fetchFile2Categories();
		$options = array('0' => '選択してください');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->name;
		}
		$form = new Form();
		$element = new Element\Select('category_id');
		$element->setLabel('ファイル・カテゴリ');
		$element->setValueOptions(array(0 => '選択してください'));
		$form->add($element);
		$form->getElement('category_id')->setValueOptions($options);
		if (!$form->isValid($request->all())) {
			$data['errors'] = $form->getMessages();
			return $this->success($data);
		}
		$table = App::make(HpFile2RepositoryInterface::class);
		$form->setData($request->all());
		$category_id = $form->getDatas();
		DB::beginTransaction();
		try {

			$where = [['id', $id], ['hp_id', $hp->id]];

			$table->update($where, $category_id);
			DB::commit();
			$data = '';
			return $this->success($data);
		} catch (Exception $e) {
			DB::rollback();
			throw $e;
		}
	}

	public function apiRemoveImage(Request $request)
	{
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id = (int)$request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();
		// 使用ページチェック
		$table = App::make(HpPageRepositoryInterface::class);
		$rowset = $table->fetchAllByUsedImageId($hp->id, $id);
		if (count($rowset)) {
			$data['error'] = "画像を使用しているページがある為\n削除できません。";
			return $this->success($data);
		}
		$where = [['id', $id], ['hp_id', $hp->id]];
		$table = App::make(HpImageRepositoryInterface::class);
		$row = $table->fetchRow($where);
		if (!$row) {
			return;
		}

		DB::beginTransaction();

		$table->delete($where);

		$table = App::make(HpImageContentRepositoryInterface::class);
		$table->delete([['id', $row->hp_image_content_id], ['hp_id', $hp->id]]);
		// CMS操作ログ
		CmsOperation::getInstance()->cmsLogRemove(config('constants.log_edit_type.IMAGE_DELETE'), $row->hp_image_content_id);
		DB::commit();
		$data = '';
		return $this->success($data);
	}

	public function apiRemoveFile2(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id = (int)$request->id;
		$hp = getInstanceUser('cms')->getCurrentHp();

		// 使用ページチェック
		$table = App::make(HpPageRepositoryInterface::class);
		$rowset = $table->fetchAllByUsedFile2Id($hp->id, $id);
		if (count($rowset)) {
			$data['error'] = "ファイルを使用しているページがある為\n削除できません。";
			return $this->success($data);
		}
		$where = [['id', $id], ['hp_id', $hp->id]];
		$table = App::make(HpFile2RepositoryInterface::class);
		$row = $table->fetchRow($where);
		if (!$row) {
			return;
		}

		DB::beginTransaction();
		$table->delete($where);

		$table = App::make(HpFile2ContentRepositoryInterface::class);
		$table->delete([['id', $row->hp_image_content_id], ['hp_id', $hp->id]]);
		// CMS操作ログ
		CmsOperation::getInstance()->cmsLogRemove(config('constants.log_edit_type.DELETE_FILE2'), $row->hp_file2_content_id);
		DB::commit();
		$data = '';
		return $this->success($data);
	}

	public function apiSaveImageCategory(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$categories = $request->input('categories', array());
		if (!is_array($categories)) {
			throw new Exception('不正なアクセスです');
		}

		$form = new ImageCategory();
		$insert = array();
		$update = array();
		$error = array();
		$data = array();
		foreach ($categories as $i => $category) {
			if (!is_array($category)) {
				throw new Exception('不正なアクセスです');
			}
			$form->setData($category);
			if (!$form->isValid($category)) {
				$error[$i] = $form->getMessages();
			}
			$data = $form->getDatas();
			if (isset($category['id']) && $category['id'] != '') {
				$update[$category['id']] = $category;
			} else {
				unset($category['id']);
				$insert[] = $category;
			}
		}

		if ($error != []) {
			$data['errors'] = $error;
			return $this->success($data);
		}
		$cmsPlan = getInstanceUser('cms')->getProfile()->cms_plan;
		$plan = CmsPlan::getCmsPLanName($cmsPlan);
		switch ($plan) {
			case "advance":
				$maxCategory = config('constants.hp_image_category.ADVANCE_CATEGORY');
				break;
			case "standard":
				$maxCategory = config('constants.hp_image_category.STANDARD_CATEGORY');
				break;
			case "lite":
				$maxCategory = config('constants.hp_image_category.LITE_CATEGORY');
				break;
		}
		if (count($categories) > $maxCategory) {
			$data['errors'] = 'Error limit categories';
			return $this->success($data);
		}
		$hp = getInstanceUser('cms')->getCurrentHp();
		$table = App::make(HpImageCategoryRepositoryInterface::class);
		DB::beginTransaction();
		$rowset = $table->fetchAll([['hp_id', $hp->id]], ['sort']);
		foreach ($rowset as $row) {
			$where = [['id', $row->id], ['hp_id', $hp->id]];
			if (isset($update[$row->id])) {
				// 更新
				$table->update($where, $update[$row->id]);
			} else {
				// 削除
				$table->update($where, ['delete_flg' => 1]);
			}
		}
		foreach ($insert as $data) {
			$data['hp_id'] = $hp->id;
			$row = $table->create($data);
			$row->id = $row->aid;
			$row->save();
		}
		DB::commit();
		$results = array();
		$newRowset = $table->fetchAll([['hp_id', $hp->id], ['delete_flg', 0]], ['sort']);
		foreach ($newRowset as $row) {
			$results[] = array(

				'id'   => $row->id,
				'name' => $row->name,
				'sort' => $row->sort,
			);
		}
		$data['categories'] = $results;
		return $this->success($data);
	}

	public function apiSaveFile2Category(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$categories = $request->input('categories', array());
		if (!is_array($categories)) {
			throw new Exception('不正なアクセスです');
		}
		$form = new File2Category();
		$insert = array();
		$update = array();
		$error = array();
		foreach ($categories as $i => $category) {
			if (!is_array($category)) {
				throw new Exception('不正なアクセスです');
			}
			$form->setData($category);
			if (!$form->isValid($category)) {
				$error[$i] = $form->getMessages();
			}

			$data = $form->getDatas();
			if (isset($category['id']) && $category['id'] != '') {
				$update[$category['id']] = $category;
			} else {
				unset($category['id']);
				$insert[] = $category;
			}
		}
		if ($error != []) {
			$data['errors'] = $error;
			return $this->success($data);
		}
		$hp			= getInstanceUser('cms')->getCurrentHp();
		$table		= App::make(HpFile2CategoryRepositoryInterface::class);;
		DB::beginTransaction();

		$rowset = $table->fetchAll([['hp_id', $hp->id]]);
		foreach ($rowset as $row) {
			$where = [['id', $row->id], ['hp_id', $hp->id]];
			if (isset($update[$row->id])) {
				// 更新
				$table->update($where, $update[$row->id]);
			} else {
				// 削除
				$table->update($where, ['delete_flg' => 1]);
			}
		}

		foreach ($insert as $data) {
			$data['hp_id'] = $hp->id;
			$row = $table->create($data);
			$row->id = $row->aid;
			$row->save();
		}

		DB::commit();

		$results = array();
		$newRowset = $table->fetchAll([['hp_id', $hp->id], ['delete_flg', 0]], ['sort']);
		foreach ($newRowset as $row) {
			$results[] = array(
				'id'	=> $row->id,
				'name'	=> $row->name,
				'sort'	=> $row->sort,
			);
		}
		$data['categories'] = $results;
		return $this->success($data);
	}

	public function apiGetHppagesByUseimage(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id = (int)$request->id;

		$hp = getInstanceUser('cms')->getCurrentHp();
		$table = App::make(HpPageRepositoryInterface::class);
		$rowset = $table->fetchAllByUsedImageId($hp->id, $id);

		$pages = array();
		foreach ($rowset as $row) {
			$pages[] = array(
				'id' => $row->id,
				'title' => $row->title,
			);
		}
		$data['pages'] = $pages;
		return $this->success($data);
	}

	public function apiGetHppagesByUsefile2(Request $request)
	{	
		$user		= getUser();
		$data['cms_plan']=$user->getProfile()->cms_plan;
		$id		= (int)$request->id;

		$hp = getInstanceUser('cms')->getCurrentHp();
		$table = App::make(HpPageRepositoryInterface::class);
		$rowset = $table->fetchAllByUsedFile2Id($hp->id, $id);
		$pages = array();
		foreach ($rowset as $row) {
			$pages[] = array(
				'id' => $row->id,
				'title' => $row->title,
			);
		}
		$data['pages'] = $pages;
		return $this->success($data);
	}
}
