<?php
namespace Library\Custom\Plan;
use Library\Custom\Plan;
use App\Repositories\HpPage\HpPageRepository;
/**
 *	アドバンス・プランの情報クラス
 */
class Advance	extends Plan
{
	public 	$initialPages	=	array(										// 初期のページ構成
		'main'	=> array(														// メインメニュー
			HpPageRepository::TYPE_TOP					=> array(),			//TOP
			HpPageRepository::TYPE_COMPANY				=> array(			// 会社紹介
				HpPageRepository::TYPE_HISTORY			=> array(),				// 会社遠隔
				HpPageRepository::TYPE_GREETING			=> array(),				// 代表挨拶
				HpPageRepository::TYPE_BUSINESS_CONTENT	=> array(),				// 事業内容
				HpPageRepository::TYPE_COMPANY_STRENGTH	=> array(),				// 当社の思い・強み
				HpPageRepository::TYPE_EVENT_INDEX		=> array(				// イベント情報一覧
					HpPageRepository::TYPE_EVENT_DETAIL 	=> array(),				// イベント情報詳細
				),
				HpPageRepository::TYPE_RECRUIT			=> array(),				// 採用情報
			),
			HpPageRepository::TYPE_SHOP_INDEX			=> array(			// 店舗案内
				HpPageRepository::TYPE_SHOP_DETAIL			=> array(			// 店舗詳細
					HpPageRepository::TYPE_STAFF_INDEX			=> array(			// スタッフ紹介一覧
						HpPageRepository::TYPE_STAFF_DETAIL			=> array(),			// スタッフ紹介一覧
					),
				),
			),
			HpPageRepository::TYPE_CUSTOMERVOICE_INDEX	=> array(			// お客様の声
				HpPageRepository::TYPE_CUSTOMERVOICE_DETAIL	=> array(),			// お客様の声詳細
			),
			HpPageRepository::TYPE_CITY					=> array(),			// 街情報
			HpPageRepository::TYPE_BLOG_INDEX			=> array(),			// ブログ一覧
			HpPageRepository::TYPE_COLUMN_INDEX			=> array(),			// コラム一覧
			HpPageRepository::TYPE_SELLINGCASE_INDEX	=> array(			// 売却事例
				HpPageRepository::TYPE_SELLINGCASE_DETAIL	=> array(),			// 売却事例詳細
			),
			HpPageRepository::TYPE_CORPORATION			=> array(),			// 法人様向け
			HpPageRepository::TYPE_OWNER				=> array(),			// オーナー向け
			HpPageRepository::TYPE_TENANT				=> array(),			// 入居者向け
			HpPageRepository::TYPE_PROPRIETARY			=> array(),			// 管理会社向け
			HpPageRepository::TYPE_BROKER				=> array(),			// 仲介会社向け
			HpPageRepository::TYPE_SCHOOL				=> array(),			// 学区情報
			HpPageRepository::TYPE_QA					=> array(),			// Q&A
            HpPageRepository::TYPE_TERMINOLOGY			=> array(),			// 不動産用語集
            // 5352 ページの作成/更新に旧ひな形を追加できないようにする
			// HpPageRepository::TYPE_RENT					=> array(),			// 住まいを借りる
			// HpPageRepository::TYPE_LEND					=> array(),			// 住まいを貸す
			// HpPageRepository::TYPE_BUY					=> array(),			// 住まいを買う
			// HpPageRepository::TYPE_SELL					=> array(),			// 住まいを売る
			// HpPageRepository::TYPE_PREVIEW				=> array(),			// 内見時のチェックポイント
			// HpPageRepository::TYPE_MOVING				=> array(),			// 引越しのチェックポイント
            HpPageRepository::TYPE_LINKS				=> array(),			// リンク集
            HpPageRepository::TYPE_FORM_DOCUMENT		=> array(),			// 資料請求
			HpPageRepository::TYPE_FORM_ASSESSMENT		=> array(),			// 査定依頼
			// 物件リクエスト
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE		=> array(),		// 物件リクエスト 居住用賃貸物件フォーム
			HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE		=> array(),		// 物件リクエスト 事務所用賃貸物件フォーム
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY		=> array(),		// 物件リクエスト 居住用売買物件フォーム
			HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY		=> array(),		// 物件リクエスト 事務所用売買物件フォーム

		),
		'fix'	=> array(														// 固定メニュー
			HpPageRepository::TYPE_INFO_INDEX			=> array(),			// お知らせ
			HpPageRepository::TYPE_PRIVACYPOLICY		=> array(),			// プライバシーポリシー
            HpPageRepository::TYPE_SITEPOLICY			=> array(),			// サイトポリシー
            // HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION => array(),
            HpPageRepository::TYPE_FORM_CONTACT			=> array(),			// 会社問い合わせ
            
		),
	) ;
	
