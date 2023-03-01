<?php
namespace Library\Custom\Model\Lists;
use App\Repositories\HpPage\HpPageRepository;

class HpPagePlaceholderData extends ListAbstract {

  protected $_list = array(
      // トップページ
      HpPageRepository::TYPE_TOP  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お知らせ一覧
      HpPageRepository::TYPE_INFO_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お知らせ
      HpPageRepository::TYPE_INFO_DETAIL  => array("title" => "", "description" => "2015年7月1日「夏季休暇のお知らせ」", "keyword1" => "夏季休暇のお知らせ", "filename" => "news20150701"),
      // 会社紹介
      HpPageRepository::TYPE_COMPANY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 会社沿革
      HpPageRepository::TYPE_HISTORY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 代表挨拶
      HpPageRepository::TYPE_GREETING  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 店舗紹介一覧
      HpPageRepository::TYPE_SHOP_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 店舗紹介
      HpPageRepository::TYPE_SHOP_DETAIL  => array("title" => "", "description" => "蒲田店の店舗紹介", "keyword1" => "蒲田店の店舗紹介", "filename" => "shop-kamata"),
      // スタッフ紹介一覧
      HpPageRepository::TYPE_STAFF_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // スタッフ紹介
      HpPageRepository::TYPE_STAFF_DETAIL  => array("title" => "", "description" => "山田●●", "keyword1" => "山田●●", "filename" => "staff-yamada"),
      // 採用情報
      HpPageRepository::TYPE_RECRUIT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件ページ(物件コマ)一覧
      HpPageRepository::TYPE_STRUCTURE_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件ページ(物件コマ)
      HpPageRepository::TYPE_STRUCTURE_DETAIL  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // ブログ一覧
      HpPageRepository::TYPE_BLOG_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // ブログ詳細
      HpPageRepository::TYPE_BLOG_DETAIL  => array("title" => "", "description" => "2015年7月1日「現地販売会の様子」", "keyword1" => "現地販売会の様子", "filename" => "blog20150701"),
      // プライバシーポリシー
      HpPageRepository::TYPE_PRIVACYPOLICY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // サイトポリシー
      HpPageRepository::TYPE_SITEPOLICY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // オーナーページ
      HpPageRepository::TYPE_OWNER  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 法人ページ
      HpPageRepository::TYPE_CORPORATION  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 入居者さま向けページ
      HpPageRepository::TYPE_TENANT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 仲介会社さま向けページ
      HpPageRepository::TYPE_BROKER  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 管理会社さま向けページ
      HpPageRepository::TYPE_PROPRIETARY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 街情報
      HpPageRepository::TYPE_CITY  => array("title" => "", "description" => "蒲田の街情報", "keyword1" => "蒲田の街情報", "filename" => "city-kamata"),
      // お客様の声一覧
      HpPageRepository::TYPE_CUSTOMERVOICE_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お客様の声
      HpPageRepository::TYPE_CUSTOMERVOICE_DETAIL  => array("title" => "", "description" => "大田区A様からの声", "keyword1" => "大田区A様", "filename" => "voice-detail1"),
      // Q＆Aページ
      HpPageRepository::TYPE_QA  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 学区情報
      HpPageRepository::TYPE_SCHOOL  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 内見時のチェックポイント
      HpPageRepository::TYPE_PREVIEW  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 引っ越しのチェックポイント
      HpPageRepository::TYPE_MOVING  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 不動産用語集
      HpPageRepository::TYPE_TERMINOLOGY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 住まいを借りる契約の流れ
      HpPageRepository::TYPE_RENT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 住まいを貸す契約の流れ
      HpPageRepository::TYPE_LEND  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 住まいを買う契約の流れ
      HpPageRepository::TYPE_BUY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お役立ち情報 住まいを売却する契約の流れ
      HpPageRepository::TYPE_SELL  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 売却事例一覧
      HpPageRepository::TYPE_SELLINGCASE_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 売却事例
      HpPageRepository::TYPE_SELLINGCASE_DETAIL  => array("title" => "", "description" => "大田区一戸建ての売却事例について", "keyword1" => "大田区一戸建て事例", "filename" => "sell-detail1"),
      // イベント情報一覧
      HpPageRepository::TYPE_EVENT_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // イベント情報
      HpPageRepository::TYPE_EVENT_DETAIL  => array("title" => "", "description" => "2015年7月1日の現地見学会について", "keyword1" => "現地見学会", "filename" => "event20150701"),
      // リンク集
      HpPageRepository::TYPE_LINKS  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // フリーページ
      HpPageRepository::TYPE_FREE  => array("title" => "", "description" => "●●について", "keyword1" => "●●について", "filename" => "xxx-page"),
      // 会社問い合わせ
      HpPageRepository::TYPE_FORM_CONTACT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 資料請求
      HpPageRepository::TYPE_FORM_DOCUMENT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 査定依頼
      HpPageRepository::TYPE_FORM_ASSESSMENT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件問い合わせ 居住用賃貸物件フォーム
      HpPageRepository::TYPE_FORM_LIVINGLEASE  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件問い合わせ 事務所用賃貸物件フォーム
      HpPageRepository::TYPE_FORM_OFFICELEASE  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件問い合わせ 居住用売買物件フォーム
      HpPageRepository::TYPE_FORM_LIVINGBUY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件問い合わせ 事務所用売買物件フォーム
      HpPageRepository::TYPE_FORM_OFFICEBUY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 会員専用ページ
      HpPageRepository::TYPE_MEMBERONLY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // サイトマップ
      HpPageRepository::TYPE_SITEMAP  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // リンク
      HpPageRepository::TYPE_LINK  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // エイリアス
      HpPageRepository::TYPE_ALIAS  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),

