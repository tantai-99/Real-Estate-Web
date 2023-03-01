<?php
namespace Library\Custom\Model\Lists;

class TopOriginalMsg
{
	public static $MESSAGES = array(
        'hp.no_current' => 'No Current HP',
        'hp.no_hp_setting' => 'No Current HP Settings',
        'submit_text' => '設定',
        'select_placeholder_text' => '（未選択）',
        'cms_disable' => 'CMS変更不',

        'error' =>'入力に誤りがあります。',
        'global_navigation.success' => '設定を保存しました',
        'global_navigation.setting.title' => 'グロナビの設定',
        'global_navigation.setting.column_1' => '設定中のグロナビ',
        'global_navigation.setting.column_2' => 'グロナビ数の変更',
        'global_navigation.setting.submit' => 'Submit',
        'global_navigation.setting.read' => '制作代行CMSの特集設定読込',
        'global_navigation.setting.read_glonavi' => '制作代行CMSのグロナビ設定読込',
        'global_navigation.glonavi_title_pc' => 'グロナビタグ（PC）',
        'global_navigation.glonavi_title_sp' => 'グロナビタグ（スマホ）',
        'global_navigation.read_menu_name' => '制作代行CMSの設定読込',


        'special_estate.setting.error' => 'システムエラーが発生しました。',
        'special_estate.setting.success' => '設定を保存しました',
        'special_estate.setting.loaded' => '読み込みが完了しました',
        'special_estate.setting.public' => '公開中',
        'special_estate.setting.not_public' => '下書き',
        'special_estate.setting.read_specials' => '制作代行CMSの特集設定読込',
        'special_estate.setting.read_specials.success' => '読み込みが完了しました',
        'special_estate.setting.save_specials' => '表示制御設定の保存',
        #3603
        //'special_estate.thead.col_1' => 'CMS表示制御',
        'special_estate.thead.col_1' => '制作代行/CMS表示制御',
        'special_estate.thead.col_2' => 'オリジナルタグ',
        'special_estate.thead.col_3' => '最終更新',
        'special_estate.thead.col_4' => '表示制御',
        'special_estate.thead.col_5' => 'ファイルアップロード',
        'special_estate.publish_status.public' => '公開中',
        'special_estate.publish_status.not_public' => '下書き',

        'special_estate.cms_editable' => 'CMS変更不可',
        #3603
        //'special_estate.display_flg' => 'CMS表示',
        'special_estate.display_flg' => '制作代行/CMS表示',

        'notification_settings.submit' => 'お知らせ設定保存',
        'notification_settings.title' => '',
        'notification_settings.news_column_1' => '設定中の表示件数',
        'notification_settings.news_column_2' => '表示件数の変更',
        'notification_settings.news_title' => 'dummy title',
        'notification_settings.news.cms_editable' => 'CMS変更不可',
        // create form
        'notification_settings.create' => 'カテゴリー',
        'notification_settings.update' => 'カテゴリー編集',
        'notification_settings.update.button' => '編集',
        'notification_settings.update.submit' => '変更',
        'notification_settings.delete' => 'カテゴリー削除',
        'notification_settings.delete.button' => '削除',
        'notification_settings.delete.submit' => '削除',
        'notification_settings.delete.cancel' => '閉じる',


        'notification_settings.row.title' => 'カテゴリー名',
        'notification_settings.row.class' => 'class名',
        'notification_settings.row.parent_page_id' => '設定お知らせ',

        'notification_settings.create.button' => '追加',
        'notification_settings.create.type_1' => 'お知らせ1',
        'notification_settings.create.type_2' => 'お知らせ2',

        'notification_settings.create.success' => 'Success create',
        'notification_settings.create.error' => 'Error create',

        'notification_settings.update.success' => 'Success update',
        'notification_settings.update.error' => 'Error update',


        'notification_settings.settings.success' => '設定を保存しました',
        'notification_settings.settings.error' => '入力に誤りがあります。',

        'notification_settings.title.required' => 'カテゴリー名が未入力です。ご入力ください',


        'notification_settings.class.invalid_format' => '半角英数字でご入力ください（ハイフンとアンダースコアは使用できます）',
        'notification_settings.class.required' => 'class名が未入力です。半角英数字でご入力ください',
        'notification_settings.class.placeholder' => '半角英数のみ',
        'notification_settings.class.is_numeric' => '数字(0〜9)、2つのハイフン(--)、ハイフン＋数字(-[0〜9]) から始まるclass名は使用できません',
        'notification_settings.class.invalid' => '同じclass名は登録できません。<br> class名の重複を防ぐために、<br>・お知らせ1のclass名には必ず「1」をつけてください <br>・お知らせ2のclass名には必ず「2」をつけてください',


        'notification_settings.list_1.title' => 'おしらせ1  カテゴリー一覧',
        'notification_settings.list_2.title' => 'おしらせ2  カテゴリー一覧',

        'notification_settings.table.1.title' => '',
        'notification_settings.table.1.column_1' => '',
        'notification_settings.table.1.column_2' => '',
        'notification_settings.table.1.button_1' => '',
        'notification_settings.table.1.button_1.title' => '',
        'notification_settings.table.1.button_2' => '',
        'notification_settings.table.2.title' => '',
        'notification_settings.table.2.column_1' => '',
        'notification_settings.table.2.column_2' => '',
        'notification_settings.table.2.button_1' => '',
        'notification_settings.table.2.button_2' => '',
        'back_to_top_detail' => 'TOPオリジナル編集へ戻る﻿',
        'back_to_list_file_edit' => '編集ファイル一覧に戻る﻿',
        'preview_text' => 'プレビュー',

        // list file edit
        'list_file_edit.upload' => 'ファイル選択',
        'list_file_edit.submit' => 'ファイルアップロード',
        'list_file_edit.back' => '初期状態に戻す',
        'list_file_edit.remove' => '削除',
        'list_file_edit.name' => '名前',

        'list_file_edit.date' => '作業更新日',
        'list_file_edit.warning_title' => '注：',
        'list_file_edit.warning_title_change_key' => '注%s：',
        'list_file_edit.warning_html' => 'このフォルダには、5MB以下のHTMLファイル・ico・pdf・xls・xlsx・doc・docx・ppt・pptxをアップロードできます。',
        'list_file_edit.warning_css' => 'このフォルダには、5MB以下のcssファイル・ico・pdf・xls・xlsx・doc・docx・ppt・pptxをアップロードできます。',
        'list_file_edit.warning_js' => 'このフォルダには、5MB以下のjsファイル・ico・pdf・xls・xlsx・doc・docx・ppt・pptxをアップロードできます。',
        'list_file_edit.warning_image' => 'このフォルダには、5MB以下のjpg・gif・png・ico・pdf・xls・xlsx・doc・docx・ppt・pptxをアップロードできます。',
        'list_file_edit.warning_2' => 'ファイルのダウンロードはできません。',
        'list_file_edit.warning_3' => 'まとめてアップロードできる容量は、合計8MBまで/最大20ファイルです。',
        'list_file_edit.warning.koma_1' => '特集を追加すると「special＊＊_pc.html」「special＊＊_sp.html」（＊＊は特集のID）というHTMLが自動的に追加されます。',
        'list_file_edit.warning.koma_2' => 'このフォルダの特集コマ用に自動生成された「special＊＊_pc.html」「special＊＊_sp.html」のファイルは削除できません',
        'list_file_edit.warning.koma_3' => '特集コマ用のHTMLとして利用するため、上記に掲載されている「special＊＊_pc.html」「special＊＊_sp.html」以外のファイル名はこのフォルダにはアップできません。',

    );
}