	public	$categoryMap	=	array(
		HpPageRepository::CATEGORY_TOP			=> array( HpPageRepository::TYPE_TOP			),
		HpPageRepository::CATEGORY_COMPANY 		=> array(
			HpPageRepository::TYPE_COMPANY					,
			HpPageRepository::TYPE_HISTORY					,
			HpPageRepository::TYPE_GREETING					,
			HpPageRepository::TYPE_RECRUIT					,
			HpPageRepository::TYPE_SHOP_INDEX				,
			HpPageRepository::TYPE_SHOP_DETAIL				,
			HpPageRepository::TYPE_STAFF_INDEX				,
			HpPageRepository::TYPE_STAFF_DETAIL				,

            //CMSテンプレートパターンの追加
            HpPageRepository::TYPE_BUSINESS_CONTENT,
            HpPageRepository::TYPE_COMPANY_STRENGTH,

		),
		HpPageRepository::CATEGORY_STRUCTURE	=> array(),
		HpPageRepository::CATEGORY_OWNER		=> array( HpPageRepository::TYPE_OWNER			),
		HpPageRepository::CATEGORY_CORPORATION	=> array( HpPageRepository::TYPE_CORPORATION	),
		HpPageRepository::CATEGORY_FOR			=> array(
			HpPageRepository::TYPE_TENANT					,
			HpPageRepository::TYPE_BROKER					,
			HpPageRepository::TYPE_PROPRIETARY				,
		),
		HpPageRepository::CATEGORY_BLOG			=> array(
			HpPageRepository::TYPE_BLOG_INDEX				,
			HpPageRepository::TYPE_BLOG_DETAIL				,
		),
		HpPageRepository::CATEGORY_COLUMN        => array(
			HpPageRepository::TYPE_COLUMN_INDEX				,
			HpPageRepository::TYPE_COLUMN_DETAIL			,
		),

        // 売却コンテンツ
        HpPageRepository::CATEGORY_SALE => array(
            HpPageRepository::TYPE_PURCHASING_REAL_ESTATE,
            HpPageRepository::TYPE_REPLACEMENTLOAN_MORTGAGELOAN,
            HpPageRepository::TYPE_REPLACEMENT_AHEAD_SALE,
            HpPageRepository::TYPE_BUILDING_EVALUATION,
            HpPageRepository::TYPE_BUYER_VISITS_DETACHEDHOUSE,
            HpPageRepository::TYPE_POINTS_SALE_OF_CONDOMINIUM,
        ),
        // 購入コンテンツ
        HpPageRepository::CATEGORY_PURCHASE => array(
            HpPageRepository::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
            HpPageRepository::TYPE_NEWCONSTRUCTION_OR_SECONDHAND,
            HpPageRepository::TYPE_ERECTIONHOUSING_ORDERHOUSE,
            HpPageRepository::TYPE_PURCHASE_BEST_TIMING,
            HpPageRepository::TYPE_LIFE_PLAN,
            HpPageRepository::TYPE_TYPES_MORTGAGE_LOANS,
            HpPageRepository::TYPE_FUNDING_PLAN,
        ),
        // オーナー向けコンテンツ〈賃貸管理〉
        HpPageRepository::CATEGORY_OWNERS_RENTAL_MANAGEMENT => array(
            HpPageRepository::TYPE_TROUBLED_LEASING_MANAGEMENT,
            HpPageRepository::TYPE_LEASING_MANAGEMENT_MENU,
            HpPageRepository::TYPE_MEASURES_AGAINST_VACANCIES,
            HpPageRepository::TYPE_HOUSE_REMODELING,
            HpPageRepository::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER,
            HpPageRepository::TYPE_UTILIZING_LAND,
            HpPageRepository::TYPE_PURCHASE_INHERITANCE_TAX,
        ),
        // 居住用賃貸コンテンツ
        HpPageRepository::CATEGORY_RESIDENTIAL_RENTAL        => array(
            HpPageRepository::TYPE_UPPER_LIMIT,
            HpPageRepository::TYPE_RENTAL_INITIAL_COST,
            HpPageRepository::TYPE_SQUEEZE_CANDIDATE,
            HpPageRepository::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE,
            HpPageRepository::TYPE_COMFORTABLELIVING_RESIDENT_RULES,
        ),
        // 事業用賃貸コンテンツ
        HpPageRepository::CATEGORY_BUSINESS_LEASE        => array(
            HpPageRepository::TYPE_STORE_SEARCH,
            HpPageRepository::TYPE_SHOP_SUCCESS_BUSINESS_PLAN,
        ),
        //CMSテンプレートパターンの追加


		HpPageRepository::CATEGORY_FREE			=> array( HpPageRepository::TYPE_FREE			),
		HpPageRepository::CATEGORY_MEMBER_ONLY	=> array( HpPageRepository::TYPE_MEMBERONLY		),
		HpPageRepository::CATEGORY_OTHER		=> array(
			HpPageRepository::TYPE_CITY						,
			HpPageRepository::TYPE_CUSTOMERVOICE_INDEX		,
			HpPageRepository::TYPE_CUSTOMERVOICE_DETAIL		,
			HpPageRepository::TYPE_SELLINGCASE_INDEX		,
			HpPageRepository::TYPE_SELLINGCASE_DETAIL		,
			HpPageRepository::TYPE_EVENT_INDEX				,
			HpPageRepository::TYPE_EVENT_DETAIL				,
			HpPageRepository::TYPE_QA						,
			HpPageRepository::TYPE_LINKS					,
            HpPageRepository::TYPE_SCHOOL					,
            HpPageRepository::TYPE_TERMINOLOGY				,
			HpPageRepository::TYPE_PREVIEW					,
			HpPageRepository::TYPE_MOVING					,
			HpPageRepository::TYPE_RENT						,
			HpPageRepository::TYPE_LEND						,
			HpPageRepository::TYPE_BUY						,
			HpPageRepository::TYPE_SELL						,
		),
		HpPageRepository::CATEGORY_INFO			=> array(
			HpPageRepository::TYPE_INFO_INDEX				,
			HpPageRepository::TYPE_INFO_DETAIL				,
		),
		HpPageRepository::CATEGORY_POLICY		=> array(
			HpPageRepository::TYPE_PRIVACYPOLICY			,
			HpPageRepository::TYPE_SITEPOLICY				,
		),
		HpPageRepository::CATEGORY_SITEMAP		=> array(),
		HpPageRepository::CATEGORY_FORM			=> array(
			HpPageRepository::TYPE_FORM_CONTACT				,
			HpPageRepository::TYPE_FORM_DOCUMENT			,
			HpPageRepository::TYPE_FORM_ASSESSMENT			,
			
			HpPageRepository::TYPE_FORM_LIVINGLEASE			,
			HpPageRepository::TYPE_FORM_OFFICELEASE			,
			HpPageRepository::TYPE_FORM_LIVINGBUY			,
			HpPageRepository::TYPE_FORM_OFFICEBUY			,
			// 物件リクエスト
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE	,
			HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE	,
			HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY	,
			HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY	,
			
		),
		HpPageRepository::CATEGORY_LINK			=> array(
			HpPageRepository::TYPE_LINK						,
			HpPageRepository::TYPE_ALIAS					,
            HpPageRepository::TYPE_ESTATE_ALIAS				,
            HpPageRepository::TYPE_LINK_HOUSE				,
		),
    ) ;
    
