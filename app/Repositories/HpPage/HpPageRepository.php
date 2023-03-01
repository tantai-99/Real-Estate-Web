<?php

namespace App\Repositories\HpPage;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;
use Illuminate\Support\Facades\App;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Model\Lists\Original;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use Library\Custom\Plan;
use Library\Custom\Model\Lists\CmsPlan;
use Library\Custom\Hp\Page\Parts\InfoList;
use Library\Custom\User\Cms;
use App\Models\AssociatedHpPageAttribute;
use ReflectionClass;
use Library\Custom\Publish\Estate\Prepare\Simple;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;

class HpPageRepository extends BaseRepository implements HpPageRepositoryInterface
{
    protected $_categories;
    protected $_categoryMap;
    protected $_editAbleCategories;
    protected $_childCategories;
    protected $_fixedMenu;
    protected $_uniquePages;
    protected $_requiredPages;
    protected $_hasPagination;
    protected $_notDisplayInSitemap;
    protected $_contactPageForSearch;
    protected $_estateRequestPage;
    protected $_articleOriginal;

    public function getModel()
    {
        return \App\Models\HpPage::class;
    }

    const MAX_LEVEL = 4;

    const MAX_ORIGINAL_LARGE = 5;

    const MAX_ORIGINAL_SMALL = 20;

    const MAX_CREATE_FILE = 99;

    // トップページ
    const TYPE_TOP = 1;

    // お知らせ一覧
    const TYPE_INFO_INDEX = 2;

    // お知らせ
    const TYPE_INFO_DETAIL = 3;

    // 会社紹介
    const TYPE_COMPANY = 4;

    // 会社沿革
    const TYPE_HISTORY = 5;

    // 代表挨拶
    const TYPE_GREETING = 6;

    // 店舗紹介一覧
    const TYPE_SHOP_INDEX = 7;

    // 店舗紹介
    const TYPE_SHOP_DETAIL = 8;

    // スタッフ紹介一覧
    const TYPE_STAFF_INDEX = 9;

    // スタッフ紹介
    const TYPE_STAFF_DETAIL = 10;

    // 採用情報
    const TYPE_RECRUIT = 11;

    // 物件ページ(物件コマ)一覧
    const TYPE_STRUCTURE_INDEX = 12;

    // 物件ページ(物件コマ)
    const TYPE_STRUCTURE_DETAIL = 13;

    // ブログ一覧
    const TYPE_BLOG_INDEX = 14;

    // ブログ詳細
    const TYPE_BLOG_DETAIL = 15;

    //CMSテンプレートパターンの追加
    // コラム一覧
    const TYPE_COLUMN_INDEX = 56;
    // コラム詳細
    const TYPE_COLUMN_DETAIL = 57;
    //CMSテンプレートパターンの追加

    // プライバシーポリシー
    const TYPE_PRIVACYPOLICY = 16;

    // サイトポリシー
    const TYPE_SITEPOLICY = 17;

    // オーナーページ
    const TYPE_OWNER = 18;

    // 法人ページ
    const TYPE_CORPORATION = 19;

    // 入居者さま向けページ
    const TYPE_TENANT = 20;

    // 仲介会社さま向けページ
    const TYPE_BROKER = 21;

    // 管理会社さま向けページ
    const TYPE_PROPRIETARY = 22;

    // 街情報
    const TYPE_CITY = 23;

    // お客様の声一覧
    const TYPE_CUSTOMERVOICE_INDEX = 24;

    // お客様の声
    const TYPE_CUSTOMERVOICE_DETAIL = 25;

    // Q＆Aページ
    const TYPE_QA = 26;

    // お役立ち情報 学区情報
    const TYPE_SCHOOL = 27;

    // お役立ち情報 内見時のチェックポイント
    const TYPE_PREVIEW = 28;

    // お役立ち情報 引越し時のチェックポイント
    const TYPE_MOVING = 29;

    // お役立ち情報 不動産用語集
    const TYPE_TERMINOLOGY = 30;

    // お役立ち情報 住まいを借りる契約の流れ
    const TYPE_RENT = 31;

    // お役立ち情報 住まいを貸す契約の流れ
    const TYPE_LEND = 32;

    // お役立ち情報 住まいを買う契約の流れ
    const TYPE_BUY = 33;

    // 売却事例一覧
    const TYPE_SELLINGCASE_INDEX = 35;

    // 売却事例
    const TYPE_SELLINGCASE_DETAIL = 36;

    // イベント情報一覧
    const TYPE_EVENT_INDEX = 37;

    // イベント情報
    const TYPE_EVENT_DETAIL = 38;

    // リンク集
    const TYPE_LINKS = 39;

    // フリーページ
    const TYPE_FREE = 40;

    // 会社問い合わせ
    const TYPE_FORM_CONTACT = 41;

    // 資料請求
    const TYPE_FORM_DOCUMENT = 42;

    // 査定依頼
    const TYPE_FORM_ASSESSMENT = 43;

    // 物件問い合わせ 居住用賃貸物件フォーム
    const TYPE_FORM_LIVINGLEASE = 44;

    // 物件問い合わせ 事務所用賃貸物件フォーム
    const TYPE_FORM_OFFICELEASE = 45;

    // 物件問い合わせ 居住用売買物件フォーム
    const TYPE_FORM_LIVINGBUY = 46;

    // 物件問い合わせ 事務所用売買物件フォーム
    const TYPE_FORM_OFFICEBUY = 47;

    // 会員専用ページ
    const TYPE_MEMBERONLY = 48;

    // サイトマップ
    const TYPE_SITEMAP = 49;

    // 物件リクエスト
    // 物件リクエスト 居住用賃貸物件フォーム
    const TYPE_FORM_REQUEST_LIVINGLEASE = 50;

    // 物件リクエスト 事務所用賃貸物件フォーム
    const TYPE_FORM_REQUEST_OFFICELEASE = 51;

    // 物件リクエスト 居住用売買物件フォーム
    const TYPE_FORM_REQUEST_LIVINGBUY = 52;

    // 物件リクエスト 事務所用売買物件フォーム
    const TYPE_FORM_REQUEST_OFFICEBUY = 53;

    //CMSテンプレートパターンの追加
    // 事業内容
    const TYPE_BUSINESS_CONTENT = 54;
    // 当社の思い・強み
    const TYPE_COMPANY_STRENGTH = 55;

    // 雛形
    // うまく売る方法について知る
    // TYPE_KNOW_HOW_TO_SALE 114
    // 住まいの売却時期を決める4つのポイント
    const TYPE_BAIKYAKU_JIKI = 173;
    // 築20年以上の家を売却するためのポイント
    const TYPE_BAIKYAKU_20Y = 174;
    // 築30年超の古家の売却について
    const TYPE_BAIKYAKU_30Y = 175;
    // 物件がなかなか売れない…その理由と対処法
    const TYPE_URENAI_RIYUU = 176;
    // 不動産「買取り」について
    const TYPE_PURCHASING_REAL_ESTATE = 58;
    // 売却を依頼する不動産会社はどう選ぶ
    const TYPE_IRAISAKI_SENTAKU = 177;
    // 居住中の内見希望への対応ポイント
    const TYPE_NAIRAN_TAIOU = 178;
    // 再建築不可物件を売却するときのポイント
    const TYPE_SAIKENCHIKU_FUKA = 179;

    // 「買い換えローン」と「住宅ローン」の違い
    const TYPE_REPLACEMENTLOAN_MORTGAGELOAN = 59;

    // 査定について知る
    // TYPE_KNOW_SALE_ASSESSMENT 112
    // 価格査定を複数会社に依頼する理由
    const TYPE_FUKUSUU_SATEI = 165;
    // うまく使いたい「簡易査定」と「訪問査定」
    const TYPE_KANNI_SATEI = 166;
    // 中古戸建ての「建物評価」の仕組み
    const TYPE_BUILDING_EVALUATION = 61;

    // 一戸建てを買い手が見学するとき、気にするポイント
    const TYPE_BUYER_VISITS_DETACHEDHOUSE = 62;
    // マンションの売却を有利にするポイント（専有部分）
    const TYPE_POINTS_SALE_OF_CONDOMINIUM = 63;
    // マンションと一戸建て どちらを選ぶ？
    const TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE = 64;
    // 新築？中古？ 選ぶときの考え方
    const TYPE_NEWCONSTRUCTION_OR_SECONDHAND = 65;
    // 建売住宅と注文住宅の違いと選び方
    const TYPE_ERECTIONHOUSING_ORDERHOUSE = 66;
    // 住宅購入のベストタイミングは？
    const TYPE_PURCHASE_BEST_TIMING = 67;
    // ライフプランを立ててみましょう
    const TYPE_LIFE_PLAN = 68;
    // 住宅ローンの種類
    const TYPE_TYPES_MORTGAGE_LOANS = 69;
    // 資金計画を立てましょう
    const TYPE_FUNDING_PLAN = 70;
    // 賃貸管理でお困りのオーナー様へ
    const TYPE_TROUBLED_LEASING_MANAGEMENT = 71;
    // 賃貸管理業務メニュー
    const TYPE_LEASING_MANAGEMENT_MENU = 72;
    // 空室対策（概論的）
    const TYPE_MEASURES_AGAINST_VACANCIES = 73;
    // 競合物件に差をつける住戸リフォーム
    const TYPE_HOUSE_REMODELING = 74;
    // 土地活用をお考えのオーナー様へ（事業化の流れ含む）
    const TYPE_CONSIDERS_LAND_UTILIZATION_OWNER = 75;
    // 土地活用の方法について（賃貸M・AP経営、等価交換M、高齢者向け住宅）
    const TYPE_UTILIZING_LAND = 76;
    // 不動産の購入と相続税対策（税務専門的）
    const TYPE_PURCHASE_INHERITANCE_TAX = 77;
    // 収入から払える家賃の上限はどれくらい？
    const TYPE_UPPER_LIMIT = 78;
    // 賃貸住宅を借りるときの「初期費用」とは
    const TYPE_RENTAL_INITIAL_COST = 79;
    // 候補物件のしぼり方
    const TYPE_SQUEEZE_CANDIDATE = 80;
    // 引越し時の不用品・粗大ゴミなどの処分方法
    const TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE = 81;
    // 快適に暮らすための居住ルール（不動産会社視点）
    const TYPE_COMFORTABLELIVING_RESIDENT_RULES = 82;
    // 店舗探し・自分でできる商圏調査
    const TYPE_STORE_SEARCH = 83;
    // お店成功のためには事業計画書が大切
    const TYPE_SHOP_SUCCESS_BUSINESS_PLAN = 84;
    //CMSテンプレートパターンの追加

    // 不動産お役立ち情報
    const TYPE_USEFUL_REAL_ESTATE_INFORMATION = 100;

    // Large category
    // 売却コンテンツ
    const TYPE_SALE = 101;
    // 購入コンテンツ
    const TYPE_PURCHASE = 102;
    // オーナー向けコンテンツ〈賃貸管理〉
    const TYPE_OWNERS_RENTAL_MANAGEMENT = 103;
    // 居住用賃貸コンテンツ
    const TYPE_RESIDENTIAL_RENTAL = 104;
    // 事業用賃貸コンテンツ
    const TYPE_BUSINESS_LEASE = 105;

    // 売却の流れを確認する - 101
    const TYPE_CHECK_FLOW_OF_SALE = 108;
    // 売却の基礎知識について知る - 101
    const TYPE_LEARN_BASIC_OF_SALE = 109;
    // 仲介について知る - 101
    const TYPE_KNOW_MEDIATION = 110;
    // 費用や税金について知る - 101
    const TYPE_KNOW_COSTS_AND_TAXES = 111;
    // 査定について知る - 101
    const TYPE_KNOW_SALE_ASSESSMENT = 112;
    // 土地売却について知る - 101
    const TYPE_LEARN_LAND_SALES = 113;
    // うまく売る方法について知る - 101
    const TYPE_KNOW_HOW_TO_SALE = 114;
    // 購入の基礎知識について知る - 102
    const TYPE_KNOW_BASIC_OF_PURCHASE = 115;
    // 購入のタイミングについて知る - 102
    const TYPE_KNOW_WHEN_TO_BUY = 116;
    // 一戸建ての購入について知る - 102
    const TYPE_LEARN_BUY_SINGLE_FAMILY = 117;
    // マンションの購入について知る - 102
    const TYPE_LEARN_BUY_APARTMENT = 118;
    // 中古住宅、リノベーションについて知る - 102
    const TYPE_LEARN_PRE_OWNED_RENOVATION = 119;
    // 購入時の費用について知る - 102
    const TYPE_KNOW_COST_OF_PURCHASE = 120;
    // 住宅ローンについて知る - 102
    const TYPE_LEARN_MORTGAGES = 121;
    // 住まいの将来性について考える - 102
    const TYPE_THINK_FUTURE_OF_YOUR_HOME = 122;
    // 物件の現地見学、内覧について知る - 102
    const TYPE_LEARN_SITE_AND_PREVIEWS = 123;
    // 売買契約について知る - 102
    const TYPE_LEARN_SALES_CONTRACTS = 124;
    // 不動産投資について知る - 103
    const TYPE_LEARN_REAL_ESTATE_INVESTMENT = 125;
    // 資金やローンについて知る - 103
    const TYPE_KNOW_FUNDS_AND_LOANS = 126;
    // 賃貸管理について知る - 103
    const TYPE_LEARN_RENTAL_MANAGEMENT = 127;
    // 相続について知る - 103
    const TYPE_KNOW_INHERITANCE = 128;
    // 店舗開業の基礎知識について知る - 104
    const TYPE_LEARN_BASIC_STORE_OPENING = 129;
    // 開業資金について知る - 104
    const TYPE_LEARN_START_UP_FUNDS = 130;
    // 店舗物件の選び方について知る - 104
    const TYPE_LEARN_CHOOSE_STORE = 131;
    // 手続き・契約について知る - 104
    const TYPE_LEARN_PROCEDURES_AND_CONTRACTS = 132;
    // 店舗設計について知る - 104
    const TYPE_LEARN_STORE_DESIGN = 133;
    // 賃貸物件の種類について知る - 105
    const TYPE_LEARN_TYPES_OF_RENTAL = 134;
    // 賃貸物件の希望条件を整理する - 105
    const TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL = 135;
    // 家賃・諸費用について知る - 105
    const TYPE_LEARN_RENT_EXPENSES = 136;
    // 不動産会社への訪問・現地見学について知る - 105
    const TYPE_LEARN_VISITS_COMPANIES_AND_SITE = 137;
    // 賃貸借契約について知る - 105
    const TYPE_LEARN_LEASE_AGREEMENTS = 138;
    // 引越しについて知る - 105
    const TYPE_KNOW_MOVING = 139;
    // 賃貸での暮らしについて知る - 105
    const TYPE_LEARN_LIVING_RENT = 140;

    // small category

    // article
    // 売却は「売却理由」と「取引の流れ」が大切
    const TYPE_BAIKYAKU_POINT = 141;
    // 自宅に「住みながら上手に売る方法」とは
    const TYPE_KYOJUUCHUU_BAIKYAKU = 142;
    // 物件の引渡しまでに売主がしておく準備とは
    const TYPE_SELL_HIKIWATASHI = 143;
    // 住まいを売る契約の流れ
    const TYPE_SELL = 34;
    // 家の買い替えは、購入が先か売却が先か？
    const TYPE_REPLACEMENT_AHEAD_SALE = 60;


    // article
    // 売却の基礎知識について知る
    // TYPE_LEARN_BASIC_OF_SALE 109
    // 仲介だけではない不動産売却の4つの方法
    const TYPE_BAIKYAKU_TYPE = 144;
    // 不動産価格の「相場」を知り上手に売るには
    const TYPE_BAIKYAKU_SOUBA = 145;
    // 査定から成約までの「価格」の違いとは
    const TYPE_SEIYAKU_KAKAKU = 146;
    // 売主が負う「契約不適合責任」とは
    const TYPE_KEIYAKU_FUTEKIGOU = 147;
    // 不動産売却時に必要な書類と取得方法
    const TYPE_SHINSEI_SHORUI = 148;
    // 自力でも売れる？個人売買の可能性とリスク
    const TYPE_KOJIN_TORIHIKI = 149;
    // 「任意売却」でローン滞納の損害を最小限に
    const TYPE_NINBAI = 150;

    // 仲介について知る
    // TYPE_KNOW_MEDIATION 110
    // 不動産の売却方法「仲介」を詳しく知ろう
    const TYPE_CHUUKAI_KISO = 151;
    // 「共同仲介」と「単独仲介」とは何か
    const TYPE_KYOUDOU_CHUUKAI = 152;
    // 不動産会社と結ぶ「媒介契約」の種類とは
    const TYPE_BAIKAI_KEIYAKU = 153;
    // 売却時に選ぶ「一般媒介契約」とは
    const TYPE_IPPAN_BAIKAI = 154;
    // 売却時に選ぶ専任・専属専任媒介契約とは
    const TYPE_SENZOKU_SENNIN = 155;
    // 知っておきたい「買取保証付き仲介」とは
    const TYPE_KAITORI_OSHOU = 156;

    // 費用や税金について知る
    // TYPE_KNOW_COSTS_AND_TAXES 111
    // 不動産の評価額はどのように決まるのか
    const TYPE_HYOUKAGAKU = 157;
    // 住まいの買い換えの成否は資金計画がカギ
    const TYPE_KAIKAE_KEIKAKU = 158;
    // 不動産を売るときの諸費用はいくらかかる？
    const TYPE_BAIKYAKU_COST = 159;
    // ローン残債がある住まいの抵当権抹消とは
    const TYPE_TEITOUKEN = 160;
    // 土地売却時にかかる「譲渡所得」課税とは
    const TYPE_JOUTOSHOTOKU = 161;
    // 売却時の「3000万円特別控除」とは
    const TYPE_TOKUBETSU_KOUJO = 162;
    // 不動産売却後の確定申告は必要？不要？
    const TYPE_KAKUTEI_SHINKOKU = 163;
    // 住まいの買い換えで使う「つなぎ融資」とは
    const TYPE_TSUNAGI_YUUSHI = 164;

    // 土地売却について知る
    // TYPE_LEARN_LAND_SALES 113
    // 売れやすい土地の条件と売るための対策とは
    const TYPE_URERU_TOCHI = 167;
    // 土地は「古家付き」「更地」どちらで売る？
    const TYPE_FURUYATSUKI_SARACHI = 168;
    // 土地売却を円滑に進めるためのポイント
    const TYPE_TOCHI_BAIKYAKU = 169;
    // 売却時に必要な土地の「境界確定測量」とは
    const TYPE_KAKUTEI_SOKURYOU = 170;
    // 「旗竿地」を売るために知っておきたいこと
    const TYPE_HATAZAOCHI = 171;
    // 農地はどうすれば売れる？地目の変更とは
    const TYPE_NOUCHI = 172;

    // 購入の基礎知識について知る
    // TYPE_KNOW_BASIC_OF_PURCHASE 115
    // 「賃貸」にはない「持ち家」のメリットとは
    const TYPE_MOCHIIE_MERIT = 180;
    // 購入物件の希望条件を整理する
    const TYPE_BUY_JOUKENSEIRI = 181;
    // 住宅購入時は希望立地をよく考えよう
    const TYPE_BUY_RICCHI = 182;
    // 間取りの考え方を理解して住まいを選ぶ
    const TYPE_MADORI = 183;
    // 世帯タイプ別の住まい選び
    const TYPE_SETAIBETSU = 184;
    // 購入前に知っておきたい住まいの「階段」
    const TYPE_KAIDAN_TYPE = 185;
    // 「住宅の性能評価」とは
    const TYPE_SEINOU_HYOUKA = 186;
    // 物件購入の申込み前から売買契約までの流れ
    const TYPE_BUY_KEIYAKU_FLOW = 187;
    // 物件の最終確認と残代金の精算・引渡し
    const TYPE_SAISHUU_KAKUNIN = 188;
    // マイホームの引渡しから入居までの流れ
    const TYPE_NYUUKYO_FLOW = 189;
    // 販売担当者との上手なコミュニケーション術
    const TYPE_COMMUNICATION = 190;
    // 新築物件の内覧会と入居説明会について
    const TYPE_SHINCHIKU_NAIRANKAI = 191;
    // 入居後のトラブルへの対応について
    const TYPE_NYUUKYO_TROUBLE = 192;

    // 購入のタイミングについて知る
    // TYPE_KNOW_WHEN_TO_BUY 116
    // 住まいの「買いどき」について考えよう
    const TYPE_KOUNYUU_JIKI = 193;
    // 20代の住まい購入のポイント
    const TYPE_20DAI_KOUNYUU = 194;
    // 30～40代の住まい購入のポイント
    const TYPE_30DAI_KOUNYUU = 195;
    // 50～60代の住まい購入のポイント
    const TYPE_50DAI_KOUNYUU = 196;

    // 一戸建ての購入について知る
    // TYPE_LEARN_BUY_SINGLE_FAMILY 117
    // 一戸建て購入で大切な土地選び
    const TYPE_TOCHI_ERABI = 197;
    // 意外に知らない「建築条件付き土地」とは
    const TYPE_KENCHIKU_JOUKENTSUKI = 198;
    // 住まい方で違う「二世帯住宅」のタイプとは
    const TYPE_NISETAI_JUUTAKU = 199;
    // 一戸建ての新生活について
    const TYPE_KODATE_SHINSEIKATSU = 200;

    // マンションの購入について知る
    // TYPE_LEARN_BUY_APARTMENT 118
    // 大規模？高層？マンションのタイプを知ろう
    const TYPE_MANSION_TYPE = 201;
    // 一戸建て感覚で住めるマンションとは
    const TYPE_MAISONETTE_MANSION = 202;
    // 魅力的なマンションの共用施設・サービス
    const TYPE_MANSION_SERVICE = 203;
    // マンションの新生活について
    const TYPE_MANSION_SHINSEIKATSU = 204;

    // 中古住宅、リノベーションについて知る
    // TYPE_LEARN_PRE_OWNED_RENOVATION 119
    // 注目の「リノベーション物件」とは
    const TYPE_RENOVATION_BUKKEN = 205;
    // 理想への近道は「中古＋リノベーション」
    const TYPE_CHUUKO_RENOVATION = 206;
    // 「建物状況調査（インスペクション）」とは
    const TYPE_HOME_INSPECTION = 207;

    // 購入時の費用について知る
    // TYPE_KNOW_COST_OF_PURCHASE 120
    // 年収、ローン…家の購入予算はどう決める？
    const TYPE_KOUNYUU_YOSAN = 208;
    // 住宅購入時に「頭金」はどのくらい必要か
    const TYPE_KOUNYUU_ATAMAKIN = 209;
    // 予算よりも高い物件は買える？その方法は？
    const TYPE_YOSAN_OVER = 210;
    // 住宅購入に必要な初期費用とは
    const TYPE_KOUNYUU_SHOKIHIYOU = 211;
    // ローン以外に住まい購入後にかかる費用は
    const TYPE_KOUNYUUGO_COST = 212;

    // 住宅ローンについて知る
    // TYPE_LEARN_MORTGAGES 121
    // 住宅ローンを利用するメリットについて
    const TYPE_LOAN_MERIT = 213;
    // 住宅ローンの金利タイプとは
    const TYPE_KINRI_TYPE = 214;
    // 住宅ローンの返済方法について
    const TYPE_HENSAI_TYPE = 215;
    // 住宅ローンの返済期間はどう考える
    const TYPE_HENSAI_KIKAN = 216;
    // 住宅ローンの審査基準ってどういうもの？
    const TYPE_SHINSA_KIJUN = 217;
    // 住宅ローンのボーナス返済とは
    const TYPE_BONUS_HENSAI = 218;
    // 住宅ローンの申込みから融資実行までの流れ
    const TYPE_SHINSA_FLOW = 219;
    // 返済で失敗しない適正な住宅ローンの組み方
    const TYPE_LOAN_KEIKAKU = 220;
    // 「フラット35」について
    const TYPE_FLAT35 = 221;
    // 住宅ローン返済を楽にする「繰上げ返済」
    const TYPE_KURIAGE_HENSAI = 222;
    // 共働き世帯のための住宅ローンとは
    const TYPE_TOMOBATARAKI_LOAN = 223;
    // 住宅ローンの借り換えについて
    const TYPE_LOAN_KARIKAE = 224;

    // 住まいの将来性について考える
    // TYPE_THINK_FUTURE_OF_YOUR_HOME 122
    // 購入時に考えるべき「住まいの将来性」とは
    const TYPE_SUMAI_SHOURAISEI = 225;
    // 購入時に考えるべき住まいの「資産価値」
    const TYPE_SHISAN_KACHI = 226;

    // 物件の現地見学、内覧について知る
    // TYPE_LEARN_SITE_AND_PREVIEWS 123
    // 一戸建て見学時の留意点
    const TYPE_KODATE_KENGAKU = 227;
    // マンション見学時の留意点
    const TYPE_MANSION_KENGAKU = 228;
    // 物件以外にも重要な現地確認とは
    const TYPE_GENCHI_KAKUNIN = 229;

    // 売買契約について知る
    // TYPE_LEARN_SALES_CONTRACTS 124
    // 購入申込みは何をする？留意点は？
    const TYPE_BUY_MOUSHIKOMI = 230;
    // 売買契約時の留意点とは
    const TYPE_BUY_KEIYAKU = 231;
    // 「重要事項説明」と注意点について
    const TYPE_BUY_JUUYOUJIKOU = 232;
    // 不動産登記手続きを知っておこう
    const TYPE_TOUKI_TETSUZUKI = 233;

    // 不動産投資について知る
    // TYPE_LEARN_REAL_ESTATE_INVESTMENT 125
    // 副業としての不動産投資を考える
    const TYPE_TOUSHI_FUKUGYOU = 234;
    // 不動産投資とはどういうものかを知ろう
    const TYPE_TOUSHI_SALARYMAN = 235;
    // 投資物件の種目ごとのメリット・デメリット
    const TYPE_TOUSHI_BUKKEN = 236;
    // 不動産投資で重要な「利回り」を理解しよう
    const TYPE_RIMAWARI = 237;
    // オーナーチェンジ物件での投資とは
    const TYPE_OWNER_CHANGE = 238;
    // 不動産投資の節税効果とは
    const TYPE_TOUSHI_SETSUZEI = 239;
    // 不動産投資のリスクを減らす分散投資とは
    const TYPE_BUNSAN_TOUSHI = 240;
    // 不動産投資の概要～目的に合った投資を～
    const TYPE_TOUSHI_TYPE = 241;
    // マンション投資で重要な「管理状況」とは
    const TYPE_MANSION_TOUSHI = 242;
    // 防犯性の高いマンションの投資効果と確認点
    const TYPE_BOUHANSEI = 243;
    // 遠方への転勤時、持ち家は売却か？賃貸か？
    const TYPE_TENKIN_MOCHIIE = 244;

    // 資金やローンについて知る
    // TYPE_KNOW_FUNDS_AND_LOANS 126
    // 不動産投資の必要経費と確定申告について
    const TYPE_TOUSHI_COST = 245;
    // 賃貸経営でのランニングコストについて
    const TYPE_RUNNING_COST = 246;
    // 「不動産投資ローン」を知ろう
    const TYPE_TOUSHI_LOAN = 247;
    // 賃貸経営で必要な「修繕」について考えよう
    const TYPE_SHUUZEN_KEIKAKU = 248;
    // 「リバースモーゲージ」とはどんなもの？
    const TYPE_REVERSE_MORTGAGE = 249;

    // 賃貸管理について知る
    // TYPE_LEARN_RENTAL_MANAGEMENT 127
    // 賃貸経営で発生するトラブル・苦情への対応
    const TYPE_TROUBLE_TAIOU = 250;
    // 「家賃滞納」時にオーナーはどう対応するか
    const TYPE_YACHIN_TAINOU = 251;
    // 「家賃保証会社」とはどういうもの？
    const TYPE_CHINTAI_HOSHOU = 252;
    // 退去時の原状回復義務と敷金返還について
    const TYPE_GENJOU_KAIFUKU = 253;
    // 所有物件の「付加価値」を高めるリフォーム
    const TYPE_CHINTAI_REFORM = 254;
    // 賃貸物件入居者のプチリフォームについて
    const TYPE_CHINTAI_DIY = 255;

    // 相続について知る
    // TYPE_KNOW_INHERITANCE 128
    // 空き家を相続したらどうすればいい？
    const TYPE_AKIYA_SOUZOKU = 256;
    // 複数の相続人での不動産相続について
    const TYPE_ISAN_BUNKATSU = 257;
    // 実家を売却する場合、相続前後でどう違う？
    const TYPE_JIKKA_BAIKYAKU = 258;
    // 不動産のみを相続した場合の相続税について
    const TYPE_SOUZOKU_ZEI = 259;
    // 相続した不動産の名義変更について
    const TYPE_MEIGI_HENKOU = 260;

    // 店舗開業の基礎知識について知る
    // TYPE_LEARN_BASIC_STORE_OPENING 129
    // 起業の形は法人設立と個人事業主のどっち？
    const TYPE_HOUJIN_KOJIN = 261;
    // 店舗開業の手順 コンセプト固め～開店まで
    const TYPE_KAIGYOU_FLOW = 262;
    // 「店舗コンセプト」が重要な理由と設定方法
    const TYPE_STORE_CONCEPT = 263;
    // 貸店舗物件の種類・特徴を知ろう
    const TYPE_KASHITENPO_TYPE = 264;
    // 事業用賃貸物件と居住用賃貸物件はどう違う
    const TYPE_JIGYOUYOU_BUKKEN = 265;
    // 店舗経営に伴うリスクと備えについて
    const TYPE_KEIEI_RISK = 266;
    // 店舗物件の「二度の引渡し」とは
    const TYPE_TENANT_HIKIWATASHI = 267;
    // 事業主が知っておくべき店舗の内装制限とは
    const TYPE_NAISOU_SEIGEN = 268;
    // フランチャイズという起業の選択肢を考える
    const TYPE_FRANCHISE = 269;
    // リースバック方式での新規店舗開業について
    const TYPE_LEASEBACK = 270;
    // 個人開業で知っておくべき確定申告について
    const TYPE_AOIRO_SHINKOKU = 271;
    // 「商店会」などの団体について
    const TYPE_SHOUTENKAI = 272;

    // 開業資金について知る
    // TYPE_LEARN_START_UP_FUNDS 130
    // 店舗開業時にかかる費用には何がある？
    const TYPE_OPENING_COST = 273;
    // 開業資金の準備・調達について
    const TYPE_KAIGYOU_SHIKIN = 274;
    // 「開業費」「創立費」の取り扱いと節税効果
    const TYPE_SOURITSUHI = 275;
    // 事業用不動産賃貸借での「権利金」について
    const TYPE_KENRI_KIN = 276;

    // 店舗物件の選び方について知る
    // TYPE_LEARN_CHOOSE_STORE 131
    // 入居する店舗はどう選ぶか
    const TYPE_STORE_ERABI = 277;
    // 失敗しない「出店場所選び」をするために
    const TYPE_STORE_LOCATION = 278;
    // 事業の成否を左右する出店立地について
    const TYPE_TENANT_RICCHI = 279;
    // 居抜き物件で開業するメリット・デメリット
    const TYPE_INUKI_BUKKEN = 280;
    // スケルトン物件を選ぶメリット・デメリット
    const TYPE_SKELETON_BUKKEN = 281;
    // 「空中店舗」での集客を考える
    const TYPE_KUUCHUU_TENPO = 282;

