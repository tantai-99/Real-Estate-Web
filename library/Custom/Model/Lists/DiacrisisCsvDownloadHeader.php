<?php
namespace Library\Custom\Model\Lists;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Model\Estate\FdpType;
use Library\Custom\Assessment\Features;
class DiacrisisCsvDownloadHeader extends ListAbstract {
	
	static public	$pageTypeList	=	array(
		array(
			HpPageRepository::TYPE_TOP											,	// トップページ
			HpPageRepository::TYPE_COMPANY										,	// 会社紹介
			HpPageRepository::TYPE_HISTORY										,	// 会社沿革
			HpPageRepository::TYPE_GREETING										,	// 代表挨拶
			HpPageRepository::TYPE_RECRUIT										,	// 採用情報
			HpPageRepository::TYPE_SHOP_DETAIL									,	// 店舗詳細
			HpPageRepository::TYPE_STAFF_DETAIL									,	// スタッフ詳細
			HpPageRepository::TYPE_OWNER										,	// オーナーさま向け
			HpPageRepository::TYPE_CORPORATION									,	// 法人向け
			HpPageRepository::TYPE_TENANT										,	// 入居者さま向け
			HpPageRepository::TYPE_BROKER										,	// 仲介会社さま向け
			HpPageRepository::TYPE_PROPRIETARY									,	// 管理会社さま向け
			HpPageRepository::TYPE_BLOG_DETAIL									,	// ブログ詳細
			HpPageRepository::TYPE_FREE											,	// フリーページ
			HpPageRepository::TYPE_MEMBERONLY									,	// 会員さま専用ページ
			HpPageRepository::TYPE_CITY											,	// 街情報
			HpPageRepository::TYPE_CUSTOMERVOICE_DETAIL							,	// お客様の声詳細
			HpPageRepository::TYPE_SELLINGCASE_DETAIL							,	// 売却事例詳細
			HpPageRepository::TYPE_EVENT_DETAIL									,	// イベント情報詳細
			HpPageRepository::TYPE_QA											,	// よくあるご質問
			HpPageRepository::TYPE_LINKS										,	// リンク集
			HpPageRepository::TYPE_SCHOOL										,	// 学区情報
			HpPageRepository::TYPE_PREVIEW										,	// 内見時のチェックポイント
			HpPageRepository::TYPE_MOVING										,	// 引っ越しのチェックポイント
			HpPageRepository::TYPE_TERMINOLOGY									,	// 不動産用語集
			HpPageRepository::TYPE_RENT											,	// 住まいを借りる契約の流れ
			HpPageRepository::TYPE_LEND											,	// 住まいを貸す契約の流れ
			HpPageRepository::TYPE_BUY											,	// 住まいを買う契約の流れ
			HpPageRepository::TYPE_SELL											,	// 住まいを売る契約の流れ
			HpPageRepository::TYPE_INFO_DETAIL									,	// お知らせ
			HpPageRepository::TYPE_PRIVACYPOLICY								,	// プライバシーポリシー
			HpPageRepository::TYPE_SITEPOLICY									,	// サイトポリシー
			HpPageRepository::TYPE_FORM_CONTACT									,	// お問い合わせ
			HpPageRepository::TYPE_FORM_DOCUMENT								,	// 資料請求
			HpPageRepository::TYPE_FORM_ASSESSMENT								,	// 査定依頼
			HpPageRepository::TYPE_FORM_LIVINGLEASE								,	// 物件問合せ（居住用賃貸）
			HpPageRepository::TYPE_FORM_OFFICELEASE								,	// 物件問合せ（事業用賃貸）
			HpPageRepository::TYPE_FORM_LIVINGBUY								,	// 物件問合せ（居住用売買）
			HpPageRepository::TYPE_FORM_OFFICEBUY								,	// 物件問合せ（事業用売買）
			HpPageRepository::TYPE_LINK											,	// リンク
			HpPageRepository::TYPE_ALIAS										,	// 内部リンク
			HpPageRepository::TYPE_SITEMAP										,	// サイトマップ
		),
		array(
			HpPageRepository::TYPE_BUSINESS_CONTENT								,	// 事業内容
			HpPageRepository::TYPE_COMPANY_STRENGTH								,	// 当社の思い・強み
			HpPageRepository::TYPE_COLUMN_DETAIL								,	// コラム詳細
			HpPageRepository::TYPE_PURCHASING_REAL_ESTATE						,	// 「買取り」を利用してスムーズに不動産売却
			HpPageRepository::TYPE_REPLACEMENTLOAN_MORTGAGELOAN					,	// 家を買い替える強い味方「買い替えローン」
			HpPageRepository::TYPE_REPLACEMENT_AHEAD_SALE						,	// 家の買い替えは、購入が先か売却が先か？
			HpPageRepository::TYPE_BUILDING_EVALUATION							,	// 中古戸建てはどのように評価されるのか？
			HpPageRepository::TYPE_BUYER_VISITS_DETACHEDHOUSE					,	// 現地見学で物件をアピールする方法あれこれ
			HpPageRepository::TYPE_POINTS_SALE_OF_CONDOMINIUM					,	// マンションを有利な条件で売却する戦術とは
			HpPageRepository::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE			,	// マンションVS一戸建て 選び方の基準は？
			HpPageRepository::TYPE_NEWCONSTRUCTION_OR_SECONDHAND				,	// 新築と中古どちらを買う？その違いを知ろう
			HpPageRepository::TYPE_ERECTIONHOUSING_ORDERHOUSE					,	// 建売住宅と注文住宅の特徴と違いとは？
			HpPageRepository::TYPE_PURCHASE_BEST_TIMING							,	// マイホームはいつ買う？判断する3つの基準
			HpPageRepository::TYPE_LIFE_PLAN									,	// 住宅資金の前にライフプランを考えよう
			HpPageRepository::TYPE_TYPES_MORTGAGE_LOANS							,	// 住宅ローンにはどんな種類がある？
			HpPageRepository::TYPE_FUNDING_PLAN									,	// 資金計画を考えよう！ 諸費用も忘れずに
			HpPageRepository::TYPE_TROUBLED_LEASING_MANAGEMENT					,	// 賃貸管理はプロに任せるのが安心な理由
			HpPageRepository::TYPE_LEASING_MANAGEMENT_MENU						,	// 賃貸管理サービスについて
			HpPageRepository::TYPE_MEASURES_AGAINST_VACANCIES					,	// 空室対策の基本ポイント
			HpPageRepository::TYPE_HOUSE_REMODELING								,	// ライバル物件に差をつけるリフォーム活用法
			HpPageRepository::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER				,	// なぜ土地活用が必要なのか
			HpPageRepository::TYPE_UTILIZING_LAND								,	// 土地活用方法それぞれの魅力とは
			HpPageRepository::TYPE_PURCHASE_INHERITANCE_TAX						,	// 不動産の購入が相続税対策に有効な理由
			HpPageRepository::TYPE_UPPER_LIMIT									,	// 家賃の上限はどれくらい？考慮すべきは何？
			HpPageRepository::TYPE_RENTAL_INITIAL_COST							,	// 賃貸住宅の初期費用には何がある？
			HpPageRepository::TYPE_SQUEEZE_CANDIDATE							,	// 賃貸住み替え、物件を絞り込む3ステップ
			HpPageRepository::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE				,	// 引越しのときのゴミはどうやって処分する？
			HpPageRepository::TYPE_COMFORTABLELIVING_RESIDENT_RULES				,	// 快適に暮らすために居住ルールを確認しよう
			HpPageRepository::TYPE_STORE_SEARCH									,	// 商圏調査の基本とは？長く続けるお店づくり
			HpPageRepository::TYPE_SHOP_SUCCESS_BUSINESS_PLAN					,	// 店舗開業成功のカギ！事業計画書の作り方
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE						,	// 物件リクエスト（居住用賃貸）
			HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE						,	// 物件リクエスト（事業用賃貸）
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY						,	// 物件リクエスト（居住用売買）
			HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY						,	// 物件リクエスト（事業用売買）
		),
	) ;

