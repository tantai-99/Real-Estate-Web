<?php
/**
 * 
 * 地図オプション
 *
 */
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\Regex;
use App\Rules\StringLength;
use App\Rules\DateFormat;
use Library\Custom\Kaiin\Tanto\TantoParams;
use Library\Custom\Kaiin\Tanto\GetTanto;

class MapOptionInfo extends Form
{
	
	public function init()
	{
		// companyid
		$element = new Element\Hidden(	'id'							) ;
		$this->add( $element) ;
		
		// 利用開始申請日
		$element	= new Element\Text(	'map_applied_start_date'		) ;
		$element->setLabel(		'利用開始申請日'	) ;
        $element->addValidator( new DateFormat(array( 'messages' => '利用開始申請日が正しくありません。', 'format' => 'Y-m-d'				) ) );
		$element->setDescription( "※yyyy-mm-dd" ) ;
		$element->setAttribute( "class", "datepicker" ) ;
		$this->add( $element ) ;

		// 利用開始日
		$element	= new Element\Text(	'map_start_date'				) ;
		$element->setLabel(		'利用開始日'		) ;
        $element->addValidator( new DateFormat(array( 'messages' => '利用開始日が正しくありません。'	, 'format' => 'Y-m-d'				) ) );
		$element->setDescription( "※yyyy-mm-dd" ) ;
		$element->setAttribute( "class", "datepicker" ) ;
		$this->add( $element ) ;

		// 契約担当者ID
		$element = new Element\Text(		'map_contract_staff_id'			) ;
		$element->setLabel(		'契約担当者'	) ;
		$element->addValidator( new Regex(array( 'messages' => '半角数字のみです。'				, 'pattern' => '/^[0-9]+$/'	) ) );
		$this->add($element);

		// 契約担当者名
		$element = new Element\Text(		'map_contract_staff_name'		) ;
		$element->setLabel(		'契約担当者名'	) ;
		$element->setAttribute( "style", "width:90%;" ) ;
		$element->addValidator( new StringLength( array( 'max' => 30 ) 															) ) ;
		$this->add( $element ) ;

		// 契約担当者部署
		$element = new Element\Text(		'map_contract_staff_department'	) ;
		$element->setLabel(		'契約担当者部署'		) ;
		$element->setAttribute(	"style", "width:90%;"	) ;
		$this->add( $element ) ;
		
		// 利用停止申請日
		$element = new Element\Text(		'map_applied_end_date'		) ;
		$element->setLabel(		'利用停止申請日'	) ;
		$element->addValidator( new DateFormat(array( 'messages' => '利用停止申請日が正しくありません。', 'format' => 'Y-m-d'				) ) );
		$element->setDescription(	"※yyyy-mm-dd"			) ;
		$element->setAttribute(		"class", "datepicker"	) ;
		$this->add( $element ) ;
		
		// 利用停止日
		$element = new Element\Text(		'map_end_date'				) ;
		$element->setLabel(		'利用停止日'		) ;
		$element->addValidator( new DateFormat(array( 'messages' => '利用停止日が正しくありません。'	, 'format' => 'Y-m-d'				) ) );
		$element->setDescription(	"※yyyy-mm-dd"			) ;
		$element->setAttribute(		"class", "datepicker"	) ;
		$this->add( $element ) ;
		
		// 解約担当者ID
		$element = new Element\Text(		'map_cancel_staff_id'			) ;
		$element->setLabel(		'解約担当者'			) ;
		$element->addValidator( new Regex(array( 'messages' => '半角数字のみです。'				, 'pattern' => '/^[0-9]+$/'	 ) ) );
		$this->add( $element ) ;
		
		// 解約担当者名
		$element = new Element\Text( 		'map_cancel_staff_name'			) ;
		$element->setLabel(		'解約担当者名'			) ;
		$element->addValidator( new StringLength( array('max' => 30 )															) ) ;
		$element->setAttribute(	"style", "width:90%;"	) ;
		$this->add( $element ) ;
		
		// 解約担当者部署
		$element = new Element\Text(		'map_cancel_staff_department'	) ;
		$element->setLabel(		'解約担当者部署'		) ;
		$element->setAttribute(	"style", "width:90%;"	) ;
		$this->add( $element ) ;
		
		//備考
		$element = new Element\Textarea(	'map_remarks'					) ;
		$element->setLabel(		'備考'					) ;
		$element->setAttributes(	array( 'rows' => 5 )	) ;
		$element->addValidator(	new StringLength(array( 'max' => 1000 ) 														) );
		$this->add($element);
	}

    public function isValid( $params , $checkError = true)
    {
		$error_flg	= true							;
        $error_flg	= parent::isValid( $params )	;

		// 契約担当者IDチェック
		if( $params[ 'map' ][ 'map_contract_staff_id' ] != "" )
		{
            // 会員APIに接続して担当者情報を取得
            $tantoApiParam	= new TantoParams() ;
            $tantoApiParam->setTantoCd( $params[ 'map' ][ 'map_contract_staff_id' ] ) ;
            $tantoapiObj	= new GetTanto() ;
            $tantouInfo		= $tantoapiObj->get( $tantoApiParam, '担当者取得' ) ;
            if ( is_null( $tantouInfo ) || empty( $tantouInfo ) )
            {
                $this->getElement( 'contract_staff_id' )->setMessages( array( "契約担当者番号に誤りがあります。" ) ) ;
                $error_flg = false ;
            }
		}
		
		// 解約担当者IDチェック
		if( $params[ 'map' ][ 'map_cancel_staff_id' ] != "" )
		{
			// 会員APIに接続して担当者情報を取得
			$tantoApiParam	= new TantoParams()	;
			$tantoApiParam->setTantoCd( $params[ 'map' ][ 'map_cancel_staff_id' ]	) ;
			$tantoapiObj	= new GetTanto()		;
			$tantouInfo		= $tantoapiObj->get( $tantoApiParam, '担当者取得' )	;
			if ( is_null( $tantouInfo ) || empty( $tantouInfo ) )
			{
				$this->getElement( 'cancel_staff_id' )->setMessages( array( "解約担当者番号に誤りがあります。" ) ) ;
				$error_flg = false ;
			}
		}
		
		return $error_flg ;
	}
}