    // 手続き・契約について知る
    // TYPE_LEARN_PROCEDURES_AND_CONTRACTS 132
    // 店舗開業に必要な手続き・資格について
    const TYPE_KAIGYOU_TETSUZUKI = 283;
    // 「飲食店営業許可」と申請手続きについて
    const TYPE_EIGYOU_KYOKA = 284;
    // 深夜営業するときの届け出と注意すべきこと
    const TYPE_SHINYA_EIGYOU = 285;
    // 店舗の賃貸借契約時の留意点
    const TYPE_TENANT_KEIYAKU = 286;

    // 店舗設計について知る
    // TYPE_LEARN_STORE_DESIG 133
    // 失敗しないための店舗デザインの考え方
    const TYPE_STORE_DESIGN = 287;
    // 店舗レイアウトを考えよう
    const TYPE_STORE_LAYOUT = 288;
    // 店舗内外装デザインから施工の依頼について
    const TYPE_SEKOU_IRAI = 289;
    // 店舗施工でまず重要な見積もりとその見方
    const TYPE_SEKOU_MITSUMORI = 290;
    // 店舗施工で失敗しない依頼先選定時の注意点
    const TYPE_SEKOU_ITAKUSAKI = 291;
    // バリアフリーを考えた店舗づくりについて
    const TYPE_BARRIER_FREE = 292;
    // 店舗の外装について知っておくべきこと
    const TYPE_GAISOU_DESIGN = 293;

    // 賃貸物件の種類について知る
    // TYPE_LEARN_TYPES_OF_RENTAL 134
    // アパートとマンション、どちらがいいの？
    const TYPE_APART_VS_MANSION = 294;
    // タウンハウスとテラスハウスはどんなもの？
    const TYPE_TOWNHOUSE_TERRACEHOUSE = 295;
    // 賃貸で一戸建てに住むという選択肢
    const TYPE_IKKODATE_CHINTAI = 296;

    // 賃貸物件の希望条件を整理する
    // TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL 135
    // 住みたい物件の希望条件を整理しよう
    const TYPE_CHINTAI_JOUKENSEIRI = 297;
    // もう迷わない！候補物件の絞り方・選び方
    const TYPE_KOUHO_BUKKEN = 298;
    // 希望の立地条件を考える
    const TYPE_CHINTAI_RICCHI = 299;
    // 住み心地を左右する住まいの設備・仕様
    const TYPE_SETSUBI_SHIYOU = 300;
    // 気になるセキュリティー対策を確認しよう
    const TYPE_SECURITY_TAISAKU = 301;
    // インターネット環境は事前に確認しよう
    const TYPE_INTERNET_KANKYOU = 302;
    // 南向き以外も魅力！方角選びのヒント
    const TYPE_MINAMIMUKI = 303;
    // アパート・マンションの管理形態を知ろう
    const TYPE_KANRI_KEITAI = 304;
    // 子育て世帯の賃貸物件の選び方
    const TYPE_KOSODATE_CHINTAI = 305;
    // ペットと暮らすための部屋選び
    const TYPE_PET_OK = 306;
    // 住む期間に合わせた物件選びをしよう
    const TYPE_CHINTAI_KIKAN = 307;
    // 住まい選び～駅から離れた物件について～
    const TYPE_EKITOOI = 308;
    // 検討の価値あり！女性専用物件とは
    const TYPE_WOMEN_ONLY = 309;
    // 「家具・家電付き物件」を借りるという選択
    const TYPE_KAGU_KADEN = 310;

    // 家賃・諸費用について知る
    // TYPE_LEARN_RENT_EXPENSES 136
    // 家賃月額予算は全体支出をイメージしよう
    const TYPE_YACHIN_YOSAN = 311;
    // 学生一人暮らしの家賃上限はどう考える
    const TYPE_GAKUSEI_YACHIN = 312;
    // 家賃の相場を調べよう
    const TYPE_YACHIN_SOUBA = 313;
    // 賃貸物件の管理費・共益費って何？
    const TYPE_KANRIHI_KYOUEKIHI = 314;
    // 敷金・礼金・更新料についてきちんと知ろう
    const TYPE_SHIKIREI = 315;
    // 引越し費用について考えよう
    const TYPE_HIKKOSHI_COST = 316;
    // 新生活で必要なものを予算内でそろえるには
    const TYPE_SHINSEIKATSU_COST = 317;

    // 不動産会社への訪問・現地見学について知る
    // TYPE_LEARN_VISITS_COMPANIES_AND_SITE 137
    // 不動産会社に相談・訪問するときのポイント
    const TYPE_SOUDAN_POINT = 318;
    // 安心して不動産会社に行くために
    const TYPE_HOUMON_JUNBI = 319;
    // 物件内見の準備と注意点について
    const TYPE_NAIKEN_JUNBI = 320;
    // 内見での室内・建物周りのチェックポイント
    const TYPE_NAIKEN_POINT = 321;
    // 快適な暮らしに不可欠な周辺環境をチェック
    const TYPE_SHUUHEN_KANKYOU = 322;

    // 賃貸借契約について知る
    // TYPE_LEARN_LEASE_AGREEMENTS 138
    // 住みたい物件を決めたら入居申込みをしよう
    const TYPE_LENT_MOUSHIKOMI = 323;
    // 入居審査ってどういうもの？
    const TYPE_NYUUKYO_SHINSA = 324;
    // 賃貸借契約時に必要な書類とお金について
    const TYPE_KEIYAKU_COST = 325;
    // 契約前の「重要事項説明」について
    const TYPE_LENT_JUUYOUJIKOU = 326;
    // 住まいの賃貸借契約で確認しておくべきこと
    const TYPE_CHINTAISHAKU = 327;
    // 「家賃保証会社」の利用とはどういうものか
    const TYPE_YACHIN_HOSHOU = 328;
    // 賃貸物件の借主側からの中途解約について
    const TYPE_CHUUTO_KAIYAKU = 329;
    // 退去時の原状回復と敷金について
    const TYPE_KEIYAKU_GENJOU_KAIFUKU = 330;

    // 引越しについて知る
    // TYPE_KNOW_MOVING 139
    // 引越し会社の選び方
    const TYPE_HIKKOSHI_KAISHA = 331;
    // 引越し準備と当日の流れ
    const TYPE_HIKKOSHI_FLOW = 332;
    // 引越し前に新居の掃除と原状確認をしよう
    const TYPE_GENJOU_KAKUNIN = 333;
    // 効率的に引越し当日をこなすための注意点
    const TYPE_JIZEN_HANNYUU = 334;
    // 不用品・大きなゴミの処分について
    const TYPE_SODAIGOMI = 335;
    // 役所への届け出など新生活に必要な手続き
    const TYPE_TODOKEDE = 336;
    // 引越し直後から快適に暮らすために
    const TYPE_HIKKOSHI_JUNBI = 337;

    // 賃貸での暮らしについて知る
    // TYPE_LEARN_LIVING_RENT 140
    // 間取り図を見て新生活をイメージしよう
    const TYPE_MADORIZU = 338;
    // 近隣へのあいさつで安心・円滑なお付き合い
    const TYPE_KINRIN_MANNERS = 339;
    // 町内会・自治会は加入必須？メリットは？
    const TYPE_JICHIKAI = 340;

    const TYPE_LARGE_ORIGINAL = 341;
    // オリジナル小カテゴリー
    const TYPE_SMALL_ORIGINAL = 342;
    // オリジナル記事
    const TYPE_ARTICLE_ORIGINAL = 343;

    // リンク
    const TYPE_LINK = 90;

    // エイリアス
    const TYPE_ALIAS = 91;

    // 物件ページエイリアス
    const TYPE_ESTATE_ALIAS = 92;

    const TYPE_LINK_HOUSE = 93;

    /***** CATEGORY *******************/
    // カテゴリ トップページ
    const CATEGORY_TOP = 1;

    // カテゴリ 会社
    const CATEGORY_COMPANY = 2;

    // カテゴリ 物件
    const CATEGORY_STRUCTURE = 3;

    // カテゴリ オーナー向けページ
    const CATEGORY_OWNER = 4;

    // カテゴリ 法人向けページ
    const CATEGORY_CORPORATION = 5;

    // カテゴリ ○○さま向けページ
    const CATEGORY_FOR = 6;

    // カテゴリ ブログ
    const CATEGORY_BLOG = 7;

    //CMSテンプレートパターンの追加
    // カテゴリ コラム
    const CATEGORY_COLUMN = 16;
    //CMSテンプレートパターンの追加

    // カテゴリ フリーページ
    const CATEGORY_FREE = 8;

    // カテゴリ 会員ページ
    const CATEGORY_MEMBER_ONLY = 9;

    // カテゴリ その他コンテンツ
    const CATEGORY_OTHER = 10;

    // カテゴリ お知らせ
    const CATEGORY_INFO = 11;

    // カテゴリ 規約
    const CATEGORY_POLICY = 12;

    // カテゴリ サイトマップ
    const CATEGORY_SITEMAP = 13;

    // カテゴリ お問い合わせ
    const CATEGORY_FORM = 14;

    // カテゴリ リンク
    const CATEGORY_LINK = 15;

    //CMSテンプレートパターンの追加
    // 売却コンテンツ
    const CATEGORY_SALE = 17;
    // 購入コンテンツ
    const CATEGORY_PURCHASE = 18;
    // オーナー向けコンテンツ〈賃貸管理〉
    const CATEGORY_OWNERS_RENTAL_MANAGEMENT = 19;
    // 居住用賃貸コンテンツ
    const CATEGORY_RESIDENTIAL_RENTAL = 20;
    // 事業用賃貸コンテンツ
    const CATEGORY_BUSINESS_LEASE = 21;

    const CATEGORY_TOP_ARTICLE = 22;

    const CATEGORY_LARGE = 23;

    const CATEGORY_SMALL = 24;

    const CATEGORY_ARTICLE = 25;

    protected $_estateFormMap = [
        ClassList::CLASS_CHINTAI_KYOJU    => self::TYPE_FORM_LIVINGLEASE,
        ClassList::CLASS_CHINTAI_JIGYO    => self::TYPE_FORM_OFFICELEASE,
        ClassList::CLASS_BAIBAI_KYOJU    => self::TYPE_FORM_LIVINGBUY,
        ClassList::CLASS_BAIBAI_JIGYO    => self::TYPE_FORM_OFFICEBUY,
    ];

    //物件リクエスト用
    protected $_estateFormRequestMap = [
        ClassList::CLASS_CHINTAI_KYOJU => self::TYPE_FORM_REQUEST_LIVINGLEASE,
        ClassList::CLASS_CHINTAI_JIGYO => self::TYPE_FORM_REQUEST_OFFICELEASE,
        ClassList::CLASS_BAIBAI_KYOJU  => self::TYPE_FORM_REQUEST_LIVINGBUY,
        ClassList::CLASS_BAIBAI_JIGYO  => self::TYPE_FORM_REQUEST_OFFICEBUY,
    ];

    private $pages = array(

        self::TYPE_TOP                  => 'トップページ',
        self::TYPE_INFO_INDEX           => 'お知らせ一覧',
        self::TYPE_INFO_DETAIL          => 'お知らせ',
        self::TYPE_COMPANY              => '会社紹介',
        self::TYPE_HISTORY              => '会社沿革',
        self::TYPE_GREETING             => '代表挨拶',
        self::TYPE_SHOP_INDEX           => '店舗紹介',
        self::TYPE_SHOP_DETAIL          => '店舗詳細',
        self::TYPE_STAFF_INDEX          => 'スタッフ一覧',
        self::TYPE_STAFF_DETAIL         => 'スタッフ詳細',
        self::TYPE_RECRUIT              => '採用情報',
        self::TYPE_STRUCTURE_INDEX      => '物件ページ(物件コマ)一覧',
        self::TYPE_STRUCTURE_DETAIL     => '物件ページ(物件コマ)',
        self::TYPE_BLOG_INDEX           => 'ブログ一覧',
        self::TYPE_BLOG_DETAIL          => 'ブログ詳細',
        self::TYPE_PRIVACYPOLICY        => 'プライバシーポリシー',
        self::TYPE_SITEPOLICY           => 'サイトポリシー',
        self::TYPE_OWNER                => 'オーナーさま向け',
        self::TYPE_CORPORATION          => '法人向け',
        self::TYPE_TENANT               => '入居者さま向け',
        self::TYPE_BROKER               => '仲介会社さま向け',
        self::TYPE_PROPRIETARY          => '管理会社さま向け',
        self::TYPE_CITY                 => '街情報',
        self::TYPE_CUSTOMERVOICE_INDEX  => 'お客様の声一覧',
        self::TYPE_CUSTOMERVOICE_DETAIL => 'お客様の声詳細',
        self::TYPE_QA                   => 'よくあるご質問',

        /*
        self::TYPE_SCHOOL         => 'お役立ち情報 学区情報',
        self::TYPE_PREVIEW            => 'お役立ち情報 内見時のチェックポイント',
        self::TYPE_MOVING                  => 'お役立ち情報 引っ越しのチェックポイント',
        self::TYPE_TERMINOLOGY             => 'お役立ち情報 不動産用語集',
        self::TYPE_RENT               => 'お役立ち情報 住まいを借りる契約の流れ',
        self::TYPE_LEND                   => 'お役立ち情報 住まいを貸す契約の流れ',
        self::TYPE_BUY                => 'お役立ち情報 住まいを買う契約の流れ',
        self::TYPE_SELL               => 'お役立ち情報 住まいを売却する契約の流れ',
        */
        self::TYPE_SCHOOL               => '学区情報',
        self::TYPE_PREVIEW              => '内見時のチェックポイント',
        self::TYPE_MOVING               => '引越し時のチェックポイント',
        self::TYPE_TERMINOLOGY          => '不動産用語集',
        self::TYPE_RENT                 => '住まいを借りる契約の流れ',
        self::TYPE_LEND                 => '住まいを貸す契約の流れ',
        self::TYPE_BUY                  => '住まいを買う契約の流れ',
        self::TYPE_SELL                 => '住まいを売る契約の流れ',

        self::TYPE_SELLINGCASE_INDEX    => '売却事例一覧',
        self::TYPE_SELLINGCASE_DETAIL   => '売却事例詳細',
        self::TYPE_EVENT_INDEX          => 'イベント情報一覧',
        self::TYPE_EVENT_DETAIL         => 'イベント情報詳細',
        self::TYPE_LINKS                => 'リンク集',
        self::TYPE_FREE                 => 'フリーページ',
        self::TYPE_FORM_CONTACT         => 'お問い合わせ',
        //#4274 Change spec form FDP contact
        //self::TYPE_FORM_FDP_CONTACT     => '周辺情報お問い合わせ',
        self::TYPE_FORM_DOCUMENT        => '資料請求',
        self::TYPE_FORM_ASSESSMENT      => '査定依頼',

        /*
        self::TYPE_FORM_LIVINGLEASE       => '物件問い合わせ 居住用賃貸物件フォーム',
        self::TYPE_FORM_OFFICELEASE       => '物件問い合わせ 事務所用賃貸物件フォーム',
        self::TYPE_FORM_LIVINGBUY         => '物件問い合わせ 居住用売買物件フォーム',
        self::TYPE_FORM_OFFICEBUY         => '物件問い合わせ 事務所用売買物件フォーム',

        self::TYPE_FORM_LIVINGLEASE     => '居住用賃貸物件フォーム',
        self::TYPE_FORM_OFFICELEASE     => '事業用賃貸物件フォーム',
        self::TYPE_FORM_LIVINGBUY       => '居住用売買物件フォーム',
        self::TYPE_FORM_OFFICEBUY       => '事業用売買物件フォーム',
        */
        self::TYPE_FORM_LIVINGLEASE     => '物件問合せ（居住用賃貸）',
        self::TYPE_FORM_OFFICELEASE     => '物件問合せ（事業用賃貸）',
        self::TYPE_FORM_LIVINGBUY       => '物件問合せ（居住用売買）',
        self::TYPE_FORM_OFFICEBUY       => '物件問合せ（事業用売買）',

        self::TYPE_MEMBERONLY           => '会員さま専用ページ',
        self::TYPE_SITEMAP              => 'サイトマップ',

        // 物件リクエスト
        self::TYPE_FORM_REQUEST_LIVINGLEASE     => '物件リクエスト（居住用賃貸）',
        self::TYPE_FORM_REQUEST_OFFICELEASE     => '物件リクエスト（事業用賃貸）',
        self::TYPE_FORM_REQUEST_LIVINGBUY       => '物件リクエスト（居住用売買）',
        self::TYPE_FORM_REQUEST_OFFICEBUY       => '物件リクエスト（事業用売買）',

        //CMSテンプレートパターンの追加
        self::TYPE_BUSINESS_CONTENT  => '事業内容',
        self::TYPE_COLUMN_INDEX      => 'コラム一覧',
        self::TYPE_COLUMN_DETAIL     => 'コラム詳細',
        self::TYPE_COMPANY_STRENGTH  => '当社の思い・強み',
        self::TYPE_PURCHASING_REAL_ESTATE       => '「買取り」を利用してスムーズに不動産売却',
        self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN => '家を買い替える強い味方「買い替えローン」',
        self::TYPE_REPLACEMENT_AHEAD_SALE       => '家の買い替えは、購入が先か売却が先か？',
        self::TYPE_BUILDING_EVALUATION          => '中古一戸建てはどのように評価されるのか？',
        self::TYPE_BUYER_VISITS_DETACHEDHOUSE   => '現地見学で物件をアピールする方法あれこれ',
        self::TYPE_POINTS_SALE_OF_CONDOMINIUM   => 'マンションを有利な条件で売却する戦術とは',
        self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE => 'マンションVS一戸建て 選び方の基準は？',
        self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND => '新築と中古どちらを買う？その違いを知ろう',
        self::TYPE_ERECTIONHOUSING_ORDERHOUSE    => '建売住宅と注文住宅の特徴と違いとは？',
        self::TYPE_PURCHASE_BEST_TIMING          => 'マイホームはいつ買う？判断する3つの基準',
        self::TYPE_LIFE_PLAN            => '住宅資金の前にライフプランを考えよう',
        self::TYPE_TYPES_MORTGAGE_LOANS => '住宅ローンにはどんな種類がある？',
        self::TYPE_FUNDING_PLAN         => '資金計画を考えよう！ 諸費用も忘れずに',
        self::TYPE_TROUBLED_LEASING_MANAGEMENT => '賃貸管理はプロに任せるのが安心な理由',
        self::TYPE_LEASING_MANAGEMENT_MENU     => '賃貸管理サービスについて',
        self::TYPE_MEASURES_AGAINST_VACANCIES  => '空室対策の基本ポイント',
        self::TYPE_HOUSE_REMODELING            => 'ライバル物件に差をつけるリフォーム活用法',
        self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER => 'なぜ土地活用が必要なのか',
        self::TYPE_UTILIZING_LAND           => '土地活用方法それぞれの魅力とは',
        self::TYPE_PURCHASE_INHERITANCE_TAX => '不動産の購入が相続税対策に有効な理由',
        self::TYPE_UPPER_LIMIT         => '家賃の上限はどれくらい？考慮すべきは何？',
        self::TYPE_RENTAL_INITIAL_COST => '賃貸住宅の初期費用には何がある？',
        self::TYPE_SQUEEZE_CANDIDATE   => '賃貸住み替え、物件を絞り込む3ステップ',
        self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE   => '引越しのときのゴミはどうやって処分する？',
        self::TYPE_COMFORTABLELIVING_RESIDENT_RULES => '快適に暮らすために居住ルールを確認しよう',
        self::TYPE_STORE_SEARCH => '商圏調査の基本とは？長く続けるお店づくり',
        self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN       => '店舗開業成功のカギ！事業計画書の作り方',
        //CMSテンプレートパターンの追加

        self::TYPE_USEFUL_REAL_ESTATE_INFORMATION => '不動産お役立ち情報',
        self::TYPE_SALE => '売却コンテンツ',
        self::TYPE_PURCHASE => '購入コンテンツ',
        self::TYPE_OWNERS_RENTAL_MANAGEMENT => '不動産のオーナー様向けコンテンツ',
        self::TYPE_RESIDENTIAL_RENTAL => '賃貸事業用コンテンツ',
        self::TYPE_BUSINESS_LEASE => '賃貸コンテンツ',
        self::TYPE_LARGE_ORIGINAL => 'オリジナル大カテゴリー',

        self::TYPE_CHECK_FLOW_OF_SALE => '売却の流れを確認する',
        self::TYPE_LEARN_BASIC_OF_SALE => '売却の基礎知識について知る',
        self::TYPE_KNOW_MEDIATION => '仲介について知る',
        self::TYPE_KNOW_COSTS_AND_TAXES => '費用や税金について知る',
        self::TYPE_KNOW_SALE_ASSESSMENT => '査定について知る',
        self::TYPE_LEARN_LAND_SALES => '土地売却について知る',
        self::TYPE_KNOW_HOW_TO_SALE => 'うまく売る方法について知る',
        self::TYPE_KNOW_BASIC_OF_PURCHASE => '購入の基礎知識について知る',
        self::TYPE_KNOW_WHEN_TO_BUY => '購入のタイミングについて知る',
        self::TYPE_LEARN_BUY_SINGLE_FAMILY => '一戸建ての購入について知る',
        self::TYPE_LEARN_BUY_APARTMENT => 'マンションの購入について知る',
        self::TYPE_LEARN_PRE_OWNED_RENOVATION => '中古住宅、リノベーションについて知る',
        self::TYPE_KNOW_COST_OF_PURCHASE => '購入時の費用について知る',
        self::TYPE_LEARN_MORTGAGES => '住宅ローンについて知る',
        self::TYPE_THINK_FUTURE_OF_YOUR_HOME => '住まいの将来性について考える',
        self::TYPE_LEARN_SITE_AND_PREVIEWS => '物件の現地見学、内覧について知る',
        self::TYPE_LEARN_SALES_CONTRACTS => '売買契約について知る',
        self::TYPE_LEARN_REAL_ESTATE_INVESTMENT => '不動産投資について知る',
        self::TYPE_KNOW_FUNDS_AND_LOANS => '資金やローンについて知る',
        self::TYPE_LEARN_RENTAL_MANAGEMENT => '賃貸管理について知る',
        self::TYPE_KNOW_INHERITANCE => '相続について知る',
        self::TYPE_LEARN_BASIC_STORE_OPENING => '店舗開業の基礎知識について知る',
        self::TYPE_LEARN_START_UP_FUNDS => '開業資金について知る',
        self::TYPE_LEARN_CHOOSE_STORE => '店舗物件の選び方について知る',
        self::TYPE_LEARN_PROCEDURES_AND_CONTRACTS => '手続き・契約について知る',
        self::TYPE_LEARN_STORE_DESIGN => '店舗設計について知る',
        self::TYPE_LEARN_TYPES_OF_RENTAL => '賃貸物件の種類について知る',
        self::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL => '賃貸物件の希望条件を整理する',
        self::TYPE_LEARN_RENT_EXPENSES => '家賃・諸費用について知る',
        self::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => '不動産会社への訪問・現地見学について知る',
        self::TYPE_LEARN_LEASE_AGREEMENTS => '賃貸借契約について知る',
        self::TYPE_KNOW_MOVING => '引越しについて知る',
        self::TYPE_LEARN_LIVING_RENT => '賃貸での暮らしについて知る',
        self::TYPE_SMALL_ORIGINAL => 'オリジナル小カテゴリー',

        self::TYPE_LINK                 => '外部リンク',
        self::TYPE_ALIAS                => '内部リンク',
        self::TYPE_ESTATE_ALIAS         => '内部リンク',
        self::TYPE_LINK_HOUSE           => '内部リンク（物件詳細）',

        self::TYPE_BAIKYAKU_POINT => '売却は「売却理由」と「取引の流れ」が大切',
        self::TYPE_KYOJUUCHUU_BAIKYAKU => '自宅に「住みながら上手に売る方法」とは',
        self::TYPE_SELL_HIKIWATASHI => '物件の引渡しまでに売主がしておく準備とは',
        self::TYPE_BAIKYAKU_TYPE => '仲介だけではない不動産売却の4つの方法',
        self::TYPE_BAIKYAKU_SOUBA => '不動産価格の「相場」を知り上手に売るには',
        self::TYPE_SEIYAKU_KAKAKU => '査定から成約までの「価格」の違いとは',
        self::TYPE_KEIYAKU_FUTEKIGOU => '売主が負う「契約不適合責任」とは',
        self::TYPE_SHINSEI_SHORUI => '不動産売却時に必要な書類と取得方法',
        self::TYPE_KOJIN_TORIHIKI => '自力でも売れる？個人売買の可能性とリスク',
        self::TYPE_NINBAI => '「任意売却」でローン滞納の損害を最小限に',
        self::TYPE_CHUUKAI_KISO => '不動産の売却方法「仲介」を詳しく知ろう',
        self::TYPE_KYOUDOU_CHUUKAI => '「共同仲介」と「単独仲介」とは何か',
        self::TYPE_BAIKAI_KEIYAKU => '不動産会社と結ぶ「媒介契約」の種類とは',
        self::TYPE_IPPAN_BAIKAI => '売却時に選ぶ「一般媒介契約」とは',
        self::TYPE_SENZOKU_SENNIN => '売却時に選ぶ専任・専属専任媒介契約とは',
        self::TYPE_KAITORI_OSHOU => '知っておきたい「買取保証付き仲介」とは',
        self::TYPE_HYOUKAGAKU => '不動産の評価額はどのように決まるのか',
        self::TYPE_KAIKAE_KEIKAKU => '住まいの買い換えの成否は資金計画がカギ',
        self::TYPE_BAIKYAKU_COST => '不動産を売るときの諸費用はいくらかかる？',
        self::TYPE_TEITOUKEN => 'ローン残債がある住まいの抵当権抹消とは',
        self::TYPE_JOUTOSHOTOKU => '土地売却時にかかる「譲渡所得」課税とは',
        self::TYPE_TOKUBETSU_KOUJO => '売却時の「3000万円特別控除」とは',
        self::TYPE_KAKUTEI_SHINKOKU => '不動産売却後の確定申告は必要？不要？',
        self::TYPE_TSUNAGI_YUUSHI => '住まいの買い換えで使う「つなぎ融資」とは',
        self::TYPE_FUKUSUU_SATEI => '価格査定を複数会社に依頼する理由',
        self::TYPE_KANNI_SATEI => 'うまく使いたい「簡易査定」と「訪問査定」',
        self::TYPE_URERU_TOCHI => '売れやすい土地の条件と売るための対策とは',
        self::TYPE_FURUYATSUKI_SARACHI => '土地は「古家付き」「更地」どちらで売る？',
        self::TYPE_TOCHI_BAIKYAKU => '土地売却を円滑に進めるためのポイント',
        self::TYPE_KAKUTEI_SOKURYOU => '売却時に必要な土地の「境界確定測量」とは',
        self::TYPE_HATAZAOCHI => '「旗竿地」を売るために知っておきたいこと',
        self::TYPE_NOUCHI => '農地はどうすれば売れる？地目の変更とは',
        self::TYPE_BAIKYAKU_JIKI => '住まいの売却時期を決める4つのポイント',
        self::TYPE_BAIKYAKU_20Y => '築20年以上の家を売却するためのポイント',
        self::TYPE_BAIKYAKU_30Y => '築30年超の古家の売却について',
        self::TYPE_URENAI_RIYUU => '物件がなかなか売れない…その理由と対処法',
        self::TYPE_IRAISAKI_SENTAKU => '売却を依頼する不動産会社はどう選ぶ',
        self::TYPE_NAIRAN_TAIOU => '居住中の内見希望への対応ポイント',
        self::TYPE_SAIKENCHIKU_FUKA => '再建築不可物件を売却するときのポイント',
        self::TYPE_MOCHIIE_MERIT => '「賃貸」にはない「持ち家」のメリットとは',
        self::TYPE_BUY_JOUKENSEIRI => '購入物件の希望条件を整理する',
        self::TYPE_BUY_RICCHI => '住宅購入時は希望立地をよく考えよう',
        self::TYPE_MADORI => '間取りの考え方を理解して住まいを選ぶ',
        self::TYPE_SETAIBETSU => '世帯タイプ別の住まい選び',
        self::TYPE_KAIDAN_TYPE => '購入前に知っておきたい住まいの「階段」',
        self::TYPE_SEINOU_HYOUKA => '「住宅の性能評価」とは',
        self::TYPE_BUY_KEIYAKU_FLOW => '物件購入の申込み前から売買契約までの流れ',
        self::TYPE_SAISHUU_KAKUNIN => '物件の最終確認と残代金の精算・引渡し',
        self::TYPE_NYUUKYO_FLOW => 'マイホームの引渡しから入居までの流れ',
        self::TYPE_COMMUNICATION => '販売担当者との上手なコミュニケーション術',
        self::TYPE_SHINCHIKU_NAIRANKAI => '新築物件の内覧会と入居説明会について',
        self::TYPE_NYUUKYO_TROUBLE => '入居後のトラブルへの対応について',
        self::TYPE_KOUNYUU_JIKI => '住まいの「買いどき」について考えよう',
        self::TYPE_20DAI_KOUNYUU => '20代の住まい購入のポイント',
        self::TYPE_30DAI_KOUNYUU => '30～40代の住まい購入のポイント',
        self::TYPE_50DAI_KOUNYUU => '50～60代の住まい購入のポイント',
        self::TYPE_TOCHI_ERABI => '一戸建て購入で大切な土地選び',
        self::TYPE_KENCHIKU_JOUKENTSUKI => '意外に知らない「建築条件付き土地」とは',
        self::TYPE_NISETAI_JUUTAKU => '住まい方で違う「二世帯住宅」のタイプとは',
        self::TYPE_KODATE_SHINSEIKATSU => '一戸建ての新生活について',
        self::TYPE_MANSION_TYPE => '大規模？高層？マンションのタイプを知ろう',
        self::TYPE_MAISONETTE_MANSION => '一戸建て感覚で住めるマンションとは',
        self::TYPE_MANSION_SERVICE => '魅力的なマンションの共用施設・サービス',
        self::TYPE_MANSION_SHINSEIKATSU => 'マンションの新生活について',
        self::TYPE_RENOVATION_BUKKEN => '注目の「リノベーション物件」とは',
        self::TYPE_CHUUKO_RENOVATION => '理想への近道は「中古＋リノベーション」',
        self::TYPE_HOME_INSPECTION => '「建物状況調査（インスペクション）」とは',
        self::TYPE_KOUNYUU_YOSAN => '年収、ローン…家の購入予算はどう決める？',
        self::TYPE_KOUNYUU_ATAMAKIN => '住宅購入時に「頭金」はどのくらい必要か',
        self::TYPE_YOSAN_OVER => '予算よりも高い物件は買える？その方法は？',
        self::TYPE_KOUNYUU_SHOKIHIYOU => '住宅購入に必要な初期費用とは',
        self::TYPE_KOUNYUUGO_COST => 'ローン以外に住まい購入後にかかる費用は',
        self::TYPE_LOAN_MERIT => '住宅ローンを利用するメリットについて',
        self::TYPE_KINRI_TYPE => '住宅ローンの金利タイプとは',
        self::TYPE_HENSAI_TYPE => '住宅ローンの返済方法について',
        self::TYPE_HENSAI_KIKAN => '住宅ローンの返済期間はどう考える',
        self::TYPE_SHINSA_KIJUN => '住宅ローンの審査基準ってどういうもの？',
        self::TYPE_BONUS_HENSAI => '住宅ローンのボーナス返済とは',
        self::TYPE_SHINSA_FLOW => '住宅ローンの申込みから融資実行までの流れ',
        self::TYPE_LOAN_KEIKAKU => '返済で失敗しない適正な住宅ローンの組み方',
        self::TYPE_FLAT35 => '「フラット35」について',
        self::TYPE_KURIAGE_HENSAI => '住宅ローン返済を楽にする「繰上げ返済」',
        self::TYPE_TOMOBATARAKI_LOAN => '共働き世帯のための住宅ローンとは',
        self::TYPE_LOAN_KARIKAE => '住宅ローンの借り換えについて',
        self::TYPE_SUMAI_SHOURAISEI => '購入時に考えるべき「住まいの将来性」とは',
        self::TYPE_SHISAN_KACHI => '購入時に考えるべき住まいの「資産価値」',
        self::TYPE_KODATE_KENGAKU => '一戸建て見学時の留意点',
        self::TYPE_MANSION_KENGAKU => 'マンション見学時の留意点',
        self::TYPE_GENCHI_KAKUNIN => '物件以外にも重要な現地確認とは',
        self::TYPE_BUY_MOUSHIKOMI => '購入申込みは何をする？留意点は？',
        self::TYPE_BUY_KEIYAKU => '売買契約時の留意点とは',
        self::TYPE_BUY_JUUYOUJIKOU => '「重要事項説明」と注意点について',
        self::TYPE_TOUKI_TETSUZUKI => '不動産登記手続きを知っておこう',
        self::TYPE_TOUSHI_FUKUGYOU => '副業としての不動産投資を考える',
        self::TYPE_TOUSHI_SALARYMAN => '不動産投資とはどういうものかを知ろう',
        self::TYPE_TOUSHI_BUKKEN => '投資物件の種目ごとのメリット・デメリット',
        self::TYPE_RIMAWARI => '不動産投資で重要な「利回り」を理解しよう',
        self::TYPE_OWNER_CHANGE => 'オーナーチェンジ物件での投資とは',
        self::TYPE_TOUSHI_SETSUZEI => '不動産投資の節税効果とは',
        self::TYPE_BUNSAN_TOUSHI => '不動産投資のリスクを減らす分散投資とは',
        self::TYPE_TOUSHI_TYPE => '不動産投資の概要～目的に合った投資を～',
        self::TYPE_MANSION_TOUSHI => 'マンション投資で重要な「管理状況」とは',
        self::TYPE_BOUHANSEI => '防犯性の高いマンションの投資効果と確認点',
        self::TYPE_TENKIN_MOCHIIE => '遠方への転勤時、持ち家は売却か？賃貸か？',
        self::TYPE_TOUSHI_COST => '不動産投資の必要経費と確定申告について',
        self::TYPE_RUNNING_COST => '賃貸経営でのランニングコストについて',
        self::TYPE_TOUSHI_LOAN => '「不動産投資ローン」を知ろう',
        self::TYPE_SHUUZEN_KEIKAKU => '賃貸経営で必要な「修繕」について考えよう',
        self::TYPE_REVERSE_MORTGAGE => '「リバースモーゲージ」とはどんなもの？',
        self::TYPE_TROUBLE_TAIOU => '賃貸経営で発生するトラブル・苦情への対応',
        self::TYPE_YACHIN_TAINOU => '「家賃滞納」時にオーナーはどう対応するか',
        self::TYPE_CHINTAI_HOSHOU => '「家賃保証会社」とはどういうもの？',
        self::TYPE_GENJOU_KAIFUKU => '退去時の原状回復義務と敷金返還について',
        self::TYPE_CHINTAI_REFORM => '所有物件の「付加価値」を高めるリフォーム',
        self::TYPE_CHINTAI_DIY => '賃貸物件入居者のプチリフォームについて',
        self::TYPE_AKIYA_SOUZOKU => '空き家を相続したらどうすればいい？',
        self::TYPE_ISAN_BUNKATSU => '複数の相続人での不動産相続について',
        self::TYPE_JIKKA_BAIKYAKU => '実家を売却する場合、相続前後でどう違う？',
        self::TYPE_SOUZOKU_ZEI => '不動産のみを相続した場合の相続税について',
        self::TYPE_MEIGI_HENKOU => '相続した不動産の名義変更について',
        self::TYPE_HOUJIN_KOJIN => '起業の形は法人設立と個人事業主のどっち？',
        self::TYPE_KAIGYOU_FLOW => '店舗開業の手順 コンセプト固め～開店まで',
        self::TYPE_STORE_CONCEPT => '「店舗コンセプト」が重要な理由と設定方法',
        self::TYPE_KASHITENPO_TYPE => '貸店舗物件の種類・特徴を知ろう',
        self::TYPE_JIGYOUYOU_BUKKEN => '事業用賃貸物件と居住用賃貸物件はどう違う',
        self::TYPE_KEIEI_RISK => '店舗経営に伴うリスクと備えについて',
        self::TYPE_TENANT_HIKIWATASHI => '店舗物件の「二度の引渡し」とは',
        self::TYPE_NAISOU_SEIGEN => '事業主が知っておくべき店舗の内装制限とは',
        self::TYPE_FRANCHISE => 'フランチャイズという起業の選択肢を考える',
        self::TYPE_LEASEBACK => 'リースバック方式での新規店舗開業について',
        self::TYPE_AOIRO_SHINKOKU => '個人開業で知っておくべき確定申告について',
        self::TYPE_SHOUTENKAI => '「商店会」などの団体について',
        self::TYPE_OPENING_COST => '店舗開業時にかかる費用には何がある？',
        self::TYPE_KAIGYOU_SHIKIN => '開業資金の準備・調達について',
        self::TYPE_SOURITSUHI => '「開業費」「創立費」の取り扱いと節税効果',
        self::TYPE_KENRI_KIN => '事業用不動産賃貸借での「権利金」について',
        self::TYPE_STORE_ERABI => '入居する店舗はどう選ぶか',
        self::TYPE_STORE_LOCATION => '失敗しない「出店場所選び」をするために',
        self::TYPE_TENANT_RICCHI => '事業の成否を左右する出店立地について',
        self::TYPE_INUKI_BUKKEN => '居抜き物件で開業するメリット・デメリット',
        self::TYPE_SKELETON_BUKKEN => 'スケルトン物件を選ぶメリット・デメリット',
        self::TYPE_KUUCHUU_TENPO => '「空中店舗」での集客を考える',
        self::TYPE_KAIGYOU_TETSUZUKI => '店舗開業に必要な手続き・資格について',
        self::TYPE_EIGYOU_KYOKA => '「飲食店営業許可」と申請手続きについて',
        self::TYPE_SHINYA_EIGYOU => '深夜営業するときの届け出と注意すべきこと',
        self::TYPE_TENANT_KEIYAKU => '店舗の賃貸借契約時の留意点',
        self::TYPE_STORE_DESIGN => '失敗しないための店舗デザインの考え方',
        self::TYPE_STORE_LAYOUT => '店舗レイアウトを考えよう',
        self::TYPE_SEKOU_IRAI => '店舗内外装デザインから施工の依頼について',
        self::TYPE_SEKOU_MITSUMORI => '店舗施工でまず重要な見積もりとその見方',
        self::TYPE_SEKOU_ITAKUSAKI => '店舗施工で失敗しない依頼先選定時の注意点',
        self::TYPE_BARRIER_FREE => 'バリアフリーを考えた店舗づくりについて',
        self::TYPE_GAISOU_DESIGN => '店舗の外装について知っておくべきこと',
        self::TYPE_APART_VS_MANSION => 'アパートとマンション、どちらがいいの？',
        self::TYPE_TOWNHOUSE_TERRACEHOUSE => 'タウンハウスとテラスハウスはどんなもの？',
        self::TYPE_IKKODATE_CHINTAI => '賃貸で一戸建てに住むという選択肢',
        self::TYPE_CHINTAI_JOUKENSEIRI => '住みたい物件の希望条件を整理しよう',
        self::TYPE_KOUHO_BUKKEN => 'もう迷わない！候補物件の絞り方・選び方',
        self::TYPE_CHINTAI_RICCHI => '希望の立地条件を考える',
        self::TYPE_SETSUBI_SHIYOU => '住み心地を左右する住まいの設備・仕様',
        self::TYPE_SECURITY_TAISAKU => '気になるセキュリティー対策を確認しよう',
        self::TYPE_INTERNET_KANKYOU => 'インターネット環境は事前に確認しよう',
        self::TYPE_MINAMIMUKI => '南向き以外も魅力！方角選びのヒント',
        self::TYPE_KANRI_KEITAI => 'アパート・マンションの管理形態を知ろう',
        self::TYPE_KOSODATE_CHINTAI => '子育て世帯の賃貸物件の選び方',
        self::TYPE_PET_OK => 'ペットと暮らすための部屋選び',
        self::TYPE_CHINTAI_KIKAN => '住む期間に合わせた物件選びをしよう',
        self::TYPE_EKITOOI => '住まい選び～駅から離れた物件について～',
        self::TYPE_WOMEN_ONLY => '検討の価値あり！女性専用物件とは',
        self::TYPE_KAGU_KADEN => '「家具・家電付き物件」を借りるという選択',
        self::TYPE_YACHIN_YOSAN => '家賃月額予算は全体支出をイメージしよう',
        self::TYPE_GAKUSEI_YACHIN => '学生一人暮らしの家賃上限はどう考える',
        self::TYPE_YACHIN_SOUBA => '家賃の相場を調べよう',
        self::TYPE_KANRIHI_KYOUEKIHI => '賃貸物件の管理費・共益費って何？',
        self::TYPE_SHIKIREI => '敷金・礼金・更新料についてきちんと知ろう',
        self::TYPE_HIKKOSHI_COST => '引越し費用について考えよう',
        self::TYPE_SHINSEIKATSU_COST => '新生活で必要なものを予算内でそろえるには',
        self::TYPE_SOUDAN_POINT => '不動産会社に相談・訪問するときのポイント',
        self::TYPE_HOUMON_JUNBI => '安心して不動産会社に行くために',
        self::TYPE_NAIKEN_JUNBI => '物件内見の準備と注意点について',
        self::TYPE_NAIKEN_POINT => '内見での室内・建物周りのチェックポイント',
        self::TYPE_SHUUHEN_KANKYOU => '快適な暮らしに不可欠な周辺環境をチェック',
        self::TYPE_LENT_MOUSHIKOMI => '住みたい物件を決めたら入居申込みをしよう',
        self::TYPE_NYUUKYO_SHINSA => '入居審査ってどういうもの？',
        self::TYPE_KEIYAKU_COST => '賃貸借契約時に必要な書類とお金について',
        self::TYPE_LENT_JUUYOUJIKOU => '契約前の「重要事項説明」について',
        self::TYPE_CHINTAISHAKU => '住まいの賃貸借契約で確認しておくべきこと',
        self::TYPE_YACHIN_HOSHOU => '「家賃保証会社」の利用とはどういうものか',
        self::TYPE_CHUUTO_KAIYAKU => '賃貸物件の借主側からの中途解約について',
        self::TYPE_KEIYAKU_GENJOU_KAIFUKU => '退去時の原状回復と敷金について',
        self::TYPE_HIKKOSHI_KAISHA => '引越し会社の選び方',
        self::TYPE_HIKKOSHI_FLOW => '引越し準備と当日の流れ',
        self::TYPE_GENJOU_KAKUNIN => '引越し前に新居の掃除と原状確認をしよう',
        self::TYPE_JIZEN_HANNYUU => '効率的に引越し当日をこなすための注意点',
        self::TYPE_SODAIGOMI => '不用品・大きなゴミの処分について',
        self::TYPE_TODOKEDE => '役所への届け出など新生活に必要な手続き',
        self::TYPE_HIKKOSHI_JUNBI => '引越し直後から快適に暮らすために',
        self::TYPE_MADORIZU => '間取り図を見て新生活をイメージしよう',
        self::TYPE_KINRIN_MANNERS => '近隣へのあいさつで安心・円滑なお付き合い',
        self::TYPE_JICHIKAI => '町内会・自治会は加入必須？メリットは？',
        self::TYPE_ARTICLE_ORIGINAL => 'オリジナル記事',
    );