      // 物件リクエスト 居住用賃貸物件フォーム
      HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件リクエスト 事務所用賃貸物件フォーム
      HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件リクエスト 居住用売買物件フォーム
      HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 物件リクエスト 事務所用売買物件フォーム
      HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),

      //CMSテンプレートパターンの追加
      // 事業内容
      HpPageRepository::TYPE_BUSINESS_CONTENT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // コラム一覧
      HpPageRepository::TYPE_COLUMN_INDEX  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // コラム詳細
      HpPageRepository::TYPE_COLUMN_DETAIL  => array("title" => "", "description" => "2015年7月1日「◯◯とは？そのメリットとデメリット」", "keyword1" => "◯◯とは？そのメリットとデメリット", "filename" => "column20150701"),
      // 当社の思い・強み
      HpPageRepository::TYPE_COMPANY_STRENGTH  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 雛形
      // 不動産「買取り」について
      HpPageRepository::TYPE_PURCHASING_REAL_ESTATE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 「買い換えローン」と「住宅ローン」の違い
      HpPageRepository::TYPE_REPLACEMENTLOAN_MORTGAGELOAN => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 買い換えは売却が先？
      HpPageRepository::TYPE_REPLACEMENT_AHEAD_SALE  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 中古戸建ての「建物評価」の仕組み
      HpPageRepository::TYPE_BUILDING_EVALUATION => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 一戸建てを買い手が見学するとき、気にするポイント
      HpPageRepository::TYPE_BUYER_VISITS_DETACHEDHOUSE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // マンションの売却を有利にするポイント（専有部分）
      HpPageRepository::TYPE_POINTS_SALE_OF_CONDOMINIUM => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // マンションと一戸建て どちらを選ぶ？
      HpPageRepository::TYPE_CHOOSE_APARTMENT_OR_DETACHEDHOUSE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 新築？中古？ 選ぶときの考え方
      HpPageRepository::TYPE_NEWCONSTRUCTION_OR_SECONDHAND => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 建売住宅と注文住宅の違いと選び方
      HpPageRepository::TYPE_ERECTIONHOUSING_ORDERHOUSE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 住宅購入のベストタイミングは？
      HpPageRepository::TYPE_PURCHASE_BEST_TIMING => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // ライフプランを立ててみましょう
      HpPageRepository::TYPE_LIFE_PLAN => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 住宅ローンの種類
      HpPageRepository::TYPE_TYPES_MORTGAGE_LOANS => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 資金計画を立てましょう
      HpPageRepository::TYPE_FUNDING_PLAN => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 賃貸管理でお困りのオーナー様へ
      HpPageRepository::TYPE_TROUBLED_LEASING_MANAGEMENT => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 賃貸管理業務メニュー
      HpPageRepository::TYPE_LEASING_MANAGEMENT_MENU => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 空室対策（概論的）
      HpPageRepository::TYPE_MEASURES_AGAINST_VACANCIES => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 競合物件に差をつける住戸リフォーム
      HpPageRepository::TYPE_HOUSE_REMODELING => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 土地活用をお考えのオーナー様へ（事業化の流れ含む）
      HpPageRepository::TYPE_CONSIDERS_LAND_UTILIZATION_OWNER => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 土地活用の方法について（賃貸M・AP経営、等価交換M、高齢者向け住宅）
      HpPageRepository::TYPE_UTILIZING_LAND => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 不動産の購入と相続税対策（税務専門的）
      HpPageRepository::TYPE_PURCHASE_INHERITANCE_TAX => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 収入から払える家賃の上限はどれくらい？
      HpPageRepository::TYPE_UPPER_LIMIT => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 賃貸住宅を借りるときの「初期費用」とは
      HpPageRepository::TYPE_RENTAL_INITIAL_COST => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 候補物件のしぼり方
      HpPageRepository::TYPE_SQUEEZE_CANDIDATE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 引越し時の不用品・粗大ゴミなどの処分方法
      HpPageRepository::TYPE_UNUSED_ITEMS_AND_COARSEGARBAGE => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 快適に暮らすための居住ルール（不動産会社視点）
      HpPageRepository::TYPE_COMFORTABLELIVING_RESIDENT_RULES => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // 店舗探し・自分でできる商圏調査
      HpPageRepository::TYPE_STORE_SEARCH  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      // お店成功のためには事業計画書が大切
      HpPageRepository::TYPE_SHOP_SUCCESS_BUSINESS_PLAN => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
      //CMSテンプレートパターンの追加
      //#4274 Change spec form FDP contact
      //HpPageRepository::TYPE_FORM_FDP_CONTACT  => array("title" => "", "description" => "", "keyword1" => "", "filename" => ""),
  );
}
