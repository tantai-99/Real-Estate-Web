<?php
namespace App\Repositories;

use Library\Custom\Mail;
use Carbon\Carbon;

abstract class CountRepository extends BaseRepository {
    // 桁数
	const COMPANY_ID = 10;
	const PAGE_TYPE_CODE = 2;
	const ESTATE_NUMBER = 10;
	const SECOND_ESTATE_FLG = 1;
	const SPECIAL_ID = 10;
	const RECOMMEND_FLG = 1;
	const FROM_SEARCHMAP = 1;
	const DEVICE = 2;
	const PERIPHERAL_FLG = 1;

	public function getCount( $companyId, $satrtDate = null, $endDate = null, $pageTypeCode = null )
	{
		$select = $this->model->selectRaw('count(*) as `count`') ;
		$select->where( 'company_id', $companyId	) ;
		$select->where( 'recieve_date',	'>=', $satrtDate	) ;
		$select->where( 'recieve_date',	'<=', $endDate		) ;
		if( !is_null( $pageTypeCode ) )
		{
			$select->where( 'page_type_code', $pageTypeCode ) ;
		}
		$row = $select->first() ;
		
		return $row[ 'count' ] ;
	}
	
	/*
	 * サマリ状況を取得
	 *
	 * @param	int		$company_id
	 * @param	string	$baseMonth
	 *
	 */
	public function getSummary( $company_id, $baseMonth = "", $data = array() )
	{
		$nowDate  = Carbon::now();
		if( $baseMonth == "" )
		{
			$baseMonth = $nowDate->format('Y-m') ;
		}
		
		// 取得月
		$baseMonth		= ( new Carbon($baseMonth))->format('Y-m') ;
		$prevMonth		= ( new Carbon($baseMonth))->subMonth(1)->format('Y-m') ;
		$prevYearMonth	= ( new Carbon($baseMonth))->subMonth(12)->format('Y-m') ;
		$monthList		= array( $baseMonth, $prevMonth, $prevYearMonth ) ;
		
		$base_cnt		=  $this->_getCountForMonth( $company_id, $baseMonth		) ;			// 当月
		$prev_cnt		=  $this->_getCountForMonth( $company_id, $prevMonth		) ;			// 前月
		$prev_year_cnt	=  $this->_getCountForMonth( $company_id, $prevYearMonth	) ;			// 1年前
		
		if( ( $base_cnt == null ) && ( $prev_cnt == null ) && ( $prev_year_cnt == null ) )
		{
			return array() ;
		}
		
		@$data[ 'prev-year-month'	] += $prev_year_cnt	->count ;
		@$data[ 'prev-month'		] += $prev_cnt		->count ;
		@$data[ 'base-month'		] += $base_cnt		->count ;
		
		return $data ;
	}

	/*
	* 不正なリクエストを管理者あてにメール送信する
	*
 	* @param array $companyObj
	*/
	protected function sendMail($companyObj)
	{
		$publishConfig = getConfigs('publish')->publish;
		$this->_envJp = $publishConfig->env_jp;
		$this->_mailFrom = $publishConfig->mail_from;
		$this->_mailTo = $publishConfig->mail_tos;
		$subject = '【' . $this->_envJp . '環境】不正なお問い合わせリクエストを検出しました';
		$body = '会員番号：' . $companyObj->member_no . "\n" . '会社名：' . $companyObj->company_name;
		try {
			// $mail = new \Library\Custom\Mail('ISO-2022-JP');
			$mail = new Mail();
			// $mail->setBodyText(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"), null, Zend_Mime::ENCODING_7BIT);
			$mail->setBody(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"));
			$mail->setFrom($this->_mailFrom);
			$mail->addTo($this->_mailTo);
			// $mail->setSubject(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8"));
			$mail->setSubject($subject);
			$mail->send();
		} catch (\Exception $e) {
			throw new \Exception('メール送信に失敗しました。');
		}
	}

	/**
	 * 桁数チェック
	 * @param array $data DBに書き込まれるデータ
	 * @param array $apiData リクエストから送られてきたデータ
	 * @param string $name 問い合わせログの名称
	 * @return boolean
	 */
	protected function isValidate($data, $apiData, $name) {
		$requests = array();
		$company_id = mb_strlen($data['company_id']) <= self::COMPANY_ID ? 1 : 0;
		$page_type_code = mb_strlen($data['page_type_code']) <= self::PAGE_TYPE_CODE ? 1 : 0;
		$device = mb_strlen($data['device']) <= self::DEVICE ? 1 : 0;
		switch ($name) {
			case 'contact_count':
				array_push($requests, $company_id, $page_type_code, $device);
				break;
			case 'estate_contact_count':
				$estate_number = mb_strlen($data['estate_number']) <= self::ESTATE_NUMBER ? 1 : 0;
				$second_estate_flg = mb_strlen($data['second_estate_flg']) <= self::SECOND_ESTATE_FLG ? 1 : 0;
				$special_id = mb_strlen($data['special_id']) <= self::SPECIAL_ID ? 1 : 0;
				$recommend_flg = mb_strlen($data['recommend_flg']) <= self::RECOMMEND_FLG ? 1 : 0;
				$from_searchmap = mb_strlen($data['from_searchmap']) <= self::FROM_SEARCHMAP ? 1 : 0;
				$peripheral_flg = mb_strlen($data['peripheral_flg']) <= self::PERIPHERAL_FLG ? 1 : 0;
				array_push($requests, $company_id, $page_type_code, $estate_number, $second_estate_flg, $special_id, $recommend_flg, $from_searchmap, $device, $peripheral_flg);
				break;
			default:
				return false;
		}
		if (in_array(0, $requests, true)) {
			return false;
		}
		if ($apiData['device'] != 'pc' && $apiData['device'] != 'sp') {
			return false;
		}
		return true;
	}
	
	/*
	 * 年月で件数を取得
	 *
	 * @param	int		$company_id
	 * @param	string	$month				yyyy-mm
	 *
	 */
	private function _getCountForMonth( $company_id, $month )
	{
		$select = $this->model->selectRaw('count(*) as `count`')	;
		$select->where( 'company_id', $company_id	) ;
		$select->whereRaw( "DATE_FORMAT( recieve_date, '%Y-%m' )	= '".$month."'"	) ;
		return $this->fetchRow( $select ) ;
	}
}