    //各ページのディスクリプション用の文言
    private $pages_description = array(
        self::TYPE_TOP                  => '',
        self::TYPE_COMPANY              => '会社紹介',
        self::TYPE_HISTORY              => '会社沿革',
        self::TYPE_GREETING             => '代表挨拶',
        self::TYPE_RECRUIT              => '採用情報',
        self::TYPE_SHOP_INDEX           => '店舗紹介',
        self::TYPE_SHOP_DETAIL          => '',
        self::TYPE_STAFF_INDEX          => 'スタッフ一覧',
        self::TYPE_STAFF_DETAIL         => '',
        self::TYPE_OWNER                => 'オーナーさま向け',
        self::TYPE_CORPORATION          => '法人向け',
        self::TYPE_TENANT               => '入居者さま向け',
        self::TYPE_BROKER               => '仲介会社さま向け',
        self::TYPE_PROPRIETARY          => '管理会社さま向け',
        self::TYPE_BLOG_INDEX           => 'ブログ一覧',
        self::TYPE_BLOG_DETAIL          => '',
        self::TYPE_FREE                 => '',
        self::TYPE_MEMBERONLY           => '会員さま専用ページ',
        self::TYPE_CITY                 => '',
        self::TYPE_CUSTOMERVOICE_INDEX  => 'お客様の声一覧',
        self::TYPE_CUSTOMERVOICE_DETAIL => '',
        self::TYPE_SELLINGCASE_INDEX    => '売却事例一覧',
        self::TYPE_SELLINGCASE_DETAIL   => '',
        self::TYPE_EVENT_INDEX          => 'イベント情報一覧',
        self::TYPE_EVENT_DETAIL         => '',
        self::TYPE_QA                   => 'よくあるご質問',
        self::TYPE_LINKS                => 'リンク集',
        self::TYPE_SCHOOL               => '学区情報',
        self::TYPE_PREVIEW              => '内見時のチェックポイント',
        self::TYPE_MOVING               => '引越し時のチェックポイント',
        self::TYPE_TERMINOLOGY          => '不動産用語集',
        self::TYPE_RENT                 => '住まいを借りる契約の流れ',
        self::TYPE_LEND                 => '住まいを貸す契約の流れ',
        self::TYPE_BUY                  => '住まいを買う契約の流れ',
        self::TYPE_SELL                 => '住まいを売る契約の流れ',
        self::TYPE_INFO_INDEX           => 'お知らせ一覧',
        self::TYPE_INFO_DETAIL          => '',
        self::TYPE_PRIVACYPOLICY        => 'プライバシーポリシー',
        self::TYPE_SITEPOLICY           => 'サイトポリシー',
        self::TYPE_SITEMAP              => 'サイトマップ',
        self::TYPE_FORM_CONTACT         => 'お問い合わせ',
        //#4274 Change spec form FDP contact
        //self::TYPE_FORM_FDP_CONTACT     => '周辺情報お問い合わせ',
        self::TYPE_FORM_DOCUMENT        => '資料請求',
        self::TYPE_FORM_ASSESSMENT      => '査定依頼',

        // 物件リクエスト
        self::TYPE_FORM_REQUEST_LIVINGLEASE     => '物件リクエスト（居住用賃貸）',
        self::TYPE_FORM_REQUEST_OFFICELEASE     => '物件リクエスト（事業用賃貸）',
        self::TYPE_FORM_REQUEST_LIVINGBUY       => '物件リクエスト（居住用売買）',
        self::TYPE_FORM_REQUEST_OFFICEBUY       => '物件リクエスト（事業用売買）',

        //CMSテンプレートパターンの追加
        self::TYPE_BUSINESS_CONTENT  => '事業内容',
        self::TYPE_COLUMN_INDEX      => 'コラム一覧',
        self::TYPE_COLUMN_DETAIL     => '',
        self::TYPE_COMPANY_STRENGTH  => '当社の思い・強み',
        self::TYPE_PURCHASING_REAL_ESTATE       => '「買取り」を利用してスムーズに不動産売却',
        self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN => '家を買い替える強い味方「買い替えローン」',
        self::TYPE_REPLACEMENT_AHEAD_SALE       => '家の買い替えは、購入が先か売却が先か？',
        self::TYPE_BUILDING_EVALUATION          => '中古一戸建てはどのように評価されるのか？',
        self::TYPE_BUYER_VISITS_DETACHEDHOUSE   => '現地見学で物件をアピールする方法あれこれ',
        self::TYPE_POINTS_SALE_OF_CONDOMINIUM   => 'マンションを有利な条件で売却する戦術とは',
        self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE => 'マンションVS一戸建て 選び方の基準は？',
        self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND => '新築と中古どちらを買う？その違いを知ろう',
        self::TYPE_ERECTIONHOUSING_ORDERHOUSE    => '建売住宅と注文住宅の特徴と違いとは？',
        self::TYPE_PURCHASE_BEST_TIMING          => 'マイホームはいつ買う？判断する3つの基準',
        self::TYPE_LIFE_PLAN            => '住宅資金の前にライフプランを考えよう',
        self::TYPE_TYPES_MORTGAGE_LOANS => '住宅ローンにはどんな種類がある？',
        self::TYPE_FUNDING_PLAN         => '資金計画を考えよう！ 諸費用も忘れずに',
        self::TYPE_TROUBLED_LEASING_MANAGEMENT => '賃貸管理はプロに任せるのが安心な理由',
        self::TYPE_LEASING_MANAGEMENT_MENU     => '賃貸管理サービスについて',
        self::TYPE_MEASURES_AGAINST_VACANCIES  => '空室対策の基本ポイント',
        self::TYPE_HOUSE_REMODELING            => 'ライバル物件に差をつけるリフォーム活用法',
        self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER => 'なぜ土地活用が必要なのか',
        self::TYPE_UTILIZING_LAND           => '土地活用方法それぞれの魅力とは',
        self::TYPE_PURCHASE_INHERITANCE_TAX => '不動産の購入が相続税対策に有効な理由',
        self::TYPE_UPPER_LIMIT         => '家賃の上限はどれくらい？考慮すべきは何？',
        self::TYPE_RENTAL_INITIAL_COST => '賃貸住宅の初期費用には何がある？',
        self::TYPE_SQUEEZE_CANDIDATE   => '賃貸住み替え、物件を絞り込む3ステップ',
        self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE   => '引越しのときのゴミはどうやって処分する？',
        self::TYPE_COMFORTABLELIVING_RESIDENT_RULES => '快適に暮らすために居住ルールを確認しよう',
        self::TYPE_STORE_SEARCH                     => '商圏調査の基本とは？長く続けるお店づくり',
        self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN       => '店舗開業成功のカギ！事業計画書の作り方',
        //CMSテンプレートパターンの追加

        self::TYPE_USEFUL_REAL_ESTATE_INFORMATION => '不動産お役立ち情報',
        self::TYPE_SALE => '売却コンテンツ',
        self::TYPE_PURCHASE => '購入コンテンツ',
        self::TYPE_OWNERS_RENTAL_MANAGEMENT => '不動産のオーナー様向けコンテンツ',
        self::TYPE_RESIDENTIAL_RENTAL => '賃貸事業用コンテンツ',
        self::TYPE_BUSINESS_LEASE => '賃貸コンテンツ',
        self::TYPE_LARGE_ORIGINAL => 'カテゴリー一覧',

        self::TYPE_CHECK_FLOW_OF_SALE => '売却の流れを確認する',
        self::TYPE_LEARN_BASIC_OF_SALE => '売却の基礎知識について知る',
        self::TYPE_KNOW_MEDIATION => '仲介について知る',
        self::TYPE_KNOW_COSTS_AND_TAXES => '費用や税金について知る',
        self::TYPE_KNOW_SALE_ASSESSMENT => '査定について知る',
        self::TYPE_LEARN_LAND_SALES => '土地売却について知る',
        self::TYPE_KNOW_HOW_TO_SALE => 'うまく売る方法について知る',
        self::TYPE_KNOW_BASIC_OF_PURCHASE => '購入の基礎知識について知る',
        self::TYPE_KNOW_WHEN_TO_BUY => '購入のタイミングについて知る',
        self::TYPE_LEARN_BUY_SINGLE_FAMILY => '一戸建ての購入について知る',
        self::TYPE_LEARN_BUY_APARTMENT => 'マンションの購入について知る',
        self::TYPE_LEARN_PRE_OWNED_RENOVATION => '中古住宅、リノベーションについて知る',
        self::TYPE_KNOW_COST_OF_PURCHASE => '購入時の費用について知る',
        self::TYPE_LEARN_MORTGAGES => '住宅ローンについて知る',
        self::TYPE_THINK_FUTURE_OF_YOUR_HOME => '住まいの将来性について考える',
        self::TYPE_LEARN_SITE_AND_PREVIEWS => '物件の現地見学、内覧について知る',
        self::TYPE_LEARN_SALES_CONTRACTS => '売買契約について知る',
        self::TYPE_LEARN_REAL_ESTATE_INVESTMENT => '不動産投資について知る',
        self::TYPE_KNOW_FUNDS_AND_LOANS => '資金やローンについて知る',
        self::TYPE_LEARN_RENTAL_MANAGEMENT => '賃貸管理について知る',
        self::TYPE_KNOW_INHERITANCE => '相続について知る',
        self::TYPE_LEARN_BASIC_STORE_OPENING => '店舗開業の基礎知識について知る',
        self::TYPE_LEARN_START_UP_FUNDS => '開業資金について知る',
        self::TYPE_LEARN_CHOOSE_STORE => '店舗物件の選び方について知る',
        self::TYPE_LEARN_PROCEDURES_AND_CONTRACTS => '手続き・契約について知る',
        self::TYPE_LEARN_STORE_DESIGN => '店舗設計について知る',
        self::TYPE_LEARN_TYPES_OF_RENTAL => '賃貸物件の種類について知る',
        self::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL => '賃貸物件の希望条件を整理する',
        self::TYPE_LEARN_RENT_EXPENSES => '家賃・諸費用について知る',
        self::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => '不動産会社への訪問・現地見学について知る',
        self::TYPE_LEARN_LEASE_AGREEMENTS => '賃貸借契約について知る',
        self::TYPE_KNOW_MOVING => '引越しについて知る',
        self::TYPE_LEARN_LIVING_RENT => '賃貸での暮らしについて知る',
        self::TYPE_SMALL_ORIGINAL => '記事一覧',

        self::TYPE_BAIKYAKU_POINT => '売却は「売却理由」と「取引の流れ」が大切',
        self::TYPE_KYOJUUCHUU_BAIKYAKU => '自宅に「住みながら上手に売る方法」とは',
        self::TYPE_SELL_HIKIWATASHI => '物件の引渡しまでに売主がしておく準備とは',
        self::TYPE_BAIKYAKU_TYPE => '仲介だけではない不動産売却の4つの方法',
        self::TYPE_BAIKYAKU_SOUBA => '不動産価格の「相場」を知り上手に売るには',
        self::TYPE_SEIYAKU_KAKAKU => '査定から成約までの「価格」の違いとは',
        self::TYPE_KEIYAKU_FUTEKIGOU => '売主が負う「契約不適合責任」とは',
        self::TYPE_SHINSEI_SHORUI => '不動産売却時に必要な書類と取得方法',
        self::TYPE_KOJIN_TORIHIKI => '自力でも売れる？個人売買の可能性とリスク',
        self::TYPE_NINBAI => '「任意売却」でローン滞納の損害を最小限に',
        self::TYPE_CHUUKAI_KISO => '不動産の売却方法「仲介」を詳しく知ろう',
        self::TYPE_KYOUDOU_CHUUKAI => '「共同仲介」と「単独仲介」とは何か',
        self::TYPE_BAIKAI_KEIYAKU => '不動産会社と結ぶ「媒介契約」の種類とは',
        self::TYPE_IPPAN_BAIKAI => '売却時に選ぶ「一般媒介契約」とは',
        self::TYPE_SENZOKU_SENNIN => '売却時に選ぶ専任・専属専任媒介契約とは',
        self::TYPE_KAITORI_OSHOU => '知っておきたい「買取保証付き仲介」とは',
        self::TYPE_HYOUKAGAKU => '不動産の評価額はどのように決まるのか',
        self::TYPE_KAIKAE_KEIKAKU => '住まいの買い換えの成否は資金計画がカギ',
        self::TYPE_BAIKYAKU_COST => '不動産を売るときの諸費用はいくらかかる？',
        self::TYPE_TEITOUKEN => 'ローン残債がある住まいの抵当権抹消とは',
        self::TYPE_JOUTOSHOTOKU => '土地売却時にかかる「譲渡所得」課税とは',
        self::TYPE_TOKUBETSU_KOUJO => '売却時の「3000万円特別控除」とは',
        self::TYPE_KAKUTEI_SHINKOKU => '不動産売却後の確定申告は必要？不要？',
        self::TYPE_TSUNAGI_YUUSHI => '住まいの買い換えで使う「つなぎ融資」とは',
        self::TYPE_FUKUSUU_SATEI => '価格査定を複数会社に依頼する理由',
        self::TYPE_KANNI_SATEI => 'うまく使いたい「簡易査定」と「訪問査定」',
        self::TYPE_URERU_TOCHI => '売れやすい土地の条件と売るための対策とは',
        self::TYPE_FURUYATSUKI_SARACHI => '土地は「古家付き」「更地」どちらで売る？',
        self::TYPE_TOCHI_BAIKYAKU => '土地売却を円滑に進めるためのポイント',
        self::TYPE_KAKUTEI_SOKURYOU => '売却時に必要な土地の「境界確定測量」とは',
        self::TYPE_HATAZAOCHI => '「旗竿地」を売るために知っておきたいこと',
        self::TYPE_NOUCHI => '農地はどうすれば売れる？地目の変更とは',
        self::TYPE_BAIKYAKU_JIKI => '住まいの売却時期を決める4つのポイント',
        self::TYPE_BAIKYAKU_20Y => '築20年以上の家を売却するためのポイント',
        self::TYPE_BAIKYAKU_30Y => '築30年超の古家の売却について',
        self::TYPE_URENAI_RIYUU => '物件がなかなか売れない…その理由と対処法',
        self::TYPE_IRAISAKI_SENTAKU => '売却を依頼する不動産会社はどう選ぶ',
        self::TYPE_NAIRAN_TAIOU => '居住中の内見希望への対応ポイント',
        self::TYPE_SAIKENCHIKU_FUKA => '再建築不可物件を売却するときのポイント',
        self::TYPE_MOCHIIE_MERIT => '「賃貸」にはない「持ち家」のメリットとは',
        self::TYPE_BUY_JOUKENSEIRI => '購入物件の希望条件を整理する',
        self::TYPE_BUY_RICCHI => '住宅購入時は希望立地をよく考えよう',
        self::TYPE_MADORI => '間取りの考え方を理解して住まいを選ぶ',
        self::TYPE_SETAIBETSU => '世帯タイプ別の住まい選び',
        self::TYPE_KAIDAN_TYPE => '購入前に知っておきたい住まいの「階段」',
        self::TYPE_SEINOU_HYOUKA => '「住宅の性能評価」とは',
        self::TYPE_BUY_KEIYAKU_FLOW => '物件購入の申込み前から売買契約までの流れ',
        self::TYPE_SAISHUU_KAKUNIN => '物件の最終確認と残代金の精算・引渡し',
        self::TYPE_NYUUKYO_FLOW => 'マイホームの引渡しから入居までの流れ',
        self::TYPE_COMMUNICATION => '販売担当者との上手なコミュニケーション術',
        self::TYPE_SHINCHIKU_NAIRANKAI => '新築物件の内覧会と入居説明会について',
        self::TYPE_NYUUKYO_TROUBLE => '入居後のトラブルへの対応について',
        self::TYPE_KOUNYUU_JIKI => '住まいの「買いどき」について考えよう',
        self::TYPE_20DAI_KOUNYUU => '20代の住まい購入のポイント',
        self::TYPE_30DAI_KOUNYUU => '30～40代の住まい購入のポイント',
        self::TYPE_50DAI_KOUNYUU => '50～60代の住まい購入のポイント',
        self::TYPE_TOCHI_ERABI => '一戸建て購入で大切な土地選び',
        self::TYPE_KENCHIKU_JOUKENTSUKI => '意外に知らない「建築条件付き土地」とは',
        self::TYPE_NISETAI_JUUTAKU => '住まい方で違う「二世帯住宅」のタイプとは',
        self::TYPE_KODATE_SHINSEIKATSU => '一戸建ての新生活について',
        self::TYPE_MANSION_TYPE => '大規模？高層？マンションのタイプを知ろう',
        self::TYPE_MAISONETTE_MANSION => '一戸建て感覚で住めるマンションとは',
        self::TYPE_MANSION_SERVICE => '魅力的なマンションの共用施設・サービス',
        self::TYPE_MANSION_SHINSEIKATSU => 'マンションの新生活について',
        self::TYPE_RENOVATION_BUKKEN => '注目の「リノベーション物件」とは',
        self::TYPE_CHUUKO_RENOVATION => '理想への近道は「中古＋リノベーション」',
        self::TYPE_HOME_INSPECTION => '「建物状況調査（インスペクション）」とは',
        self::TYPE_KOUNYUU_YOSAN => '年収、ローン…家の購入予算はどう決める？',
        self::TYPE_KOUNYUU_ATAMAKIN => '住宅購入時に「頭金」はどのくらい必要か',
        self::TYPE_YOSAN_OVER => '予算よりも高い物件は買える？その方法は？',
        self::TYPE_KOUNYUU_SHOKIHIYOU => '住宅購入に必要な初期費用とは',
        self::TYPE_KOUNYUUGO_COST => 'ローン以外に住まい購入後にかかる費用は',
        self::TYPE_LOAN_MERIT => '住宅ローンを利用するメリットについて',
        self::TYPE_KINRI_TYPE => '住宅ローンの金利タイプとは',
        self::TYPE_HENSAI_TYPE => '住宅ローンの返済方法について',
        self::TYPE_HENSAI_KIKAN => '住宅ローンの返済期間はどう考える',
        self::TYPE_SHINSA_KIJUN => '住宅ローンの審査基準ってどういうもの？',
        self::TYPE_BONUS_HENSAI => '住宅ローンのボーナス返済とは',
        self::TYPE_SHINSA_FLOW => '住宅ローンの申込みから融資実行までの流れ',
        self::TYPE_LOAN_KEIKAKU => '返済で失敗しない適正な住宅ローンの組み方',
        self::TYPE_FLAT35 => '「フラット35」について',
        self::TYPE_KURIAGE_HENSAI => '住宅ローン返済を楽にする「繰上げ返済」',
        self::TYPE_TOMOBATARAKI_LOAN => '共働き世帯のための住宅ローンとは',
        self::TYPE_LOAN_KARIKAE => '住宅ローンの借り換えについて',
        self::TYPE_SUMAI_SHOURAISEI => '購入時に考えるべき「住まいの将来性」とは',
        self::TYPE_SHISAN_KACHI => '購入時に考えるべき住まいの「資産価値」',
        self::TYPE_KODATE_KENGAKU => '一戸建て見学時の留意点',
        self::TYPE_MANSION_KENGAKU => 'マンション見学時の留意点',
        self::TYPE_GENCHI_KAKUNIN => '物件以外にも重要な現地確認とは',
        self::TYPE_BUY_MOUSHIKOMI => '購入申込みは何をする？留意点は？',
        self::TYPE_BUY_KEIYAKU => '売買契約時の留意点とは',
        self::TYPE_BUY_JUUYOUJIKOU => '「重要事項説明」と注意点について',
        self::TYPE_TOUKI_TETSUZUKI => '不動産登記手続きを知っておこう',
        self::TYPE_TOUSHI_FUKUGYOU => '副業としての不動産投資を考える',
        self::TYPE_TOUSHI_SALARYMAN => '不動産投資とはどういうものかを知ろう',
        self::TYPE_TOUSHI_BUKKEN => '投資物件の種目ごとのメリット・デメリット',
        self::TYPE_RIMAWARI => '不動産投資で重要な「利回り」を理解しよう',
        self::TYPE_OWNER_CHANGE => 'オーナーチェンジ物件での投資とは',
        self::TYPE_TOUSHI_SETSUZEI => '不動産投資の節税効果とは',
        self::TYPE_BUNSAN_TOUSHI => '不動産投資のリスクを減らす分散投資とは',
        self::TYPE_TOUSHI_TYPE => '不動産投資の概要～目的に合った投資を～',
        self::TYPE_MANSION_TOUSHI => 'マンション投資で重要な「管理状況」とは',
        self::TYPE_BOUHANSEI => '防犯性の高いマンションの投資効果と確認点',
        self::TYPE_TENKIN_MOCHIIE => '遠方への転勤時、持ち家は売却か？賃貸か？',
        self::TYPE_TOUSHI_COST => '不動産投資の必要経費と確定申告について',
        self::TYPE_RUNNING_COST => '賃貸経営でのランニングコストについて',
        self::TYPE_TOUSHI_LOAN => '「不動産投資ローン」を知ろう',
        self::TYPE_SHUUZEN_KEIKAKU => '賃貸経営で必要な「修繕」について考えよう',
        self::TYPE_REVERSE_MORTGAGE => '「リバースモーゲージ」とはどんなもの？',
        self::TYPE_TROUBLE_TAIOU => '賃貸経営で発生するトラブル・苦情への対応',
        self::TYPE_YACHIN_TAINOU => '「家賃滞納」時にオーナーはどう対応するか',
        self::TYPE_CHINTAI_HOSHOU => '「家賃保証会社」とはどういうもの？',
        self::TYPE_GENJOU_KAIFUKU => '退去時の原状回復義務と敷金返還について',
        self::TYPE_CHINTAI_REFORM => '所有物件の「付加価値」を高めるリフォーム',
        self::TYPE_CHINTAI_DIY => '賃貸物件入居者のプチリフォームについて',
        self::TYPE_AKIYA_SOUZOKU => '空き家を相続したらどうすればいい？',
        self::TYPE_ISAN_BUNKATSU => '複数の相続人での不動産相続について',
        self::TYPE_JIKKA_BAIKYAKU => '実家を売却する場合、相続前後でどう違う？',
        self::TYPE_SOUZOKU_ZEI => '不動産のみを相続した場合の相続税について',
        self::TYPE_MEIGI_HENKOU => '相続した不動産の名義変更について',
        self::TYPE_HOUJIN_KOJIN => '起業の形は法人設立と個人事業主のどっち？',
        self::TYPE_KAIGYOU_FLOW => '店舗開業の手順 コンセプト固め～開店まで',
        self::TYPE_STORE_CONCEPT => '「店舗コンセプト」が重要な理由と設定方法',
        self::TYPE_KASHITENPO_TYPE => '貸店舗物件の種類・特徴を知ろう',
        self::TYPE_JIGYOUYOU_BUKKEN => '事業用賃貸物件と居住用賃貸物件はどう違う',
        self::TYPE_KEIEI_RISK => '店舗経営に伴うリスクと備えについて',
        self::TYPE_TENANT_HIKIWATASHI => '店舗物件の「二度の引渡し」とは',
        self::TYPE_NAISOU_SEIGEN => '事業主が知っておくべき店舗の内装制限とは',
        self::TYPE_FRANCHISE => 'フランチャイズという起業の選択肢を考える',
        self::TYPE_LEASEBACK => 'リースバック方式での新規店舗開業について',
        self::TYPE_AOIRO_SHINKOKU => '個人開業で知っておくべき確定申告について',
        self::TYPE_SHOUTENKAI => '「商店会」などの団体について',
        self::TYPE_OPENING_COST => '店舗開業時にかかる費用には何がある？',
        self::TYPE_KAIGYOU_SHIKIN => '開業資金の準備・調達について',
        self::TYPE_SOURITSUHI => '「開業費」「創立費」の取り扱いと節税効果',
        self::TYPE_KENRI_KIN => '事業用不動産賃貸借での「権利金」について',
        self::TYPE_STORE_ERABI => '入居する店舗はどう選ぶか',
        self::TYPE_STORE_LOCATION => '失敗しない「出店場所選び」をするために',
        self::TYPE_TENANT_RICCHI => '事業の成否を左右する出店立地について',
        self::TYPE_INUKI_BUKKEN => '居抜き物件で開業するメリット・デメリット',
        self::TYPE_SKELETON_BUKKEN => 'スケルトン物件を選ぶメリット・デメリット',
        self::TYPE_KUUCHUU_TENPO => '「空中店舗」での集客を考える',
        self::TYPE_KAIGYOU_TETSUZUKI => '店舗開業に必要な手続き・資格について',
        self::TYPE_EIGYOU_KYOKA => '「飲食店営業許可」と申請手続きについて',
        self::TYPE_SHINYA_EIGYOU => '深夜営業するときの届け出と注意すべきこと',
        self::TYPE_TENANT_KEIYAKU => '店舗の賃貸借契約時の留意点',
        self::TYPE_STORE_DESIGN => '失敗しないための店舗デザインの考え方',
        self::TYPE_STORE_LAYOUT => '店舗レイアウトを考えよう',
        self::TYPE_SEKOU_IRAI => '店舗内外装デザインから施工の依頼について',
        self::TYPE_SEKOU_MITSUMORI => '店舗施工でまず重要な見積もりとその見方',
        self::TYPE_SEKOU_ITAKUSAKI => '店舗施工で失敗しない依頼先選定時の注意点',
        self::TYPE_BARRIER_FREE => 'バリアフリーを考えた店舗づくりについて',
        self::TYPE_GAISOU_DESIGN => '店舗の外装について知っておくべきこと',
        self::TYPE_APART_VS_MANSION => 'アパートとマンション、どちらがいいの？',
        self::TYPE_TOWNHOUSE_TERRACEHOUSE => 'タウンハウスとテラスハウスはどんなもの？',
        self::TYPE_IKKODATE_CHINTAI => '賃貸で一戸建てに住むという選択肢',
        self::TYPE_CHINTAI_JOUKENSEIRI => '住みたい物件の希望条件を整理しよう',
        self::TYPE_KOUHO_BUKKEN => 'もう迷わない！候補物件の絞り方・選び方',
        self::TYPE_CHINTAI_RICCHI => '希望の立地条件を考える',
        self::TYPE_SETSUBI_SHIYOU => '住み心地を左右する住まいの設備・仕様',
        self::TYPE_SECURITY_TAISAKU => '気になるセキュリティー対策を確認しよう',
        self::TYPE_INTERNET_KANKYOU => 'インターネット環境は事前に確認しよう',
        self::TYPE_MINAMIMUKI => '南向き以外も魅力！方角選びのヒント',
        self::TYPE_KANRI_KEITAI => 'アパート・マンションの管理形態を知ろう',
        self::TYPE_KOSODATE_CHINTAI => '子育て世帯の賃貸物件の選び方',
        self::TYPE_PET_OK => 'ペットと暮らすための部屋選び',
        self::TYPE_CHINTAI_KIKAN => '住む期間に合わせた物件選びをしよう',
        self::TYPE_EKITOOI => '住まい選び～駅から離れた物件について～',
        self::TYPE_WOMEN_ONLY => '検討の価値あり！女性専用物件とは',
        self::TYPE_KAGU_KADEN => '「家具・家電付き物件」を借りるという選択',
        self::TYPE_YACHIN_YOSAN => '家賃月額予算は全体支出をイメージしよう',
        self::TYPE_GAKUSEI_YACHIN => '学生一人暮らしの家賃上限はどう考える',
        self::TYPE_YACHIN_SOUBA => '家賃の相場を調べよう',
        self::TYPE_KANRIHI_KYOUEKIHI => '賃貸物件の管理費・共益費って何？',
        self::TYPE_SHIKIREI => '敷金・礼金・更新料についてきちんと知ろう',
        self::TYPE_HIKKOSHI_COST => '引越し費用について考えよう',
        self::TYPE_SHINSEIKATSU_COST => '新生活で必要なものを予算内でそろえるには',
        self::TYPE_SOUDAN_POINT => '不動産会社に相談・訪問するときのポイント',
        self::TYPE_HOUMON_JUNBI => '安心して不動産会社に行くために',
        self::TYPE_NAIKEN_JUNBI => '物件内見の準備と注意点について',
        self::TYPE_NAIKEN_POINT => '内見での室内・建物周りのチェックポイント',
        self::TYPE_SHUUHEN_KANKYOU => '快適な暮らしに不可欠な周辺環境をチェック',
        self::TYPE_LENT_MOUSHIKOMI => '住みたい物件を決めたら入居申込みをしよう',
        self::TYPE_NYUUKYO_SHINSA => '入居審査ってどういうもの？',
        self::TYPE_KEIYAKU_COST => '賃貸借契約時に必要な書類とお金について',
        self::TYPE_LENT_JUUYOUJIKOU => '契約前の「重要事項説明」について',
        self::TYPE_CHINTAISHAKU => '住まいの賃貸借契約で確認しておくべきこと',
        self::TYPE_YACHIN_HOSHOU => '「家賃保証会社」の利用とはどういうものか',
        self::TYPE_CHUUTO_KAIYAKU => '賃貸物件の借主側からの中途解約について',
        self::TYPE_KEIYAKU_GENJOU_KAIFUKU => '退去時の原状回復と敷金について',
        self::TYPE_HIKKOSHI_KAISHA => '引越し会社の選び方',
        self::TYPE_HIKKOSHI_FLOW => '引越し準備と当日の流れ',
        self::TYPE_GENJOU_KAKUNIN => '引越し前に新居の掃除と原状確認をしよう',
        self::TYPE_JIZEN_HANNYUU => '効率的に引越し当日をこなすための注意点',
        self::TYPE_SODAIGOMI => '不用品・大きなゴミの処分について',
        self::TYPE_TODOKEDE => '役所への届け出など新生活に必要な手続き',
        self::TYPE_HIKKOSHI_JUNBI => '引越し直後から快適に暮らすために',
        self::TYPE_MADORIZU => '間取り図を見て新生活をイメージしよう',
        self::TYPE_KINRIN_MANNERS => '近隣へのあいさつで安心・円滑なお付き合い',
        self::TYPE_JICHIKAI => '町内会・自治会は加入必須？メリットは？',
        self::TYPE_ARTICLE_ORIGINAL => '記事',
    );

