<?php
namespace Library\Custom\View\Helper;

class ToolTip extends  HelperAbstract
{
	static protected $_tooltips = array(
			
			'site_title'				=> 'ホームページの名前になります。<br>屋号、商号と、エリア名や得意とされている物件種別を組み合せて作ります。<br>TOPページのソース記述上では、titleタグにあたります。',
			'site_description'			=> '自社の得意物件種別、得意エリア、営業時間やお客様にご説明をしたい内容をお書きください。TOPページのソース上ではdescriptionタグにあたります。',
			'site_keyword'			=> '自社の得意物件種別、得意エリアの名称を単語単位で記載してください。TOPページのソース上ではkeywordsタグにあたります。',
			'site_favicon'				=> 'ブラウザの一番上やホームページのアドレス欄の一番左側に出てくる小さな画像です。<br>サイトのロゴなどを入れることでお客様への訴求度が上がります。',
			'site_webclip'				=> 'スマートフォンの「ホーム画面」等に表示される画像です。<br/>サイトのロゴなどを入れることでお客様への訴求度があがります。<br/>※スマートフォンの種類によって表示されない可能性があります',
			'site_company_name'		=> '自社の社名を入れてください。<br><br>なお、文字が正常に表示されないことがありますので「㈱」や「㈲」は使わないでください。',
			'site_adress'				=> '自社の住所を入れてください。<br>例：東京都中央区1-1-1',
			'site_tel'					=> '自社の電話番号をハイフン付きでいれてください。<br>例：03-0000-0000',
			'site_office_hour'			=> '営業時間をご入力ください。<br>例：09:30〜18:30（水曜日除く）',
			'site_outline'				=> 'ホームページのロゴの上に出てくる文字です。<br>SEO的にも重要です。<br>自社のご紹介文を入れてください。<br>TOPページのソース上ではh1タグにあたります。',
			'site_footer_link_level'	=> 'フッターに自動作成されるリンク一覧に表示する内容を決めてください。<br/>ブログ詳細および会員さま専用ページ配下に設置したページは、設定にかかわらず表示されません。',
			'site_logo_pc'			=> 'パソコン版のホームページの上部に開催するロゴの登録ができます。<br>「ロゴ画像だけ」「画像と文字で社名」「文字での社名だけ」のタイプが作れます。',
			'site_logo_sp'			=> 'スマートフォン版のホームページの上部に開催するロゴの登録ができます。<br>「ロゴ画像だけ」「画像と文字で社名」「文字での社名だけ」のタイプが作れます。',
			'copylight'				=> 'ページ下部に表示するコピーライトの記述です。<br>一般的には自社名の英語表記をご入力ください。',
			'site_facebook'			=> 'SNSの「Facebook」に関する設定ができます。<br>サイトに「いいねボタン」をつけたり、自社でFcebookページを持っている場合は、Facebookページの内容を埋め込む事が可能です。',
			'site_twitter'				=> 'SNSの「Twitter」に関する設定ができます。<br>サイトに、そのページをツイートするボタンをつけたり、会社でTwitterを行っている場合は、Twitterのタイムラインの内容を埋め込む事が可能です。',
			'site_line'				=> 'スマートフォン版のサイトにLINEのボタンを付け、友人・知人にLINEでそのページを紹介することが出来ます。',
            'site_line_at_freiend_qrcode'	=> 'PC版のサイトに、LINE公式アカウントの「友だち追加」QRコードを表示させるための設定ができます。こちらの設定を行うことによりトップページなど表示させたいページのサイドコンテンツより追加することができます。<br>※埋め込みコードはLINE公式アカウント管理画面より取得してください。',
            'site_line_at_freiend_button'	=> 'LINE公式アカウントの「友だち追加」ボタンを表示させるための設定ができます。こちらの設定を行うことによりトップページなど表示させたいページのサイドコンテンツより追加することができます。<br>※埋め込みコードはLINE公式アカウント管理画面より取得してください。',
			'site_qr'					=> 'PC版のサイトに、自社のページのURLをQRコードにしたものの掲載設定です。<br>「全ページ共通」の場合にはトップページのURLが、「各ページ個別」の場合にはQRコードが貼ってあるページのURLが埋め込まれます。',
			'site_test_password'		=> 'テストサイト用のパスワードを設定いただけます。テストサイトとは、「http://test.(自社のドメイン)」で表示され、ここで設定したパスワードを利用して見ることができる確認用ホームページとなります。',
			'hankyo_plus'		=> '反響プラスとは、エンドユーザーの閲覧履歴が確認でき、ユーザーの隠れた希望条件を推測できるサービスです。',
			'hankyo_plus_check'		=> '「表示する」を選択した場合、物件問合せフォームにユーザー同意のためのチェックボックスが表示されます。ユーザー同意 が得られた反響情報は、反響プラスでエンドユーザーの閲覧履歴が確認できます。',
			
			'design_theme'	=> '自社のホームページデザインの「テーマ」を選べます。<br>左右の矢印をクリックすると次のページが見られます。',
			'design_color'	=> 'お選びいただいた「テーマ」に応じた色の種類があります。<br>お好きな色をお選びください。',
			'design_color_code'	=> 'お選びいただいた「テーマ」に応じた色の種類があります。<br>お好きな色をお選びください。',
			'design_layout'	=> 'PC版ではサイドメニューが表示されますが、そのメニューを左右どちらに配置するかをお選びください。',

			'tdk_title'			=> '作るページを端的に説明するタイトルをお付けください。<br>当該ページのソース上は、TOPページの「サイト名」と組合さり、titleタグとなります。',
			'tdk_description'	=> 'このページの中身の概要を入力してください。<br>当該ページのソース上は、TOPページの「サイトの説明」と組合さり、descriptionタグとなります。',
			'tdk_keyword'		=> 'このページの中身を紹介する上でのキーワードを単語で入力してください。<br>当該ページのソース上は、TOPページの「キーワード」と組合さり、keywordsタグとなります。',
			'tdk_filename'		=> 'ここで入れた英語記述が当該ページのアドレスになります。<br>ただし、以下の記述はシステム上使用することが出来ませんのでご了承ください。<br>404notFound/all/api/article<br>baibai-jigyo-1/baibai-jigyo-2<br>baibai-kyoju-1/baibai-kyoju-2<br>chintai/chintai-jigyo-1<br>chintai-jigyo-2/chintai-jigyo-3<br>complete/confirm/edit/error<br>file2s/files/howtoinfo/images<br>index/inquiry/kasi-office/kasi-other<br>kasi-tenpo/kasi-tochi/kodate/mansion<br>parking/pc/personal/search<br>sitemap/sp/sp-（任意）/top<br>uri-office/uri-other/uri-tenpo/uri-tochi<br>validate',
            'tdk_filename_special' => 'ここで入れた英語記述が当該ページのアドレスになります。',
			'tdk_date'			=> 'このページの作成日を入力してください。',
            'tdk_notification_class' => 'お知らせのカテゴリーをアイコンとして表示させることができます。<br>任意で選ぶことができます。',
            'tdk_list_title' => 'お知らせ一覧に表示するタイトル（内容）を入力します。',
            'tdk_new_mark' => 'お知らせ一覧と詳細に「NEW」マークを設置したい場合に利用します。各お知らせにて設定した「日付」を含む期間でマークが表示されます。<br>例：「3日間表示する」を選択した場合、「日付」が4月1日のお知らせに4月1日0：00～4月3日23：59の間「NEW」マークが表示されます。',
            'preview-detail_add_list' => '表示イメージはお知らせ一覧のプレビューより確認できます。',

			'page_main_contents'		=> 'ページの一番大きな領域の中身を作る場所となります。<br>すでに埋め込まれている設定を参考にしたりして、自由にホームページをお作りください。',
			'page_side_common_contents'		=> 'PC版のサイドコンテンツ、スマートフォン版でページ下部に表示させる領域の中身を作る場所となります。',
			'page_side_contents'		=> 'TOPページで編集した 「カスタマイズサイドコンテンツ」 の下部に配置されます。',
			'page_insert_area'		=> '主たるコンテンツ部分に列で分割した要素を入れることが出来ます。<br>それぞれの要素の中にパーツの要素をいれていくことが可能です。',
			'page_insert_parts'		=> 'ページに追加したい要素の選択が出来ます。',

			'page_parts_companyoutline'			=> '自社の会社概要を入れてください。<br>初期セットされている項目の削除も追加も可能です。',
			'page_parts_history'					=> '自社の会社沿革を入れてください。<br>初期セットされている項目の削除も追加も可能です。',
			'page_parts_privacypolicy'				=> '貴社の個人情報の取り扱いに関するポリシーを設定することができます。<br>「雛形選択」から雛形文をお選びいただき、適宜変更いただくこともできます。',
			'page_parts_sitepolicy'				=> '自社のWebサイト運営に関してのポリシーを入れてください。<br>右側の「雛形選択」から雛形文面をお選びいただき、適宜変更いただくのもの可能です。',
			'page_parts_greeting'					=> '自社の代表による、お客様へのメッセージなど、代表挨拶を入れてください。',
			'page_parts_eventdetail'				=> '自社でお客様にお越しいただく「イベント」の詳細情報を入れてください。',
			'page_parts_recruit'					=> '自社の従業員の採用情報を入れてください。',
			'page_parts_shopdetail'				=> '自社の店舗に関する情報を入れてください。<br>住所や電話番号情報など詳しくご入力いただく方がお客様に分かりやすくなります。',
			'page_parts_staffdetail'				=> '自社のスタッフ情報をご入力ください。<br>店舗にいらっしゃるお客様にとってはここの情報が安心につながります。',
			'page_parts_customervoicedetail'		=> '御社でご契約をされたお客様からのアンケートなど、お客様の生の「声」を入れてください。',
			'page_parts_city'						=> '自社の得意エリアの街の情報を入れてください。<br>これから引っ越しをするお客様にとっては、引越し先の情報は何よりの情報になります。',
			'page_parts_blogdetail'				=> '自社のブログの記事を入れてください。',
			'page_parts_sellingcasedetail'			=> '自社での売却事例を入れてください。<br>事例はお客様にとっては重要な参考資料になります。',
			'page_parts_forserviceintroduction'	=> 'サービスに関しての名称、詳細を入れてください。',
			'page_parts_forexample'				=> 'サービスに関しての事例のタイトル、詳細を入れてください。',
			'page_parts_forcorporationreview'		=> '法人向けサービスを利用されているお客様の声を入れてください。',
			'page_parts_forownerreview'			=> 'オーナー様向けサービスを利用されているお客様の声を入れてください。',
			'page_parts_description'				=> 'このページの概要説明をまず入れてください。',
			'page_parts_forservice'				=> 'このサービスの内容を入れてください。',
			'page_parts_forsupport'				=> 'このサービスに対して、御社のサポート体制があれば、入れてください。',
			'page_parts_forcase'					=> 'このサービスに対して、御社での事例や実績がある場合、入れてください。',
			'page_parts_fordownloadapplication'	=> '事前にお客様に記載をしていただく必要のある書類等があれば、こちらにアップロードをしてください。<br>PDFやWord、Excel、PowerPointのデータがアップロード可能です。',
			'page_parts_school'					=> '自社の得意エリアの学校区の情報を入れてください。<br>これから引っ越しをするお客様にとっては、引越し先の情報は何よりの情報になります。',
			'page_parts_qa'						=> '店舗やこれまでメールなどでお客様から届いている質問に対しての答えをここに登録していただくことで、今後のお客様の疑問・不安が解消されるコンテンツになります。',
			'page_parts_terminology'				=> '不動産業界の難解な用語について解説している「用語集」を作成ができます。<br>「用語を追加」ボタンから用語の詳細情報を入れてください。',
			'page_parts_rent'						=> '住まいを「借りる」場合にお客様がおこなう契約作業の流れを入れてください。<br>契約の行程をあらかじめお客様が分かることで心理的な負荷も下がります。',
			'page_parts_lend'						=> '住まいを「貸す」場合にお客様がおこなう契約作業の流れを入れてください。<br>契約の行程をあらかじめお客様が分かることで心理的な負荷も下がります。',
			'page_parts_buy'						=> 'お客様が住まいを「購入」する場合におこなう契約作業の流れを入れてください。<br>契約の行程をあらかじめお客様が分かることで心理的な負荷も下がります。',
			'page_parts_sell'						=> 'お客様が住まいを「売却」する場合におこなう契約作業の流れを入れてください。<br>契約の行程をあらかじめお客様が分かることで心理的な負荷も下がります。',
			'page_parts_preview'					=> '物件をお客様が内覧する時に「見ておいた方がいいポイント」をご紹介ください。<br>見るべきポイントをご紹介しておくことでトラブルの回避にも繋がります。',
			'page_parts_moving'					=> '物件の契約に必ず必要になる「引っ越し」。お客様は引っ越しのプロではないため、不明な手順を確認します。その時に重要なページです。',
			'page_parts_links'					=> '役所や行政の機関など、契約〜引っ越しなど、「お客様の生活」に関するサイトのご紹介を入れてください。<br>近隣情報は「街情報」、学校情報は「学区情報」の方に掲載をお薦めします。',
			'page_parts_infodetail'				=> '自社からお客様に案内する「お知らせ」の詳細を入れてください。',

			'page_parts_mainimage'	=> 'トップページに大きく使うイメージを掲載します。「画像の追加」の部分をクリックして、表示させる画像を選択してください。',
			'page_parts_infolist'		=> 'トップページに表示させる、お客様へのお知らせを掲載する部分の名前と、表示する見出しの件数を制御してください。<br>プレビュー時には現在公開中のお知らせのみ表示されます。',
			'page_parts_text'			=> '文字情報を入れる要素になります。<br>文章に対しての「見出し」と「本文」を入れてください。',
			'page_parts_list'			=> '箇条書きで情報を入れる要素です。<br>箇条書きに対しての「見出し」と、「箇条書きの内容」を入れてください。',
			'page_parts_table'		=> '表組みの情報をいれる要素です。<br>表全体を表す「見出し」、また表の左側は「ラベル」、右側が「表示項目内容」を入れてください。',
			'page_parts_map'			=> 'Google Mapをページに埋め込むことができる要素です。<br>「中心位置」の部分に住所をいれ検索するか、ピンをクリックして任意の場所に配置してください。',
			'page_parts_image'		=> '画像をページに埋め込むことができる要素です。<br>画像に対しての「見出し」と、差し込む「画像」をお選びください。',
			'page_parts_youtube'		=> 'YouTubeの動画をページに埋め込むことができる要素です。<br>動画に対しての「見出し」と、差し込むyoutubeの埋め込みコードをお選びください。<br>「埋め込みコード」はYouTubeの動画の下にある「共有」部分から取得できます。',
            'page_parts_panorama'	=> '「アットホーム　VR内見・パノラマ」の埋め込みコードを登録することで、パノラマ画面を表示できます。',
			'page_parts_lists'			=> '箇条書きで情報を入れる要素です。<br>箇条書きに対しての「見出し」と、「箇条書きの内容」を入れてください。',

	        //CMSテンプレートパターンの追加
			'page_parts_companystrength'	=> '貴店の思いや強みをホームページへ訪れたユーザーへアピールします。',
			'page_parts_businesscontent'	=> '自社の事業内容の詳細を記載します。事業内容ごとに項目を追加します。',
			'page_parts_row_setting'	=> '入力した業務内容のパソコンサイトでの表示列数を選択します。　※スマートフォンサイトでは列設定に関わらず1列表示されます。', // 業務内容列設定
			'page_parts_columndetail'	=> 'このブロックはコラム一覧とコラム詳細上部に表示されます。コラム一覧にはリード文の50文字まで表示されます。', // コラム詳細
			'page_parts_purchasingrealestate'			=> false,
			'page_parts_replacementloanmortgageloan'	=> false,
			'page_parts_replacementaheadsale'			=> false,
			'page_parts_buildingevaluation'				=> false,
			'page_parts_buyervisitsdetachedhouse'		=> false,
			'page_parts_pointssaleofcondominium'		=> false,
			'page_parts_chooseapartmentordetachedhouse'	=> false,
			'page_parts_newconstructionorsecondhand'		=> false,
			'page_parts_erectionhousingorderhouse'	=> false,
			'page_parts_purchasebesttiming'			=> false,
			'page_parts_lifeplan'					=> false,
			'page_parts_typesmortgageloans'			=> false,
			'page_parts_fundingplan'				=> false,
			'page_parts_troubledleasingmanagement'	=> false,
			'page_parts_leasingmanagementmenu'		=> false,
			'page_parts_measuresagainstvacancies'	=> false,
			'page_parts_houseremodeling'			=> false,
			'page_parts_considerslandutilizationowner'	=> false,
			'page_parts_utilizingland'				=> false,
			'page_parts_purchaseinheritancetax'		=> false,
			'page_parts_upperlimit'					=> false,
			'page_parts_rentalinitialcost'		=> false,
			'page_parts_squeezecandidate'		=> false,
			'page_parts_unuseditemsandcoarsegarbage'		=> false,
			'page_parts_comfortablelivingresidentrules'		=> false,
			'page_parts_storesearch'				=> false,
			'page_parts_shopsuccessbusinessplan'	=> false,
	        //CMSテンプレートパターンの追加

			// 物件リクエスト
			'page_form_parts_requestlivinglease'	=> '物件リクエストを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_requestofficelease'	=> '物件リクエストを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_requestlivingbuy'	=> '物件リクエストを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_requestofficebuy'	=> '物件リクエストを受け付けるフォームの設問項目を設定していただきます。',

			// @todo 物件コマ
			'page_parts_estatekoma'		=> '特集設定で作成した特集を物件コマとして表示します。<br>表示したい特集を選択し、表示方法・表示順を選択します。<br>特集は「基本設定」にある「特集設定」で作成します。',
			'page_parts_estatekomasearcher'=> '検索エンジンレンタルの特集コマを表示させることができます。',
			'page_parts_estatekoma_rows'=> 'パソコンサイトは１行に4物件表示します。<br>スマートフォンサイトは選択した行数に関わらずすべて1行で表示します。',
            'page_parts_estatekomasearcher_heading' => '特集コマの上部に見出しを表示させたい場合は入力してください。<br>（例）○○市のペット相談可物件特集',
            'page_parts_estatekomasearcher_htmltag' => '検索エンジンレンタルのコントロールパネルにて特集コマのHTMLタグを取得し、貼り付けてください。',
			'page_parts_slide_show_title' => '複数の画像を自動的に切替えて表示する機能です。画像を2枚以上いれた場合に「有効」にできます。',
			
			'page_side_parts_link'		=> 'リンクをいれることが出来ます。<br>リンク部分に対しての「見出し」と「リンク」を入れることが出来ます。<br>リンクはホームページの中のページにも外部のページにも設置可能です。',
			'page_side_parts_image'	=> '画像をページに埋め込むことができる要素です。<br>画像に対しての「見出し」と、差し込む「画像」をお選びください。',
			'page_side_parts_text'	=> '文字情報を入れる要素になります。<br>文章に対しての「見出し」と「本文」を入れてください。',
			'page_side_parts_qr'		=> 'QRコードを入れる要素になります。<br>基本設定→初期設定の画面の「QRコード」で設定したQRコードが表示されます。',
			'page_side_parts_fb'		=> 'Facebookのタイムラインを入れる要素になります。<br>基本設定→初期設定の画面の「SNS」で設定したFacebookアカウントのタイムラインが表示されます。',
			'page_side_parts_tw'		=> 'Twitterのタイムラインを入れる要素になります。<br>基本設定→初期設定の画面の「SNS」で設定したTwitterアカウントのタイムラインが表示されます。',
			'page_side_parts_map'	=> 'Google Mapをページに埋め込むことができる要素です。<br>「中心位置」の部分に住所をいれ検索するか、ピンをクリックして任意の場所に配置してください。',
            'page_side_parts_lineatqr'	=> 'LINE公式アカウントの「友だち追加」QRコードを入れる要素になります。<br>基本設定→初期設定の画面の「LINE公式アカウント 友だち追加QRコード」で設定したQRコードが表示されます。',
            'page_side_parts_lineatbtn'	=> 'LINE公式アカウントの「友だち追加」ボタンを入れる要素になります。<br>基本設定→初期設定の画面の「LINE公式アカウント 友だち追加ボタン」で設定したQRコードが表示されます。',
            'page_side_parts_panorama'	=> '「アットホーム　VR内見・パノラマ」の埋め込みコードを登録することで、パノラマ画面を表示できます。',

			'page_form_parts_contact'		=> '会社へのお問い合わせを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_document'		=> '会社へのお問い合わせを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_assessment'	=> 'お客様からの不動産の査定依頼を受け付けるフォームの設問項目を設定していただきます。',

			'page_form_parts_officebuy'		=> '物件のお問い合わせを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_livinglease'	=> '物件のお問い合わせを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_officelease'	=> '物件のお問い合わせを受け付けるフォームの設問項目を設定していただきます。',
			'page_form_parts_livingbuy'		=> '物件のお問い合わせを受け付けるフォームの設問項目を設定していただきます。',

			'page_parts_searchfreewordenginerental'				=> '検索エンジンレンタルの物件を検索するためのパーツを表示させることができます。',
			'page_parts_searchfreewordenginerental_html_tag'		=> '検索エンジンレンタルのコントロールパネルにて、フリーワード検索パーツのHTMLタグを取得し、貼り付けて下さい。',
			'page_parts_searchfreewordenginerental_heading'		=> '見出しを表示させたい場合は入力してください。<br/>（例）フリーワード検索',
			'page_parts_freeword_heading'		=> '物件のフリーワード検索を入れる要素になります。<br/>設定することで、物件検索のフリーワード入力欄が表示されます。',

			'estate_form_notification_to'			=> 'ホームページから物件のお問い合わせを受信するアドレスになります。',

			'form_notification_to'			=> 'お問い合わせがあった際に、お問い合わせがあった旨の通知メールを送る宛先のメールアドレスを登録してください。',
			'form_notification_subject'	=> 'お問い合わせがあった際に、お問い合わせがあった旨の通知メールの件名を登録してください。',
			'form_autoreply_flg'			=> 'お問い合わせがあった際に、お客様にお問い合わせを受領した旨の返信メールを自動で送信するか否かの設定が行えます。',
			'form_autoreply_from'		=> 'お問い合わせがあった際に、お客様にお問い合わせを受領した旨の返信メールの差出人＝御社のメールアドレスが登録できます。<br>このメールに返信される可能性もあるので、実際に使っているアドレスを入れてください。',
			'form_autoreply_sender'		=> 'お問い合わせがあった際に、お客様にお問い合わせを受領した旨の返信メールの差出人名の名前が登録できます。<br>例：アットホーム：佐藤',
			'form_autoreply_subject'		=> 'お問い合わせがあった際に、お客様にお問い合わせを受領した旨の返信メールの件名が登録できます。',
			'form_autoreply_body'		=> 'お問い合わせがあった際に、お客様にお問い合わせを受領した旨の返信メールの本文が登録できます。',
			
			
			'rating_total'		=> '総合評価とは<br>更新頻度や各項目に対しての登録有無、登録ページ数に応じて評価点を算出しております。<br>複数ページが作成できる項目は上限点数が決められております。<br>各項目もれなく登録して、より多くのページを作成することで評価点をあげていきましょう。',
			'rating_update'		=> '更新とは<br>前回公開更新した日付となります。メーターは更新頻度が多ければ右を差しますので、なるべく右を差すように更新頻度を増やしましょう。',
			'rating_page'		=> '作成したページ数や種類から総合的に評価します。<br>様々なページを作成することで評価があがります。<br>「ページの作成/更新」にて「未作成」となっているページを作成していきましょう。<br>※評価に関わる配点はプラン共通です。<br>ページテンプレート数が少ないスタンダード／ライトプランの<br>評価は最大値「5」には達しません。',
			'rating_function'	=> '機能設定とは<br>本ツールでの登録状況となります。メーターは、全体の登録数が多ければ右を差しますので、なるべく右を差すように登録を増やしましょう。',
			'analysis_total'	=> 'アクセス状況 総合評価とは<br>自社サイトが、どれぐらいのページビュー、実際のお客様の数が来ているのかを表示しています。<br>Google Analyticsのデータを元にして表示をしています。<br>サイトのコンテンツを充実させて、アクセス数アップを目指してください。',

			'estate_search_setting-search_type'	=> 'ユーザの検索方法を設定します。',
			'estate_search_setting-enabled_estate_type'	=> '公開させたい物件種目を選択します。',
			'estate_search_setting-pref'	=> '公開させたい物件の都道府県を選択します。',
			'estate_search_setting-estate_request_flg'	=> '物件一覧の絞り込み条件選択箇所の下部や該当する物件がない場合にリンクが表示されます。チェックを入れた場合は「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。<br>※該当種別で組まれた特集の一覧にも表示されます。',

			'second_estate_search_setting-enabled'	=> 'この物件種別で2次広告自動公開を使用するか設定します。',
			'second_estate_search_setting-search_type'	=> '2次広告自動公開物件を取り込む対象を選択します。',
			'second_estate_search_setting-enabled_estate_type'	=> '2次広告自動公開物件を取り込む物件種目を選択します。',
			'second_estate_search_setting-pref'	=> '2次広告自動公開自動物件を取り込む都道府県を選択します。',
			
			'estate_special-title'	=> '特集名を設定します。',
			'estate_special-comment'	=> '特集ページに表示させる紹介コメントを設定します。',
			'page_parts_slide_show_title' => '複数の画像を自動的に切替えて表示する機能です。画像を2枚以上いれた場合に「有効」にできます。',
            'access_log_inquiry' => '対象となる問い合わせフォームは以下となります。<br>アドバンス：お問い合わせ・資料請求・売却査定・物件リクエスト・物件問合わせ <br>スタンダード：お問い合わせ・物件問合わせ<br>ライト：お問い合わせ',
            //4396: change text
            'estate_search_setting-display_fdp' => '不動産データプロ・エリア情報プランをご契約の場合、物件詳細ページに表示する周辺エリア情報を任意で選択できます。',
            'estate_search_setting-display_freeword' => '物件検索の絞り込み画面や物件一覧で基本の設定項目に追加して、エンドユーザがフリーワードで絞り込み条件を入力する欄が追加されます。',
            'site_map' => 'メインメニュー内に配置（移動）させたい場合はメインメニュー内「追加」のボタンをクリックし、「既存ページを追加」より追加します。',
            'display_house_title' => '現在設定されている物件の建物名、間取り、所在地等が表示されます。<br>※物件種目や物件登録の内容によって表示される内容は異なります。',
            // 5444
            'page_structure' => '作成上限数に達した大カテゴリーおよび小カテゴリーには「追加」ボタンが表示されません。',
            'large_category' => '配下の記事と合わせて作成、公開する必要があります。ひな形5種のほか、オリジナルカテゴリーを5ページ作成できます。',
            'small_category' => '配下の記事と合わせて作成、公開する必要があります。ひな形のほか、同一大カテゴリー内にオリジナルカテゴリーを20ページ作成できます。',
            'article_category' => '追加ボタンから記事を選択できます。上位のカテゴリーと合わせて作成、公開する必要があります。',
            'set_link_article' => 'サイドコンテンツ（全ページ共通）に表示させるリンクの表示方法を選択します。サイドコンテンツに表示させたくない場合は、トップページ編集画面＞サイドコンテンツ（全ページ共通）＞「不動産お役立ち情報」要素にて非表示の設定をしてください。',
            'set_link_article_1' => '小カテゴリーを折りたたんだ状態で表示します。 記事へのリンクは表示されません。',
            'set_link_article_2' => '小カテゴリーを展開した状態で表示します。 記事へのリンクは表示されません。',
            'set_link_article_3' => '記事を折りたたんだ状態で表示します。',
            'set_link_article_4' => '記事を展開した状態で表示します。',
            'set_link_article_5' => '記事のみを表示します。',
            'category_classification_legend' => '「トップ」は不動産お役立ち情報ページ、「大」は大カテゴリーページ、「小」は小カテゴリーページ、「記」は記事ページを示しています。',
            'tdk_filename_article_top' => 'ここで入れた英語記述が当該ページのアドレスになります。このページの「ページ名」はシステムの都合上変更できません。',
            'tdk_filename_article' => 'ここで入れた英語記述が当該ページのアドレスになります。<br>ページの作成/更新（不動産お役立ち情報）内のカテゴリーページ・記事ページで利用しているまたは初期設定値として利用されるページ名は登録できません。<br>また、以下の記述はシステム上使用することが出来ませんのでご了承ください。<br>404notFound/all/api/article<br>baibai-jigyo-1/baibai-jigyo-2<br>baibai-kyoju-1/baibai-kyoju-2<br>chintai/chintai-jigyo-1<br>chintai-jigyo-2/chintai-jigyo-3<br>complete/confirm/edit/error<br>file2s/files/howtoinfo/images<br>index/inquiry/kasi-office/kasi-other<br>kasi-tenpo/kasi-tochi/kodate/mansion<br>parking/pc/personal/search<br>sitemap/sp/sp-（任意）/top<br>uri-office/uri-other/uri-tenpo/uri-tochi<br>validate',
            'modal_publish_option' => '「公開（更新）する」か「非公開（下書き）にする」かを選択してください。<br>「不動産お役立ち情報」で作成できるページは「日時指定」での公開/停止はできません。<br>「非公開（下書き）にする」が非活性の場合、「共通設定」の変更を反映する必要があります。<br>公開中かつ修正アイコンがついたページを公開することで「非公開（下書き）にする」が選択できます。',
            'modal_publish_page' => '公開（または非公開に）するカテゴリー・記事を選択してください。',
            'modal_publish_page_article_top' => 'カテゴリーページおよび記事を公開（または非公開）にする場合、「不動産お役立ち情報」ページの公開（または非公開）が必要です。',
            // ATHOME_HP_DEV-5457
            'page_list_title' => '公開中または下書きのページが対象となります。',
            'page_list_update_date' => '最後にページを保存した日付です。<br>※「サイトの公開/更新」でページを公開または更新した日付ではありません。',
            'search_special_label' => 'ライトプランの場合は物件検索設定機能が付いていないため、対象となるページはありません。',

            // ATHOME_HP_DEV-5622
            'log_edit_operation_date' => '・担当者ＣＤ・会員No・会社名いずれの値も指定していない場合、操作日時で指定できる期間は以下とする<br>&nbsp;&nbsp;代行ログイン操作ログ／代行作成操作ログ／公開処理ログ：最大31日間（一か月）<br>&nbsp;&nbsp;会員操作ログ：最大14日間（２週間）<br><br>・担当者ＣＤ・会員No・会社名いずれかで絞り込みを行っている場合、操作日時で指定できる期間は以下とする<br>&nbsp;&nbsp;いずれのログも最大180日間（約6ヶ月）',
	);


