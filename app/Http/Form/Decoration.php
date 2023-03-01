<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;

class Decoration extends Form {

	static private	$_housing	= array(
		'nochara'					=> array ( 'label' => "文字なし"						, 'pattern' => "/_nochara_/"					),
		'search_chintai'			=> array ( 'label' => "賃貸物件を探す"					, 'pattern' => "/_search_chintai_/"				),
		'search_baibai'				=> array ( 'label' => "売買物件を探す"					, 'pattern' => "/_search_baibai_/"				),
		'search_osusume'			=> array ( 'label' => "おすすめ物件"					, 'pattern' => "/_search_osusume_/"				),
		'search_pet'				=> array ( 'label' => "ペット可物件"					, 'pattern' => "/_search_pet_/"					),
		'search_single'				=> array ( 'label' => "一人暮らし向け物件"				, 'pattern' => "/_search_single_/"				),
		'search_double'				=> array ( 'label' => "二人暮らし向け物件"				, 'pattern' => "/_search_double_/"				),
		'search_family'				=> array ( 'label' => "ファミリー向け物件"				, 'pattern' => "/_search_family_/"				),
		'search_newlybuilt'			=> array ( 'label' => "新築物件"						, 'pattern' => "/_search_newlybuilt_/"			),
		'search_builtrecently'		=> array ( 'label' => "築浅物件"						, 'pattern' => "/_search_builtrecently_/"		),
		'search_newlybuilthouse'	=> array ( 'label' => "新築一戸建て"					, 'pattern' => "/_search_newlybuilthouse_/"		),
		'search_usedhome'			=> array ( 'label' => "中古一戸建て"					, 'pattern' => "/_search_usedhome_/"			),
		'search_mansion'			=> array ( 'label' => "マンション"						, 'pattern' => "/_search_mansion_/"				),
		'search_land'				=> array ( 'label' => "土地"							, 'pattern' => "/_search_land_/"				),
		'search_rentalstore'		=> array ( 'label' => "貸店舗"							, 'pattern' => "/_search_rentalstore_/"			),
		'search_rentaloffice'		=> array ( 'label' => "貸事務所"						, 'pattern' => "/_search_rentaloffice_/"		),
		'search_revenue'			=> array ( 'label' => "収益物件"						, 'pattern' => "/_search_revenue_/"				),
		'search_business'			=> array ( 'label' => "事業用物件"						, 'pattern' => "/_search_business_/"			),
		'search_renovation'			=> array ( 'label' => "リフォームリノベーション物件"	, 'pattern' => "/_search_renovation_/"			),
		'search_furnished'			=> array ( 'label' => "居抜き物件"						, 'pattern' => "/_search_furnished_/"			),
		'search_turnkey'			=> array ( 'label' => "即入居可物件"					, 'pattern' => "/_search_turnkey_/"				),
		'search_openhouse'			=> array ( 'label' => "オープンハウス情報"				, 'pattern' => "/_search_openhouse_/"			),
		'search_openroom'			=> array ( 'label' => "オープンルーム情報"				, 'pattern' => "/_search_openroom_/"			),
		'search_salesevent'			=> array ( 'label' => "現地販売会"						, 'pattern' => "/_search_salesevent_/"			),
	) ;
	