    //各ページのkeyword用の文言
    private $pages_keyword = array(
        self::TYPE_TOP                  => '',
        self::TYPE_COMPANY              => '会社紹介',
        self::TYPE_HISTORY              => '会社沿革',
        self::TYPE_GREETING             => '代表挨拶',
        self::TYPE_RECRUIT              => '採用情報',
        self::TYPE_SHOP_INDEX           => '店舗紹介',
        self::TYPE_SHOP_DETAIL          => '',
        self::TYPE_STAFF_INDEX          => 'スタッフ一覧',
        self::TYPE_STAFF_DETAIL         => '',
        self::TYPE_OWNER                => 'オーナーさま向け',
        self::TYPE_CORPORATION          => '法人向け',
        self::TYPE_TENANT               => '入居者さま向け',
        self::TYPE_BROKER               => '仲介会社さま向け',
        self::TYPE_PROPRIETARY          => '管理会社さま向け',
        self::TYPE_BLOG_INDEX           => 'ブログ一覧',
        self::TYPE_BLOG_DETAIL          => '',
        self::TYPE_FREE                 => '',
        self::TYPE_MEMBERONLY           => '会員さま専用ページ',
        self::TYPE_CITY                 => '',
        self::TYPE_CUSTOMERVOICE_INDEX  => 'お客様の声一覧',
        self::TYPE_CUSTOMERVOICE_DETAIL => '',
        self::TYPE_SELLINGCASE_INDEX    => '売却事例一覧',
        self::TYPE_SELLINGCASE_DETAIL   => '',
        self::TYPE_EVENT_INDEX          => 'イベント情報一覧',
        self::TYPE_EVENT_DETAIL         => '',
        self::TYPE_QA                   => 'よくあるご質問',
        self::TYPE_LINKS                => 'リンク集',
        self::TYPE_SCHOOL               => '学区情報',
        self::TYPE_PREVIEW              => '内見時のチェックポイント',
        self::TYPE_MOVING               => '引越し時のチェックポイント',
        self::TYPE_TERMINOLOGY          => '不動産用語集',
        self::TYPE_RENT                 => '住まいを借りる契約の流れ',
        self::TYPE_LEND                 => '住まいを貸す契約の流れ',
        self::TYPE_BUY                  => '住まいを買う契約の流れ',
        self::TYPE_SELL                 => '住まいを売る契約の流れ',
        self::TYPE_INFO_INDEX           => 'お知らせ一覧',
        self::TYPE_INFO_DETAIL          => '',
        self::TYPE_PRIVACYPOLICY        => 'プライバシーポリシー',
        self::TYPE_SITEPOLICY           => 'サイトポリシー',
        self::TYPE_SITEMAP              => 'サイトマップ',
        self::TYPE_FORM_CONTACT         => 'お問い合わせ',
        //#4274 Change spec form FDP contact
        //self::TYPE_FORM_FDP_CONTACT     => '周辺情報お問い合わせ',
        self::TYPE_FORM_DOCUMENT        => '資料請求',
        self::TYPE_FORM_ASSESSMENT      => '査定依頼',

        // 物件リクエスト
        self::TYPE_FORM_REQUEST_LIVINGLEASE     => '物件リクエスト（居住用賃貸）',
        self::TYPE_FORM_REQUEST_OFFICELEASE     => '物件リクエスト（事業用賃貸）',
        self::TYPE_FORM_REQUEST_LIVINGBUY       => '物件リクエスト（居住用売買）',
        self::TYPE_FORM_REQUEST_OFFICEBUY       => '物件リクエスト（事業用売買）',

        //CMSテンプレートパターンの追加
        self::TYPE_BUSINESS_CONTENT  => '事業内容',
        self::TYPE_COLUMN_INDEX      => 'コラム一覧',
        self::TYPE_COLUMN_DETAIL     => '',
        self::TYPE_COMPANY_STRENGTH  => '当社の思い・強み',
        self::TYPE_PURCHASING_REAL_ESTATE       => '「買取り」を利用してスムーズに不動産売却',
        self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN => '家を買い替える強い味方「買い替えローン」',
        self::TYPE_REPLACEMENT_AHEAD_SALE       => '家の買い替えは、購入が先か売却が先か？',
        self::TYPE_BUILDING_EVALUATION          => '中古一戸建てはどのように評価されるのか？',
        self::TYPE_BUYER_VISITS_DETACHEDHOUSE   => '現地見学で物件をアピールする方法あれこれ',
        self::TYPE_POINTS_SALE_OF_CONDOMINIUM   => 'マンションを有利な条件で売却する戦術とは',
        self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE => 'マンションVS一戸建て 選び方の基準は？',
        self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND => '新築と中古どちらを買う？その違いを知ろう',
        self::TYPE_ERECTIONHOUSING_ORDERHOUSE    => '建売住宅と注文住宅の特徴と違いとは？',
        self::TYPE_PURCHASE_BEST_TIMING          => 'マイホームはいつ買う？判断する3つの基準',
        self::TYPE_LIFE_PLAN            => '住宅資金の前にライフプランを考えよう',
        self::TYPE_TYPES_MORTGAGE_LOANS => '住宅ローンにはどんな種類がある？',
        self::TYPE_FUNDING_PLAN         => '資金計画を考えよう！ 諸費用も忘れずに',
        self::TYPE_TROUBLED_LEASING_MANAGEMENT => '賃貸管理はプロに任せるのが安心な理由',
        self::TYPE_LEASING_MANAGEMENT_MENU     => '賃貸管理サービスについて',
        self::TYPE_MEASURES_AGAINST_VACANCIES  => '空室対策の基本ポイント',
        self::TYPE_HOUSE_REMODELING            => 'ライバル物件に差をつけるリフォーム活用法',
        self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER => 'なぜ土地活用が必要なのか',
        self::TYPE_UTILIZING_LAND           => '土地活用方法それぞれの魅力とは',
        self::TYPE_PURCHASE_INHERITANCE_TAX => '不動産の購入が相続税対策に有効な理由',
        self::TYPE_UPPER_LIMIT         => '家賃の上限はどれくらい？考慮すべきは何？',
        self::TYPE_RENTAL_INITIAL_COST => '賃貸住宅の初期費用には何がある？',
        self::TYPE_SQUEEZE_CANDIDATE   => '賃貸住み替え、物件を絞り込む3ステップ',
        self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE   => '引越しのときのゴミはどうやって処分する？',
        self::TYPE_COMFORTABLELIVING_RESIDENT_RULES => '快適に暮らすために居住ルールを確認しよう',
        self::TYPE_STORE_SEARCH                     => '商圏調査の基本とは？長く続けるお店づくり',
        self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN       => '店舗開業成功のカギ！事業計画書の作り方',

        self::TYPE_USEFUL_REAL_ESTATE_INFORMATION => '不動産お役立ち情報',
        self::TYPE_SALE => '売却コンテンツ',
        self::TYPE_PURCHASE => '購入コンテンツ',
        self::TYPE_OWNERS_RENTAL_MANAGEMENT => '不動産のオーナー様向けコンテンツ',
        self::TYPE_RESIDENTIAL_RENTAL => '賃貸事業用コンテンツ',
        self::TYPE_BUSINESS_LEASE => '賃貸コンテンツ',
        self::TYPE_LARGE_ORIGINAL => 'カテゴリー一覧',

        self::TYPE_CHECK_FLOW_OF_SALE => '売却の流れを確認する',
        self::TYPE_LEARN_BASIC_OF_SALE => '売却の基礎知識について知る',
        self::TYPE_KNOW_MEDIATION => '仲介について知る',
        self::TYPE_KNOW_COSTS_AND_TAXES => '費用や税金について知る',
        self::TYPE_KNOW_SALE_ASSESSMENT => '査定について知る',
        self::TYPE_LEARN_LAND_SALES => '土地売却について知る',
        self::TYPE_KNOW_HOW_TO_SALE => 'うまく売る方法について知る',
        self::TYPE_KNOW_BASIC_OF_PURCHASE => '購入の基礎知識について知る',
        self::TYPE_KNOW_WHEN_TO_BUY => '購入のタイミングについて知る',
        self::TYPE_LEARN_BUY_SINGLE_FAMILY => '一戸建ての購入について知る',
        self::TYPE_LEARN_BUY_APARTMENT => 'マンションの購入について知る',
        self::TYPE_LEARN_PRE_OWNED_RENOVATION => '中古住宅、リノベーションについて知る',
        self::TYPE_KNOW_COST_OF_PURCHASE => '購入時の費用について知る',
        self::TYPE_LEARN_MORTGAGES => '住宅ローンについて知る',
        self::TYPE_THINK_FUTURE_OF_YOUR_HOME => '住まいの将来性について考える',
        self::TYPE_LEARN_SITE_AND_PREVIEWS => '物件の現地見学、内覧について知る',
        self::TYPE_LEARN_SALES_CONTRACTS => '売買契約について知る',
        self::TYPE_LEARN_REAL_ESTATE_INVESTMENT => '不動産投資について知る',
        self::TYPE_KNOW_FUNDS_AND_LOANS => '資金やローンについて知る',
        self::TYPE_LEARN_RENTAL_MANAGEMENT => '賃貸管理について知る',
        self::TYPE_KNOW_INHERITANCE => '相続について知る',
        self::TYPE_LEARN_BASIC_STORE_OPENING => '店舗開業の基礎知識について知る',
        self::TYPE_LEARN_START_UP_FUNDS => '開業資金について知る',
        self::TYPE_LEARN_CHOOSE_STORE => '店舗物件の選び方について知る',
        self::TYPE_LEARN_PROCEDURES_AND_CONTRACTS => '手続き・契約について知る',
        self::TYPE_LEARN_STORE_DESIGN => '店舗設計について知る',
        self::TYPE_LEARN_TYPES_OF_RENTAL => '賃貸物件の種類について知る',
        self::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL => '賃貸物件の希望条件を整理する',
        self::TYPE_LEARN_RENT_EXPENSES => '家賃・諸費用について知る',
        self::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => '不動産会社への訪問・現地見学について知る',
        self::TYPE_LEARN_LEASE_AGREEMENTS => '賃貸借契約について知る',
        self::TYPE_KNOW_MOVING => '引越しについて知る',
        self::TYPE_LEARN_LIVING_RENT => '賃貸での暮らしについて知る',
        self::TYPE_SMALL_ORIGINAL => '記事一覧',

        self::TYPE_BAIKYAKU_POINT => '売却は「売却理由」と「取引の流れ」が大切',
        self::TYPE_KYOJUUCHUU_BAIKYAKU => '自宅に「住みながら上手に売る方法」とは',
        self::TYPE_SELL_HIKIWATASHI => '物件の引渡しまでに売主がしておく準備とは',
        self::TYPE_BAIKYAKU_TYPE => '仲介だけではない不動産売却の4つの方法',
        self::TYPE_BAIKYAKU_SOUBA => '不動産価格の「相場」を知り上手に売るには',
        self::TYPE_SEIYAKU_KAKAKU => '査定から成約までの「価格」の違いとは',
        self::TYPE_KEIYAKU_FUTEKIGOU => '売主が負う「契約不適合責任」とは',
        self::TYPE_SHINSEI_SHORUI => '不動産売却時に必要な書類と取得方法',
        self::TYPE_KOJIN_TORIHIKI => '自力でも売れる？個人売買の可能性とリスク',
        self::TYPE_NINBAI => '「任意売却」でローン滞納の損害を最小限に',
        self::TYPE_CHUUKAI_KISO => '不動産の売却方法「仲介」を詳しく知ろう',
        self::TYPE_KYOUDOU_CHUUKAI => '「共同仲介」と「単独仲介」とは何か',
        self::TYPE_BAIKAI_KEIYAKU => '不動産会社と結ぶ「媒介契約」の種類とは',
        self::TYPE_IPPAN_BAIKAI => '売却時に選ぶ「一般媒介契約」とは',
        self::TYPE_SENZOKU_SENNIN => '売却時に選ぶ専任・専属専任媒介契約とは',
        self::TYPE_KAITORI_OSHOU => '知っておきたい「買取保証付き仲介」とは',
        self::TYPE_HYOUKAGAKU => '不動産の評価額はどのように決まるのか',
        self::TYPE_KAIKAE_KEIKAKU => '住まいの買い換えの成否は資金計画がカギ',
        self::TYPE_BAIKYAKU_COST => '不動産を売るときの諸費用はいくらかかる？',
        self::TYPE_TEITOUKEN => 'ローン残債がある住まいの抵当権抹消とは',
        self::TYPE_JOUTOSHOTOKU => '土地売却時にかかる「譲渡所得」課税とは',
        self::TYPE_TOKUBETSU_KOUJO => '売却時の「3000万円特別控除」とは',
        self::TYPE_KAKUTEI_SHINKOKU => '不動産売却後の確定申告は必要？不要？',
        self::TYPE_TSUNAGI_YUUSHI => '住まいの買い換えで使う「つなぎ融資」とは',
        self::TYPE_FUKUSUU_SATEI => '価格査定を複数会社に依頼する理由',
        self::TYPE_KANNI_SATEI => 'うまく使いたい「簡易査定」と「訪問査定」',
        self::TYPE_URERU_TOCHI => '売れやすい土地の条件と売るための対策とは',
        self::TYPE_FURUYATSUKI_SARACHI => '土地は「古家付き」「更地」どちらで売る？',
        self::TYPE_TOCHI_BAIKYAKU => '土地売却を円滑に進めるためのポイント',
        self::TYPE_KAKUTEI_SOKURYOU => '売却時に必要な土地の「境界確定測量」とは',
        self::TYPE_HATAZAOCHI => '「旗竿地」を売るために知っておきたいこと',
        self::TYPE_NOUCHI => '農地はどうすれば売れる？地目の変更とは',
        self::TYPE_BAIKYAKU_JIKI => '住まいの売却時期を決める4つのポイント',
        self::TYPE_BAIKYAKU_20Y => '築20年以上の家を売却するためのポイント',
        self::TYPE_BAIKYAKU_30Y => '築30年超の古家の売却について',
        self::TYPE_URENAI_RIYUU => '物件がなかなか売れない…その理由と対処法',
        self::TYPE_IRAISAKI_SENTAKU => '売却を依頼する不動産会社はどう選ぶ',
        self::TYPE_NAIRAN_TAIOU => '居住中の内見希望への対応ポイント',
        self::TYPE_SAIKENCHIKU_FUKA => '再建築不可物件を売却するときのポイント',
        self::TYPE_MOCHIIE_MERIT => '「賃貸」にはない「持ち家」のメリットとは',
        self::TYPE_BUY_JOUKENSEIRI => '購入物件の希望条件を整理する',
        self::TYPE_BUY_RICCHI => '住宅購入時は希望立地をよく考えよう',
        self::TYPE_MADORI => '間取りの考え方を理解して住まいを選ぶ',
        self::TYPE_SETAIBETSU => '世帯タイプ別の住まい選び',
        self::TYPE_KAIDAN_TYPE => '購入前に知っておきたい住まいの「階段」',
        self::TYPE_SEINOU_HYOUKA => '「住宅の性能評価」とは',
        self::TYPE_BUY_KEIYAKU_FLOW => '物件購入の申込み前から売買契約までの流れ',
        self::TYPE_SAISHUU_KAKUNIN => '物件の最終確認と残代金の精算・引渡し',
        self::TYPE_NYUUKYO_FLOW => 'マイホームの引渡しから入居までの流れ',
        self::TYPE_COMMUNICATION => '販売担当者との上手なコミュニケーション術',
        self::TYPE_SHINCHIKU_NAIRANKAI => '新築物件の内覧会と入居説明会について',
        self::TYPE_NYUUKYO_TROUBLE => '入居後のトラブルへの対応について',
        self::TYPE_KOUNYUU_JIKI => '住まいの「買いどき」について考えよう',
        self::TYPE_20DAI_KOUNYUU => '20代の住まい購入のポイント',
        self::TYPE_30DAI_KOUNYUU => '30～40代の住まい購入のポイント',
        self::TYPE_50DAI_KOUNYUU => '50～60代の住まい購入のポイント',
        self::TYPE_TOCHI_ERABI => '一戸建て購入で大切な土地選び',
        self::TYPE_KENCHIKU_JOUKENTSUKI => '意外に知らない「建築条件付き土地」とは',
        self::TYPE_NISETAI_JUUTAKU => '住まい方で違う「二世帯住宅」のタイプとは',
        self::TYPE_KODATE_SHINSEIKATSU => '一戸建ての新生活について',
        self::TYPE_MANSION_TYPE => '大規模？高層？マンションのタイプを知ろう',
        self::TYPE_MAISONETTE_MANSION => '一戸建て感覚で住めるマンションとは',
        self::TYPE_MANSION_SERVICE => '魅力的なマンションの共用施設・サービス',
        self::TYPE_MANSION_SHINSEIKATSU => 'マンションの新生活について',
        self::TYPE_RENOVATION_BUKKEN => '注目の「リノベーション物件」とは',
        self::TYPE_CHUUKO_RENOVATION => '理想への近道は「中古＋リノベーション」',
        self::TYPE_HOME_INSPECTION => '「建物状況調査（インスペクション）」とは',
        self::TYPE_KOUNYUU_YOSAN => '年収、ローン…家の購入予算はどう決める？',
        self::TYPE_KOUNYUU_ATAMAKIN => '住宅購入時に「頭金」はどのくらい必要か',
        self::TYPE_YOSAN_OVER => '予算よりも高い物件は買える？その方法は？',
        self::TYPE_KOUNYUU_SHOKIHIYOU => '住宅購入に必要な初期費用とは',
        self::TYPE_KOUNYUUGO_COST => 'ローン以外に住まい購入後にかかる費用は',
        self::TYPE_LOAN_MERIT => '住宅ローンを利用するメリットについて',
        self::TYPE_KINRI_TYPE => '住宅ローンの金利タイプとは',
        self::TYPE_HENSAI_TYPE => '住宅ローンの返済方法について',
        self::TYPE_HENSAI_KIKAN => '住宅ローンの返済期間はどう考える',
        self::TYPE_SHINSA_KIJUN => '住宅ローンの審査基準ってどういうもの？',
        self::TYPE_BONUS_HENSAI => '住宅ローンのボーナス返済とは',
        self::TYPE_SHINSA_FLOW => '住宅ローンの申込みから融資実行までの流れ',
        self::TYPE_LOAN_KEIKAKU => '返済で失敗しない適正な住宅ローンの組み方',
        self::TYPE_FLAT35 => '「フラット35」について',
        self::TYPE_KURIAGE_HENSAI => '住宅ローン返済を楽にする「繰上げ返済」',
        self::TYPE_TOMOBATARAKI_LOAN => '共働き世帯のための住宅ローンとは',
        self::TYPE_LOAN_KARIKAE => '住宅ローンの借り換えについて',
        self::TYPE_SUMAI_SHOURAISEI => '購入時に考えるべき「住まいの将来性」とは',
        self::TYPE_SHISAN_KACHI => '購入時に考えるべき住まいの「資産価値」',
        self::TYPE_KODATE_KENGAKU => '一戸建て見学時の留意点',
        self::TYPE_MANSION_KENGAKU => 'マンション見学時の留意点',
        self::TYPE_GENCHI_KAKUNIN => '物件以外にも重要な現地確認とは',
        self::TYPE_BUY_MOUSHIKOMI => '購入申込みは何をする？留意点は？',
        self::TYPE_BUY_KEIYAKU => '売買契約時の留意点とは',
        self::TYPE_BUY_JUUYOUJIKOU => '「重要事項説明」と注意点について',
        self::TYPE_TOUKI_TETSUZUKI => '不動産登記手続きを知っておこう',
        self::TYPE_TOUSHI_FUKUGYOU => '副業としての不動産投資を考える',
        self::TYPE_TOUSHI_SALARYMAN => '不動産投資とはどういうものかを知ろう',
        self::TYPE_TOUSHI_BUKKEN => '投資物件の種目ごとのメリット・デメリット',
        self::TYPE_RIMAWARI => '不動産投資で重要な「利回り」を理解しよう',
        self::TYPE_OWNER_CHANGE => 'オーナーチェンジ物件での投資とは',
        self::TYPE_TOUSHI_SETSUZEI => '不動産投資の節税効果とは',
        self::TYPE_BUNSAN_TOUSHI => '不動産投資のリスクを減らす分散投資とは',
        self::TYPE_TOUSHI_TYPE => '不動産投資の概要～目的に合った投資を～',
        self::TYPE_MANSION_TOUSHI => 'マンション投資で重要な「管理状況」とは',
        self::TYPE_BOUHANSEI => '防犯性の高いマンションの投資効果と確認点',
        self::TYPE_TENKIN_MOCHIIE => '遠方への転勤時、持ち家は売却か？賃貸か？',
        self::TYPE_TOUSHI_COST => '不動産投資の必要経費と確定申告について',
        self::TYPE_RUNNING_COST => '賃貸経営でのランニングコストについて',
        self::TYPE_TOUSHI_LOAN => '「不動産投資ローン」を知ろう',
        self::TYPE_SHUUZEN_KEIKAKU => '賃貸経営で必要な「修繕」について考えよう',
        self::TYPE_REVERSE_MORTGAGE => '「リバースモーゲージ」とはどんなもの？',
        self::TYPE_TROUBLE_TAIOU => '賃貸経営で発生するトラブル・苦情への対応',
        self::TYPE_YACHIN_TAINOU => '「家賃滞納」時にオーナーはどう対応するか',
        self::TYPE_CHINTAI_HOSHOU => '「家賃保証会社」とはどういうもの？',
        self::TYPE_GENJOU_KAIFUKU => '退去時の原状回復義務と敷金返還について',
        self::TYPE_CHINTAI_REFORM => '所有物件の「付加価値」を高めるリフォーム',
        self::TYPE_CHINTAI_DIY => '賃貸物件入居者のプチリフォームについて',
        self::TYPE_AKIYA_SOUZOKU => '空き家を相続したらどうすればいい？',
        self::TYPE_ISAN_BUNKATSU => '複数の相続人での不動産相続について',
        self::TYPE_JIKKA_BAIKYAKU => '実家を売却する場合、相続前後でどう違う？',
        self::TYPE_SOUZOKU_ZEI => '不動産のみを相続した場合の相続税について',
        self::TYPE_MEIGI_HENKOU => '相続した不動産の名義変更について',
        self::TYPE_HOUJIN_KOJIN => '起業の形は法人設立と個人事業主のどっち？',
        self::TYPE_KAIGYOU_FLOW => '店舗開業の手順 コンセプト固め～開店まで',
        self::TYPE_STORE_CONCEPT => '「店舗コンセプト」が重要な理由と設定方法',
        self::TYPE_KASHITENPO_TYPE => '貸店舗物件の種類・特徴を知ろう',
        self::TYPE_JIGYOUYOU_BUKKEN => '事業用賃貸物件と居住用賃貸物件はどう違う',
        self::TYPE_KEIEI_RISK => '店舗経営に伴うリスクと備えについて',
        self::TYPE_TENANT_HIKIWATASHI => '店舗物件の「二度の引渡し」とは',
        self::TYPE_NAISOU_SEIGEN => '事業主が知っておくべき店舗の内装制限とは',
        self::TYPE_FRANCHISE => 'フランチャイズという起業の選択肢を考える',
        self::TYPE_LEASEBACK => 'リースバック方式での新規店舗開業について',
        self::TYPE_AOIRO_SHINKOKU => '個人開業で知っておくべき確定申告について',
        self::TYPE_SHOUTENKAI => '「商店会」などの団体について',
        self::TYPE_OPENING_COST => '店舗開業時にかかる費用には何がある？',
        self::TYPE_KAIGYOU_SHIKIN => '開業資金の準備・調達について',
        self::TYPE_SOURITSUHI => '「開業費」「創立費」の取り扱いと節税効果',
        self::TYPE_KENRI_KIN => '事業用不動産賃貸借での「権利金」について',
        self::TYPE_STORE_ERABI => '入居する店舗はどう選ぶか',
        self::TYPE_STORE_LOCATION => '失敗しない「出店場所選び」をするために',
        self::TYPE_TENANT_RICCHI => '事業の成否を左右する出店立地について',
        self::TYPE_INUKI_BUKKEN => '居抜き物件で開業するメリット・デメリット',
        self::TYPE_SKELETON_BUKKEN => 'スケルトン物件を選ぶメリット・デメリット',
        self::TYPE_KUUCHUU_TENPO => '「空中店舗」での集客を考える',
        self::TYPE_KAIGYOU_TETSUZUKI => '店舗開業に必要な手続き・資格について',
        self::TYPE_EIGYOU_KYOKA => '「飲食店営業許可」と申請手続きについて',
        self::TYPE_SHINYA_EIGYOU => '深夜営業するときの届け出と注意すべきこと',
        self::TYPE_TENANT_KEIYAKU => '店舗の賃貸借契約時の留意点',
        self::TYPE_STORE_DESIGN => '失敗しないための店舗デザインの考え方',
        self::TYPE_STORE_LAYOUT => '店舗レイアウトを考えよう',
        self::TYPE_SEKOU_IRAI => '店舗内外装デザインから施工の依頼について',
        self::TYPE_SEKOU_MITSUMORI => '店舗施工でまず重要な見積もりとその見方',
        self::TYPE_SEKOU_ITAKUSAKI => '店舗施工で失敗しない依頼先選定時の注意点',
        self::TYPE_BARRIER_FREE => 'バリアフリーを考えた店舗づくりについて',
        self::TYPE_GAISOU_DESIGN => '店舗の外装について知っておくべきこと',
        self::TYPE_APART_VS_MANSION => 'アパートとマンション、どちらがいいの？',
        self::TYPE_TOWNHOUSE_TERRACEHOUSE => 'タウンハウスとテラスハウスはどんなもの？',
        self::TYPE_IKKODATE_CHINTAI => '賃貸で一戸建てに住むという選択肢',
        self::TYPE_CHINTAI_JOUKENSEIRI => '住みたい物件の希望条件を整理しよう',
        self::TYPE_KOUHO_BUKKEN => 'もう迷わない！候補物件の絞り方・選び方',
        self::TYPE_CHINTAI_RICCHI => '希望の立地条件を考える',
        self::TYPE_SETSUBI_SHIYOU => '住み心地を左右する住まいの設備・仕様',
        self::TYPE_SECURITY_TAISAKU => '気になるセキュリティー対策を確認しよう',
        self::TYPE_INTERNET_KANKYOU => 'インターネット環境は事前に確認しよう',
        self::TYPE_MINAMIMUKI => '南向き以外も魅力！方角選びのヒント',
        self::TYPE_KANRI_KEITAI => 'アパート・マンションの管理形態を知ろう',
        self::TYPE_KOSODATE_CHINTAI => '子育て世帯の賃貸物件の選び方',
        self::TYPE_PET_OK => 'ペットと暮らすための部屋選び',
        self::TYPE_CHINTAI_KIKAN => '住む期間に合わせた物件選びをしよう',
        self::TYPE_EKITOOI => '住まい選び～駅から離れた物件について～',
        self::TYPE_WOMEN_ONLY => '検討の価値あり！女性専用物件とは',
        self::TYPE_KAGU_KADEN => '「家具・家電付き物件」を借りるという選択',
        self::TYPE_YACHIN_YOSAN => '家賃月額予算は全体支出をイメージしよう',
        self::TYPE_GAKUSEI_YACHIN => '学生一人暮らしの家賃上限はどう考える',
        self::TYPE_YACHIN_SOUBA => '家賃の相場を調べよう',
        self::TYPE_KANRIHI_KYOUEKIHI => '賃貸物件の管理費・共益費って何？',
        self::TYPE_SHIKIREI => '敷金・礼金・更新料についてきちんと知ろう',
        self::TYPE_HIKKOSHI_COST => '引越し費用について考えよう',
        self::TYPE_SHINSEIKATSU_COST => '新生活で必要なものを予算内でそろえるには',
        self::TYPE_SOUDAN_POINT => '不動産会社に相談・訪問するときのポイント',
        self::TYPE_HOUMON_JUNBI => '安心して不動産会社に行くために',
        self::TYPE_NAIKEN_JUNBI => '物件内見の準備と注意点について',
        self::TYPE_NAIKEN_POINT => '内見での室内・建物周りのチェックポイント',
        self::TYPE_SHUUHEN_KANKYOU => '快適な暮らしに不可欠な周辺環境をチェック',
        self::TYPE_LENT_MOUSHIKOMI => '住みたい物件を決めたら入居申込みをしよう',
        self::TYPE_NYUUKYO_SHINSA => '入居審査ってどういうもの？',
        self::TYPE_KEIYAKU_COST => '賃貸借契約時に必要な書類とお金について',
        self::TYPE_LENT_JUUYOUJIKOU => '契約前の「重要事項説明」について',
        self::TYPE_CHINTAISHAKU => '住まいの賃貸借契約で確認しておくべきこと',
        self::TYPE_YACHIN_HOSHOU => '「家賃保証会社」の利用とはどういうものか',
        self::TYPE_CHUUTO_KAIYAKU => '賃貸物件の借主側からの中途解約について',
        self::TYPE_KEIYAKU_GENJOU_KAIFUKU => '退去時の原状回復と敷金について',
        self::TYPE_HIKKOSHI_KAISHA => '引越し会社の選び方',
        self::TYPE_HIKKOSHI_FLOW => '引越し準備と当日の流れ',
        self::TYPE_GENJOU_KAKUNIN => '引越し前に新居の掃除と原状確認をしよう',
        self::TYPE_JIZEN_HANNYUU => '効率的に引越し当日をこなすための注意点',
        self::TYPE_SODAIGOMI => '不用品・大きなゴミの処分について',
        self::TYPE_TODOKEDE => '役所への届け出など新生活に必要な手続き',
        self::TYPE_HIKKOSHI_JUNBI => '引越し直後から快適に暮らすために',
        self::TYPE_MADORIZU => '間取り図を見て新生活をイメージしよう',
        self::TYPE_KINRIN_MANNERS => '近隣へのあいさつで安心・円滑なお付き合い',
        self::TYPE_JICHIKAI => '町内会・自治会は加入必須？メリットは？',
        self::TYPE_ARTICLE_ORIGINAL => '記事',
    );

