<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Special;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;
use Library\Custom\Model\Estate\TypeList;

class ItemLabel extends Abstract\ItemLabel {
	
	static protected $_instance;
	
	public function __construct() {
$labels['shumoku']['13']='一棟売アパート';
$labels['shumoku']['14']='一棟売マンション';
$labels['shumoku']['15']='売ビル';
$labels['shumoku']['16']='その他';

$labels['shumoku']['17']='アパート';
$labels['shumoku']['18']='マンション';
$labels['shumoku']['19']='一戸建て';
$labels['shumoku']['20']='ビル';
$labels['shumoku']['21']='倉庫';
$labels['shumoku']['22']='工場';
$labels['shumoku']['23']='その他';

// 賃貸用種目詳細(NHP-4586追加分)
// ------------------------------------------------------------------------------------------
$labels['shumoku']['24']='テラスハウス';
$labels['shumoku']['25']='タウンハウス';

$labels['shumoku']['26']='作業所';
$labels['shumoku']['27']='マンション一括';
$labels['shumoku']['28']='アパート一括';
$labels['shumoku']['29']='寮';
$labels['shumoku']['30']='旅館';
$labels['shumoku']['31']='別荘';
$labels['shumoku']['32']='ホテル';
$labels['shumoku']['33']='モーテル';
$labels['shumoku']['34']='医院';
$labels['shumoku']['35']='ガソリンスタンド';
$labels['shumoku']['36']='特殊浴場';
$labels['shumoku']['37']='サウナ';
$labels['shumoku']['38']='保養所';
$labels['shumoku']['61']='貸家';

$labels['shumoku']['39']='一戸建て';
$labels['shumoku']['40']='建築条件付き土地';

$labels['shumoku']['41']='戸建住宅（オーナーチェンジのみ）';
$labels['shumoku']['42']='テラスハウス（オーナーチェンジのみ）';
$labels['shumoku']['43']='マンション（オーナーチェンジのみ）';
$labels['shumoku']['44']='公団（オーナーチェンジのみ）';
$labels['shumoku']['45']='公社（オーナーチェンジのみ）';
$labels['shumoku']['46']='タウンハウス（オーナーチェンジのみ）';
$labels['shumoku']['47']='工場';
$labels['shumoku']['48']='倉庫';
$labels['shumoku']['49']='寮';
$labels['shumoku']['50']='旅館';
$labels['shumoku']['51']='ホテル';
$labels['shumoku']['52']='別荘';
$labels['shumoku']['53']='モーテル';
$labels['shumoku']['54']='医院';
$labels['shumoku']['55']='ガソリンスタンド';
$labels['shumoku']['56']='特殊浴場';
$labels['shumoku']['57']='サウナ';
$labels['shumoku']['58']='保養所';
$labels['shumoku']['59']='作業所';
$labels['shumoku']['60']='駐車場';
// ------------------------------------------------------------------------------------------
// 賃貸用種目詳細(NHP-4586追加分) -- End --


$labels['kakaku']['1']='下限：賃料（価格）';
$labels['kakaku']['2']='上限：賃料（価格）';
$labels['kakaku']['3']='管理費等含む';
$labels['kakaku']['4']='駐車場料金含む';
$labels['kakaku']['5']='礼金なし';
$labels['kakaku']['6']='敷金／保証金なし';

$labels['rimawari']['1']='下限：利回り';
$labels['rimawari']['2']='上限：利回り';

$labels['keiyaku_joken']['1']='定期借家除く';
$labels['keiyaku_joken']['2']='定期借家含む';
$labels['keiyaku_joken']['3']='定期借家のみ';
$labels['keiyaku_joken']['4']='短期貸し物件';
$labels['madori']['1']='間取り';
$labels['menseki']['1']='面積（建物面積・専有面積・使用部分面積）';
$labels['menseki']['2']='土地面積';
$labels['menseki']['3']='';
$labels['menseki']['4']='';

$labels['tatemono_kozo']['1']='鉄筋系';



$labels['tatemono_kozo']['2']='鉄骨系';



$labels['tatemono_kozo']['3']='木造';
$labels['tatemono_kozo']['4']='その他';
$labels['saiteki_yoto']['1']='住宅用地のみ';
$labels['saiteki_yoto']['2']='住宅用地を除く';
$labels['eki_toho_fun']['1']='駅からの徒歩';

$labels['chikunensu']['1']='築年数';
$labels['chikunensu']['2']='築年数:From';

$labels['reform_renovation']['1']='リフォーム・リノベーション済/予定含む';

$labels['reformable_parts']['1']='水回り';
$labels['reformable_parts']['2']='内装';
$labels['reformable_parts']['3']='その他';

$labels['open_room']['1']='オープンルーム/モデルルーム';
$labels['open_house']['1']='オープンハウス/モデルハウス';
$labels['genchi_hanbaikai']['1']='現地販売会';
$labels['joho_kokai']['1']='指定なし';
$labels['joho_kokai']['2']='本日公開';
$labels['joho_kokai']['3']='3日以内に公開';
$labels['joho_kokai']['4']='1週間以内に公開';
$labels['pro_comment']['1']='「おすすめコメント」あり';
$labels['pro_comment']['2']='「エンド向けアピール」あり';
$labels['image']['1']='間取図あり';
$labels['image']['2']='図面あり';
$labels['image']['3']='地形図あり';
$labels['image']['4']='写真あり';
$labels['image']['5']='パノラマ・ムービーあり';

$labels['kitchen']['1']='システムキッチン';
$labels['kitchen']['2']='カウンターキッチン';
$labels['kitchen']['3']='独立型キッチン';
$labels['kitchen']['4']='オープンキッチン';
$labels['kitchen']['5']='Ⅱ型キッチン';
$labels['kitchen']['6']='アイランドキッチン';
$labels['kitchen']['7']='２WAYキッチン';
$labels['kitchen']['8']='３WAYキッチン';
$labels['kitchen']['9']='IHクッキングヒーター';
$labels['kitchen']['10']='グリル';
$labels['kitchen']['11']='ガスオーブン';
$labels['kitchen']['12']='ガスコンロ使用可';
$labels['kitchen']['13']='電気コンロ';
$labels['kitchen']['14']='2口以上コンロ';

$labels['kitchen']['16']='３口以上コンロ';
$labels['kitchen']['17']='浄水器';
$labels['kitchen']['18']='食器(洗浄)乾燥機';
$labels['kitchen']['19']='冷蔵庫';
$labels['kitchen']['20']='ディスポーザー';
$labels['kitchen']['21']='ダウンウォール収納';

$labels['bath_toilet']['1']='バス・トイレ別';
$labels['bath_toilet']['2']='バス・トイレ同室';
$labels['bath_toilet']['3']='追焚き機能';
$labels['bath_toilet']['4']='浴室乾燥機';
$labels['bath_toilet']['5']='シャワー';
$labels['bath_toilet']['6']='オートバス     ';
$labels['bath_toilet']['7']='バス1坪以上';
$labels['bath_toilet']['8']='浴室暖房';
$labels['bath_toilet']['9']='ＴＶ付浴室';
$labels['bath_toilet']['10']='サウナ';
$labels['bath_toilet']['11']='ミストサウナ';
$labels['bath_toilet']['12']='バスオーディオ';
$labels['bath_toilet']['13']='洗面所';
$labels['bath_toilet']['14']='シャワー付洗面化粧台';
$labels['bath_toilet']['15']='洗面台';
$labels['bath_toilet']['16']='洗面所独立';
$labels['bath_toilet']['17']='温水洗浄便座';
$labels['bath_toilet']['18']='トイレ';
$labels['bath_toilet']['19']='トイレ2ヶ所';
$labels['bath_toilet']['20']='男女別トイレ';
$labels['bath_toilet']['21']='タンクレストイレ';
$labels['bath_toilet']['22']='シャワールーム'; //追加
$labels['bath_toilet']['23']='高温差湯式'; //追加
$labels['bath_toilet']['24']='多機能トイレ'; //追加
$labels['bath_toilet']['25']='バス（共同）';//追加
$labels['bath_toilet']['26']='シャワールーム（共同）';//追加
$labels['bath_toilet']['27']='トイレ（共同）';//追加

$labels['reidanbo']['1']='エアコン';
$labels['reidanbo']['2']='床暖房';
$labels['reidanbo']['3']='キッチン床暖房';
$labels['reidanbo']['4']='暖房';
$labels['reidanbo']['5']='FF暖房';
$labels['reidanbo']['6']='灯油';
$labels['reidanbo']['7']='ボイラー';
$labels['reidanbo']['8']='冷房';
$labels['reidanbo']['9']='セントラル';
$labels['reidanbo']['10']='個別空調';
$labels['reidanbo']['11']='シーリングファン';

$labels['shuno']['1']='ウォークインクローゼット';
$labels['shuno']['2']='クローゼット';
$labels['shuno']['3']='収納スペース';
$labels['shuno']['4']='床下収納';
$labels['shuno']['5']='トランクルーム';
$labels['shuno']['6']='シューズボックス';
$labels['shuno']['7']='シューズウォークインクローゼット';
$labels['shuno']['8']='全居室収納';
$labels['shuno']['9']='グルニエ';

$labels['tv_tsusin']['1']='ＢＳ端子';
$labels['tv_tsusin']['2']='ＣＳ';
$labels['tv_tsusin']['3']='ＣＡＴＶ';
$labels['tv_tsusin']['4']='インターネット対応';
$labels['tv_tsusin']['10']='光ファイバー'; // 追加
$labels['tv_tsusin']['5']='電話機';
$labels['tv_tsusin']['6']='有線放送';
$labels['tv_tsusin']['7']='無線ＬＡＮ';
$labels['tv_tsusin']['8']='ＭＭコンセント（マルチメディアコンセント）';
$labels['tv_tsusin']['9']='インターネット使用料無料';

$labels['security']['1']='オートロック';
$labels['security']['2']='モニタ付インターホン';
$labels['security']['3']='宅配ボックス';
$labels['security']['4']='24時間セキュリティ';
$labels['security']['5']='ディンプルキー';
$labels['security']['6']='カードキー';
$labels['security']['7']='非接触型ICカードキー';
$labels['security']['8']='シャッター雨戸';
$labels['security']['9']='ダブルロックドア';
$labels['security']['10']='電動シャッター';
$labels['security']['11']='防犯カメラ';
$labels['security']['12']='防犯用ガラス';
$labels['security']['13']='有人警備';

$labels['ichi']['1']='2階以上';
$labels['ichi']['2']='10階建て以上';
$labels['ichi']['3']='最上階';
$labels['ichi']['4']='1階';
$labels['ichi']['5']='角部屋';
$labels['ichi']['6']='角地';
$labels['ichi']['7']='南道路';
$labels['ichi']['8']='20階建て以上';

$labels['joken']['1']='即入居可';
$labels['joken']['2']='即引渡し可';
$labels['joken']['3']='二人入居可';
$labels['joken']['4']='女性限定     ';
$labels['joken']['5']='女性限定除く';
$labels['joken']['6']='男性限定';
$labels['joken']['7']='男性限定除く';
$labels['joken']['8']='高齢者相談';
$labels['joken']['9']='ペット相談';
$labels['joken']['10']='楽器相談';
$labels['joken']['11']='事務所可';
$labels['joken']['12']='フリーレント';
$labels['joken']['13']='振分';
$labels['joken']['14']='二世帯向き';
$labels['joken']['15']='法人契約限定';
$labels['joken']['16']='法人契約希望';
$labels['joken']['17']='学生限定';
$labels['joken']['18']='SOHO向き';
$labels['joken']['19']='設計住宅性能評価取得';
$labels['joken']['20']='建設住宅性能評価取得';
$labels['joken']['21']='住宅性能保証付';
$labels['joken']['22']='分割可';
$labels['joken']['23']='建築条件なし';
$labels['joken']['24']='所有権';
$labels['joken']['25']='セットバック要';
$labels['joken']['26']='セットバック済';
$labels['joken']['27']='飲食店可';
$labels['joken']['28']='居抜き';
$labels['joken']['29']='スケルトン';
$labels['joken']['30']='24時間利用可';
$labels['joken']['31']='飲食店不可';
$labels['joken']['32']='ルームシェア可';
$labels['joken']['33']='常時ゴミ出し可能';
$labels['joken']['34']='大型トラック搬入可';
$labels['joken']['35']='オーナーチェンジ物件';
$labels['joken']['36']='単身者限定';    //追加
$labels['joken']['37']='非喫煙者限定';  //追加
$labels['joken']['38']='シェアハウス';  //追加
$labels['joken']['39']='DIY可';       //追加
$labels['joken']['40']='１フロア１テナント';  //追加（2017/09）
$labels['joken']['41']='土曜日利用可';       //追加（2017/09）
$labels['joken']['42']='土日・祝日利用可';    //追加（2017/09）
$labels['joken']['43']='保証人不要'; //追加（2018/9/20）


$labels['genkyo']['1']='更地';

$labels['kyouyu_shisetsu']['1']='エレベーター';
$labels['kyouyu_shisetsu']['2']='コインランドリー';
$labels['kyouyu_shisetsu']['3']='ペット用施設';
$labels['kyouyu_shisetsu']['4']='管理人常駐';
$labels['kyouyu_shisetsu']['5']='キッズルーム';
$labels['kyouyu_shisetsu']['6']='託児所';
$labels['kyouyu_shisetsu']['7']='浄化槽';
$labels['kyouyu_shisetsu']['8']='クレーン';
$labels['kyouyu_shisetsu']['9']='リフト';
$labels['kyouyu_shisetsu']['10']='高床式プラットホーム';
$labels['kyouyu_shisetsu']['11']='共用パーティルーム';
$labels['kyouyu_shisetsu']['12']='共用シアタールーム';
$labels['kyouyu_shisetsu']['13']='屋上庭園';
$labels['kyouyu_shisetsu']['14']='フィットネス施設';
$labels['kyouyu_shisetsu']['15']='敷地内公園';
$labels['kyouyu_shisetsu']['16']='共用ゲストルーム';
$labels['kyouyu_shisetsu']['17']='敷地内ＡＥＤ有';
$labels['kyouyu_shisetsu']['18']='施設内貸会議室';
$labels['kyouyu_shisetsu']['19']='車寄せスペース';
$labels['kyouyu_shisetsu']['20']='荷積みスペース';
$labels['kyouyu_shisetsu']['21']='ドックレベラー';
$labels['kyouyu_shisetsu']['22']='来客用駐車場';
$labels['kyouyu_shisetsu']['23']='シャトルバス';
$labels['kyouyu_shisetsu']['24']='フロントサービス';
$labels['kyouyu_shisetsu']['25']='クリーニングサービス';
$labels['kyouyu_shisetsu']['26']='人荷用エレベーター';
$labels['kyouyu_shisetsu']['27']='施設内喫煙所';

$labels['setsubi_kinou']['1']='フローリング';
$labels['setsubi_kinou']['2']='クッションフロア';
$labels['setsubi_kinou']['3']='室内洗濯機置き場';
$labels['setsubi_kinou']['4']='洗濯機置き場';
$labels['setsubi_kinou']['5']='洗濯・衣類乾燥機';
$labels['setsubi_kinou']['6']='室内物干し';
$labels['setsubi_kinou']['7']='給湯';
$labels['setsubi_kinou']['8']='都市ガス';
$labels['setsubi_kinou']['9']='プロパンガス';
$labels['setsubi_kinou']['10']='地下室';
$labels['setsubi_kinou']['11']='側溝';
$labels['setsubi_kinou']['12']='上水道';
$labels['setsubi_kinou']['13']='下水道';
$labels['setsubi_kinou']['14']='電気';
$labels['setsubi_kinou']['15']='動力あり';
$labels['setsubi_kinou']['16']='太陽光発電システム';
$labels['setsubi_kinou']['17']='可動間仕切り';
$labels['setsubi_kinou']['18']='アルコーブ';
$labels['setsubi_kinou']['19']='無停電電源装置';
$labels['setsubi_kinou']['20']='EV車充電設備';
$labels['setsubi_kinou']['21']='省エネ給湯器';
$labels['setsubi_kinou']['22']='複層ガラス';
$labels['setsubi_kinou']['23']='24時間換気システム';

$labels['tokucho']['1']='採光2面以上';
$labels['tokucho']['2']='全室２面採光';
$labels['tokucho']['3']='閑静な住宅街';
$labels['tokucho']['4']='分譲タイプ';
$labels['tokucho']['5']='バリアフリー';
$labels['tokucho']['6']='オール電化';
$labels['tokucho']['7']='家具付き';
$labels['tokucho']['8']='出窓';
$labels['tokucho']['9']='メゾネット';
$labels['tokucho']['10']='ロフト';
$labels['tokucho']['11']='吹抜';
$labels['tokucho']['12']='ロードヒーティング';
$labels['tokucho']['13']='デザイナーズ';
$labels['tokucho']['14']='スマートハウス';
$labels['tokucho']['15']='外観タイル張り';
$labels['tokucho']['16']='整形地';
$labels['tokucho']['17']='平坦地';
$labels['tokucho']['18']='南向き';    //追加
$labels['tokucho']['19']='家電付き';  //追加

$labels['koho_kozo']['1']='制震構造';
$labels['koho_kozo']['2']='免震構造';
$labels['koho_kozo']['3']='天井高２.５Ｍ以上';
$labels['koho_kozo']['4']='システム天井';
$labels['koho_kozo']['5']='二重床・二重天井';
$labels['koho_kozo']['6']='ハイサッシ採用';
$labels['koho_kozo']['7']='高強度コンクリート';
$labels['koho_kozo']['8']='１００年コンクリート';
$labels['koho_kozo']['9']='外壁コンクリート';
$labels['koho_kozo']['10']='内装コンクリート';
$labels['koho_kozo']['11']='外壁サイディング';
$labels['koho_kozo']['12']='外断熱工法';
$labels['koho_kozo']['13']='高気密高断熱住宅';
$labels['koho_kozo']['14']='アウトフレーム工法';
$labels['koho_kozo']['15']='逆梁工法';
$labels['koho_kozo']['16']='２×４工法';
$labels['koho_kozo']['17']='２×６工法';
$labels['koho_kozo']['18']='ノンホルムアルデヒド';
$labels['koho_kozo']['19']='OAフロア';
$labels['koho_kozo']['20']='スケルトンインフィル';
$labels['koho_kozo']['21']='整形無柱空間';

$labels['torihiki_taiyo']['1']='取引態様';

$labels['other']['1']='バルコニー';
$labels['other']['2']='ルーフバルコニー';
$labels['other']['3']='バルコニー2面以上';
$labels['other']['4']='南バルコニー';
$labels['other']['5']='ウッドデッキ';
$labels['other']['6']=[
	'default'=>'庭',
	TypeList::TYPE_CHINTAI=>'庭(専用庭)',
	TypeList::TYPE_MANSION=>'専用庭',
];
$labels['other']['7']='駐車場（近隣含む）';
$labels['other']['8']='駐車場２台分';
$labels['other']['9']='駐車場３台分';
$labels['other']['10']='駐輪場';
$labels['other']['11']='バイク置き場';
$labels['other']['12']='テラス';
$labels['other']['13']='サンルーム';
$labels['other']['14']='縁側';
$labels['other']['15']='ベッド';
$labels['other']['16']='照明器具';
$labels['other']['17']='間接照明';
$labels['other']['18']='人感センサー付照明';
$labels['other']['19']='ダウンライト';
$labels['other']['20']='フットライト';
$labels['other']['21']='火災警報器（報知機）';
$labels['other']['22']='掘ごたつ';
$labels['other']['23']='汲取';
//$labels['other']['24']='長期優良住宅';
$labels['other']['24']='長期優良住宅（耐震、省エネ性等高い）';
//$labels['other']['25']='フラット35適合証明書';
$labels['other']['25']='フラット35・S適合証明書あり';
//$labels['other']['26']='耐震基準適合証明書有';
$labels['other']['26']='耐震基準適合証明書あり';
$labels['other']['27']='前面棟無';
$labels['other']['28']='隣接建物距離2ｍ以上';
$labels['other']['29']='地盤調査書有';
$labels['other']['30']='区画整理地内';
$labels['other']['31']='住宅性能評価書あり';   //追加
$labels['other']['32']='住宅性能保証付';      //追加
$labels['other']['33']='建築工事完了後の完了検査済証あり';         //追加
$labels['other']['34']='低炭素住宅（省エネ性高い）';       //追加
$labels['other']['35']='瑕疵保険（国交省指定）による保証';  //追加
$labels['other']['36']='瑕疵保証(不動産会社独自)';        //追加
$labels['other']['37']='インスペクション（建物検査）済み';  //追加
$labels['other']['38']='新築時・増改築時の設計図書あり';   //追加
$labels['other']['39']='修繕・点検の記録あり';    //追加
$labels['other']['40']='クレジットカード決済';    //追加
$labels['other']['41']='IT重説対応物件';    //追加
$labels['other']['42']='温泉（引込み済）';  //追加
$labels['other']['43']='温泉（引込み可）';  //追加
$labels['other']['44']='温泉（運び湯）';   //追加
$labels['other']['45']='再建築不可';       //追加
$labels['other']['46']='建築不可';        //追加
$labels['other']['47']='耐震構造（新耐震基準）';       //追加（2017/09）
$labels['other']['48']='障がい者等用駐車場'; //追加（2017/10）
$labels['other']['49']='安心Ｒ住宅'; //追加（2018/06）：安心Ｒ住宅


		$this->_list = $labels;
	}
}