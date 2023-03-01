<?php
namespace Modules\Admin\Http\Controllers;

use App;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Library\Custom\Form;
use Library\Custom\User\Admin;
use Library\Custom\Model\Lists\Original;
use Modules\Admin\Http\Form\MapOptionInfo;
use App\Repositories\Company\CompanyRepositoryInterface;

class MapOptionController extends Controller
{
	protected $companyRepository;
	public function init($request, $next) {
		$this->companyRepository = App::make(CompanyRepositoryInterface::class);
		return $next($request);
	}
	/**
	 * 編集表示
	 */
    public function edit(Request $request)
    {
    	
    	//パンクズ設定
    	$pan_arr = array( "id" => $request->id ) ;
    	$this->view->topicPath( '契約管理'	, "index"	, "company"				) ;
    	$this->view->topicPath( "契約者詳細", "detail"	, "company", $pan_arr	) ;
    	$this->view->topicPath( "地図検索"										) ;
    	
		//設定系の情報取得
		$company_config = getConfigs('admin.company');
		//API系のURIなど
		$defailt_backbone		= $company_config->backbone->api	;
		$this->view->backbone	= $defailt_backbone					;

		//フォーム設定
		$this->view->form = $form = new Form();
		$form->addSubForm(new MapOptionInfo()				, 'map'			) ;
		//パラメータ取得
		$params		= $request->all()	;
		
		//登録ボタン押下時
		if( $request->has( "asd" ) && $request->asd != "" )
		{
			//バリデーション
			$form->setData($params);
			$map = $form->getSubForm('map');
			if( $form->isValid( $params ) )
			{
				$error_flg = false;
				// 利用日チェック
				if( $params[ 'map' ][ 'map_applied_start_date'] != "" && $params[ 'map' ][ 'map_start_date' ] != "" )
				{
					$applied_start_date	= str_replace( "-", "", $params[ 'map' ][ 'map_applied_start_date'	] ) ;
					$start_date			= str_replace( "-", "", $params[ 'map' ][ 'map_start_date'			] ) ;
					if( $applied_start_date > $start_date )
					{
						$map->getElement('map_applied_start_date')->setMessages( array("利用開始申請日は、利用開始日より過去日を設定してください。") );
						$error_flg = true ;
					}
				}

				// 利用日チェック
				if( $params[ 'map' ][ 'map_applied_end_date' ] != "" && $params[ 'map' ][ 'map_end_date' ] != "" )
				{
					$applied_end_date	= str_replace( "-", "", $params[ 'map' ][ 'map_applied_end_date'	] ) ;
					$end_date			= str_replace( "-", "", $params[ 'map' ][ 'map_end_date'			] ) ;
					if( $applied_end_date > $end_date )
					{
						$map->getElement('map_applied_end_date')->setMessages( array("利用停止申請日は、利用停止日より過去日を設定してください。") );
						$error_flg = true ;
					}
				}

				// 利用開始日と利用停止日のチェック
				if( $params[ 'map' ][ 'map_start_date' ] != "" && $params[ 'map' ][ 'map_end_date' ] != "" )
				{
					$start	= str_replace( "-", "", $params[ 'map'	][ 'map_start_date'	] ) ;
					$end	= str_replace( "-", "", $params[ 'map'	][ 'map_end_date'	] ) ;
					if( $start > $end )
					{
						$map->getElement('map_end_date')->setMessages( array("利用停止日は、利用開始日より未来日を設定してください。") );
						$error_flg = true ;
					}
				}
				
				// 解約担当者系の設定
				if( $params[ 'map' ][ 'map_cancel_staff_id' ] != "" && ( $params[ 'map' ][ 'map_cancel_staff_name' ] == "" || $params[ 'map' ][ 'map_cancel_staff_department' ] == "" ) )
				{
					$map->getElement( 'map_cancel_staff_name'	)->setMessages( array("解約担当者名が設定されていません。参照ボタンより取得してください。") );
					$error_flg = true ;
				} else
				if( $params[ 'map' ][ 'map_cancel_staff_id' ] == "" && ( $params[ 'map' ][ 'map_cancel_staff_name' ] != "" || $params[ 'map' ][ 'map_cancel_staff_department' ] != "" ) )
				{
					$map->getElement( 'map_cancel_staff_id'	)->setMessages( array("解約担当者が設定されていません。") );
					$error_flg = true ;
				}

                // 4304: Check start date plan future
                if ($request->has("id") && $request->id != "") {
                    $row = array();
                    $row = $this->companyRepository->getDataForId($request->id)->toArray();
                    if ($row != null) {
                        if (($row['cms_plan'] == config('constants.cms_plan.CMS_PLAN_LITE') || $row['cms_plan'] == config('constants.cms_plan.CMS_PLAN_ADVANCE')) && $row['reserve_cms_plan'] == config('constants.cms_plan.CMS_PLAN_STANDARD') && $params['map']['map_start_date'] != "") {
                            $startDate = str_replace("-", "", $params['map']['map_start_date']);
                            $reserveStartDate = substr($row[ 'reserve_start_date'], 0, 10);
                            $reserveStartDate = str_replace("-", "", $reserveStartDate);
                            if ($startDate < $reserveStartDate) {
                                $map->getElement('map_start_date')->setMessages(array("利用開始日はプラン契約適用日以降の日付を設定してください。"));
                                $error_flg = true ;
                            }
                        }
                    }
                }

				if( !$error_flg )
				{
					//submit削除
					$request->input( "asd"	, "" ) ;
					$request->input( "back", "" ) ;	
					return view('admin::map-option.conf');
				}
			}

			//チェックが終わったら、必須系を戻す（見た目が気持ち悪い感じになるので）
			foreach( $map->getElements() as $key => $element )
			{
				if( !in_array( $key, array( 'map_applied_end_date', 'map_end_date', 'map_cancel_staff_id', 'map_cancel_staff_name', 'map_cancel_staff_department', 'map_remarks' ) ) )
				{
					$element->setRequired( true ) ;
				}
			}

		} elseif( $request->has("submit_regist") && $request->submit_regist != "" )
		{
			
			$conf_error_str = array();

			if( $request->has( "conf_error" ) && ( $request->conf_error != "" ) )
			{
				$request->input( "conf_error_str", $conf_error_str ) ;
				return $this->edit($request);
			}

			DB::beginTransaction()					;

			// 契約者登録
			unset( $params[ "module"		] ) ;
			unset( $params[ "controller"	] ) ;
			unset( $params[ "action"		] ) ;
			unset( $params[ "submit"		] ) ;
			unset( $params[ "submit_regist" ] ) ;

			$row = $this->companyRepository->getDataForId( $params[ 'map' ][ "id" ] ) ;
			if( $row == null )
			{
				throw new Exception( "No Company Data." ) ;
				return ;
			}

			unset( $row->delete_flg		) ;
			unset( $row->create_id		) ;
			unset( $row->create_date	) ;
			unset( $row->update_date	) ;

			//契約者更新
			$no_update_arr = array( "id", "delete_flg", "create_id", "create_date", "update_id", "update_date" ) ;

			foreach( $params as $name => $arr )
			{
				if ($name == '_token') continue;
				if ($name == 'id') continue;
				foreach( $arr as $key => $val )
				{
					if ( $key == "id" 				) continue	;
					if ( $key == "map_applied_start_date"	&& $val == "" ) $val = Null ;
					if ( $key == "map_start_date"			&& $val == "" ) $val = Null ;
					if ( $key == "map_applied_end_date"		&& $val == "" ) $val = Null ;
					if ( $key == "map_end_date"				&& $val == "" ) $val = Null ;
					$row->$key = $val ;
				}
			}
			$row->save() ;
			$id = $params['id'];

			DB::commit() ;
			return redirect( "/admin/map-option/comp/id/{$id}" ) ;
		} else if ( $request->has( "back" ) && $request->back != "" )
		{
			// 戻るボタン押下時
			unset( $params[ 'back' ] ) ;
			$form->setData( $params ) ;

		} elseif ($request->has( "back_map" ) && $request->back_map != "") {
			// 戻るボタン押下時
			unset( $params[ 'back_map' ] ) ;
			$form->setData( $params ) ;
		} else if ( $request->has( "conf_error" ) && $request->conf_error != "" )
		{
			// 確認画面でエラーになった場合
			$form->setData( $params ) ;
			// エラー内容の設定
			foreach( $request->conf_error_str as $name => $data )
			{
				foreach( $data as $key => $val )
				{
					$form->getSubForm($name)->getElement( $key )->setMessages( array($val) );
				}
			}
			unset( $params[ 'conf_error'		] ) ;
			unset( $params[ 'conf_error_str'	] ) ;

		// 初期データ取得時
		}else if( $request->has("id") && $request->id != "" )
		{
			// 契約者情報の取得
			$row = array() ;
			$row = $this->companyRepository->getDataForId( $request->id )->toArray() ;
			
			if ( $row != null )
			{
				// 日付周りの調整
				$date = substr( $row[ 'map_applied_start_date'	], 0, 10 ) ;
				$row[ 'map_applied_start_date'	] = ( ( $date == "0000-00-00" ) ? "" : $date	) ;
				
				$date = substr( $row[ 'map_start_date'			], 0, 10 ) ;
				$row[ 'map_start_date'			] = ( ( $date == "0000-00-00" ) ? "" : $date	) ;
				
				$date = substr( $row[ 'map_applied_end_date'	], 0, 10 ) ;
				$row[ 'map_applied_end_date'	] = ( ( $date == "0000-00-00" ) ? "" : $date	) ;
				
				$date = substr( $row[ 'map_end_date'			], 0, 10 ) ;
				$row[ 'map_end_date'			] = ( ( $date == "0000-00-00" ) ? "" : $date	) ;
			}

			$form->setData( $row ) ;
		}

		$this->view->params = $params ;

		return view('admin::map-option.edit');

    }