	/**
	 * データ取得用のヘッダー名一覧
	 */
	public static function getCsvHeader() {

		return array(
//			'id',
			'member_no',
			'contract_type',
			'member_name',
			'company_name',
			'domain',
			'applied_start_date',
			'start_date',
			'contract_staff_id',
			'contract_staff_name',
			'contract_staff_department',
			'applied_end_date',
			'end_date',
			'cancel_staff_id',
			'cancel_staff_name',
			'cancel_staff_department',
			'ftp_server_name',
			'ftp_server_port',
			'ftp_user_id',
			'ftp_password',
			'ftp_directory',
			'ftp_pasv_flg',
			'cp_url',
			'cp_user_id',
			'cp_password',
			'cms_id',
			'cms_password',
			'create_date',
			'update_date',
			'release_date',
			'published_stop_date',
			'remarks',
			'cp_password_used_flg',
			'first_publish_date'
		);
	}

	/**
	 * CSV表示用ヘッダー名一覧
	 */
	public static function getCsvHeaderName() {

		$header = array(
			'会員No',
			'テーマ',
			'ベースカラー',
			'レイアウト',
			'階層外のページ',
			'公開設定',
			'総合点数',
			'更新 - 点数（1～5）',
			'更新 - サイト全体の日付',
			'更新 - お知らせ の日付',
			'ページ作成 - 点数（1～5）',
			'ページ作成 - 「公開」ページ数',
			'ページ作成 - 「下書き」ページ数',
			'ページ作成 - 「未作成」かどうか',
			'機能設定 - 点数（1～5）',
			'機能設定 - 「登録」項目数',
			'機能設定 - 「未登録」かどうか'
		);

		//ページ作成系
		$page_types = \App::make(HpPageRepositoryInterface::class)->getTypeListJp() ;
		$site_header = [];
		$pagePart = 0 ;
		self::_setPageHeader( $header, $page_types, $pagePart ) ;

		//機能設定系
		$header = array_merge($header, Features::getFeatureNameAll());

		$header = array_merge($header, array(
			'問合せ件数（問い合わせ）',
			'問合せ件数（資料請求）',
			'問合せ件数（売却査定）',
			'使用容量(MB)',
			'ログイン日',
	));
		
	self::_setPageHeader( $header, $page_types, $pagePart ) ;
	
        $header = array_merge($header, array(
            'ウェブクリップアイコン',
            'フッターリンク一覧',
        ));

        // 4174 CMS内のFDP項目を設定している会員の出力
        self::_setFDPHeader( $header );

        // 5422 HPAD_DIACRISISに200記事の項目を追加する
        self::_setArticleHeader( $header );

        // ATHOME_HP_DEV-5739 HPAD_DIACRISISに物件詳細リンクの項目を追加する
        $header = array_merge($header, array(
            '内部リンク（物件詳細） -「公開中」ページ数',
            '内部リンク（物件詳細） - 「下書き(非公開)」ページ数',
        ));

		// ATHOME_HP_DEV-6172 HPAD_DIACRISISに反響プラスの項目を追加する
        $header = array_merge($header, array('反響プラス', 'プライバシーポリシー'));

		return $header;
	}
	