    //各ページのページ名の初期用の文言
    private $pages_name = array(
        self::TYPE_TOP                  => '',
        self::TYPE_COMPANY              => 'company',
        self::TYPE_HISTORY              => 'history',
        self::TYPE_GREETING             => 'message',
        self::TYPE_RECRUIT              => 'recruit',
        self::TYPE_SHOP_INDEX           => 'shoplist',
        self::TYPE_SHOP_DETAIL          => '',
        self::TYPE_STAFF_INDEX          => 'stafflist',
        self::TYPE_STAFF_DETAIL         => '',
        self::TYPE_OWNER                => 'owner',
        self::TYPE_CORPORATION          => 'biz',
        self::TYPE_TENANT               => 'resident',
        self::TYPE_BROKER               => 'intermediary',
        self::TYPE_PROPRIETARY          => 'management',
        self::TYPE_BLOG_INDEX           => 'blog',
        self::TYPE_BLOG_DETAIL          => '',
        self::TYPE_FREE                 => '',
        self::TYPE_MEMBERONLY           => 'member',
        self::TYPE_CITY                 => '',
        self::TYPE_CUSTOMERVOICE_INDEX  => 'voice',
        self::TYPE_CUSTOMERVOICE_DETAIL => '',
        self::TYPE_SELLINGCASE_INDEX    => 'selllist',
        self::TYPE_SELLINGCASE_DETAIL   => '',
        self::TYPE_EVENT_INDEX          => 'eventlist',
        self::TYPE_EVENT_DETAIL         => '',
        self::TYPE_QA                   => 'faq',
        self::TYPE_LINKS                => 'link',
        self::TYPE_SCHOOL               => 'school',
        self::TYPE_PREVIEW              => 'naiken',
        self::TYPE_MOVING               => 'hikkoshi',
        self::TYPE_TERMINOLOGY          => 'glossary',
        self::TYPE_RENT                 => 'rent-flow',
        self::TYPE_LEND                 => 'lend-flow',
        self::TYPE_BUY                  => 'buy-flow',
        self::TYPE_SELL                 => 'sell-flow',
        self::TYPE_INFO_INDEX           => 'news',
        self::TYPE_INFO_DETAIL          => '',
        self::TYPE_PRIVACYPOLICY        => 'privacy',
        self::TYPE_SITEPOLICY           => 'sitepolicy',
        self::TYPE_SITEMAP              => 'sitemap',
        self::TYPE_FORM_CONTACT         => 'contact',
        //#4274 Change spec form FDP contact
        //self::TYPE_FORM_FDP_CONTACT     => 'request-shuhen',
        self::TYPE_FORM_DOCUMENT        => 'request',
        self::TYPE_FORM_ASSESSMENT      => 'assess',
        self::TYPE_FORM_LIVINGLEASE     => 'kasi-kyojuu',
        self::TYPE_FORM_LIVINGBUY       => 'uri-kyojuu',
        self::TYPE_FORM_OFFICELEASE     => 'kasi-jigyou',
        self::TYPE_FORM_OFFICEBUY       => 'uri-jigyou',
        // 物件リクエスト
        self::TYPE_FORM_REQUEST_LIVINGLEASE     => 'request-kasi-kyojuu',
        self::TYPE_FORM_REQUEST_LIVINGBUY       => 'request-uri-kyojuu',
        self::TYPE_FORM_REQUEST_OFFICELEASE     => 'request-kasi-jigyou',
        self::TYPE_FORM_REQUEST_OFFICEBUY       => 'request-uri-jigyou',

        //CMSテンプレートパターンの追加
        self::TYPE_BUSINESS_CONTENT  => 'business',
        self::TYPE_COLUMN_INDEX      => 'column',
        self::TYPE_COLUMN_DETAIL     => '',
        self::TYPE_COMPANY_STRENGTH  => 'advantage',
        self::TYPE_PURCHASING_REAL_ESTATE => 'kaitori',
        self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN => 'kaikae-loan',
        self::TYPE_REPLACEMENT_AHEAD_SALE       => 'kaikae-select',
        self::TYPE_BUILDING_EVALUATION          => 'jyuutaku-hyouka',
        self::TYPE_BUYER_VISITS_DETACHEDHOUSE   => 'pr-bukken',
        self::TYPE_POINTS_SALE_OF_CONDOMINIUM   => 'mansion-baikyaku',
        self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE => 'mansion-vs-kodate',
        self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND => 'new-or-used',
        self::TYPE_ERECTIONHOUSING_ORDERHOUSE    => 'chumon-vs-tateuri',
        self::TYPE_PURCHASE_BEST_TIMING          => 'myhome-besttiming',
        self::TYPE_LIFE_PLAN            => 'lifeplan',
        self::TYPE_TYPES_MORTGAGE_LOANS => 'homeloan',
        self::TYPE_FUNDING_PLAN         => 'shikin-keikaku',
        self::TYPE_TROUBLED_LEASING_MANAGEMENT => 'chintaikanri-anshin',
        self::TYPE_LEASING_MANAGEMENT_MENU     => 'chintaikanri-service',
        self::TYPE_MEASURES_AGAINST_VACANCIES  => 'kuushitsu-taisaku',
        self::TYPE_HOUSE_REMODELING            => 'method-reform',
        self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER => 'tochikatsuyou-reason',
        self::TYPE_UTILIZING_LAND           => 'tochikatsuyou-charm',
        self::TYPE_PURCHASE_INHERITANCE_TAX => 'souzoku-reason',
        self::TYPE_UPPER_LIMIT         => 'guide-yachin',
        self::TYPE_RENTAL_INITIAL_COST => 'rent-shokihiyou',
        self::TYPE_SQUEEZE_CANDIDATE   => 'rent-select-3step',
        self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE   => 'disposal-rules',
        self::TYPE_COMFORTABLELIVING_RESIDENT_RULES => 'residence-rules',
        self::TYPE_STORE_SEARCH                     => 'market-research',
        self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN       => 'business-plan',

        self::TYPE_USEFUL_REAL_ESTATE_INFORMATION => 'article',
        self::TYPE_SALE => 'for-sellers',
        self::TYPE_PURCHASE => 'for-buyers',
        self::TYPE_OWNERS_RENTAL_MANAGEMENT => 'for-owners',
        self::TYPE_RESIDENTIAL_RENTAL => 'for-tenant',
        self::TYPE_BUSINESS_LEASE => 'for-lessees',

        self::TYPE_CHECK_FLOW_OF_SALE => 'sellers-flow',
        self::TYPE_LEARN_BASIC_OF_SALE => 'sellers-kiso',
        self::TYPE_KNOW_MEDIATION => 'chuukai',
        self::TYPE_KNOW_COSTS_AND_TAXES => 'sellers-cost',
        self::TYPE_KNOW_SALE_ASSESSMENT => 'fudousan-satei',
        self::TYPE_LEARN_LAND_SALES => 'tochi',
        self::TYPE_KNOW_HOW_TO_SALE => 'know-how',
        self::TYPE_KNOW_BASIC_OF_PURCHASE => 'buyers-kiso',
        self::TYPE_KNOW_WHEN_TO_BUY => 'timing',
        self::TYPE_LEARN_BUY_SINGLE_FAMILY => 'house',
        self::TYPE_LEARN_BUY_APARTMENT => 'ms',
        self::TYPE_LEARN_PRE_OWNED_RENOVATION => 'chuuko',
        self::TYPE_KNOW_COST_OF_PURCHASE => 'buyers-cost',
        self::TYPE_LEARN_MORTGAGES => 'juutaku-loan',
        self::TYPE_THINK_FUTURE_OF_YOUR_HOME => 'shouraisei',
        self::TYPE_LEARN_SITE_AND_PREVIEWS => 'nairan',
        self::TYPE_LEARN_SALES_CONTRACTS => 'baibai-keiyaku',
        self::TYPE_LEARN_REAL_ESTATE_INVESTMENT => 'toushi',
        self::TYPE_KNOW_FUNDS_AND_LOANS => 'owners-cost',
        self::TYPE_LEARN_RENTAL_MANAGEMENT => 'chintai-kanri',
        self::TYPE_KNOW_INHERITANCE => 'souzoku',
        self::TYPE_LEARN_BASIC_STORE_OPENING => 'kaigyou-kiso',
        self::TYPE_LEARN_START_UP_FUNDS => 'kaigyou-cost',
        self::TYPE_LEARN_CHOOSE_STORE => 'tenpo-bukken',
        self::TYPE_LEARN_PROCEDURES_AND_CONTRACTS => 'tetsuzuki',
        self::TYPE_LEARN_STORE_DESIGN => 'tenpo-sekkei',
        self::TYPE_LEARN_TYPES_OF_RENTAL => 'shubetsu',
        self::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL => 'kibou-jouken',
        self::TYPE_LEARN_RENT_EXPENSES => 'yachin',
        self::TYPE_LEARN_VISITS_COMPANIES_AND_SITE => 'genchi-kengaku',
        self::TYPE_LEARN_LEASE_AGREEMENTS => 'chintai-keiyaku',
        self::TYPE_KNOW_MOVING => 'tenkyo',
        self::TYPE_LEARN_LIVING_RENT => 'kurashi',

        self::TYPE_BAIKYAKU_POINT => 'baikyaku-point',
        self::TYPE_KYOJUUCHUU_BAIKYAKU => 'kyojuuchuu-baikyaku',
        self::TYPE_SELL_HIKIWATASHI => 'sell-hikiwatashi',
        self::TYPE_BAIKYAKU_TYPE => 'baikyaku-type',
        self::TYPE_BAIKYAKU_SOUBA => 'baikyaku-souba',
        self::TYPE_SEIYAKU_KAKAKU => 'seiyaku-kakaku',
        self::TYPE_KEIYAKU_FUTEKIGOU => 'keiyaku-futekigou',
        self::TYPE_SHINSEI_SHORUI => 'shinsei-shorui',
        self::TYPE_KOJIN_TORIHIKI => 'kojin-torihiki',
        self::TYPE_NINBAI => 'ninbai',
        self::TYPE_CHUUKAI_KISO => 'chuukai-kiso',
        self::TYPE_KYOUDOU_CHUUKAI => 'kyoudou-chuukai',
        self::TYPE_BAIKAI_KEIYAKU => 'baikai-keiyaku',
        self::TYPE_IPPAN_BAIKAI => 'ippan-baikai',
        self::TYPE_SENZOKU_SENNIN => 'senzoku-sennin',
        self::TYPE_KAITORI_OSHOU => 'kaitori-hoshou',
        self::TYPE_HYOUKAGAKU => 'hyoukagaku',
        self::TYPE_KAIKAE_KEIKAKU => 'kaikae-keikaku',
        self::TYPE_BAIKYAKU_COST => 'baikyaku-cost',
        self::TYPE_TEITOUKEN => 'teitouken',
        self::TYPE_JOUTOSHOTOKU => 'joutoshotoku',
        self::TYPE_TOKUBETSU_KOUJO => 'tokubetsu-koujo',
        self::TYPE_KAKUTEI_SHINKOKU => 'kakutei-shinkoku',
        self::TYPE_TSUNAGI_YUUSHI => 'tsunagi-yuushi',
        self::TYPE_FUKUSUU_SATEI => 'fukusuu-satei',
        self::TYPE_KANNI_SATEI => 'kani-satei',
        self::TYPE_URERU_TOCHI => 'ureru-tochi',
        self::TYPE_FURUYATSUKI_SARACHI => 'furuyatsuki-sarachi',
        self::TYPE_TOCHI_BAIKYAKU => 'tochi-baikyaku',
        self::TYPE_KAKUTEI_SOKURYOU => 'kakutei-sokuryou',
        self::TYPE_HATAZAOCHI => 'hatazaochi',
        self::TYPE_NOUCHI => 'nouchi',
        self::TYPE_BAIKYAKU_JIKI => 'baikyaku-jiki',
        self::TYPE_BAIKYAKU_20Y => 'baikyaku-20y',
        self::TYPE_BAIKYAKU_30Y => 'baikyaku-30y',
        self::TYPE_URENAI_RIYUU => 'urenai-riyuu',
        self::TYPE_IRAISAKI_SENTAKU => 'iraisaki-sentaku',
        self::TYPE_NAIRAN_TAIOU => 'naiken-taiou',
        self::TYPE_SAIKENCHIKU_FUKA => 'saikenchiku-fuka',
        self::TYPE_MOCHIIE_MERIT => 'mochiie-merit',
        self::TYPE_BUY_JOUKENSEIRI => 'buy-joukenseiri',
        self::TYPE_BUY_RICCHI => 'buy-ricchi',
        self::TYPE_MADORI => 'madori',
        self::TYPE_SETAIBETSU => 'setaibetsu',
        self::TYPE_KAIDAN_TYPE => 'kaidan-type',
        self::TYPE_SEINOU_HYOUKA => 'seinou-hyouka',
        self::TYPE_BUY_KEIYAKU_FLOW => 'buy-keiyaku-flow',
        self::TYPE_SAISHUU_KAKUNIN => 'saishuu-kakunin',
        self::TYPE_NYUUKYO_FLOW => 'nyuukyo-flow',
        self::TYPE_COMMUNICATION => 'communication',
        self::TYPE_SHINCHIKU_NAIRANKAI => 'shinchiku-nairankai',
        self::TYPE_NYUUKYO_TROUBLE => 'nyuukyo-trouble',
        self::TYPE_KOUNYUU_JIKI => 'kounyuu-jiki',
        self::TYPE_20DAI_KOUNYUU => '20dai-kounyuu',
        self::TYPE_30DAI_KOUNYUU => '30dai-kounyuu',
        self::TYPE_50DAI_KOUNYUU => '50dai-kounyuu',
        self::TYPE_TOCHI_ERABI => 'tochi-erabi',
        self::TYPE_KENCHIKU_JOUKENTSUKI => 'kenchiku-joukentsuki',
        self::TYPE_NISETAI_JUUTAKU => 'nisetai-juutaku',
        self::TYPE_KODATE_SHINSEIKATSU => 'kodate-shinseikatsu',
        self::TYPE_MANSION_TYPE => 'mansion-type',
        self::TYPE_MAISONETTE_MANSION => 'maisonette-mansion',
        self::TYPE_MANSION_SERVICE => 'mansion-service',
        self::TYPE_MANSION_SHINSEIKATSU => 'mansion-shinseikatsu',
        self::TYPE_RENOVATION_BUKKEN => 'renovation-bukken',
        self::TYPE_CHUUKO_RENOVATION => 'chuuko-renovation',
        self::TYPE_HOME_INSPECTION => 'home-inspection',
        self::TYPE_KOUNYUU_YOSAN => 'kounyuu-yosan',
        self::TYPE_KOUNYUU_ATAMAKIN => 'kounyuu-atamakin',
        self::TYPE_YOSAN_OVER => 'yosan-over',
        self::TYPE_KOUNYUU_SHOKIHIYOU => 'kounyuu-shokihiyou',
        self::TYPE_KOUNYUUGO_COST => 'kounyuugo-cost',
        self::TYPE_LOAN_MERIT => 'loan-merit',
        self::TYPE_KINRI_TYPE => 'kinri-type',
        self::TYPE_HENSAI_TYPE => 'hensai-type',
        self::TYPE_HENSAI_KIKAN => 'hensai-kikan',
        self::TYPE_SHINSA_KIJUN => 'shinsa-kijun',
        self::TYPE_BONUS_HENSAI => 'bonus-hensai',
        self::TYPE_SHINSA_FLOW => 'shinsa-flow',
        self::TYPE_LOAN_KEIKAKU => 'loan-keikaku',
        self::TYPE_FLAT35 => 'flat35',
        self::TYPE_KURIAGE_HENSAI => 'kuriage-hensai',
        self::TYPE_TOMOBATARAKI_LOAN => 'tomobataraki-loan',
        self::TYPE_LOAN_KARIKAE => 'loan-karikae',
        self::TYPE_SUMAI_SHOURAISEI => 'sumai-shouraisei',
        self::TYPE_SHISAN_KACHI => 'shisan-kachi',
        self::TYPE_KODATE_KENGAKU => 'kodate-kengaku',
        self::TYPE_MANSION_KENGAKU => 'mansion-kengaku',
        self::TYPE_GENCHI_KAKUNIN => 'genchi-kakunin',
        self::TYPE_BUY_MOUSHIKOMI => 'buy-moushikomi',
        self::TYPE_BUY_KEIYAKU => 'buy-keiyaku',
        self::TYPE_BUY_JUUYOUJIKOU => 'buy-juuyoujikou',
        self::TYPE_TOUKI_TETSUZUKI => 'touki-tetsuzuki',
        self::TYPE_TOUSHI_FUKUGYOU => 'toushi-fukugyou',
        self::TYPE_TOUSHI_SALARYMAN => 'toushi-fudousan',
        self::TYPE_TOUSHI_BUKKEN => 'toushi-bukken',
        self::TYPE_RIMAWARI => 'rimawari',
        self::TYPE_OWNER_CHANGE => 'owner-change',
        self::TYPE_TOUSHI_SETSUZEI => 'toushi-setsuzei',
        self::TYPE_BUNSAN_TOUSHI => 'bunsan-toushi',
        self::TYPE_TOUSHI_TYPE => 'toushi-type',
        self::TYPE_MANSION_TOUSHI => 'mansion-toushi',
        self::TYPE_BOUHANSEI => 'bouhansei',
        self::TYPE_TENKIN_MOCHIIE => 'tenkin-mochiie',
        self::TYPE_TOUSHI_COST => 'toushi-cost',
        self::TYPE_RUNNING_COST => 'running-cost',
        self::TYPE_TOUSHI_LOAN => 'toushi-loan',
        self::TYPE_SHUUZEN_KEIKAKU => 'shuuzen-keikaku',
        self::TYPE_REVERSE_MORTGAGE => 'reverse-mortgage',
        self::TYPE_TROUBLE_TAIOU => 'trouble-taiou',
        self::TYPE_YACHIN_TAINOU => 'yachin-tainou',
        self::TYPE_CHINTAI_HOSHOU => 'chintai-hoshou',
        self::TYPE_GENJOU_KAIFUKU => 'genjou-kaifuku',
        self::TYPE_CHINTAI_REFORM => 'chintai-reform',
        self::TYPE_CHINTAI_DIY => 'chintai-diy',
        self::TYPE_AKIYA_SOUZOKU => 'akiya-souzoku',
        self::TYPE_ISAN_BUNKATSU => 'isan-bunkatsu',
        self::TYPE_JIKKA_BAIKYAKU => 'jikka-baikyaku',
        self::TYPE_SOUZOKU_ZEI => 'souzoku-zei',
        self::TYPE_MEIGI_HENKOU => 'meigi-henkou',
        self::TYPE_HOUJIN_KOJIN => 'houjin-kojin',
        self::TYPE_KAIGYOU_FLOW => 'kaigyou-flow',
        self::TYPE_STORE_CONCEPT => 'store-concept',
        self::TYPE_KASHITENPO_TYPE => 'kashitenpo-type',
        self::TYPE_JIGYOUYOU_BUKKEN => 'jigyouyou-bukken',
        self::TYPE_KEIEI_RISK => 'keiei-risk',
        self::TYPE_TENANT_HIKIWATASHI => 'tenant-hikiwatashi',
        self::TYPE_NAISOU_SEIGEN => 'naisou-seigen',
        self::TYPE_FRANCHISE => 'franchise',
        self::TYPE_LEASEBACK => 'leaseback',
        self::TYPE_AOIRO_SHINKOKU => 'aoiro-shinkoku',
        self::TYPE_SHOUTENKAI => 'shoutenkai',
        self::TYPE_OPENING_COST => 'opening-cost',
        self::TYPE_KAIGYOU_SHIKIN => 'kaigyou-shikin',
        self::TYPE_SOURITSUHI => 'souritsuhi',
        self::TYPE_KENRI_KIN => 'kenri-kin',
        self::TYPE_STORE_ERABI => 'store-erabi',
        self::TYPE_STORE_LOCATION => 'store-location',
        self::TYPE_TENANT_RICCHI => 'tenant-ricchi',
        self::TYPE_INUKI_BUKKEN => 'inuki-bukken',
        self::TYPE_SKELETON_BUKKEN => 'skeleton-bukken',
        self::TYPE_KUUCHUU_TENPO => 'kuuchuu-tenpo',
        self::TYPE_KAIGYOU_TETSUZUKI => 'kaigyou-tetsuzuki',
        self::TYPE_EIGYOU_KYOKA => 'eigyou-kyoka',
        self::TYPE_SHINYA_EIGYOU => 'shinya-eigyou',
        self::TYPE_TENANT_KEIYAKU => 'tenant-keiyaku',
        self::TYPE_STORE_DESIGN => 'store-design',
        self::TYPE_STORE_LAYOUT => 'store-layout',
        self::TYPE_SEKOU_IRAI => 'sekou-irai',
        self::TYPE_SEKOU_MITSUMORI => 'sekou-mitsumori',
        self::TYPE_SEKOU_ITAKUSAKI => 'sekou-itakusaki',
        self::TYPE_BARRIER_FREE => 'barrier-free',
        self::TYPE_GAISOU_DESIGN => 'gaisou-design',
        self::TYPE_APART_VS_MANSION => 'apart-vs-mansion',
        self::TYPE_TOWNHOUSE_TERRACEHOUSE => 'town-terraced',
        self::TYPE_IKKODATE_CHINTAI => 'ikkodate-chintai',
        self::TYPE_CHINTAI_JOUKENSEIRI => 'chintai-joukenseiri',
        self::TYPE_KOUHO_BUKKEN => 'kouho-bukken',
        self::TYPE_CHINTAI_RICCHI => 'chintai-ricchi',
        self::TYPE_SETSUBI_SHIYOU => 'setsubi-shiyou',
        self::TYPE_SECURITY_TAISAKU => 'security-taisaku',
        self::TYPE_INTERNET_KANKYOU => 'internet-kankyou',
        self::TYPE_MINAMIMUKI => 'minamimuki',
        self::TYPE_KANRI_KEITAI => 'kanri-keitai',
        self::TYPE_KOSODATE_CHINTAI => 'kosodate-chintai',
        self::TYPE_PET_OK => 'pet-ok',
        self::TYPE_CHINTAI_KIKAN => 'chintai-kikan',
        self::TYPE_EKITOOI => 'ekitooi',
        self::TYPE_WOMEN_ONLY => 'women-only',
        self::TYPE_KAGU_KADEN => 'kagu-kaden',
        self::TYPE_YACHIN_YOSAN => 'yachin-yosan',
        self::TYPE_GAKUSEI_YACHIN => 'gakusei-yachin',
        self::TYPE_YACHIN_SOUBA => 'yachin-souba',
        self::TYPE_KANRIHI_KYOUEKIHI => 'kanrihi-kyouekihi',
        self::TYPE_SHIKIREI => 'shikirei',
        self::TYPE_HIKKOSHI_COST => 'hikkoshi-cost',
        self::TYPE_SHINSEIKATSU_COST => 'shinseikatsu-cost',
        self::TYPE_SOUDAN_POINT => 'soudan-point',
        self::TYPE_HOUMON_JUNBI => 'houmon-junbi',
        self::TYPE_NAIKEN_JUNBI => 'naiken-junbi',
        self::TYPE_NAIKEN_POINT => 'naiken-point',
        self::TYPE_SHUUHEN_KANKYOU => 'shuuhen-kankyou',
        self::TYPE_LENT_MOUSHIKOMI => 'lent-moushikomi',
        self::TYPE_NYUUKYO_SHINSA => 'nyuukyo-shinsa',
        self::TYPE_KEIYAKU_COST => 'keiyaku-cost',
        self::TYPE_LENT_JUUYOUJIKOU => 'lent-juuyoujikou',
        self::TYPE_CHINTAISHAKU => 'chintaishaku',
        self::TYPE_YACHIN_HOSHOU => 'yachin-hoshou',
        self::TYPE_CHUUTO_KAIYAKU => 'chuuto-kaiyaku',
        self::TYPE_KEIYAKU_GENJOU_KAIFUKU => 'shikikin-henkan',
        self::TYPE_HIKKOSHI_KAISHA => 'hikkoshi-kaisha',
        self::TYPE_HIKKOSHI_FLOW => 'hikkoshi-flow',
        self::TYPE_GENJOU_KAKUNIN => 'genjou-kakunin',
        self::TYPE_JIZEN_HANNYUU => 'jizen-hannyuu',
        self::TYPE_SODAIGOMI => 'sodaigomi',
        self::TYPE_TODOKEDE => 'todokede',
        self::TYPE_HIKKOSHI_JUNBI => 'hikkoshi-junbi',
        self::TYPE_MADORIZU => 'madorizu',
        self::TYPE_KINRIN_MANNERS => 'kinrin-manners',
        self::TYPE_JICHIKAI => 'jichikai',
        self::TYPE_ARTICLE_ORIGINAL => '',
    );