    public function toolTip($key, $position = 'left')
    {
    	$message = $this->getMessage($key);
    	if ($message === false) {
    		return '';
    	}
    	$tooltip =
            '<a class="tooltip '.$position.'" href="javascript:;">' .
                '<i class="i-s-tooltip"></i>'.
                '<div class="tooltip-body">' .
					$message .
                '</div>' .
            '</a>';

    	// LINE公式アカウントの友達追加ボタンのツールチップは画像付き
        if($key=='page_side_parts_lineatbtn' || $key=='page_side_parts_lineatqr'){
            $tooltip = $this->getCustomTooltipLineAt($key,$message,$position);
        }
        return $tooltip;
    }
    
    public function getMessage($key) {
    	return isset(self::$_tooltips[$key]) ? self::$_tooltips[$key] : $key;
    }

    // LINE公式アカウントの友達追加ボタンのツールチップは画像付き
    private function getCustomTooltipLineAt($key,$message,$position)
    {
        $imagePathMap= [];
        $imagePathMap['page_side_parts_lineatbtn'] = "../images/page-edit/line_balloon.png";
        $imagePathMap['page_side_parts_lineatqr']  = "../images/page-edit/line_balloon_qr.png";

        $tooltip =
            '<a class="tooltip '.$position.'" href="javascript:;">' .
                '<i class="i-s-tooltip"></i>'.
                '<div class="tooltip-body">' .
                    $message .
                    '<img src="'.$imagePathMap[$key].'">'.
                '</div>' .
            '</a>';
        return $tooltip;

    }

}