	static private	$_misc	= array(
		'nochara'					=> array ( 'label' => "文字なし"						, 'pattern' => "/_nochara_/"					),
		'stafflist'					=> array ( 'label' => "スタッフ紹介"					, 'pattern' => "/_stafflist_/"					),
		'blog'						=> array ( 'label' => "ブログ"							, 'pattern' => "/_blog_/"						),
		'voice'						=> array ( 'label' => "お客様の声"						, 'pattern' => "/_voice_/"						),
		'towninfo'					=> array ( 'label' => "街情報"							, 'pattern' => "/_towninfo_/"					),
		'selllist'					=> array ( 'label' => "売却事例"						, 'pattern' => "/_selllist_/"					),
		'owner'						=> array ( 'label' => "オーナー様へ"					, 'pattern' => "/_owner_/"						),
		'resident'					=> array ( 'label' => "入居者様へ"						, 'pattern' => "/_resident_/"					),
		'management'				=> array ( 'label' => "管理会社様へ"					, 'pattern' => "/_management_/"					),
		'intermediary'				=> array ( 'label' => "仲介会社様へ"					, 'pattern' => "/_intermediary_/"				),
		'school'					=> array ( 'label' => "学区情報"						, 'pattern' => "/_school_/"						),
		'glossary'					=> array ( 'label' => "不動産用語集"					, 'pattern' => "/_glossary_/"					),
		'faq'						=> array ( 'label' => "よくあるご質問"					, 'pattern' => "/_faq_/"						),
		'link'						=> array ( 'label' => "リンク集"						, 'pattern' => "/_link_/"						),
		'siryou_seikyu'				=> array ( 'label' => "資料請求はこちら"				, 'pattern' => "/_siryou-seikyu_/"				),
		'satei_irai'				=> array ( 'label' => "査定依頼はこちら"				, 'pattern' => "/_satei-irai_/"					),
		'contact'					=> array ( 'label' => "お問い合わせ"					, 'pattern' => "/_contact_/"					),
		'eventlist'					=> array ( 'label' => "イベント情報"					, 'pattern' => "/_eventlist_/"					),
		'recruit'					=> array ( 'label' => "採用情報"						, 'pattern' => "/_recruit_/"					),
		'search_construction'		=> array ( 'label' => "施工事例"						, 'pattern' => "/_search_construction_/"		),
		'search_webflyer'			=> array ( 'label' => "WEBチラシ情報"					, 'pattern' => "/_search_webflyer_/"			),
		'search_application'		=> array ( 'label' => "各種申請書類"					, 'pattern' => "/_search_application_/"			),
		'search_vacanthouse'		=> array ( 'label' => "空き家対策"						, 'pattern' => "/_search_vacanthouse_/"			),
		'search_memberpage'			=> array ( 'label' => "会員ページ"						, 'pattern' => "/_search_memberpage_/"			),
		'search_registration'		=> array ( 'label' => "会員登録はこちら"				, 'pattern' => "/_search_registration_/"		),
		'realestate_tips'		    => array ( 'label' => "不動産お役立ち情報"				, 'pattern' => "/_realestate_tips_/"		),
		'sell_contents'		        => array ( 'label' => "売却コンテンツ"				    , 'pattern' => "/_sell_contents_/"		),
		'buy_contents'		        => array ( 'label' => "購入コンテンツ"				    , 'pattern' => "/_buy_contents_/"		),
		'forowners_contents'		=> array ( 'label' => "不動産オーナー様向けコンテンツ"	, 'pattern' => "/_forowners_contents_/"		),
		'rentbusiness_contents'		=> array ( 'label' => "賃貸事業用コンテンツ"  	        , 'pattern' => "/_rentbusiness_contents_/"		),
		'rent_contents'		        => array ( 'label' => "賃貸コンテンツ"  	            , 'pattern' => "/_rent_contents_/"		),
	) ;
	
	protected $_checkBoxes	;
	protected $_params		;
	
	public function __construct( $params = array() )
	{
		$kind				= isset( $params[ 'kind' ] ) ? $params[ 'kind' ] : '_housing' ;
		$kind				= ( $kind == '_misc' )       ? $params[ 'kind' ] : '_housing' ;
		$this->_checkBoxes	= self::$$kind		;
		$this->_params		= $params			;
		parent::__construct();
	}
	
	public function init()
	{
		foreach ( $this->_checkBoxes as $name => $box )
		{
			$element = new Element\Checkbox( $name	) ;
			$element->setLabel(		$box[ 'label' ] 			) ;
			$element->setAttribute( 	"class"	, "step1"			) ;
			$element->setChecked(	@$this->_params[ $name ]	) ;
			$this->add( $element )	;
		}
	}

	public function getPattern( $key )
	{
		return $this->_checkBoxes[ $key ][ 'pattern' ] ;
	}
}