    //ファイル追加可能ページ一覧
    protected $_files_added_pages = array(
        // 入居者さま向けページ
        self::TYPE_TENANT,
        // 仲介会社さま向けページ
        self::TYPE_BROKER,
        // 管理会社さま向けページ
        self::TYPE_PROPRIETARY
    );

    private $_link_pages = array(
        self::TYPE_LINK,
        self::TYPE_ALIAS,
        self::TYPE_ESTATE_ALIAS,
        self::TYPE_LINK_HOUSE
    );

    // add new list category
    private $new_category = array(
        self::CATEGORY_SALE,
        self::CATEGORY_PURCHASE,
        self::CATEGORY_OWNERS_RENTAL_MANAGEMENT,
        self::CATEGORY_RESIDENTIAL_RENTAL,
        self::CATEGORY_BUSINESS_LEASE
    );

    private $useful_real_estate_page = array(
        self::CATEGORY_TOP_ARTICLE => array(
            self::TYPE_USEFUL_REAL_ESTATE_INFORMATION,
        ),
        self::CATEGORY_LARGE => array(
            self::TYPE_SALE,
            self::TYPE_PURCHASE,
            self::TYPE_OWNERS_RENTAL_MANAGEMENT,
            self::TYPE_RESIDENTIAL_RENTAL,
            self::TYPE_BUSINESS_LEASE,
            self::TYPE_LARGE_ORIGINAL,
        ),
        self::CATEGORY_SMALL => array(
            self::TYPE_CHECK_FLOW_OF_SALE,
            self::TYPE_LEARN_BASIC_OF_SALE,
            self::TYPE_KNOW_MEDIATION,
            self::TYPE_KNOW_COSTS_AND_TAXES,
            self::TYPE_KNOW_SALE_ASSESSMENT,
            self::TYPE_LEARN_LAND_SALES,
            self::TYPE_KNOW_HOW_TO_SALE,
            self::TYPE_KNOW_BASIC_OF_PURCHASE,
            self::TYPE_KNOW_WHEN_TO_BUY,
            self::TYPE_LEARN_BUY_SINGLE_FAMILY,
            self::TYPE_LEARN_BUY_APARTMENT,
            self::TYPE_LEARN_PRE_OWNED_RENOVATION,
            self::TYPE_KNOW_COST_OF_PURCHASE,
            self::TYPE_LEARN_MORTGAGES,
            self::TYPE_THINK_FUTURE_OF_YOUR_HOME,
            self::TYPE_LEARN_SITE_AND_PREVIEWS,
            self::TYPE_LEARN_SALES_CONTRACTS,
            self::TYPE_LEARN_REAL_ESTATE_INVESTMENT,
            self::TYPE_KNOW_FUNDS_AND_LOANS,
            self::TYPE_LEARN_RENTAL_MANAGEMENT,
            self::TYPE_KNOW_INHERITANCE,
            self::TYPE_LEARN_BASIC_STORE_OPENING,
            self::TYPE_LEARN_START_UP_FUNDS,
            self::TYPE_LEARN_CHOOSE_STORE,
            self::TYPE_LEARN_PROCEDURES_AND_CONTRACTS,
            self::TYPE_LEARN_STORE_DESIGN,
            self::TYPE_LEARN_TYPES_OF_RENTAL,
            self::TYPE_ORGANIZE_DESIRED_CONDITIONS_FOR_RENTAL,
            self::TYPE_LEARN_RENT_EXPENSES,
            self::TYPE_LEARN_VISITS_COMPANIES_AND_SITE,
            self::TYPE_LEARN_LEASE_AGREEMENTS,
            self::TYPE_KNOW_MOVING,
            self::TYPE_LEARN_LIVING_RENT,
            self::TYPE_SMALL_ORIGINAL,
        ),
        self::CATEGORY_ARTICLE => array(
            self::TYPE_BAIKYAKU_POINT,
            self::TYPE_SELL,
            self::TYPE_REPLACEMENT_AHEAD_SALE,
            self::TYPE_KYOJUUCHUU_BAIKYAKU,
            self::TYPE_SELL_HIKIWATASHI,
            self::TYPE_BAIKYAKU_TYPE,
            self::TYPE_BAIKYAKU_SOUBA,
            self::TYPE_SEIYAKU_KAKAKU,
            self::TYPE_KEIYAKU_FUTEKIGOU,
            self::TYPE_SHINSEI_SHORUI,
            self::TYPE_KOJIN_TORIHIKI,
            self::TYPE_NINBAI,
            self::TYPE_CHUUKAI_KISO,
            self::TYPE_KYOUDOU_CHUUKAI,
            self::TYPE_BAIKAI_KEIYAKU,
            self::TYPE_IPPAN_BAIKAI,
            self::TYPE_SENZOKU_SENNIN,
            self::TYPE_KAITORI_OSHOU,
            self::TYPE_HYOUKAGAKU,
            self::TYPE_KAIKAE_KEIKAKU,
            self::TYPE_BAIKYAKU_COST,
            self::TYPE_TEITOUKEN,
            self::TYPE_JOUTOSHOTOKU,
            self::TYPE_TOKUBETSU_KOUJO,
            self::TYPE_KAKUTEI_SHINKOKU,
            self::TYPE_TSUNAGI_YUUSHI,
            self::TYPE_FUKUSUU_SATEI,
            self::TYPE_KANNI_SATEI,
            self::TYPE_BUILDING_EVALUATION,
            self::TYPE_URERU_TOCHI,
            self::TYPE_FURUYATSUKI_SARACHI,
            self::TYPE_TOCHI_BAIKYAKU,
            self::TYPE_KAKUTEI_SOKURYOU,
            self::TYPE_HATAZAOCHI,
            self::TYPE_NOUCHI,
            self::TYPE_BAIKYAKU_JIKI,
            self::TYPE_BAIKYAKU_20Y,
            self::TYPE_BAIKYAKU_30Y,
            self::TYPE_URENAI_RIYUU,
            self::TYPE_PURCHASING_REAL_ESTATE,
            self::TYPE_IRAISAKI_SENTAKU,
            self::TYPE_NAIRAN_TAIOU,
            self::TYPE_BUYER_VISITS_DETACHEDHOUSE,
            self::TYPE_POINTS_SALE_OF_CONDOMINIUM,
            self::TYPE_SAIKENCHIKU_FUKA,
            self::TYPE_MOCHIIE_MERIT,
            self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
            self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND,
            self::TYPE_BUY_JOUKENSEIRI,
            self::TYPE_BUY_RICCHI,
            self::TYPE_MADORI,
            self::TYPE_SETAIBETSU,
            self::TYPE_KAIDAN_TYPE,
            self::TYPE_SEINOU_HYOUKA,
            self::TYPE_LIFE_PLAN,
            self::TYPE_BUY,
            self::TYPE_BUY_KEIYAKU_FLOW,
            self::TYPE_SAISHUU_KAKUNIN,
            self::TYPE_NYUUKYO_FLOW,
            self::TYPE_COMMUNICATION,
            self::TYPE_SHINCHIKU_NAIRANKAI,
            self::TYPE_NYUUKYO_TROUBLE,
            self::TYPE_KOUNYUU_JIKI,
            self::TYPE_PURCHASE_BEST_TIMING,
            self::TYPE_20DAI_KOUNYUU,
            self::TYPE_30DAI_KOUNYUU,
            self::TYPE_50DAI_KOUNYUU,
            self::TYPE_TOCHI_ERABI,
            self::TYPE_ERECTIONHOUSING_ORDERHOUSE,
            self::TYPE_KENCHIKU_JOUKENTSUKI,
            self::TYPE_NISETAI_JUUTAKU,
            self::TYPE_KODATE_SHINSEIKATSU,
            self::TYPE_MANSION_TYPE,
            self::TYPE_MAISONETTE_MANSION,
            self::TYPE_MANSION_SERVICE,
            self::TYPE_MANSION_SHINSEIKATSU,
            self::TYPE_RENOVATION_BUKKEN,
            self::TYPE_CHUUKO_RENOVATION,
            self::TYPE_HOME_INSPECTION,
            self::TYPE_FUNDING_PLAN,
            self::TYPE_KOUNYUU_YOSAN,
            self::TYPE_KOUNYUU_ATAMAKIN,
            self::TYPE_YOSAN_OVER,
            self::TYPE_KOUNYUU_SHOKIHIYOU,
            self::TYPE_KOUNYUUGO_COST,
            self::TYPE_LOAN_MERIT,
            self::TYPE_TYPES_MORTGAGE_LOANS,
            self::TYPE_KINRI_TYPE,
            self::TYPE_HENSAI_TYPE,
            self::TYPE_HENSAI_KIKAN,
            self::TYPE_SHINSA_KIJUN,
            self::TYPE_BONUS_HENSAI,
            self::TYPE_SHINSA_FLOW,
            self::TYPE_LOAN_KEIKAKU,
            self::TYPE_FLAT35,
            self::TYPE_KURIAGE_HENSAI,
            self::TYPE_TOMOBATARAKI_LOAN,
            self::TYPE_LOAN_KARIKAE,
            self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN,
            self::TYPE_SUMAI_SHOURAISEI,
            self::TYPE_SHISAN_KACHI,
            self::TYPE_KODATE_KENGAKU,
            self::TYPE_MANSION_KENGAKU,
            self::TYPE_GENCHI_KAKUNIN,
            self::TYPE_BUY_MOUSHIKOMI,
            self::TYPE_BUY_KEIYAKU,
            self::TYPE_BUY_JUUYOUJIKOU,
            self::TYPE_TOUKI_TETSUZUKI,
            self::TYPE_TOUSHI_FUKUGYOU,
            self::TYPE_TOUSHI_SALARYMAN,
            self::TYPE_TOUSHI_BUKKEN,
            self::TYPE_RIMAWARI,
            self::TYPE_OWNER_CHANGE,
            self::TYPE_TOUSHI_SETSUZEI,
            self::TYPE_BUNSAN_TOUSHI,
            self::TYPE_TOUSHI_TYPE,
            self::TYPE_MANSION_TOUSHI,
            self::TYPE_BOUHANSEI,
            self::TYPE_TENKIN_MOCHIIE,
            self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER,
            self::TYPE_UTILIZING_LAND,
            self::TYPE_TOUSHI_COST,
            self::TYPE_RUNNING_COST,
            self::TYPE_TOUSHI_LOAN,
            self::TYPE_SHUUZEN_KEIKAKU,
            self::TYPE_REVERSE_MORTGAGE,
            self::TYPE_MEASURES_AGAINST_VACANCIES,
            self::TYPE_TROUBLE_TAIOU,
            self::TYPE_YACHIN_TAINOU,
            self::TYPE_CHINTAI_HOSHOU,
            self::TYPE_GENJOU_KAIFUKU,
            self::TYPE_CHINTAI_REFORM,
            self::TYPE_HOUSE_REMODELING,
            self::TYPE_CHINTAI_DIY,
            self::TYPE_LEASING_MANAGEMENT_MENU,
            self::TYPE_TROUBLED_LEASING_MANAGEMENT,
            self::TYPE_LEND,
            self::TYPE_AKIYA_SOUZOKU,
            self::TYPE_ISAN_BUNKATSU,
            self::TYPE_JIKKA_BAIKYAKU,
            self::TYPE_SOUZOKU_ZEI,
            self::TYPE_PURCHASE_INHERITANCE_TAX,
            self::TYPE_MEIGI_HENKOU,
            self::TYPE_HOUJIN_KOJIN,
            self::TYPE_KAIGYOU_FLOW,
            self::TYPE_STORE_CONCEPT,
            self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN,
            self::TYPE_STORE_SEARCH,
            self::TYPE_KASHITENPO_TYPE,
            self::TYPE_JIGYOUYOU_BUKKEN,
            self::TYPE_KEIEI_RISK,
            self::TYPE_TENANT_HIKIWATASHI,
            self::TYPE_NAISOU_SEIGEN,
            self::TYPE_FRANCHISE,
            self::TYPE_LEASEBACK,
            self::TYPE_AOIRO_SHINKOKU,
            self::TYPE_SHOUTENKAI,
            self::TYPE_OPENING_COST,
            self::TYPE_KAIGYOU_SHIKIN,
            self::TYPE_SOURITSUHI,
            self::TYPE_KENRI_KIN,
            self::TYPE_STORE_ERABI,
            self::TYPE_STORE_LOCATION,
            self::TYPE_TENANT_RICCHI,
            self::TYPE_INUKI_BUKKEN,
            self::TYPE_SKELETON_BUKKEN,
            self::TYPE_KUUCHUU_TENPO,
            self::TYPE_KAIGYOU_TETSUZUKI,
            self::TYPE_EIGYOU_KYOKA,
            self::TYPE_SHINYA_EIGYOU,
            self::TYPE_TENANT_KEIYAKU,
            self::TYPE_STORE_DESIGN,
            self::TYPE_STORE_LAYOUT,
            self::TYPE_SEKOU_IRAI,
            self::TYPE_SEKOU_MITSUMORI,
            self::TYPE_SEKOU_ITAKUSAKI,
            self::TYPE_BARRIER_FREE,
            self::TYPE_GAISOU_DESIGN,
            self::TYPE_APART_VS_MANSION,
            self::TYPE_TOWNHOUSE_TERRACEHOUSE,
            self::TYPE_IKKODATE_CHINTAI,
            self::TYPE_CHINTAI_JOUKENSEIRI,
            self::TYPE_KOUHO_BUKKEN,
            self::TYPE_SQUEEZE_CANDIDATE,
            self::TYPE_CHINTAI_RICCHI,
            self::TYPE_SETSUBI_SHIYOU,
            self::TYPE_SECURITY_TAISAKU,
            self::TYPE_INTERNET_KANKYOU,
            self::TYPE_MINAMIMUKI,
            self::TYPE_KANRI_KEITAI,
            self::TYPE_KOSODATE_CHINTAI,
            self::TYPE_PET_OK,
            self::TYPE_CHINTAI_KIKAN,
            self::TYPE_EKITOOI,
            self::TYPE_WOMEN_ONLY,
            self::TYPE_KAGU_KADEN,
            self::TYPE_YACHIN_YOSAN,
            self::TYPE_UPPER_LIMIT,
            self::TYPE_GAKUSEI_YACHIN,
            self::TYPE_YACHIN_SOUBA,
            self::TYPE_KANRIHI_KYOUEKIHI,
            self::TYPE_RENTAL_INITIAL_COST,
            self::TYPE_SHIKIREI,
            self::TYPE_HIKKOSHI_COST,
            self::TYPE_SHINSEIKATSU_COST,
            self::TYPE_SOUDAN_POINT,
            self::TYPE_HOUMON_JUNBI,
            self::TYPE_NAIKEN_JUNBI,
            self::TYPE_PREVIEW,
            self::TYPE_NAIKEN_POINT,
            self::TYPE_SHUUHEN_KANKYOU,
            self::TYPE_RENT,
            self::TYPE_LENT_MOUSHIKOMI,
            self::TYPE_NYUUKYO_SHINSA,
            self::TYPE_KEIYAKU_COST,
            self::TYPE_LENT_JUUYOUJIKOU,
            self::TYPE_CHINTAISHAKU,
            self::TYPE_YACHIN_HOSHOU,
            self::TYPE_CHUUTO_KAIYAKU,
            self::TYPE_KEIYAKU_GENJOU_KAIFUKU,
            self::TYPE_HIKKOSHI_KAISHA,
            self::TYPE_HIKKOSHI_FLOW,
            self::TYPE_GENJOU_KAKUNIN,
            self::TYPE_JIZEN_HANNYUU,
            self::TYPE_MOVING,
            self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE,
            self::TYPE_SODAIGOMI,
            self::TYPE_TODOKEDE,
            self::TYPE_HIKKOSHI_JUNBI,
            self::TYPE_MADORIZU,
            self::TYPE_COMFORTABLELIVING_RESIDENT_RULES,
            self::TYPE_KINRIN_MANNERS,
            self::TYPE_JICHIKAI,
            self::TYPE_ARTICLE_ORIGINAL,
        ),
    );

    // public function init() {
    public function __construct()
    {

        parent::__construct();

        $this->_categories = array(
            self::CATEGORY_TOP         => 'トップページ',
            self::CATEGORY_COMPANY     => '会社',
            self::CATEGORY_STRUCTURE   => '物件',
            self::CATEGORY_OWNER       => 'オーナーさま向けページ',
            self::CATEGORY_CORPORATION => '法人向けページ',
            self::CATEGORY_FOR         => '○○さま向けページ',
            self::CATEGORY_BLOG        => 'ブログ',
            //CMSテンプレートパターンの追加
            self::CATEGORY_COLUMN      => 'コラム',
            //CMSテンプレートパターンの追加
            self::CATEGORY_FREE        => 'フリーページ',
            self::CATEGORY_MEMBER_ONLY => '会員さま専用ページ',
            self::CATEGORY_OTHER       => 'その他コンテンツ',
            self::CATEGORY_INFO        => 'お知らせ',
            self::CATEGORY_POLICY      => '規約',
            self::CATEGORY_FORM        => 'お問い合わせ',
            self::CATEGORY_LINK        => 'リンク',

            //CMSテンプレートパターンの追加
            self::CATEGORY_SALE     => '売却コンテンツ',
            self::CATEGORY_PURCHASE => '購入コンテンツ',
            self::CATEGORY_OWNERS_RENTAL_MANAGEMENT => 'オーナー向けコンテンツ〈賃貸管理〉',
            self::CATEGORY_RESIDENTIAL_RENTAL       => '居住用賃貸コンテンツ',
            self::CATEGORY_BUSINESS_LEASE           => '事業用賃貸コンテンツ',
            self::CATEGORY_LARGE    => '大カテゴリー',
            self::CATEGORY_SMALL    => '小カテゴリー',
            self::CATEGORY_ARTICLE    => '記事',
            self::CATEGORY_TOP_ARTICLE => '不動産お役立ち情報',
            //CMSテンプレートパターンの追加
        );

        $profile = Cms::getInstance()->getProfile();
        if (($profile != null) && ($profile->__isset('cms_plan'))) {
            $this->plan      =  Plan::factory(CmsPlan::getCmsPLanName($profile->cms_plan));
        } else {
            $this->plan      =  Plan::factory('Top');
        }

        $this->_categoryMap     = &$this->plan->categoryMap;

        // カテゴリごとの下層に指定できるカテゴリ
        $this->_childCategories = array(
            self::CATEGORY_COMPANY     => array(
                self::CATEGORY_COMPANY,
                self::CATEGORY_FREE,
                self::CATEGORY_OTHER
            ),
            self::CATEGORY_FREE        => array(
                self::CATEGORY_FREE,
                self::CATEGORY_OTHER
            ),
            self::CATEGORY_MEMBER_ONLY => array(
                self::CATEGORY_STRUCTURE,
                self::CATEGORY_OWNER,
                self::CATEGORY_CORPORATION,
                self::CATEGORY_FOR,
                self::CATEGORY_FREE
            ),
            self::CATEGORY_OTHER       => array(
                self::CATEGORY_OTHER,
                self::CATEGORY_FREE
            )
        );

        // $this->_childPagesUsefulEstate = array(
        //     self::TYPE_USEFUL_REAL_ESTATE_INFORMATION => array(
        //         self::TYPE_SALE,
        //         self::TYPE_PURCHASE,
        //         self::TYPE_OWNERS_RENTAL_MANAGEMENT,
        //         self::TYPE_RESIDENTIAL_RENTAL,
        //         self::TYPE_BUSINESS_LEASE,
        //     ),
        //     self::TYPE_SALE => array(
        //         self::TYPE_CHECK_FLOW_OF_SALE,
        //         self::TYPE_LEARN_BASIC_OF_SALE,
        //         self::TYPE_KNOW_MEDIATION,
        //         self::TYPE_KNOW_COSTS_AND_TAXES,
        //         self::TYPE_KNOW_SALE_ASSESSMENT,
        //         self::TYPE_LEARN_LAND_SALES,
        //         self::TYPE_KNOW_HOW_TO_SALE
        //     ),
        //     self::TYPE_PURCHASE => array(
        //         self::TYPE_KNOW_BASIC_OF_PURCHASE,
        //         self::TYPE_KNOW_WHEN_TO_BUY,
        //         self::TYPE_LEARN_BUY_SINGLE_FAMILY,
        //         self::TYPE_LEARN_BUY_APARTMENT,
        //         self::TYPE_LEARN_PRE_OWNED_RENOVATION,
        //         self::TYPE_KNOW_COST_OF_PURCHASE,
        //         self::TYPE_LEARN_MORTGAGES,
        //         self::TYPE_THINK_FUTURE_OF_YOUR_HOME,
        //         self::TYPE_LEARN_SITE_AND_PREVIEWS,
        //         self::TYPE_LEARN_SALES_CONTRACTS,
        //     ),
        //     self::TYPE_RESIDENTIAL_RENTAL => array(

        //     ),
        //     self::TYPE_OWNERS_RENTAL_MANAGEMENT => array(

        //     ),
        //     self::TYPE_BUSINESS_LEASE => array(

        //     ),

        // );

        // Add new category company (Advance)
        // if ($this->plan instanceof \Plan\Top || $this->plan instanceof \Plan\Advance) {
        //     $this->_childCategories = $this->addNewCategory($this->_childCategories, $this->new_category, self::CATEGORY_FREE);
        // }

        // 固定メニュー
        $this->_fixedMenu = array(
            self::TYPE_INFO_INDEX,
            self::TYPE_INFO_DETAIL,
            self::TYPE_PRIVACYPOLICY,
            self::TYPE_SITEPOLICY,
            self::TYPE_USEFUL_REAL_ESTATE_INFORMATION,
            self::TYPE_FORM_CONTACT,

            self::TYPE_FORM_LIVINGLEASE,
            self::TYPE_FORM_OFFICELEASE,
            self::TYPE_FORM_LIVINGBUY,
            self::TYPE_FORM_OFFICEBUY,
            //#4274 Change spec form FDP contact
            //self::TYPE_FORM_FDP_CONTACT,
        );


        // 一意なページ
        $this->_uniquePages = array(
            self::TYPE_TOP,
            self::TYPE_INFO_INDEX,
            self::TYPE_PRIVACYPOLICY,
            self::TYPE_SITEPOLICY,
            self::TYPE_FORM_CONTACT,
            //#4274 Change spec form FDP contact
            //self::TYPE_FORM_FDP_CONTACT,
            self::TYPE_FORM_DOCUMENT,
            self::TYPE_FORM_ASSESSMENT,

            // 物件問い合わせ
            self::TYPE_FORM_LIVINGBUY,
            self::TYPE_FORM_LIVINGLEASE,
            self::TYPE_FORM_OFFICEBUY,
            self::TYPE_FORM_OFFICELEASE,

            // 物件リクエスト
            self::TYPE_FORM_REQUEST_LIVINGBUY,
            self::TYPE_FORM_REQUEST_LIVINGLEASE,
            self::TYPE_FORM_REQUEST_OFFICEBUY,
            self::TYPE_FORM_REQUEST_OFFICELEASE,

        );

        // 公開必須ページ
        $this->_requiredPages = array(
            self::TYPE_TOP,
            self::TYPE_COMPANY,
            self::TYPE_PRIVACYPOLICY,
            self::TYPE_SITEPOLICY,
            self::TYPE_SITEMAP,
            self::TYPE_FORM_CONTACT,
        );

        // ページネーションを含むページ
        $this->_hasPagination = array(
            self::TYPE_BLOG_INDEX,
            self::TYPE_CUSTOMERVOICE_INDEX,
            self::TYPE_EVENT_INDEX,
            self::TYPE_INFO_INDEX,
            self::TYPE_SELLINGCASE_INDEX,
            self::TYPE_SHOP_INDEX,
            self::TYPE_STAFF_INDEX,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_INDEX,

        );

        // サイトマップページでリストに表示しないページ
        $this->_notDisplayInSitemap = array(
            HpPageRepository::TYPE_BLOG_DETAIL,
            HpPageRepository::TYPE_INFO_DETAIL,
            HpPageRepository::TYPE_SITEPOLICY,
            HpPageRepository::TYPE_PRIVACYPOLICY,
            //CMSテンプレートパターンの追加
            HpPageRepository::TYPE_COLUMN_DETAIL,

        );

        // 物件検索のお問い合わせ
        $this->_contactPageForSearch = [
            HpPageRepository::TYPE_FORM_LIVINGBUY,
            HpPageRepository::TYPE_FORM_LIVINGLEASE,
            HpPageRepository::TYPE_FORM_OFFICEBUY,
            HpPageRepository::TYPE_FORM_OFFICELEASE,
        ];

        $this->_estateRequestPage = [
            HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY,
            HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE,
            HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY,
            HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE,
        ];

        $this->_articleOriginal = [
            HpPageRepository::TYPE_LARGE_ORIGINAL,
            HpPageRepository::TYPE_SMALL_ORIGINAL,
            HpPageRepository::TYPE_ARTICLE_ORIGINAL,
        ];
    }

    /**
     * サイトマップで使用するデータを取得する
     * @return HpPageRepository_Rowset
     */
    public function fetchSiteMapRows($hpId)
    {
        $notIn = array(
            self::TYPE_BLOG_DETAIL,
            self::TYPE_INFO_DETAIL,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_DETAIL,

            // // 5444
            // self::TYPE_LARGE_ORIGINAL,
            // self::TYPE_SMALL_ORIGINAL,
            // self::TYPE_ARTICLE_ORIGINAL,

        );
        // $notIn = array_merge($notIn, $this->getAllPagesUsefulEstate(array(self::TYPE_USEFUL_REAL_ESTATE_INFORMATION)));
        return $this->model->where('hp_id', $hpId)->whereNotIn('page_type_code', $notIn)->orderBy('sort')->get();
    }

    /**
     * サイトマップで使用するデータを取得する
     * @return HpPageRepository_Rowset
     */
    public function fetchUsefulRealEstatePages($hpId)
    {   
        $select = $this->model->select();
        $select->where('hp_id', $hpId);
        $select->whereIn('page_type_code', $this->getAllPagesUsefulEstate());
        $select->whereIn('page_category_code', $this->getCategoryCodeArticle());
        $select->orderBy('sort');
        return $this->fetchAll($select , array('sort'));
    }

    /**
     * サイトマップで使用する一覧系のデータを取得する
     * @return HpPageRepository_Rowset
     */
    public function fetchSiteMapIndexRows($hpId)
    {
        $in = array(
            self::TYPE_BLOG_DETAIL,
            self::TYPE_INFO_DETAIL,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_DETAIL,
        );
        return $this->model->where('hp_id', $hpId)->whereIn('page_type_code', $in)->orderBy('sort')->get();
    }

    /**
     * サイトマップで使用するデータを取得する
     * @return HpPageRepository_Rowset
     */
    public function fetchIndexRows($hpId)
    {
        $in = array(
            self::TYPE_BLOG_DETAIL,
            self::TYPE_INFO_DETAIL,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_DETAIL,
        );
        return $this->fetchAll(array(
            ['hp_id', $hpId],
            'whereIn' => ['page_type_code', $in]
        ), array('sort'));
    }

    /**
     *
     */
    public function getCategoryMap()
    {
        return $this->_categoryMap;
    }

    /**
     * 「ATHOME_HP_DEV-3047 公開予約が反映されない現象を調査、解消する」で追加
     */
    public function setCategoryMap($companyRow)
    {
        $this->plan             =    Plan::factory(CmsPlan::getCmsPLanName($companyRow->cms_plan));
        $this->_categoryMap     = &$this->plan->categoryMap;
        $this->_allChildPagesArticle     = &$this->plan->pageMapArticle;
    }

    /**
     * タイプの属するカテゴリを取得する
     *
     * @param int $type
     *
     * @return int|NULL
     */
    public function getCategoryByType($type)
    {
        $type = (int)$type;
        foreach ($this->_categoryMap as $category => $types) {
            if (in_array($type, $types, true)) {
                return $category;
            }
        }
        return NULL;
    }

    /**
     * 下層に指定可能なタイプを取得する
     *
     * @param int $type
     *
     * @return array
     */
    public function getChildTypesByType($type)
    {
        $types = array();
        $categories = array();
        $canNotCreateTypes = array();

        if (in_array((int)$type, $this->getHasDetailPageTypeList(), true)) {
            // 一覧ページの下層は詳細ページのみ追加可能
            // 詳細ページIDは一覧ページIDの次の数値
            $types = array($type + 1);

            return $types;
        }

        // 一覧ページで無い場合は詳細ページ作成不可
        $canNotCreateTypes = $this->getDetailPageTypeList();

        $category = $this->getCategoryByType($type);
        // 作成可能なカテゴリ
        if (isset($this->_childCategories[$category])) {
            $categories = $this->_childCategories[$category];
        }

        // タイプ：トップ、カテゴリ：リンク、カテゴリ：ブログ、カテゴリ：おしらせ以外は下層にリンク作成可能
        $canNotCreateLinkTypes = array_merge(
            $this->_categoryMap[self::CATEGORY_LINK],
            $this->_categoryMap[self::CATEGORY_BLOG],
            $this->_categoryMap[self::CATEGORY_INFO],
            $this->_categoryMap[self::CATEGORY_COLUMN],
            array(
                self::TYPE_TOP
            )
        );
        if (!in_array((int)$type, $canNotCreateLinkTypes, true)) {
            $categories[] = self::CATEGORY_LINK;
        }


        foreach ($categories as $category) {
            $types = array_merge($types, $this->_categoryMap[$category]);
        }

        foreach ($canNotCreateTypes as $type) {
            if (false !== ($index = array_search($type, $types, true))) {
                array_splice($types, $index, 1);
            }
        }

        return $types;
    }