    public	$pageMapArticle = array(
        HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION => array(
            HpPageRepository::TYPE_SALE,
            HpPageRepository::TYPE_PURCHASE,
            HpPageRepository::TYPE_OWNERS_RENTAL_MANAGEMENT,
            HpPageRepository::TYPE_RESIDENTIAL_RENTAL,
            HpPageRepository::TYPE_BUSINESS_LEASE,
        ),
        HpPageRepository::TYPE_SALE => array(
            HpPageRepository::TYPE_CHECK_FLOW_OF_SALE,
            HpPageRepository::TYPE_LEARN_BASIC_OF_SALE,
            HpPageRepository::TYPE_KNOW_MEDIATION,
            HpPageRepository::TYPE_KNOW_COSTS_AND_TAXES,
            HpPageRepository::TYPE_KNOW_SALE_ASSESSMENT,
            HpPageRepository::TYPE_LEARN_LAND_SALES,
            HpPageRepository::TYPE_KNOW_HOW_TO_SALE
        ),
        HpPageRepository::TYPE_PURCHASE => array(
            HpPageRepository::TYPE_KNOW_BASIC_OF_PURCHASE,
            HpPageRepository::TYPE_KNOW_WHEN_TO_BUY,
            HpPageRepository::TYPE_LEARN_BUY_SINGLE_FAMILY,
            HpPageRepository::TYPE_LEARN_BUY_APARTMENT,
            HpPageRepository::TYPE_LEARN_PRE_OWNED_RENOVATION,
            HpPageRepository::TYPE_KNOW_COST_OF_PURCHASE,
            HpPageRepository::TYPE_LEARN_MORTGAGES,
            HpPageRepository::TYPE_THINK_FUTURE_OF_YOUR_HOME,
            HpPageRepository::TYPE_LEARN_SITE_AND_PREVIEWS,
            HpPageRepository::TYPE_LEARN_SALES_CONTRACTS,
        ),
        HpPageRepository::TYPE_RESIDENTIAL_RENTAL => array(
            HpPageRepository::TYPE_LEARN_BASIC_STORE_OPENING,
            HpPageRepository::TYPE_LEARN_START_UP_FUNDS,
            HpPageRepository::TYPE_LEARN_CHOOSE_STORE,
            HpPageRepository::TYPE_LEARN_PROCEDURES_AND_CONTRACTS,
            HpPageRepository::TYPE_LEARN_STORE_DESIGN,
        ),
        HpPageRepository::TYPE_OWNERS_RENTAL_MANAGEMENT => array(
            HpPageRepository::TYPE_LEARN_REAL_ESTATE_INVESTMENT,
            HpPageRepository::TYPE_KNOW_FUNDS_AND_LOANS,
            HpPageRepository::TYPE_LEARN_RENTAL_MANAGEMENT,
            HpPageRepository::TYPE_KNOW_INHERITANCE,
        ),
        HpPageRepository::TYPE_BUSINESS_LEASE => array(
            HpPageRepository::TYPE_LEARN_TYPES_OF_RENTAL,
            HpPageRepository::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL,
            HpPageRepository::TYPE_LEARN_RENT_EXPENSES,
            HpPageRepository::TYPE_LEARN_VISITS_COMPANIES_AND_SITE,
            HpPageRepository::TYPE_LEARN_LEASE_AGREEMENTS,
            HpPageRepository::TYPE_KNOW_MOVING,
            HpPageRepository::TYPE_LEARN_LIVING_RENT,
        ),
        HpPageRepository::TYPE_CHECK_FLOW_OF_SALE => array(
            HpPageRepository::TYPE_BAIKYAKU_POINT,
            HpPageRepository::TYPE_SELL,
            HpPageRepository::TYPE_REPLACEMENT_AHEAD_SALE,
            HpPageRepository::TYPE_KYOJUUCHUU_BAIKYAKU,
            HpPageRepository::TYPE_SELL_HIKIWATASHI
        ),
        HpPageRepository::TYPE_LEARN_BASIC_OF_SALE => array(
            HpPageRepository::TYPE_BAIKYAKU_TYPE,
            HpPageRepository::TYPE_BAIKYAKU_SOUBA,
            HpPageRepository::TYPE_SEIYAKU_KAKAKU,
            HpPageRepository::TYPE_KEIYAKU_FUTEKIGOU,
            HpPageRepository::TYPE_SHINSEI_SHORUI,
            HpPageRepository::TYPE_KOJIN_TORIHIKI,
            HpPageRepository::TYPE_NINBAI,
        ),
        HpPageRepository::TYPE_KNOW_MEDIATION => array(
            HpPageRepository::TYPE_CHUUKAI_KISO,
            HpPageRepository::TYPE_KYOUDOU_CHUUKAI,
            HpPageRepository::TYPE_BAIKAI_KEIYAKU,
            HpPageRepository::TYPE_IPPAN_BAIKAI,
            HpPageRepository::TYPE_SENZOKU_SENNIN,
            HpPageRepository::TYPE_KAITORI_OSHOU,
        ),
        HpPageRepository::TYPE_KNOW_COSTS_AND_TAXES => array(
            HpPageRepository::TYPE_HYOUKAGAKU,
            HpPageRepository::TYPE_KAIKAE_KEIKAKU,
            HpPageRepository::TYPE_BAIKYAKU_COST,
            HpPageRepository::TYPE_TEITOUKEN,
            HpPageRepository::TYPE_JOUTOSHOTOKU,
            HpPageRepository::TYPE_TOKUBETSU_KOUJO,
            HpPageRepository::TYPE_KAKUTEI_SHINKOKU,
            HpPageRepository::TYPE_TSUNAGI_YUUSHI,
        ),
        HpPageRepository::TYPE_KNOW_SALE_ASSESSMENT => array(
            HpPageRepository::TYPE_FUKUSUU_SATEI,
            HpPageRepository::TYPE_KANNI_SATEI,
            HpPageRepository::TYPE_BUILDING_EVALUATION,
        ),
        HpPageRepository::TYPE_LEARN_LAND_SALES => array(
            HpPageRepository::TYPE_URERU_TOCHI,
            HpPageRepository::TYPE_FURUYATSUKI_SARACHI,
            HpPageRepository::TYPE_TOCHI_BAIKYAKU,
            HpPageRepository::TYPE_KAKUTEI_SOKURYOU,
            HpPageRepository::TYPE_HATAZAOCHI,
            HpPageRepository::TYPE_NOUCHI,
        ),
        HpPageRepository::TYPE_KNOW_HOW_TO_SALE => array(
            HpPageRepository::TYPE_BAIKYAKU_JIKI,
            HpPageRepository::TYPE_BAIKYAKU_20Y,
            HpPageRepository::TYPE_BAIKYAKU_30Y,
            HpPageRepository::TYPE_URENAI_RIYUU,
            HpPageRepository::TYPE_PURCHASING_REAL_ESTATE,
            HpPageRepository::TYPE_IRAISAKI_SENTAKU,
            HpPageRepository::TYPE_NAIRAN_TAIOU,
            HpPageRepository::TYPE_BUYER_VISITS_DETACHEDHOUSE,
            HpPageRepository::TYPE_POINTS_SALE_OF_CONDOMINIUM,
            HpPageRepository::TYPE_SAIKENCHIKU_FUKA,
        ),
        HpPageRepository::TYPE_KNOW_BASIC_OF_PURCHASE => array(
            HpPageRepository::TYPE_MOCHIIE_MERIT,
            HpPageRepository::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
            HpPageRepository::TYPE_NEWCONSTRUCTION_OR_SECONDHAND,
            HpPageRepository::TYPE_BUY_JOUKENSEIRI,
            HpPageRepository::TYPE_BUY_RICCHI,
            HpPageRepository::TYPE_MADORI,
            HpPageRepository::TYPE_SETAIBETSU,
            HpPageRepository::TYPE_KAIDAN_TYPE,
            HpPageRepository::TYPE_SEINOU_HYOUKA,
            HpPageRepository::TYPE_LIFE_PLAN,
            HpPageRepository::TYPE_BUY,
            HpPageRepository::TYPE_BUY_KEIYAKU_FLOW,
            HpPageRepository::TYPE_SAISHUU_KAKUNIN,
            HpPageRepository::TYPE_NYUUKYO_FLOW,
            HpPageRepository::TYPE_COMMUNICATION,
            HpPageRepository::TYPE_SHINCHIKU_NAIRANKAI,
            HpPageRepository::TYPE_NYUUKYO_TROUBLE,
        ),
        HpPageRepository::TYPE_KNOW_WHEN_TO_BUY => array(
            HpPageRepository::TYPE_KOUNYUU_JIKI,
            HpPageRepository::TYPE_PURCHASE_BEST_TIMING,
            HpPageRepository::TYPE_20DAI_KOUNYUU,
            HpPageRepository::TYPE_30DAI_KOUNYUU,
            HpPageRepository::TYPE_50DAI_KOUNYUU,
        ),
        HpPageRepository::TYPE_LEARN_BUY_SINGLE_FAMILY => array(
            HpPageRepository::TYPE_TOCHI_ERABI,
            HpPageRepository::TYPE_ERECTIONHOUSING_ORDERHOUSE,
            HpPageRepository::TYPE_KENCHIKU_JOUKENTSUKI,
            HpPageRepository::TYPE_NISETAI_JUUTAKU,
            HpPageRepository::TYPE_KODATE_SHINSEIKATSU,
        ),
        HpPageRepository::TYPE_LEARN_BUY_APARTMENT => array(
            HpPageRepository::TYPE_MANSION_TYPE,
            HpPageRepository::TYPE_MAISONETTE_MANSION,
            HpPageRepository::TYPE_MANSION_SERVICE,
            HpPageRepository::TYPE_MANSION_SHINSEIKATSU,
        ),
        HpPageRepository::TYPE_LEARN_PRE_OWNED_RENOVATION => array(
            HpPageRepository::TYPE_RENOVATION_BUKKEN,
            HpPageRepository::TYPE_CHUUKO_RENOVATION,
            HpPageRepository::TYPE_HOME_INSPECTION,
        ),
        HpPageRepository::TYPE_KNOW_COST_OF_PURCHASE => array(
            HpPageRepository::TYPE_FUNDING_PLAN,
            HpPageRepository::TYPE_KOUNYUU_YOSAN,
            HpPageRepository::TYPE_KOUNYUU_ATAMAKIN,
            HpPageRepository::TYPE_YOSAN_OVER,
            HpPageRepository::TYPE_KOUNYUU_SHOKIHIYOU,
            HpPageRepository::TYPE_KOUNYUUGO_COST,
        ),
        HpPageRepository::TYPE_LEARN_MORTGAGES => array(
            HpPageRepository::TYPE_LOAN_MERIT,
            HpPageRepository::TYPE_TYPES_MORTGAGE_LOANS,
            HpPageRepository::TYPE_KINRI_TYPE,
            HpPageRepository::TYPE_HENSAI_TYPE,
            HpPageRepository::TYPE_HENSAI_KIKAN,
            HpPageRepository::TYPE_SHINSA_KIJUN,
            HpPageRepository::TYPE_BONUS_HENSAI,
            HpPageRepository::TYPE_SHINSA_FLOW,
            HpPageRepository::TYPE_LOAN_KEIKAKU,
            HpPageRepository::TYPE_FLAT35,
            HpPageRepository::TYPE_KURIAGE_HENSAI,
            HpPageRepository::TYPE_TOMOBATARAKI_LOAN,
            HpPageRepository::TYPE_LOAN_KARIKAE,
            HpPageRepository::TYPE_REPLACEMENTLOAN_MORTGAGELOAN,
        ),
        HpPageRepository::TYPE_THINK_FUTURE_OF_YOUR_HOME => array(
            HpPageRepository::TYPE_SUMAI_SHOURAISEI,
            HpPageRepository::TYPE_SHISAN_KACHI,
        ),
        HpPageRepository::TYPE_LEARN_SITE_AND_PREVIEWS => array(
            HpPageRepository::TYPE_KODATE_KENGAKU,
            HpPageRepository::TYPE_MANSION_KENGAKU,
            HpPageRepository::TYPE_GENCHI_KAKUNIN,
        ),
        HpPageRepository::TYPE_LEARN_SALES_CONTRACTS => array(
            HpPageRepository::TYPE_BUY_MOUSHIKOMI,
            HpPageRepository::TYPE_BUY_KEIYAKU,
            HpPageRepository::TYPE_BUY_JUUYOUJIKOU,
            HpPageRepository::TYPE_TOUKI_TETSUZUKI,
        ),
        HpPageRepository::TYPE_LEARN_REAL_ESTATE_INVESTMENT => array(
            HpPageRepository::TYPE_TOUSHI_FUKUGYOU,
            HpPageRepository::TYPE_TOUSHI_SALARYMAN,
            HpPageRepository::TYPE_TOUSHI_BUKKEN,
            HpPageRepository::TYPE_RIMAWARI,
            HpPageRepository::TYPE_OWNER_CHANGE,
            HpPageRepository::TYPE_TOUSHI_SETSUZEI,
            HpPageRepository::TYPE_BUNSAN_TOUSHI,
            HpPageRepository::TYPE_TOUSHI_TYPE,
            HpPageRepository::TYPE_MANSION_TOUSHI,
            HpPageRepository::TYPE_BOUHANSEI,
            HpPageRepository::TYPE_TENKIN_MOCHIIE,
            HpPageRepository::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER,
            HpPageRepository::TYPE_UTILIZING_LAND,
        ),
        HpPageRepository::TYPE_KNOW_FUNDS_AND_LOANS => array(
            HpPageRepository::TYPE_TOUSHI_COST,
            HpPageRepository::TYPE_RUNNING_COST,
            HpPageRepository::TYPE_TOUSHI_LOAN,
            HpPageRepository::TYPE_SHUUZEN_KEIKAKU,
            HpPageRepository::TYPE_REVERSE_MORTGAGE,
        ),
        HpPageRepository::TYPE_LEARN_RENTAL_MANAGEMENT => array(
            HpPageRepository::TYPE_MEASURES_AGAINST_VACANCIES,
            HpPageRepository::TYPE_TROUBLE_TAIOU,
            HpPageRepository::TYPE_YACHIN_TAINOU,
            HpPageRepository::TYPE_CHINTAI_HOSHOU,
            HpPageRepository::TYPE_GENJOU_KAIFUKU,
            HpPageRepository::TYPE_CHINTAI_REFORM,
            HpPageRepository::TYPE_HOUSE_REMODELING,
            HpPageRepository::TYPE_CHINTAI_DIY,
            HpPageRepository::TYPE_LEASING_MANAGEMENT_MENU,
            HpPageRepository::TYPE_TROUBLED_LEASING_MANAGEMENT,
            HpPageRepository::TYPE_LEND,
        ),
        HpPageRepository::TYPE_KNOW_INHERITANCE => array(
            HpPageRepository::TYPE_AKIYA_SOUZOKU,
            HpPageRepository::TYPE_ISAN_BUNKATSU,
            HpPageRepository::TYPE_JIKKA_BAIKYAKU,
            HpPageRepository::TYPE_SOUZOKU_ZEI,
            HpPageRepository::TYPE_PURCHASE_INHERITANCE_TAX,
            HpPageRepository::TYPE_MEIGI_HENKOU,
        ),
        HpPageRepository::TYPE_LEARN_BASIC_STORE_OPENING => array(
            HpPageRepository::TYPE_HOUJIN_KOJIN,
            HpPageRepository::TYPE_KAIGYOU_FLOW,
            HpPageRepository::TYPE_STORE_CONCEPT,
            HpPageRepository::TYPE_SHOP_SUCCESS_BUSINESS_PLAN,
            HpPageRepository::TYPE_STORE_SEARCH,
            HpPageRepository::TYPE_KASHITENPO_TYPE,
            HpPageRepository::TYPE_JIGYOUYOU_BUKKEN,
            HpPageRepository::TYPE_KEIEI_RISK,
            HpPageRepository::TYPE_TENANT_HIKIWATASHI,
            HpPageRepository::TYPE_NAISOU_SEIGEN,
            HpPageRepository::TYPE_FRANCHISE,
            HpPageRepository::TYPE_LEASEBACK,
            HpPageRepository::TYPE_AOIRO_SHINKOKU,
            HpPageRepository::TYPE_SHOUTENKAI,
        ),
        HpPageRepository::TYPE_LEARN_START_UP_FUNDS => array(
            HpPageRepository::TYPE_OPENING_COST,
            HpPageRepository::TYPE_KAIGYOU_SHIKIN,
            HpPageRepository::TYPE_SOURITSUHI,
            HpPageRepository::TYPE_KENRI_KIN,
        ),
        HpPageRepository::TYPE_LEARN_CHOOSE_STORE => array(
            HpPageRepository::TYPE_STORE_ERABI,
            HpPageRepository::TYPE_STORE_LOCATION,
            HpPageRepository::TYPE_TENANT_RICCHI,
            HpPageRepository::TYPE_INUKI_BUKKEN,
            HpPageRepository::TYPE_SKELETON_BUKKEN,
            HpPageRepository::TYPE_KUUCHUU_TENPO,
        ),
        HpPageRepository::TYPE_LEARN_PROCEDURES_AND_CONTRACTS => array(
            HpPageRepository::TYPE_KAIGYOU_TETSUZUKI,
            HpPageRepository::TYPE_EIGYOU_KYOKA,
            HpPageRepository::TYPE_SHINYA_EIGYOU,
            HpPageRepository::TYPE_TENANT_KEIYAKU,
        ),
        HpPageRepository::TYPE_LEARN_STORE_DESIGN => array(
            HpPageRepository::TYPE_STORE_DESIGN,
            HpPageRepository::TYPE_STORE_LAYOUT,
            HpPageRepository::TYPE_SEKOU_IRAI,
            HpPageRepository::TYPE_SEKOU_MITSUMORI,
            HpPageRepository::TYPE_SEKOU_ITAKUSAKI,
            HpPageRepository::TYPE_BARRIER_FREE,
            HpPageRepository::TYPE_GAISOU_DESIGN,
        ),
        HpPageRepository::TYPE_LEARN_TYPES_OF_RENTAL => array(
            HpPageRepository::TYPE_APART_VS_MANSION,
            HpPageRepository::TYPE_TOWNHOUSE_TERRACEHOUSE,
            HpPageRepository::TYPE_IKKODATE_CHINTAI,
        ),
        HpPageRepository::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL => array(
            HpPageRepository::TYPE_CHINTAI_JOUKENSEIRI,
            HpPageRepository::TYPE_KOUHO_BUKKEN,
            HpPageRepository::TYPE_SQUEEZE_CANDIDATE,
            HpPageRepository::TYPE_CHINTAI_RICCHI,
            HpPageRepository::TYPE_SETSUBI_SHIYOU,
            HpPageRepository::TYPE_SECURITY_TAISAKU,
            HpPageRepository::TYPE_INTERNET_KANKYOU,
            HpPageRepository::TYPE_MINAMIMUKI,
            HpPageRepository::TYPE_KANRI_KEITAI,
            HpPageRepository::TYPE_KOSODATE_CHINTAI,
            HpPageRepository::TYPE_PET_OK,
            HpPageRepository::TYPE_CHINTAI_KIKAN,
            HpPageRepository::TYPE_EKITOOI,
            HpPageRepository::TYPE_WOMEN_ONLY,
            HpPageRepository::TYPE_KAGU_KADEN,
        ),
        HpPageRepository::TYPE_LEARN_RENT_EXPENSES => array(
            HpPageRepository::TYPE_YACHIN_YOSAN,
            HpPageRepository::TYPE_UPPER_LIMIT,
            HpPageRepository::TYPE_GAKUSEI_YACHIN,
            HpPageRepository::TYPE_YACHIN_SOUBA,
            HpPageRepository::TYPE_KANRIHI_KYOUEKIHI,
            HpPageRepository::TYPE_RENTAL_INITIAL_COST,
            HpPageRepository::TYPE_SHIKIREI,
            HpPageRepository::TYPE_HIKKOSHI_COST,
            HpPageRepository::TYPE_SHINSEIKATSU_COST,
        ),
        HpPageRepository::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => array(
            HpPageRepository::TYPE_SOUDAN_POINT,
            HpPageRepository::TYPE_HOUMON_JUNBI,
            HpPageRepository::TYPE_NAIKEN_JUNBI,
            HpPageRepository::TYPE_PREVIEW,
            HpPageRepository::TYPE_NAIKEN_POINT,
            HpPageRepository::TYPE_SHUUHEN_KANKYOU,
        ),
        HpPageRepository::TYPE_LEARN_LEASE_AGREEMENTS => array(
            HpPageRepository::TYPE_RENT,
            HpPageRepository::TYPE_LENT_MOUSHIKOMI,
            HpPageRepository::TYPE_NYUUKYO_SHINSA,
            HpPageRepository::TYPE_KEIYAKU_COST,
            HpPageRepository::TYPE_LENT_JUUYOUJIKOU,
            HpPageRepository::TYPE_CHINTAISHAKU,
            HpPageRepository::TYPE_YACHIN_HOSHOU,
            HpPageRepository::TYPE_CHUUTO_KAIYAKU,
            HpPageRepository::TYPE_KEIYAKU_GENJOU_KAIFUKU,
        ),
        HpPageRepository::TYPE_KNOW_MOVING => array(
            HpPageRepository::TYPE_HIKKOSHI_KAISHA,
            HpPageRepository::TYPE_HIKKOSHI_FLOW,
            HpPageRepository::TYPE_GENJOU_KAKUNIN,
            HpPageRepository::TYPE_JIZEN_HANNYUU,
            HpPageRepository::TYPE_MOVING,
            HpPageRepository::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE,
            HpPageRepository::TYPE_SODAIGOMI,
            HpPageRepository::TYPE_TODOKEDE,
            HpPageRepository::TYPE_HIKKOSHI_JUNBI,
        ),
        HpPageRepository::TYPE_LEARN_LIVING_RENT => array(
            HpPageRepository::TYPE_MADORIZU,
            HpPageRepository::TYPE_COMFORTABLELIVING_RESIDENT_RULES,
            HpPageRepository::TYPE_KINRIN_MANNERS,
            HpPageRepository::TYPE_JICHIKAI,
        )
    );
	
