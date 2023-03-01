<?php
namespace Library\Custom\Model\Lists;

class LogCsvHeader extends ListAbstract {

	/**
	 * データ取得用のヘッダー名一覧
	 */
	public static function getCsvHeader() {

		return array(
			'member_no',
			'member_name',
            'contract_type',
			'athome_staff_id',
			'datetime',
			'page_id',
			'edit_type_code',
            'user_ip'
		);
	}

	/**
	 * CSV表示用ヘッダー名一覧
	 */
	public static function getCsvHeaderName() {

		return array(
			'会員ＮＯ',
			'会社名',
            '契約種別',
			'担当者ＣＤ',
			'操作日時',
			'操作内容',
            'ユーザーＩＰ'
		);
	}
}