    /**
     * 下層に指定可能なタイプ一覧を取得する
     */
    public function getAllChildTypesByType()
    {
        $types = array();
        foreach ($this->getTypeList() as $name => $type) {
            $childTypes = $this->getChildTypesByType($type);
            if ($childTypes) {
                $types[$type] = $childTypes;
            }
        }
        return $types;
    }

    /**
     * 
     */
    public function getAllChildTypesUsefulEstateByType()
    {
        $types = array();
        foreach ($this->getAllPagesUsefulEstate($this->getArticleOriginal()) as $type) {
            $childTypes = $this->getChildTypesUsefulEstateByType($type);
            if ($childTypes) {
                $types[$type] = $childTypes;
            }
        }
        return $types;
    }

    public function getChildTypesUsefulEstateByType($type)
    {
        $types = [];
        if (isset($this->_allChildPagesArticle[$type])) {
            $types = $this->_allChildPagesArticle[$type];
        }
        return $types;
    }

    public function getParentTypeArticle($page)
    {
        if ($page['page_category_code'] == self::CATEGORY_SMALL) {
            return $page['parent_page_id'];
        }
        return $page['id'];
    }

    public function getAllPagesUsefulEstate($notPage = array(), $category = null)
    {
        $results = array();
        foreach ($this->useful_real_estate_page as $key => $pages) {
            if ($category && $category == $key) {
                $results = array_merge($results, array_diff($pages, $notPage));
                break;
            } else {
                $results = array_merge($results, array_diff($pages, $notPage));
            }
        }
        return $results;
    }

    public function getCategoryUsefulEstate($type)
    {
        foreach ($this->useful_real_estate_page as $category => $pages) {
            if (in_array($type, $pages)) {
                return $category;
            }
        }
        return null;
    }

    public function getCategories()
    {
        return $this->_categories;
    }

    /**
     * ページタイプ名一覧を取得
     *
     * @return array[CONST NAME] = id
     */
    public function getTypeList()
    {

        $reflect = new \ReflectionClass(get_class());
        $consts = $reflect->getConstants();
        foreach ($consts as $name => $num) {
            if (strpos($name, 'TYPE_', 0) === false) {
                unset($consts[$name]);
            }
        }
        return $consts;
    }

    /**
     * ページカテゴリ名一覧を取得
     *
     * @return array[CONST NAME] = id
     */
    public function getCategoryList()
    {

        $reflect = new \ReflectionClass(get_class());
        $consts = $reflect->getConstants();
        foreach ($consts as $name => $num) {
            if (strpos($name, 'CATEGORY_', 0) === false) {
                unset($consts[$name]);
            }
        }
        return $consts;
    }

    /**
     * ページタイプ名（日本語）一覧を取得
     *
     * @return array[i] = 和名
     */
    public function getTypeListJp()
    {
        return $this->pages;
    }

    public function setTypeListJp($pages)
    {
        $this->pages = $pages;
    }

    /**
     * ページタイプ名（日本語）を取得
     *
     * @param $type_code
     *
     * @return string
     */
    public function getTypeNameJp($type_code)
    {

        $array = $this->getTypeListJp();
        return (string)$array[$type_code];
    }

    /**
     * ページディスクリプション（日本語）一覧を取得
     *
     * @return array[i] = 和名
     */
    public function getDescriptionListJp()
    {

        return $this->pages_description;
    }

    /**
     * ページディスクリプション名（日本語）を取得
     *
     * @param $int
     *
     * @return string
     */
    public function getDescriptionNameJp($type_code)
    {

        $array = $this->getDescriptionListJp();
        if (!isset($array[$type_code])) return "";
        return (string)$array[$type_code];
    }

    /**
     * ページキーワード（日本語）一覧を取得
     *
     * @return array[i] = 和名
     */
    public function getKeywordListJp()
    {

        return $this->pages_keyword;
    }

    /**
     * ページキーワード名（日本語）を取得
     *
     * @param $int
     *
     * @return string
     */
    public function getKeywordNameJp($type_code)
    {

        $array = $this->getKeywordListJp();
        if (!isset($array[$type_code])) return "";
        return (string)$array[$type_code];
    }
    /**
     * ページ名一覧を取得
     *
     * @return array[i] = 和名
     */
    public function getPageNameListJp()
    {

        return $this->pages_name;
    }

    /**
     * ページ名を取得
     *
     * @param $int
     *
     * @return string
     */
    public function getPageNameJp($type_code)
    {

        $array = $this->getPageNameListJp();
        if (!isset($array[$type_code])) return "";
        return (string)$array[$type_code];
    }

    /**
     * ページタイプ名（日本語）一覧を取得
     *
     * @return array[i] = 和名
     */
    public function getTypeListEn()
    {

        return array_flip($this->getTypeList());
    }

    /**
     * ページタイプ名（英語）を取得
     *
     * @param $int
     *
     * @return $string
     */
    public function getTypeNameEn($type_code)
    {

        return (string)array_search($type_code, $this->getTypeList());
    }

    /**
     * 新規作成可能なページタイプ
     *
     * @return array@return array[CONST NAME] = id
     */
    private function getCreatableTypeList()
    {

        $pageTypes = $this->getEdiableTypeList();
        foreach ($pageTypes as $name => $const) {
            switch ($name) {
                    // 一覧ページ不可
                case 'TYPE_INFO_INDEX':
                case 'TYPE_SHOP_INDEX':
                case 'TYPE_STAFF_INDEX':
                case 'TYPE_STRUCTURE_INDEX':
                case 'TYPE_BLOG_INDEX':
                case 'TYPE_CUSTOMERVOICE_INDEX':
                    //CMSテンプレートパターンの追加
                case 'TYPE_COLUMN_INDEX':
                    unset($pageTypes[$name]);
                    break;
                default:
                    break;
            }
        }
        return $pageTypes;
    }

    /**
     * 編集可能なページタイプ
     *
     * @return array[CONST NAME] = id
     */
    private function getEdiableTypeList()
    {

        $pageTypes = $this->getTypeList();
        foreach ($pageTypes as $name => $const) {
            switch ($name) {
                    // リンク、エイリアス不可
                case 'TYPE_LINK':
                case 'TYPE_ALIAS':
                case 'TYPE_ESTATE_ALIAS':
                case 'TYPE_SITEMAP':
                case 'TYPE_LINK_HOUSE':
                    unset($pageTypes[$name]);
                    break;
                default:
                    break;
            }
        }
        return $pageTypes;
    }

    public function isFixedMenuType($type)
    {
        return in_array((int)$type, $this->_fixedMenu, true);
    }

    public function getFixedMenuTypeList()
    {
        return $this->_fixedMenu;
    }

    /**
     *
     */
    public function getGlobalMenuTypeList()
    {
        $notGlobals = array_merge($this->_fixedMenu, $this->getDetailPageTypeList());
        $pageTypes = $this->getTypeList();
        foreach ($pageTypes as $name => $value) {
            if (in_array($value, $notGlobals, true)) {
                unset($pageTypes[$name]);
            }
        }
        return $pageTypes;
    }

    public function getNotInMenuTypeList()
    {
        $notIns = array_diff($this->getHasDetailPageTypeList(), $this->getHasMultiPageTypeList());
        $notIns = array_merge($notIns, $this->_categoryMap[self::CATEGORY_LINK], $this->getMultiPageTypeList());
        $notIns = array_merge($notIns, array(self::TYPE_MEMBERONLY));
        $pageTypes = $this->getTypeList();
        foreach ($pageTypes as $name => $value) {
            if (in_array($value, $notIns, true)) {
                unset($pageTypes[$name]);
            }
        }
        return $pageTypes;
    }

    public function getUniqueTypeList()
    {
        return $this->_uniquePages;
    }

    public function isUniqueType($type)
    {
        return in_array((int)$type, $this->_uniquePages, true);
    }

    /**
     * 一覧ページのリストを取得
     *
     * @return array
     */
    public function getHasDetailPageTypeList()
    {
        return array(
            self::TYPE_SHOP_INDEX,
            self::TYPE_STAFF_INDEX,
            self::TYPE_STRUCTURE_INDEX,
            self::TYPE_BLOG_INDEX,
            self::TYPE_CUSTOMERVOICE_INDEX,
            self::TYPE_SELLINGCASE_INDEX,
            self::TYPE_EVENT_INDEX,
            self::TYPE_INFO_INDEX,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_INDEX,
        );
    }
    /**
     * 階層外にページを作成する必要があるか否か
     * @param $pageCodeType
     */
    public function isRequiredWithoutHierarchyPage($pageCodeType)
    {
        if (
            self::TYPE_CUSTOMERVOICE_INDEX === $pageCodeType
            || self::TYPE_SELLINGCASE_INDEX === $pageCodeType
            || self::TYPE_EVENT_INDEX === $pageCodeType
            || self::TYPE_SHOP_INDEX === $pageCodeType
            || self::TYPE_STAFF_INDEX === $pageCodeType
            || self::TYPE_MEMBERONLY === $pageCodeType
            || in_array($pageCodeType, $this->getAllPagesUsefulEstate(array(self::TYPE_USEFUL_REAL_ESTATE_INFORMATION)))
        ) {
            return true;
        }
        return false;
    }
    /**
     * 詳細ページの情報をインクルードして一覧を生成するページ
     *
     * @param $class_name
     * @return bool
     */
    public function includeDetailPage($class_name)
    {

        $list = array(
            'Custom_Hp_Page_BlogIndex',
            'Custom_Hp_Page_CustomervoiceIndex',
            'Custom_Hp_Page_EventIndex',
            'Custom_Hp_Page_InfoIndex',
            'Custom_Hp_Page_ShopIndex',
            'Custom_Hp_Page_SellingcaseIndex',
            'Custom_Hp_Page_StaffIndex',
            //CMSテンプレートパターンの追加
            'Custom_Hp_Page_ColumnIndex',
        );

        return in_array($class_name, $list);
    }

    public function hasDetailPageType($typeCode)
    {
        return in_array((int)$typeCode, $this->getHasDetailPageTypeList(), true);
    }

    /**
     * 複数まとめ表示する子を持つタイプを取得する
     * @return array
     */
    public function getHasMultiPageTypeList()
    {
        return array(
            self::TYPE_INFO_INDEX,
            self::TYPE_BLOG_INDEX,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_INDEX,
        );
    }

    public function hasMultiPageType($type)
    {
        return in_array((int)$type, $this->getHasMultiPageTypeList(), true);
    }

    public function getMultiPageTypeList()
    {
        return array(
            self::TYPE_INFO_DETAIL,
            self::TYPE_BLOG_DETAIL,
            //CMSテンプレートパターンの追加
            self::TYPE_COLUMN_DETAIL,
        );
    }

    public function isMultiPageType($type)
    {
        return in_array((int)$type, $this->getMultiPageTypeList(), true);
    }

    public function getDetailPageTypeList()
    {
        $types = array();
        foreach ($this->getHasDetailPageTypeList() as $type) {
            $types[] = $type + 1;
        }
        return $types;
    }

    public function isDetailPageType($type)
    {
        return in_array((int)$type, $this->getDetailPageTypeList(), true);
    }

    public function isEstateAliasType($type)
    {
        return (int)$type === self::TYPE_ESTATE_ALIAS;
    }

    public function notIsPageInfoDetail($page_type, $pageFlg)
    {
        return $page_type == self::TYPE_INFO_DETAIL && $pageFlg == 1;
    }


    /**
     * 1ページのみ作成可能なページタイプ
     *
     * @return array[CONST NAME] = id
     */
    private function getSinglePageList()
    {

        $pageTypes = $this->getEdiableTypeList();
        foreach ($pageTypes as $name => $const) {
            switch ($name) {
                case 'TYPE_SHOP_DETAIL':
                case 'TYPE_STAFF_DETAIL':
                case 'TYPE_STRUCTURE_DETAIL':
                case 'TYPE_BLOG_DETAIL':
                case 'TYPE_CUSTOMERVOICE_DETAIL':
                case 'TYPE_FREE':
                    //CMSテンプレートパターンの追加
                case 'TYPE_COLUMN_DETAIL':
                    unset($pageTypes[$name]);
                    break;
                default:
                    break;
            }
        }
        return $pageTypes;
    }

    /**
     * ページタイプのバリデーション
     * - 新規作成
     *
     * @param $type
     *
     * @return bool
     */
    public function isValidType($typeCode, $hpId)
    {

        // is creatable page
        if (!in_array($typeCode, $this->getCreatableTypeList())) {
            return false;
        }

        // exist row
        $select = $this->model->select();
        $select->where('hp_id', $hpId);
        $select->where('page_type_code', $typeCode);
        $row = $select->first();

        if (!$row) {
            return true;
        }

        // creatable multi page
        if (in_array($row->page_type_code, $this->getSinglePageList())) {
            return false;
        }

        return true;
    }

    /**
     * ページIDのバリデーション
     * - 編集
     *
     * @param $id
     *
     * @return bool
     */
    public function isValidId($pageId)
    {

        $row = $this->fetchRow('id=' . $pageId);

        // exist record
        if (!$row) {
            return false;
        }

        // editable page
        if (!in_array($row->page_type_code, $this->getEdiableTypeList())) {
            return false;
        }

        // new & creatabel multi page
        if ($row->new_flg && !in_array($row->page_type_code, $this->getEdiableTypeList())) {
        }

        return true;
    }

    /**
     * ページ一覧を取得
     *
     * @param $hpId
     *
     *
     */
    public function getPages($hpId)
    {

        $select = $this->model->select('id', 'title');
        $select->where('hp_id', $hpId);
        $select->orderBy('page_type_code');
        $select->orderBy('create_date', 'DESC');
        return $select->get();
    }

    /**
     * ページの更新
     *
     * @param array        $id
     * @param array|string $params
     */
    public function rewrite($id, $params)
    {

        $row = $this->fetchRow('id=' . $id);

        $row->title = $params['title'];
        $row->filename = $params['filename'];
        $row->description = $params['description'];
        $row->keywords = $this->getKeywordsCsv($params);
        $row->diff_flg = 1;
        $row->new_flg = 0;

        $row->save();
        return $row;
    }

    public function rewriteAfterPublish($id, $publicFlg, $newPath)
    {

        $row = $this->fetchRow('id=' . $id);

        $row->public_flg = $publicFlg;
        $row->republish_flg = true;
        $row->public_path = $newPath;
        if ($publicFlg) {
            $row->diff_flg = false;
        }
        $row->save();
        return $row;
    }

    /**
     * キーワードを配列→カンマ区切りに変換
     *
     * @param $param
     *
     * @return string
     */
    private function getKeywordsCsv($param)
    {

        $str = '';
        foreach ($param as $name => $val) {
            if (is_numeric(strpos($name, 'keyword', 0))) {
                $str .= $val . ',';
            }
        }
        return rtrim($str, ",");
    }

    /**
     * キーワードをカンマ区切り→配列に変換
     *
     * @param $param
     *
     * @return string
     */
    private function getKeywordsArray($str)
    {

        $res = array();
        $keywords = explode(',', $str);
        for ($i = 1; $i <= count($keywords); $i++) {
            $res['keyword' . $i] = $keywords[$i - 1];
        }
        return $res;
    }

    public function getTdk($id)
    {

        $row = $this->fetchRow('id=' . $id);

        if (!$row) {
            return array();
        }

        $res = $this->getKeywordsArray($row->keywords);
        $res['title'] = $row->title;
        $res['description'] = $row->description;
        $res['filename'] = $row->filename;
        return $res;
    }

    /**
     * 画像を使用しているページを取得する
     *
     * @param int $hpId
     * @param int $imageId
     */
    public function fetchAllByUsedImageId($hpId, $imageId)
    {
        $select = $this->model->select('hp_page.id', 'title');
        $select->join('hp_image_used', 'hp_page.id', '=', 'hp_image_used.hp_page_id');
        $select->where('hp_page.hp_id', $hpId);
        $select->where('hp_image_used.hp_image_id', $imageId);
        $select->where('hp_page.delete_flg', 0);
        $select->where('hp_image_used.delete_flg', 0);
        return $select->get();
    }

    /**
     * ファイル２を使用しているページを取得する
     *
     * @param int $hpId
     * @param int $file2Id
     */
    public function fetchAllByUsedFile2Id($hpId, $file2Id)
    {
        $select = $this->model->select('hp_page.id', 'title');
        $select->join('hp_file2_used', 'hp_page.id', '=', 'hp_file2_used.hp_page_id');
        $select->where('hp_page.hp_id', $hpId);
        $select->where('hp_file2_used.hp_file2_id', $file2Id);
        $select->where('hp_page.delete_flg', 0);
        $select->where('hp_file2_used.delete_flg', 0);
        return $select->get();
    }

    /**
     * view scriptのファイル名を取得
     *
     * @param $typeId
     *
     * @return string
     */
    public function getViewScriptName($typeId)
    {

        switch ($typeId) {
            case self::TYPE_TOP:
            case self::TYPE_INFO_DETAIL:
            case self::TYPE_COMPANY:
            case self::TYPE_HISTORY:
            case self::TYPE_GREETING:
            case self::TYPE_SHOP_DETAIL:
            case self::TYPE_STAFF_DETAIL:
            case self::TYPE_RECRUIT:
            case self::TYPE_STRUCTURE_DETAIL:
            case self::TYPE_BLOG_DETAIL:
            case self::TYPE_PRIVACYPOLICY:
            case self::TYPE_SITEPOLICY:
            case self::TYPE_OWNER:
            case self::TYPE_CORPORATION:
            case self::TYPE_TENANT:
            case self::TYPE_BROKER:
            case self::TYPE_PROPRIETARY:
            case self::TYPE_CITY:
            case self::TYPE_CUSTOMERVOICE_DETAIL:
            case self::TYPE_QA:
            case self::TYPE_SCHOOL:
            case self::TYPE_PREVIEW:
            case self::TYPE_MOVING:
            case self::TYPE_TERMINOLOGY:
            case self::TYPE_RENT:
            case self::TYPE_LEND:
            case self::TYPE_BUY:
            case self::TYPE_SELL:
            case self::TYPE_SELLINGCASE_DETAIL:
            case self::TYPE_EVENT_DETAIL:
            case self::TYPE_LINKS:
            case self::TYPE_FREE:
            case self::TYPE_MEMBERONLY:
                // return 'edit';
                // break;
            case self::TYPE_FORM_CONTACT:
                //#4274 Change spec form FDP contact
                //case self::TYPE_FORM_FDP_CONTACT:
            case self::TYPE_FORM_DOCUMENT:
            case self::TYPE_FORM_ASSESSMENT:
            case self::TYPE_FORM_LIVINGLEASE:
            case self::TYPE_FORM_OFFICELEASE:
            case self::TYPE_FORM_LIVINGBUY:
            case self::TYPE_FORM_OFFICEBUY:
                // 物件リクエスト
            case self::TYPE_FORM_REQUEST_LIVINGLEASE:
            case self::TYPE_FORM_REQUEST_OFFICELEASE:
            case self::TYPE_FORM_REQUEST_LIVINGBUY:
            case self::TYPE_FORM_REQUEST_OFFICEBUY:
                //CMSテンプレートパターンの追加
            case self::TYPE_BUSINESS_CONTENT:
            case self::TYPE_COLUMN_DETAIL:
            case self::TYPE_COMPANY_STRENGTH:
            case self::TYPE_PURCHASING_REAL_ESTATE:
            case self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN:
            case self::TYPE_REPLACEMENT_AHEAD_SALE:
            case self::TYPE_BUILDING_EVALUATION:
            case self::TYPE_BUYER_VISITS_DETACHEDHOUSE:
            case self::TYPE_POINTS_SALE_OF_CONDOMINIUM:
            case self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE:
            case self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND:
            case self::TYPE_ERECTIONHOUSING_ORDERHOUSE:
            case self::TYPE_PURCHASE_BEST_TIMING:
            case self::TYPE_LIFE_PLAN:
            case self::TYPE_TYPES_MORTGAGE_LOANS:
            case self::TYPE_FUNDING_PLAN:
            case self::TYPE_TROUBLED_LEASING_MANAGEMENT:
            case self::TYPE_LEASING_MANAGEMENT_MENU:
            case self::TYPE_MEASURES_AGAINST_VACANCIES:
            case self::TYPE_HOUSE_REMODELING:
            case self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER:
            case self::TYPE_UTILIZING_LAND:
            case self::TYPE_PURCHASE_INHERITANCE_TAX:
            case self::TYPE_UPPER_LIMIT:
            case self::TYPE_RENTAL_INITIAL_COST:
            case self::TYPE_SQUEEZE_CANDIDATE:
            case self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE:
            case self::TYPE_COMFORTABLELIVING_RESIDENT_RULES:
            case self::TYPE_STORE_SEARCH:
            case self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN:
                // return 'form';
                // break;
                return 'edit';
                break;
            case self::TYPE_INFO_INDEX:
            case self::TYPE_SHOP_INDEX:
            case self::TYPE_STAFF_INDEX:
            case self::TYPE_STRUCTURE_INDEX:
            case self::TYPE_BLOG_INDEX:
            case self::TYPE_CUSTOMERVOICE_INDEX:
            case self::TYPE_SELLINGCASE_INDEX:
            case self::TYPE_EVENT_INDEX:
                //CMSテンプレートパターンの追加
            case self::TYPE_COLUMN_INDEX:
                return 'list';
                break;
            default:
                return 'edit';
                break;
        }
    }

    /**
     * ページ名が使用済みかチェック
     *
     * @param $fileName
     * @param $hpId
     *
     * @return bool
     */
    public function inUseFileName($fileName, $hpId, $id = null)
    {

        $select = $this->model->select();
        $select->where('filename', $fileName);
        $select->where('hp_id', $hpId);
        if ($id) {
            $select->where('id', '!=', $id);
        }
        $rows = $select->get();
        return ($rows->count() > 0) ? true : false;
    }

    /**
     * ページ名が使用済みかチェック（未作成のページ名を除く）
     *
     * @param $fileName
     * @param $hpId
     *
     * @return bool
     */
    public function inUseFileNameWithoutNew($fileName, $hpId, $id = null, $pageCategoryCode = null)
    {
        $select = $this->model->where('filename', $fileName);
        $select->where('hp_id', $hpId);
        $select->where('new_flg', 0);
        if ($id) {
            $select->where('id', '!=', $id);
            if ($pageCategoryCode) {
                $articleCategories = $this->getCategoryCodeArticle();
                if (in_array($pageCategoryCode, $articleCategories)) {
                    $select->whereIn('page_category_code', $articleCategories);
                } else {
                    $select->whereNotIn('page_category_code', $articleCategories);
                }
            }
        }
        $rows = $select->get();
        return ($rows->count() > 0) ? true : false;
    }

    public function inUseFileNameArticle($fileName, $pageTypeCode)
    {
        $allPageArticle = $this->getAllPagesUsefulEstate();
        foreach ($this->getPageNameListJp() as $type => $name) {
            if ((!in_array($type, $allPageArticle) || $pageTypeCode == $type)) {
                continue;
            }
            if ($fileName == $name) {
                return true;
            }
        }
        return false;
    }

    public function getRequiredPageList()
    {

        return $this->_requiredPages;
    }

    public function getHasPaginationList()
    {

        return $this->_hasPagination;
    }

    public function hasPagination($type)
    {

        return in_array($type, $this->getHasPaginationList());
    }

    public function isDisplayInSitemap($page)
    {

        if (in_array($page['page_type_code'], $this->_notDisplayInSitemap)) {
            return false;
        }

        // 固定メニューは表示
        if ($this->isFixedMenuType($page['page_type_code'])) {
            return true;
        };

        // 階層外は非表示
        if (is_null($page['parent_page_id']) && $page['level'] == 1) {
            return false;
        }

        // not display article page in sitemap
        if ($page['page_category_code'] == HpPageRepository::CATEGORY_ARTICLE) {
            return false;
        }

        return true;
    }

    public function isRequiredType($type)
    {
        return in_array((int)$type, $this->_requiredPages, true);
    }

    public function fetchRowById($id)
    {
        return $this->find($id);
    }

    public function fetchRowByLinkId($id, $hp_id)
    {

        $select = $this->model->where('link_id', $id);
        $select->where('hp_id', $hp_id);
        return $select->first();
    }

    public function fetchRowByLinkEstatePageId($id, $hpId)
    {
        $select = $this->model->select();
        $select->where('link_estate_page_id', $id);
        $select->where('hp_id', $hpId);
        return $select->first();
    }

    public function hasEntity($pageTypeCode)
    {

        if ($pageTypeCode == self::TYPE_ALIAS || $pageTypeCode == self::TYPE_LINK || $pageTypeCode == self::TYPE_ESTATE_ALIAS || $pageTypeCode == self::TYPE_LINK_HOUSE) {
            return false;
        }
        return true;
    }

    public function filterPageIdsByDate($hp_id, array $id_list, $year, $month = '', $day = '')
    {
        $s = $this->model->select();
        $s->where('hp_id', $hp_id);
        $s->whereIn('id', $id_list);

        $date_string = $year;
        if ($month) {
            $date_string .= '-' . $month;
        }
        if ($day) {
            $date_string .= '-' . $day;
        }

        $s->where('date', 'like', "{$date_string}%");
        $s->orderBy('date', 'DESC');
        $s->orderBy('id');

        $rowset = $s->get();
        if (count($rowset) == 0) {
            return array();
        }

        $ret = array();
        foreach ($rowset as $row) {
            $ret[] = $row->id;
        }

        return $ret;
    }

    /**
     * ページの状態(新規/下書き/公開)をpage_type_code毎に集計する
     *
     * @param int $hp_id
     * @return array
     */
    public function countPageStates($hp_id, $isArticle = false)
    {
        $s = $this->model->select('page_type_code', 'public_flg', 'new_flg');
        $s->selectRaw('count(*) as `count`');
        $s->where('hp_id', $hp_id);
        $s->where(function($query) {
            $query->whereNull('page_flg')->orWhere('page_flg', 0);
        });
        if ($isArticle) {
            $s->whereIn('page_category_code', $this->getCategoryCodeArticle());
        } else {
            $s->where(function($query) {
                $query->whereNotIn('page_category_code', $this->getCategoryCodeArticle())
                ->orWhereNull('page_category_code');
            });
        }
        $s->groupBy('page_type_code', 'public_flg', 'new_flg'); 
        $counts = $s->get();

        $result = [];
        foreach ($counts as $row) {
            $page_type = $row['page_type_code'];

            if (!isset($result[$page_type])) {
                $result[$page_type] = ['public' => 0, 'draft' => 0, 'new' => 0];
            }

            if ($row['public_flg'] === 1) {
                $result[$page_type]['public'] = $row['count'];
            } else if ($row['new_flg'] === 1) {
                $result[$page_type]['new'] = $row['count'];
            } else {
                $result[$page_type]['draft'] = $row['count'];
            }
        }
        if ($isArticle) {
            $typeList = $this->getAllPagesUsefulEstate();
        } else {
            $typeList = $this->getTypeList();
        }

        foreach ($typeList as $page_type_code) {
            if (!isset($result[$page_type_code])) {
                $result[$page_type_code] = ['public' => 0, 'draft' => 0, 'new' => 0];
            }
        }

        return $result;
    }

    public function countChildPageStates($hp_id)
    {
        $s = $this->model->select(
            'id',
            'parent_page_id',
            'page_type_code',
            'page_category_code',
            'public_flg',
            'new_flg'
        );
        $s->where('hp_id', $hp_id);
        $s->whereIn('page_category_code',$this->getCategoryCodeArticle());
        if ($this->_auto_logical_delete) {
            $s->where($this->_deleted_col, '0');
        }
        $s->orderBy('page_category_code', 'ASC');
        $counts = $s->get();
        return $counts;
    }

    public function fetchPublishedDate($hp_id)
    {

        $s = $this->model->select('page_type_code');
        $s->selectRaw('MAX(published_at) as published_at');
        $s->where('hp_id', $hp_id);
        $s->groupBy('page_type_code');
        $dates = $s->get();

        $result = [];
        foreach ($dates as $row) {
            if ($row['published_at'])
                $result[$row['page_type_code']] = $row['published_at'];
        }

        return $result;
    }

    public function fetchPublishedDatePage($hp_id, $page_type_code)
    {
        $s = $this->model->select('page_type_code');
        $s->selectRaw('MAX(published_at) as published_at');
        $s->where('hp_id', $hp_id);
        $s->where('page_type_code', $page_type_code);
        $s->groupBy('page_type_code');
        $dates = $s->first();

        return $dates;
    }

    /**
     * 指定のhp_page.idのリストを適切な順序にソートする
     *
     * @param int $index_page_type_code
     * @param array $page_id_list
     * @return array
     */
    public function sortChildPageId($index_page_type_code, array $page_id_list)
    {
        if ($this->hasMultiPageType($index_page_type_code)) {
            $sort_order = array('desc' => 'date', 'DESC' => 'id');
        } else {
            $sort_order = array('sort', 'id');
        }
        $sorted_list = [];
        foreach ($this->fetchAll(['whereIn' => ['id', $page_id_list]], $sort_order) as $row) {
            $sorted_list[] = $row->id;
        }

        return $sorted_list;
    }

    /**
     * 自身のTOPページを取得する
     *
     * @param int $hp_id
     * @return HpPageRepository_Row
     */
    public function getTopPageData($hp_id)
    {
        $select = $this->model->select();
        $select->where("hp_id", $hp_id);
        $select->where("page_type_code", self::TYPE_TOP);
        return $select->first();
    }

    public function isPublicToppage($hp_id)
    {
        $row = $this->getTopPageData($hp_id);
        return $row && $row->public_flg;
    }

    /**
     * 物件種別IDを指定して対応する問い合わせフォームページコードを取得する
     * @param int $estateClass
     */
    public function getEstateFormPageCodeByEstateClass($estateClass)
    {
        return isset($this->_estateFormMap[$estateClass]) ? $this->_estateFormMap[$estateClass] : null;
    }


    /**
     * 物件種別IDを指定して対応するリクエストフォームページコードを取得する
     * @param int $estateClass
     */
    public function getEstateRequestPageCodeByEstateClass($estateClass)
    {
        return isset($this->_estateFormRequestMap[$estateClass]) ? $this->_estateFormRequestMap[$estateClass] : null;
    }

    /**
     * 物件種別IDを指定して対応する問い合わせフォームを取得する
     * @param int $hpId
     * @param int $estateClass
     * @param boolean $ifNotExistsThenCreate
     * @return HpPageRepository_Row
     */
    public function fetchEstateFormByEstateClass($hpId, $estateClass)
    {
        $pageTypeCode = $this->getEstateFormPageCodeByEstateClass($estateClass);
        if (!$pageTypeCode) {
            return null;
        }
        return $this->fetchRow([['hp_id', $hpId], ['page_type_code', $pageTypeCode]]);
    }

    /**
     * 物件種別IDを指定して対応する問い合わせフォームを作成する
     */
    public function createEstateFormByEstateClass($hpId, $estateClass)
    {
        $data = [
            'page_type_code'    => $this->getEstateFormPageCodeByEstateClass($estateClass),
            'parent_page_id'    => null,
            'sort'                => 0,
            'level'                => 1,
        ];
        $data['title'] = $this->getTypeNameJp($data['page_type_code']);
        // 新規フラグを設定
        $data['new_flg'] = 1;
        // カテゴリを設定
        $data['page_category_code'] = $this->getCategoryByType($data['page_type_code']);
        // HPIDを設定
        $data['hp_id'] = $hpId;

        $row = $this->create($data);
        $row->save();

        // リンクIDを設定
        $row->link_id = $row->id;
        $row->save();
        return $row;
    }