	/**
	 * 新規登録・編集完了表示
	 */
    public function comp(Request $request) {

		//パンクズ設定
		$company_id = $request->id;
    	$pan_arr = array( "id" => $company_id) ;
    	$this->view->topicPath( '契約管理'	, "index"	, "company"				) ;
    	$this->view->topicPath( "契約者詳細", "detail"	, "company", $pan_arr	) ;
    	$this->view->topicPath( "地図検索設定完了"								) ;

		//パラメータ取得
		$this->view->company_id = $company_id;

		//契約者情報の取得
		$row = $this->companyRepository->getDataForId($company_id);
		$this->view->contract_type		= $row[ "contract_type"			] ;
		$this->view->reserve_cms_plan	= $row[ "reserve_cms_plan"		] ;
		$this->view->cms_plan			= $row[ "cms_plan"				] ;
		if($row == null) {
			throw new Exception("No Company Data. ");
			exit;
		}
		$this->view->contract_type = $row["contract_type"];
        ;
        $isAdmin = Admin::getInstance()->checkIsSuperAdmin(Admin::getInstance()->getProfile());
        $isAgency = Admin::getInstance()->isAgency();

        // if ($row->checkTopOriginal() && !$this->checkRedirectTopOriginal($row, $company_id, $isAdmin, $isAgency)) {
        //     $this->view->original_plan = true;
		// }
		$this->view->original = Original::getInstance();
        $this->view->original_setting_title = Original::getOriginalSettingTitle();
        $this->view->original_edit_title = Original::getOriginalEditTitle();
		$this->view->original_tag = Original::getEffectMeasurementTitle();
		
		return view('admin::map-option.comp');
	}

}