	public	$pageIndexNumbers	= array(
		HpPageRepository::TYPE_TOP									=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_COMPANY								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_HISTORY								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_GREETING								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_RECRUIT								=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_SHOP_DETAIL							=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_STAFF_DETAIL							=> [ 'importance' =>  5, 'limit' =>  3 ],
		HpPageRepository::TYPE_OWNER								=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_CORPORATION							=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_TENANT								=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_BROKER								=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_PROPRIETARY							=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_BLOG_DETAIL							=> [ 'importance' =>  3, 'limit' => 10 ],
		HpPageRepository::TYPE_FREE									=> [ 'importance' =>  3, 'limit' => 10 ],
		HpPageRepository::TYPE_MEMBERONLY							=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_CITY									=> [ 'importance' =>  5, 'limit' => 10 ],
		HpPageRepository::TYPE_CUSTOMERVOICE_DETAIL					=> [ 'importance' =>  5, 'limit' => 10 ],
		HpPageRepository::TYPE_SELLINGCASE_DETAIL					=> [ 'importance' =>  3, 'limit' => 10 ],
		HpPageRepository::TYPE_EVENT_DETAIL							=> [ 'importance' =>  3, 'limit' =>  5 ],
		HpPageRepository::TYPE_QA									=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_LINKS								=> [ 'importance' =>  3, 'limit' =>  1 ],
		HpPageRepository::TYPE_SCHOOL								=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_PREVIEW								=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_MOVING								=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_TERMINOLOGY							=> [ 'importance' =>  7, 'limit' =>  1 ],
		HpPageRepository::TYPE_RENT									=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_LEND									=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_BUY									=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_SELL									=> [ 'importance' =>  5, 'limit' =>  1 ],
		HpPageRepository::TYPE_INFO_DETAIL							=> [ 'importance' =>  3, 'limit' =>  3 ],
		HpPageRepository::TYPE_PRIVACYPOLICY						=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_SITEPOLICY							=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_SITEMAP								=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_FORM_CONTACT							=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_FORM_DOCUMENT						=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_FORM_ASSESSMENT						=> [ 'importance' => 10, 'limit' =>  1 ],
		HpPageRepository::TYPE_BUSINESS_CONTENT						=> [ 'importance' => 10, 'limit' =>  1 ],		// 事業内容
		HpPageRepository::TYPE_COLUMN_DETAIL						=> [ 'importance' =>  3, 'limit' => 10 ],		// コラム詳細
		HpPageRepository::TYPE_COMPANY_STRENGTH						=> [ 'importance' => 10, 'limit' =>  1 ],		// 当社の思い・強み
		HpPageRepository::TYPE_PURCHASING_REAL_ESTATE				=> [ 'importance' =>  5, 'limit' =>  1 ],		// 不動産「買取り」について
		HpPageRepository::TYPE_REPLACEMENTLOAN_MORTGAGELOAN			=> [ 'importance' =>  5, 'limit' =>  1 ],		// 「買い換えローン」と「住宅ローン」の違い
		HpPageRepository::TYPE_REPLACEMENT_AHEAD_SALE 				=> [ 'importance' =>  5, 'limit' =>  1 ],		// 買い換えは売却が先？
		HpPageRepository::TYPE_BUILDING_EVALUATION					=> [ 'importance' =>  5, 'limit' =>  1 ],		// 中古戸建ての「建物評価」の仕組み
		HpPageRepository::TYPE_BUYER_VISITS_DETACHEDHOUSE			=> [ 'importance' =>  5, 'limit' =>  1 ],		// 一戸建てを買い手が見学するとき、気にするポイント
		HpPageRepository::TYPE_POINTS_SALE_OF_CONDOMINIUM			=> [ 'importance' =>  5, 'limit' =>  1 ],		// マンションの売却を有利にするポイント（専有部分）
		HpPageRepository::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE	=> [ 'importance' =>  5, 'limit' =>  1 ],		// マンションと一戸建て どちらを選ぶ？
		HpPageRepository::TYPE_NEWCONSTRUCTION_OR_SECONDHAND		=> [ 'importance' =>  5, 'limit' =>  1 ],		// 新築？中古？ 選ぶときの考え方
		HpPageRepository::TYPE_ERECTIONHOUSING_ORDERHOUSE			=> [ 'importance' =>  5, 'limit' =>  1 ],		// 建売住宅と注文住宅の違いと選び方
		HpPageRepository::TYPE_PURCHASE_BEST_TIMING					=> [ 'importance' =>  5, 'limit' =>  1 ],		// 住宅購入のベストタイミングは？
		HpPageRepository::TYPE_LIFE_PLAN							=> [ 'importance' =>  5, 'limit' =>  1 ],		// ライフプランを立ててみましょう
		HpPageRepository::TYPE_TYPES_MORTGAGE_LOANS					=> [ 'importance' =>  5, 'limit' =>  1 ],		// 住宅ローンの種類
		HpPageRepository::TYPE_FUNDING_PLAN							=> [ 'importance' =>  5, 'limit' =>  1 ],		// 資金計画を立てましょう
		HpPageRepository::TYPE_TROUBLED_LEASING_MANAGEMENT			=> [ 'importance' =>  5, 'limit' =>  1 ],		// 賃貸管理でお困りのオーナー様へ
		HpPageRepository::TYPE_LEASING_MANAGEMENT_MENU				=> [ 'importance' =>  5, 'limit' =>  1 ],		// 賃貸管理業務メニュー
		HpPageRepository::TYPE_MEASURES_AGAINST_VACANCIES			=> [ 'importance' =>  5, 'limit' =>  1 ],		// 空室対策（概論的）
		HpPageRepository::TYPE_HOUSE_REMODELING						=> [ 'importance' =>  5, 'limit' =>  1 ],		// 競合物件に差をつける住戸リフォーム
		HpPageRepository::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER		=> [ 'importance' =>  5, 'limit' =>  1 ],		// 土地活用をお考えのオーナー様へ（事業化の流れ含む）
		HpPageRepository::TYPE_UTILIZING_LAND						=> [ 'importance' =>  5, 'limit' =>  1 ],		// 土地活用の方法について（賃貸M・AP経営、等価交換M、高齢者向け住宅）
		HpPageRepository::TYPE_PURCHASE_INHERITANCE_TAX				=> [ 'importance' =>  5, 'limit' =>  1 ],		// 不動産の購入と相続税対策（税務専門的）
		HpPageRepository::TYPE_UPPER_LIMIT							=> [ 'importance' =>  5, 'limit' =>  1 ],		// 収入から払える家賃の上限はどれくらい？
		HpPageRepository::TYPE_RENTAL_INITIAL_COST					=> [ 'importance' =>  5, 'limit' =>  1 ],		// 賃貸住宅を借りるときの「初期費用」とは
		HpPageRepository::TYPE_SQUEEZE_CANDIDATE					=> [ 'importance' =>  5, 'limit' =>  1 ],		// 候補物件のしぼり方
		HpPageRepository::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE		=> [ 'importance' =>  5, 'limit' =>  1 ],		// 引越し時の不用品・粗大ゴミなどの処分方法
		HpPageRepository::TYPE_COMFORTABLELIVING_RESIDENT_RULES		=> [ 'importance' =>  5, 'limit' =>  1 ],		// 快適に暮らすための居住ルール（不動産会社視点）
		HpPageRepository::TYPE_STORE_SEARCH 						=> [ 'importance' =>  5, 'limit' =>  1 ],		// 店舗探し・自分でできる商圏調査
		HpPageRepository::TYPE_SHOP_SUCCESS_BUSINESS_PLAN			=> [ 'importance' =>  5, 'limit' =>  1 ],		// お店成功のためには事業計画書が大切
    ) ;
}