	/**
	 * ページのヘッダーをセットする
	 */
	protected static function _setPageHeader( &$header, &$page_types, &$pagePart )
	{
		foreach ( self::$pageTypeList[ $pagePart ]  as $page_type )
		{
			switch ($page_type) {
				case HpPageRepository::TYPE_MOVING:
					$header[]	= "引っ越しのチェックポイント -「公開中」ページ数"				;
					$header[]	= "引っ越しのチェックポイント - 「下書き(非公開)」ページ数"	;
					break;
				case HpPageRepository::TYPE_BUILDING_EVALUATION:
					$header[]	= "中古戸建てはどのように評価されるのか？ -「公開中」ページ数"				;
					$header[]	= "中古戸建てはどのように評価されるのか？ - 「下書き(非公開)」ページ数"	;
					break;
				case HpPageRepository::TYPE_LINK:
					$header[]	= "リンク -「公開中」ページ数"				;
					$header[]	= "リンク - 「下書き(非公開)」ページ数"	;
					break;
				default:
					$header[]	= $page_types[ $page_type ] ." -「公開中」ページ数"				;
					$header[]	= $page_types[ $page_type ] ." - 「下書き(非公開)」ページ数"	;
					break;
			}
		}
		$pagePart++	;
	}
    
    /**
	 * set header FDP
	 */
	protected static function _setFDPHeader( &$header)
	{
        $label = 'FDP';
        foreach (ClassList::getInstance()->getAll() as $labelClass) {
            foreach (FdpType::getFdp() as $labelFdp) {
                $header[] = $label.'（'.$labelClass.'）- '.$labelFdp;
            }
            foreach (FdpType::getTown() as $labelTown) {
                $header[] = $label.'（'.$labelClass.'）- '.FdpType::getFdp()[FdpType::TOWN_TYPE].'（'.$labelTown.'）';
            }

        }
    }
    
    /**
	 * set header article page
	 */
    protected static function _setArticleHeader ( &$header ) {
        $table = \App::make(HpPageRepositoryInterface::class);
        $original = $table->getArticleOriginal();
        foreach($table->getAllPagesUsefulEstate() as $page_type) {
            if (in_array($page_type, $original)) {
                continue;
            }
            $header[] = $table->getTypeNameJp($page_type);
        }
        foreach($original as $page_type) {
            $header[] = $table->getTypeNameJp($page_type);
        }
    }
}
