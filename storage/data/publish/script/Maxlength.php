<?php

class Maxlength {

	public static $contactMap = array(
		// 'subject_memo'						=> 2000,	// お問い合せ内容の備考	subject_more_item_keyで動的に生成しているので紐付け不可
		'request'							=> 2000,	// リクエスト内容
		// 'request_memo'						=> 2000,	// リクエスト内容の備考	request_more_item_keyで動的に生成しているので紐付け不可
		'person_name'						=> 40,		// お名前
		'person_mail'						=> 150,		// メール
		'person_tel1'						=> 5,		// 電話番号1
		'person_tel2'						=> 4,		// 電話番号2
		'person_tel3'						=> 4,		// 電話番号3
		'person_other_connection'			=> 50,		// その他の連絡方法
		'person_time_of_connection'			=> 50,		// 希望連絡時間帯
		'person_address'					=> 250,		// 住所
		'person_age'						=> 3,		// 年齢
		'person_number_of_family'			=> 2,		// 世帯人数
		'person_annual_incom'				=> 9,		// 年収
		'person_job'						=> 30,		// 職業
		'person_office_name'				=> 140,		// 勤務先名
		'person_own_fund'					=> 9,		// 自己資金
		'property_address'					=> 250,		// 物件の住所
		'property_exclusive_area'			=> 50,		// 専有面積
		'property_building_area'			=> 50,		// 建物面積
		'property_land_area'				=> 50,		// 土地面積
		'property_number_of_house'			=> 9,		// 総戸数
		'property_age'						=> 4,		// 築年数
		'property_hope_layout'				=> 75,		// ご希望の間取り
		'property_budget'					=> 9, 		// 予算（万円）
		'property_item_of_business'			=> 2000,	// 種目
		'property_area'						=> 2000,	// エリア（沿線・駅）
		'property_school_disreict'			=> 2000,	// ご希望の学区
		'property_rent_price'				=> 2000,	// 賃料
		'property_price'					=> 2000,	// 価格(□万円～□万円)
		'property_request_layout'			=> 2000,	// 間取り
		'property_square_measure'			=> 2000,	// 面積
		'property_request_building_area'	=> 2000,	// 建物面積
		'property_request_land_area'		=> 2000,	// 土地面積
		'property_request_age'				=> 2000,	// 築年数
		'property_other_request'			=> 2000,	// その他ご希望
		'company_name'						=> 140, 	// 貴社名
		'company_business'					=> 150, 	// 事業内容
		'company_person'					=> 75, 		// ご担当者様名
		'company_person_post'				=> 75,		// ご担当者様役職
		'memo'								=> 2000,	// 備考
		'free_1'							=> 2000,	// 自由項目1
		'free_2'							=> 2000,	// 自由項目2
		'free_3'							=> 2000,	// 自由項目3
		'free_4'							=> 2000,	// 自由項目4
		'free_5'							=> 2000,	// 自由項目5
		'free_6'							=> 2000,	// 自由項目6
		'free_7'							=> 2000,	// 自由項目7
		'free_8'							=> 2000,	// 自由項目8
		'free_9'							=> 2000,	// 自由項目9
		'free_10'							=> 2000,	// 自由項目10
    );

}