    /**
     * ファイル添付が可能なページIDを返す
     */
    public function getAddedFilePages()
    {
        return $this->_files_added_pages;
    }


    /**
     * 物件お問い合わせページのページタイプコードか否かを返す
     * - 通常のお問い合わせページは含まない
     *
     * @return boolean
     */
    public function isEstateContactPageType($page_type_code)
    {
        return (in_array($page_type_code, $this->estateContactPageTypeCodeList()));
    }

    /**
     * 物件リクエストページのページタイプコードか否かを返す
     * - 通常のお問い合わせページは含まない
     * - 物件お問い合わせページは含まない
     *
     * @return boolean
     */
    public function isEstateRequestPageType($page_type_code)
    {
        return (in_array($page_type_code, $this->estateRequestPageTypeCodeList()));
    }

    /**
     * 物件お問い合わせページのページタイプコードを返す
     * - 通常のお問い合わせページは含まない
     * - 物件リクエストページは含まない
     *
     * @return array
     */
    public function estateContactPageTypeCodeList()
    {

        return array_filter($this->getCategoryMap()[self::CATEGORY_FORM], function ($code) {
            switch ($code) {
                case self::TYPE_FORM_CONTACT:
                    //#4274 Change spec form FDP contact
                    //case self::TYPE_FORM_FDP_CONTACT:
                case self::TYPE_FORM_DOCUMENT:
                case self::TYPE_FORM_ASSESSMENT:
                    //物件リクエスト
                case self::TYPE_FORM_REQUEST_LIVINGLEASE:
                case self::TYPE_FORM_REQUEST_OFFICELEASE:
                case self::TYPE_FORM_REQUEST_LIVINGBUY:
                case self::TYPE_FORM_REQUEST_OFFICEBUY:
                    return false;
                default:
                    return true;
            }
        });
    }

    /**
     * 物件リクエストページのページタイプコードを返す
     * - 通常のお問い合わせページは含まない
     * - 物件お問い合わせページは含まない
     *
     * @return array
     */
    public function estateRequestPageTypeCodeList()
    {
        return array_filter($this->getCategoryMap()[self::CATEGORY_FORM], function ($code) {
            switch ($code) {
                case self::TYPE_FORM_CONTACT:
                    //#4274 Change spec form FDP contact
                    //case self::TYPE_FORM_FDP_CONTACT:
                case self::TYPE_FORM_DOCUMENT:
                case self::TYPE_FORM_ASSESSMENT:
                case self::TYPE_FORM_LIVINGLEASE:
                case self::TYPE_FORM_OFFICELEASE:
                case self::TYPE_FORM_LIVINGBUY:
                case self::TYPE_FORM_OFFICEBUY:
                    return false;
                default:
                    return true;
            }
        });
    }

    /**
     * 物件お問い合わせページ取得
     *
     * @param      $hpId
     * @param null $new_flg
     * @param null $diff_flg
     * @return App\Collections\CustomCollection
     */
    public function fetchEstateContactPageAll($hpId, $new_flg = null, $diff_flg = null)
    {

        $pageTypeCodes    = $this->estateContactPageTypeCodeList();
        if (count($pageTypeCodes) == 0) {    // 対象が無いので有りえない値を指定
            $pageTypeCodes    = array(-1);
        }
        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['page_type_code', $pageTypeCodes],
        ];

        if ($new_flg !== null) {
            $where[] = ['new_flg', (int)$new_flg === 1 ? 1 : 0];
        }

        if ($diff_flg !== null) {
            $where[] = ['diff_flg', (int)$diff_flg === 1 ? 1 : 0];
        }

        return $this->fetchAll($where);
    }

    /**
     * 物件リクエストページ取得
     *
     * @param      $hpId
     * @param null $new_flg
     * @param null $diff_flg
     * @return App\Collections\CustomCollection
     */
    public function fetchEstateRequestPageAll($hpId, $new_flg = null, $diff_flg = null)
    {

        $pageTypeCodes    = $this->estateRequestPageTypeCodeList();
        if (count($pageTypeCodes) == 0) {    // 対象が無いので有りえない値を指定
            $pageTypeCodes    = array(-1);
        }
        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['page_type_code', $pageTypeCodes]
        ];

        if ($new_flg !== null) {
            $where[] = ['new_flg', (int)$new_flg === 1 ? 1 : 0];
        }

        if ($diff_flg !== null) {
            $where[] = ['diff_flg', (int)$diff_flg === 1 ? 1 : 0];
        }

        return $this->fetchAll($where);
    }


    public function isContactForSearch($page_type_code)
    {

        return in_array((int)$page_type_code, $this->_contactPageForSearch);
    }

    public function cannotEstateRequest($hp, $page_type_code)
    {
        if ($page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE) {
            $class = 1;
        } elseif ($page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE) {
            $class = 2;
        } elseif ($page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY) {
            $class = 3;
        } elseif ($page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY) {
            $class = 4;
        } else {
            return false;
        }
        $settingCms = (new Simple($hp))->settingCms;
        $hpPage = App::make(HpPageRepositoryInterface::class);
        if ($settingCms) {
            $classSearch = App::make(EstateClassSearchRepositoryInterface::class);
            $classSearchRow = $classSearch->getSetting($hp->id, $settingCms->id, $class);
            if ($classSearchRow && $classSearchRow->estate_request_flg == 1) {
                // $select = $hpPage->select();
                $where = array(['hp_id', $hp->id]);
                switch ($class) {
                        // 物件リクエスト 居住用賃貸物件フォーム
                    case 1:
                        $where[] = ['page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE];
                        break;
                        // 物件リクエスト 事務所用賃貸物件フォーム
                    case 2:
                        $where[] = ['page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE];
                        break;
                        // 物件リクエスト 居住用売買物件フォーム
                    case 3:
                        $where[] = ['page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY];
                        // 物件リクエスト 事務所用売買物件フォーム
                    case 4:
                        $where[] = ['page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY];
                        break;
                }
                $hpPageRow = $hpPage->fetchRow($where);
                if ($hpPageRow) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 物件検索 お問い合わせを公開中にする
     *
     * @param $hpId
     */
    public function updateStatuseEstateContactPageAll($hpId)
    {

        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['page_type_code', $this->estateContactPageTypeCodeList()],
        ];

        $rows = $this->fetchAll($where);

        if (count($rows) < 1) {
            return;
        }

        foreach ($rows as $row) {
            $row->public_path   = '';
            $row->public_flg    = 1;
            $row->diff_flg      = 0;
            $row->republish_flg = 1;
            $row->published_at  = date('Y-m-d H:i:s');
            $row->public_title  = '';

            $row->save();
        }

    }

    //特集のリンク名を保存
    public function saveLinkName($specialSettingObject, $special, $hpId)
    {
        if (empty($special->title)) {
            return;
        }
        $link_estate_page_id = 'estate_special_' . $special->origin_id;
        $row = $this->fetchRowByLinkEstatePageId($link_estate_page_id, $hpId);
        //1174修正後の初回公開時用
        if ($special->title == $specialSettingObject->title && $row && $row->title == null && $row->public_flg == 1) {
            $row->setTitle($specialSettingObject->title);
        }
        if ($special->title == $specialSettingObject->title) {
            return;
        }
        if (!$row) {
            return;
        }

        //特集公開前かつリンク設定済みで特集のタイトルを再修正した場合も実行されるが問題ない
        $row->setTitle($specialSettingObject->title);
    }

    /**
     * 物件リクエスト情報の新規作成
     * @param hpId int
     */
    public function checkEstateRequest($hpId)
    {
        $pageTypeCodes    = $this->estateRequestPageTypeCodeList();
        if (count($pageTypeCodes) == 0) {    // 対象が無いので何もしない
            return;
        }
        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['page_type_code', $pageTypeCodes],
        ];
        $rows = $this->fetchAll($where);
        if (count($rows) > 0) return;

        foreach ($this->_estateFormRequestMap as $key => $value) {
            $data = [
                'page_type_code'    => $value,
                'parent_page_id'    => null,
                'sort'              => 0,
                'level'             => 1,
                // 'filename'          => HpPageRepository::slave()->getPageNameJp($value),
            ];
            $data['title'] = $this->getTypeNameJp($data['page_type_code']);
            // 新規フラグを設定
            $data['new_flg'] = 1;
            // カテゴリを設定
            $data['page_category_code'] = $this->getCategoryByType($data['page_type_code']);
            // HPIDを設定
            $data['hp_id'] = $hpId;
            $row = $this->create($data);
            $row->save();

            // リンクIDを設定
            $row->link_id = $row->id;
            $row->save();
        }
    }

    public function getRequestPageRow($hpId, $class)
    {

        $select = $this->model->select();
        $select->where('hp_id', $hpId);
        switch ($class) {
                // 物件リクエスト 居住用賃貸物件フォーム
            case 1:
                $select->where('page_type_code', self::TYPE_FORM_REQUEST_LIVINGLEASE);
                break;
                // 物件リクエスト 事務所用賃貸物件フォーム
            case 2:
                $select->where('page_type_code', self::TYPE_FORM_REQUEST_OFFICELEASE);
                break;
                // 物件リクエスト 居住用売買物件フォーム
            case 3:
                $select->where('page_type_code', self::TYPE_FORM_REQUEST_LIVINGBUY);
                break;
                // 物件リクエスト 事務所用売買物件フォーム
            case 4:
                $select->where('page_type_code', self::TYPE_FORM_REQUEST_OFFICEBUY);
                break;
        }
        $select->where('public_flg', 1);
        return $select->first();
    }

    /**
     * 新規に追加になったページタイプコードを返却する
     *
     * @return array
     */
    public function newPageTypeCodeList()
    {
        return array(
            self::TYPE_BUSINESS_CONTENT,
            self::TYPE_COLUMN_INDEX,
            self::TYPE_COLUMN_DETAIL,
            self::TYPE_COMPANY_STRENGTH,
            self::TYPE_PURCHASING_REAL_ESTATE,
            self::TYPE_REPLACEMENTLOAN_MORTGAGELOAN,
            self::TYPE_REPLACEMENT_AHEAD_SALE,
            self::TYPE_BUILDING_EVALUATION,
            self::TYPE_BUYER_VISITS_DETACHEDHOUSE,
            self::TYPE_POINTS_SALE_OF_CONDOMINIUM,
            self::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
            self::TYPE_NEWCONSTRUCTION_OR_SECONDHAND,
            self::TYPE_ERECTIONHOUSING_ORDERHOUSE,
            self::TYPE_PURCHASE_BEST_TIMING,
            self::TYPE_LIFE_PLAN,
            self::TYPE_TYPES_MORTGAGE_LOANS,
            self::TYPE_FUNDING_PLAN,
            self::TYPE_TROUBLED_LEASING_MANAGEMENT,
            self::TYPE_LEASING_MANAGEMENT_MENU,
            self::TYPE_MEASURES_AGAINST_VACANCIES,
            self::TYPE_HOUSE_REMODELING,
            self::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER,
            self::TYPE_UTILIZING_LAND,
            self::TYPE_PURCHASE_INHERITANCE_TAX,
            self::TYPE_UPPER_LIMIT,
            self::TYPE_RENTAL_INITIAL_COST,
            self::TYPE_SQUEEZE_CANDIDATE,
            self::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE,
            self::TYPE_COMFORTABLELIVING_RESIDENT_RULES,
            self::TYPE_STORE_SEARCH,
            self::TYPE_SHOP_SUCCESS_BUSINESS_PLAN,
        );
    }

    /**
     * 新規ページの作成
     * @param hpId int
     */
    public function checkNewTemplateData($hpId)
    {
        $select = $this->model->select();
        $select->where('hp_id', $hpId);
        $select->whereIn('page_type_code', $this->newPageTypeCodeList());
        $rows = $select->withoutGlobalScopes()->get();
        if ($rows->count() > 0) return;

        foreach ($this->newPageTypeCodeList() as $key => $value) {
            $data = [
                'page_type_code'    => $value,
                'parent_page_id'    => null,
                'sort'              => 0,
                'level'             => 1,
                // 'filename'          => HpPageRepository::slave()->getPageNameJp($value),
            ];
            $data['title'] = $this->getTypeNameJp($data['page_type_code']);
            // 新規フラグを設定
            $data['new_flg'] = 1;
            // カテゴリを設定
            $data['page_category_code'] = $this->getCategoryByType($data['page_type_code']);
            // HPIDを設定
            $data['hp_id'] = $hpId;
            $row = $this->create($data);
            $row->save();

            // // リンクIDを設定
            $row->link_id = $row->id;
            $row->save();
        }
    }


    /**
     * Remove unique Notification Page if Top Original
     * @param App\Models\Hp $hp
     * @param boolean $isTopOriginal
     */
    public function setPageTypeInfoUnique($hp, $isTopOriginal = false)
    {

        if (!$isTopOriginal) {
            return;
        }

        $pageNumber = $hp->findPagesByType(HpPageRepository::TYPE_INFO_INDEX, false)->count();

        if ($pageNumber >= config('constants.original.MAX_TYPE_INFO_INDEX')) {
            return;
        }

        if (($key = array_search(self::TYPE_INFO_INDEX, $this->_uniquePages)) !== false) {
            unset($this->_uniquePages[$key]);
        }
    }


    protected function _createNotification($number, $hp, $topPage, $key = 1)
    {
        $maxNotification = config('constants.original.MAX_TYPE_INFO_INDEX');
        if ($key > $maxNotification) {
            throw new \Exception('number must smaller or equal than ' . $maxNotification);
        }
        $type = self::TYPE_INFO_INDEX;
        $areaObject = App::make(HpAreaRepositoryInterface::class);

        for ($i = 1; $i <= $number; $i++) {
            $titles = Original::getInfoPageName($key);
            $title = $titles[$type];
            $idPage = \App\Models\HpPage::create(array(
                'new_flg' => 1,
                'page_type_code' => $type,
                'page_category_code' => $this->getCategoryByType($type),
                'title' => $title,
                'description' => $title,
                'keywords' => $title,
                'filename' => $this->getPageNameJp($type) . '-' . $key,
                'parent_page_id' => null,
                'level' => 1,
                'sort' => 0,
                'hp_id' => $hp->id,
                'new_mark' => config('constants.new_mark.COMMON')
            ));
            \App\Models\HpPage::where('id', $idPage->id)->update(array('link_id' => $idPage->id));
            // App::make(HpPageRepositoryInterface::class)->update($idPage, array('link_id' => $idPage));

            $area = $areaObject->save($topPage, 0, 1, null);
            /** @var Library\Custom\Hp\Page\Parts\InfoList $part */
            $part = new InfoList(array('hp' => $hp, 'page' => $topPage, 'isTopOriginal' => true));
            $part->getElement('page_id')->setValue($idPage->id);
            $part->getElement('notification_type')->setValue($key);
            $part->save($hp, $topPage, $area->id);
            $key++;
        }
    }

    /**
     * @author LAM
     * should call by MASTER
     * Execute when top original on/off
     * @param App\Models\Hp $hp
     * @param bool $topTo
     * @param bool $topBefore
     * @throws Exception
     */
    public function generateTopOriginalData($hp, $topTo = false, $topBefore = false)
    {
        $isTopOriginal = $topTo;

        $type = self::TYPE_INFO_INDEX;
        //get define
        $maxNotification = config('constants.original.MAX_TYPE_INFO_INDEX');
        $settingPageKey = Original::$EXTEND_INFO_LIST['page_id'];
        $extendInfoList = Original::$EXTEND_INFO_LIST;

        // master tables
        $mainPartMaster = App::make(HpMainPartsRepositoryInterface::class);
        $areaMaster = App::make(HpAreaRepositoryInterface::class);
        $pageMaster = App::make(HpPageRepositoryInterface::class);
        $attrMaster = new AssociatedHpPageAttribute;

        // hp page
        $topPage = App::make(HpPageRepositoryInterface::class)->getTopPageData($hp->id);

        $infoListForm = new InfoList(array('hp' => $hp, 'page' => $topPage, 'isTopOriginal' => $isTopOriginal));

        DB::beginTransaction();

        // get all notifications - master
        $pages = $hp->findPagesByType(HpPageRepository::TYPE_INFO_INDEX, false, null, true);
        $pageCount = $pages->count();

        $ids = array_map(function ($item) {
            return $item['link_id'];
        }, $pages->toArray());

        switch ($isTopOriginal) {
            case true:

                // case if they created some news setting, remove it all
                $query = $mainPartMaster->model->where('hp_id', $hp->id)
                    ->where('parts_type_code', HpMainPartsRepository::PARTS_INFO_LIST);
                if ($ids && !empty($ids)) {
                    $query->where(function ($query) use ($ids, $settingPageKey) {
                        $query->whereNull($settingPageKey)
                            ->orWhere(function ($query) use ($ids, $settingPageKey) {
                                $query->whereNotIn($settingPageKey, $ids);
                            });
                    });
                } else {
                    $query->whereNull($settingPageKey);
                }
                // $mainPartMaster->delete($where);
                $query->update(['delete_flg' => 1]);
                // done remove


                if ($pageCount > 0) {
                    foreach ($pages as $k => $page) {
                        $setting = $mainPartMaster->getSettingForNotification(
                            $page->link_id,
                            $hp->id
                        );
                        if (!$setting) {
                            $key = $k + 1;
                            $titles = Original::getInfoPageName($key);
                            $title = $titles[$type];
                            $page->title = $page->description = $page->keywords = $title;
                            $page->filename = $this->getPageNameJp($type) . '-' . $key;
                            $page->save();

                            $area = $areaMaster->save($topPage, 0, 1, null);
                            /** @var Library\Custom\Hp\Page\Parts\InfoList $part */
                            $part = clone $infoListForm;
                            $part->getElement('page_id')->setValue($page->link_id);
                            $part->getElement('notification_type')->setValue($key);
                            $part->save($hp, $topPage, $area->id);
                        }
                    }
                }

                if ($pageCount < $maxNotification) {
                    $this->_createNotification($maxNotification - $pageCount, $hp, $topPage, $pageCount + 1);
                }

                Original::reSortAreaOriginal($topPage, $hp);

                break;
            default:

                if ($ids && !empty($ids)) {
                    $query = $mainPartMaster->model->where('hp_id', $hp->id)
                        ->where('parts_type_code', HpMainPartsRepository::PARTS_INFO_LIST);
                    $query->whereNotIn($settingPageKey, $ids);
                    // $mainPartMaster->delete([
                    //     'hp_id' => $hp->id,
                    //     'parts_type_code' => HpMainPartsRepository::PARTS_INFO_LIST,
                    //     $settingPageKey . ' NOT IN (?)' => $ids
                    // ]);
                    $query->update(['delete_flg' => 1]);
                }

                // delete category
                // $mainPartMaster->delete([
                //     'hp_id' => $hp->id,
                //     'parts_type_code' => $mainPartMaster::NEWS_CATEGORY
                // ]);
                $query = $mainPartMaster->model->where('hp_id', $hp->id)
                    ->where('parts_type_code', HpMainPartsRepository::NEWS_CATEGORY);
                $query->update(['delete_flg' => 1]);

                // delete attributes
                $attrMaster::where('hp_id', $hp->id)->update(['delete_flg' => 1]);

                //delete 2nd setting + page + page detail
                $secondSetting = $mainPartMaster->getSingleSettingForNotification($hp->id, 2);
                if ($secondSetting) {
                    $pageNeedDelete = $pageMaster->fetchRowByLinkId($secondSetting->$settingPageKey, $hp->id);
                    // delete notification + notification details
                    if ($pageNeedDelete) {
                        $id = $pageNeedDelete->id;
                        $pageNeedDelete->delete_flg = 1;
                        $pageNeedDelete->save();
                        $pageMaster->model->where([
                            ['hp_id', $hp->id],
                            ['page_type_code', HpPageRepository::TYPE_INFO_DETAIL],
                            ['parent_page_id', $id]
                        ])->update(['delete_flg' => 1]);
                    }
                    $secondSetting->delete_flg = 1;
                    $secondSetting->save();
                }

                $firstSetting =  $mainPartMaster->getSingleSettingForNotification($hp->id);
                if ($firstSetting) {
                    $firstPage = $pageMaster->fetchRowByLinkId($firstSetting->$settingPageKey, $hp->id);
                    if ($firstPage) {
                        $firstPage->title = $this->getTypeNameJp($type);
                        $firstPage->description = $this->getDescriptionNameJp($type);
                        $firstPage->keywords = $this->getKeywordNameJp($type);
                        $firstPage->filename = $this->getPageNameJp($type);
                        $firstPage->save();
                    }
                    foreach ($extendInfoList as $k => $v) {
                        $firstSetting->$v = null;
                    }
                    $heading = $infoListForm->getField('heading');
                    $page_size = $infoListForm->getField('page_size');
                    $firstSetting->$heading = 'NEWS';
                    $firstSetting->$page_size = 1;
                    $firstSetting->save();
                }

                //check if there is no info list, create one
                $infoLists = $mainPartMaster->model->where([
                    ['hp_id', $hp->id],
                    ['parts_type_code', HpMainPartsRepository::PARTS_INFO_LIST]
                ])->get();
                // always keep 1 info list as default
                if (!$infoLists) {
                    $area = $areaMaster->save($topPage, 0, 1, null);
                    $defaultInfoList = clone $infoListForm;
                    $defaultInfoList->getElement('heading')->setValue('NEWS');
                    $defaultInfoList->getElement('page_size')->setValue(1);
                    $defaultInfoList->save($hp, $topPage, $area->id);
                }

                // remove #Backlog 4285
                // #Backlog 3496 remove all parts top page
                // Original::getInstance()->totalRemoveTopOriginal($topPage, $hp);
        }

        // define that top page has changed
        $topPage->diff_flg = 1;
        $topPage->save();

        DB::commit();
    }

    /**
     * @param array $categories
     * @param array $newCategory
     * @param int $type
     */
    public function addNewCategory($categories, $newCategory, $type)
    {
        $categories[$type] = array_merge($categories[$type], $newCategory);
        return $categories;
    }

    /**
     * @param array $page
     * @param date $date
     */
    public function checkNewMark($newMark, $date)
    {
        if ($newMark == 0 || $newMark == null || strtotime($date) > time())
            return false;
        return time() <= strtotime("+" . $newMark . " day", strtotime($date));
    }

    /**
     * 使用されている物件検索設定のclassを取得する
     * @param object $setting
     * @return array $settingEstateClassList
     */
    public function getSettingEstateClassList($setting)
    {
        $settingEstateClassList = array();
        if ($setting) {
            $settingList = $setting->getSearchSettingAll();
            foreach ($settingList as $estateClassSearchRow) {
                $settingEstateClassList[] = strval($estateClassSearchRow['estate_class']);
            }
        }
        return $settingEstateClassList;
    }

    /**
     * 物件検索系の内部リンクのID一覧を取得する
     * @param object $hp
     * @param array $linkIds
     * @param array $settingEstateClassList
     */
    public function getInnerLinkPages($hp, $linkIds, $settingEstateClassList)
    {
        $pageIds = [];
        // 物件検索トップ
        if (count($settingEstateClassList) == 0) {
            $estateTops = $this->fetchAll(array(['hp_id', $hp->id], ['link_estate_page_id', 'estate_top']));
            foreach ($estateTops as $estateTop) {
                $pageIds[] = $estateTop->id;
            }
        }
        // 賃貸物件検索トップ
        if (!in_array('1', $settingEstateClassList, true) && !in_array('2', $settingEstateClassList, true)) {
            $estateRents = $this->fetchAll(array(['hp_id', $hp->id], ['link_estate_page_id', 'estate_rent']));
            foreach ($estateRents as $estateRent) {
                $pageIds[] = $estateRent->id;
            }
        }
        // 事業物件検索トップ
        if (!in_array('3', $settingEstateClassList, true) && !in_array('4', $settingEstateClassList, true)) {
            $estatePurchases = $this->fetchAll(array(['hp_id', $hp->id], ['link_estate_page_id', 'estate_purchase']));
            foreach ($estatePurchases as $estatePurchase) {
                $pageIds[] = $estatePurchase->id;
            }
        }
        // 各物件検索設定の物件種目
        foreach ($linkIds as $linkEstatePageId) {
            $linkEstatePages = $this->fetchAll(array(['hp_id', $hp->id], ['link_estate_page_id', $linkEstatePageId]));
            foreach ($linkEstatePages as $linkEstatePage) {
                $pageIds[] = $linkEstatePage->id;
            }
        }
        return $pageIds;
    }

    /**
     * ページの作成・更新画面に存在する物件検索の内部リンクを削除する
     * @param array $linkPageIds
     */
    public function deleteLinkPages($linkPageIds)
    {   
        if (count($linkPageIds) > 0) {
            // $select = $this->getAdapter()->quoteInto('id IN (?)', $linkPageIds);
            $where = ['whereIn'=>['id', $linkPageIds]];
            $this->update($where, array('delete_flg' => 1));
        }
    }

    public function getArticlePageAllPlan()
    {
        $result = array();
        $plans = CmsPlan::getInstance()->getAll();
        foreach ($plans as $index => $value) {
            if ($index == config('constants.cms_plan.CMS_PLAN_NONE'))
                continue;
            $plan             =    Plan::factory(CmsPlan::getCmsPLanName($index));
            $result[$index] = $plan->pageMapArticle;
            // foreach($plan->pageMapArticle as $pages) {
            //     if (!isset($result[$index])) {
            //         $result[$index] = array();
            //     }
            //     $result[$index] = array_merge($result[$index], $pages);
            // }
        }

        return $result;
    }

    // 5444 不動産お役立ち情報
    public function getPageArticleByCategory($category = null)
    {
        if (is_null($category)) {
            return $this->useful_real_estate_page;
        }
        return $this->useful_real_estate_page[$category];
    }

    public function getArticleOriginal()
    {
        return $this->_articleOriginal;
    }

    public function getPageNameArticle($type, $hpId = null)
    {
        if (in_array($type, $this->getArticleOriginal())) {
            switch ($type) {
                case self::TYPE_LARGE_ORIGINAL:
                    $prefix = 'l-';
                    break;
                case self::TYPE_SMALL_ORIGINAL:
                    $prefix = 's-';
                    break;
                default:
                    $prefix = 'art-';
                    break;
            }
            $name = $prefix . date('YmdHis');
            $where = [
                ['hp_id' , $hpId],
                ['filename', 'like' , $name . '%'],
            ];
            $suffix = count($this->fetchAll($where));
            if ($suffix > self::MAX_CREATE_FILE) {
                return false;
            }
            $filename = $name . sprintf('%02d', $suffix);
        } else {
            $filename = $this->getPageNameJp($type);
        }
        return $filename;
    }

    public function isUniqueFilename($filename, $hpId)
    {
        $where = [
            ['hp_id', $hpId],
            ['filename', $filename],
        ];
        return !$this->fetchRow($where);
    }

    public function createRowPageArray($type, $hpId)
    {
        $data = array(
            'new_flg'            => 1,
            'page_type_code'    => $type,
            'page_category_code' => $this->getCategoryUsefulEstate($type),
            'title'                => $this->getTypeNameJp($type),
            'description'        => $this->getDescriptionNameJp($type),
            'keywords'            => $this->getKeywordNameJp($type),
            'filename'            => $this->getPageNameArticle($type),
            'parent_page_id'    => null,
            'level'                => 1,
            'sort'                => 0,
            'hp_id'                => $hpId,
        );
        $this->model->setFromArray($data);
        return $this->model;
    }

    public function filterPageByType($pages, $type)
    {
        $results = array();
        foreach ($pages as $page) {
            if ($page['page_type_code'] == $type) {
                $results[] = $page;
            }
        }
        return $results;
    }

    public function getCategoryCodeArticle($notIns = array())
    {
        $results = array();
        foreach ($this->useful_real_estate_page as $category => $list) {
            if (!in_array($category, $notIns)) {
                $results[] = $category;
            }
        }
        return $results;
    }

    public function getLevelByType($type)
    {
        $category = $this->getCategoryUsefulEstate($type);
        switch ($category) {
            case self::CATEGORY_TOP_ARTICLE:
                $level = 1;
                break;
            case self::CATEGORY_LARGE:
                $level = 2;
                break;
            case self::CATEGORY_SMALL:
                $level = 3;
                break;
            case self::CATEGORY_ARTICLE:
                $level = 4;
                break;
            default:
                break;
        }

        return $level;
    }

    public function fetchLargeCategoryPages($hpId)
    {
        $large = $this->getPageArticleByCategory(self::CATEGORY_LARGE);
        return $this->fetchAll(array(
            ['hp_id'                , $hpId],
            'whereIn' => ['page_type_code', $large],
            ['new_flg', 0],
        ), array('sort'));
    }

    public function fetchSmallCategoryWithLargePages($hpId, $largeId)
    {
        return $this->fetchAll(array(
            ['hp_id'        , $hpId],
            ['parent_page_id', $largeId],
            ['new_flg', 0],
        ), array('sort'));
    }

    public function fetchArticleCategoryWithSmallPages($hpId, $mallList)
    {
        return $this->fetchAll(array(
            ['hp_id'            , $hpId],
            'whereIn' => ['parent_page_id', $mallList],
            ['new_flg', 0],
        ), array('sort'));
    }

    public function fetchActicleCategoryWithoutCategoryPages($hpId, $pageId)
    {
        $largeLists = $this->fetchAll(array(
            ['hp_id'              , $hpId],
            ['page_category_code', self::CATEGORY_LARGE],
            'whereNotIn' => ['id', [$pageId]],
            ['new_flg', 0],
        ), array('sort'))->toSiteMapArray();
        $smalls = [];
        foreach ($largeLists as $key => $largeList) {
            $small = $this->fetchSmallCategoryWithLargePages($hpId, $largeList['id'])->toSiteMapArray();
            foreach ($small as $key => $value) {
                $smalls[] = $value;
            }
        }
        return $smalls;
    }

    public function fetchLargeByPageTypeCodePages($hpId, $pageTypeCode)
    {
        return $this->fetchAll(array(
            ['hp_id'         , $hpId],
            ['page_type_code', $pageTypeCode],
        ), array('sort'))->toSiteMapArray();
    }

    public function isOldTemplateArticle($category = null, $pageType = null)
    {
        if ($category) {
            return in_array($category, $this->new_category);
        }
        if ($pageType) {
            $oldPages = array(
                HpPageRepository::TYPE_RENT,
                HpPageRepository::TYPE_LEND,
                HpPageRepository::TYPE_BUY,
                HpPageRepository::TYPE_SELL,
                HpPageRepository::TYPE_PREVIEW,
                HpPageRepository::TYPE_MOVING,
            );
            return in_array($pageType, $oldPages);
        }
    }

    public function isFirstCreatePageArticle($hp)
    {
        $select = $this->model->select();
        $select->where('hp_id', $hp->id);
        $select->whereIn('page_category_code', $this->getCategoryCodeArticle());
        $rows = $select->withoutGlobalScopes()->get();
        if (count($rows) > 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $pageTypeCode int
     * @return bool
     */
    public function isLink($pageTypeCode)
    {
        return in_array($pageTypeCode, $this->_link_pages, true);
    }

    public function isLinkArticle($page, $isTop = false)
    {
        $isLinkArticle = $page['page_type_code'] == HpPageRepository::TYPE_ALIAS && $page['link_article_flg'];
        if ($isTop && $isLinkArticle) {
            $isLinkArticle = $isLinkArticle && is_null($page['article_parent_id']);
        }
        return $isLinkArticle;
    }
}
