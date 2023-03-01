<?php
namespace Library\Custom\Analysis;

use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use App\Repositories\ContactCount\ContactCountRepositoryInterface;
use Illuminate\Support\Facades\App;

use Carbon\Carbon;
use DateTime;
/**
 * アクセスログ：アクセス情報
 *
 */
class General extends AbstractAnalysis
{
	static private $_periodMonthDefault = 3; //3ヶ月分

    public function init($companyId, $accessToken=null, $baseMonth=null, $periodMonth=null) {
		$this->_pageviews = array();
		$this->_visits = array();
		$this->_visitors = array();
		$this->_contactCount = array();
		
		$this->_periodMonth = (is_null($periodMonth)) ? self::$_periodMonthDefault : $periodMonth ;
		$this->setBaseMonth($baseMonth);
		parent::init($companyId, $accessToken);
	}

    public function initBatch($companyId, $accessToken=null, $baseMonth=null, $periodMonth=null) {
		$this->_pageviews = array();
		$this->_visits = array();
		$this->_visitors = array();
		$this->_contactCount = array();
		
		$this->_periodMonth = (is_null($periodMonth)) ? self::$_periodMonthDefault : $periodMonth ;
		$this->setBaseMonth($baseMonth);
    	$this->_companyId = $companyId;
	}


    public function setPeriodMonth($periodMonth) { 
    	$this->_periodMonth = $periodMonth;
    }

	
    public function setBaseMonth($baseMonth) { 
	    $this->_baseMonth = $baseMonth;
	    $this->_prevMonth = (new Carbon($baseMonth))->subMonth()->format('Y-m');
		// 期間(基軸月の3ヶ月前の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$this->_startDate = (new Carbon($baseMonth))->subMonth($this->_periodMonth-1)->format('Y-m-01');
		$this->_endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d');
		$this->_periodDateList = $this->getPeriodDateList($this->_startDate,$this->_endDate);
    }
    

    public function initData() { 

		//ディメンション(dimensions)
		$dimensionsList = array(
			'year',
			'month',
		);

		//ソート(sort)
		$sortList = array(
			'dimension' => [
				'year' => false,
				'month' => false,
			]
		);
		
		//メトリクス(metrics)
		$metricsList = array(
			'screenPageViews',				// ページビュー数
			'sessions',				// セッション数
			'totalUsers',		// ユーザー数
		);

		// Googleアナリティクスからデータを取得する
		$gaResult = $this->getGaData($this->_startDate,$this->_endDate,$dimensionsList, $sortList, $metricsList);
		if(!isset($gaResult['rows']) || is_null($gaResult['rows'])){
			return array();
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		foreach( $gaResult['rows'] as $gaRowKey => $gaRow){
				$year = $gaRow['dimensionValues'][$this->getRowKey('year', $dimensionsList)]['value'];
				$month = $gaRow['dimensionValues'][$this->getRowKey('month', $dimensionsList)]['value'];
				$yearMonth = $year."-".$month;
				$this->_pageviews[$yearMonth] = (int)$gaRow['metricValues'][$this->getRowKey('screenPageViews', $metricsList)]['value'];
				$this->_visits[$yearMonth] = (int)$gaRow['metricValues'][$this->getRowKey('sessions', $metricsList)]['value'];
				$this->_visitors[$yearMonth] = (int)$gaRow['metricValues'][$this->getRowKey('totalUsers', $metricsList)]['value'];
		}
	}

	public function getDateList(){
		return $this->_periodDateList;
	}

	// ページビュー6ヶ月
	public function getPageviews6month(){
		foreach($this->_periodDateList as $date){
			if (!isset($this->_pageviews[$date])) {
				$this->_pageviews[$date] = 0;
			} 
		}
		return $this->_pageviews;
	}
	
	// ページビュー3ヶ月
	public function getPageviewsForPeriod(){
		foreach($this->_periodDateList as $date){
			if (!isset($this->_pageviews[$date])) {
				$this->_pageviews[$date] = 0;
			} 
		}
		return $this->_pageviews;
	}
	
	// ユニークページビュー3ヶ月
	public function getUniqueVisitors3month(){

		foreach($this->_periodDateList as $date){
			if (!isset($this->_visitors[$date])) {
				$this->_visitors[$date] = 0;
			} 
		}
		return $this->_visitors;
	}

	// 問い合わせ件数3ヶ月
	public function getContactCount3month(){
		// 問い合わせ件数
		$contactCount = $this->getContactCount($this->_companyId,$this->_startDate,$this->_endDate);
		foreach($this->_periodDateList as $date){
			$this->_contactCount[$date] = $contactCount[$date]; 
		}
		$contactEstateCount = $this->getEstateContactCount($this->_companyId,$this->_startDate,$this->_endDate);
		foreach($this->_periodDateList as $date){
			$this->_contactCount[$date] += (isset($contactEstateCount[$date])) ? $contactEstateCount[$date] : 0; 
		}
		return $this->_contactCount;
	}

	// ページビュー
	public function getPageviews(){
		if(empty($this->_pageviews)){
			$data['base-month-val']  = 0;
			$data['prev-month-val']  = 0;
			$data['prev-month-gap']  = 0;
		}else{
			$data['base-month-val']  = isset($this->_pageviews[$this->_baseMonth]) ? $this->_pageviews[$this->_baseMonth]: 0;
			$data['prev-month-val']  = isset($this->_pageviews[$this->_prevMonth]) ? $this->_pageviews[$this->_prevMonth] : 0;
			$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];

		}
		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}

	// 訪問者
	public function getVisits(){
		if(empty($this->_visits)){
			$data['base-month-val']  = 0;
			$data['prev-month-val']  = 0;
			$data['prev-month-gap']  = 0;
		}else{
			$data['base-month-val']  = isset($this->_visits[$this->_baseMonth]) ? $this->_visits[$this->_baseMonth] : 0;
			$data['prev-month-val']  = isset($this->_visits[$this->_prevMonth]) ? $this->_visits[$this->_prevMonth] : 0;
			$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];
		}

		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}

	// ユニークページビュー
	public function getVisitors(){
		if(empty($this->_visitors)){
			$data['base-month-val']  = 0;
			$data['prev-month-val']  = 0;
			$data['prev-month-gap']  = 0;
		}else{
			$data['base-month-val']  = isset($this->_visitors[$this->_baseMonth]) ? $this->_visitors[$this->_baseMonth] : 0;
			$data['prev-month-val']  = isset($this->_visitors[$this->_prevMonth]) ? $this->_visitors[$this->_prevMonth] : 0;
			$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];
		}
		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}

	// 会社問い合わせ数を取得する
	public function getGeneralContactCount(){
		// 会社問い合わせ件数
		$contactCount = $this->getContactCount($this->_companyId,$this->_prevMonth,$this->_baseMonth,config('constants.hp_page.TYPE_FORM_CONTACT'));
		$data['base-month-val']  = $contactCount[$this->_baseMonth];
		$data['prev-month-val']  = $contactCount[$this->_prevMonth];
		$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];
		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}


	// 査定問い合わせ数を取得する
	public function getAssesmentContactCount(){
		// 資料請求問い合わせ件数
		$contactCount = $this->getContactCount($this->_companyId,$this->_prevMonth,$this->_baseMonth,config('constants.hp_page.TYPE_FORM_DOCUMENT'));
		$data['base-month-val']  = $contactCount[$this->_baseMonth];
		$data['prev-month-val']  = $contactCount[$this->_prevMonth];
		$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];
		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}

	// 資料請求問い合わせ数を取得する
	public function getDocumentContactCount(){
		// 売却査定問い合わせ件数
		$contactCount = $this->getContactCount($this->_companyId,$this->_prevMonth,$this->_baseMonth,config('constants.hp_page.TYPE_FORM_ASSESSMENT'));
		$data['base-month-val']  = $contactCount[$this->_baseMonth];
		$data['prev-month-val']  = $contactCount[$this->_prevMonth];
		$data['prev-month-gap']  = $data['base-month-val'] - $data['prev-month-val'];
		if($data['prev-month-gap']>0) $data['prev-month-gap-dirct']='is-up';
		else if($data['prev-month-gap']<0) $data['prev-month-gap-dirct']='is-down';
		else $data['prev-month-gap-dirct']  = 'is-unchanged';
		return $data;
	}

	// 物件リクエスト数（前月比）を取得する
	public function getEstateContactCountMoM( $type )
	{
		$count = $this->getEstateContactCount( $this->_companyId, $this->_prevMonth, $this->_baseMonth, $type ) ;
		$data[ 'base-month-val'		] = $count[ $this->_baseMonth	] ;
		$data[ 'prev-month-val'		] = $count[ $this->_prevMonth	] ;
		$data[ 'prev-month-gap'		] = $data[ 'base-month-val' ] - $data[ 'prev-month-val' ] ;
		$data[ 'prev-month-gap-dirct'	]	= 'is-unchanged'	;
		if ( $data[ 'prev-month-gap'	] > 0 )
		{
			$data[ 'prev-month-gap-dirct'	]	= 'is-up'			;
		}
		if ( $data[ 'prev-month-gap'	] < 0 )
		{
			$data[ 'prev-month-gap-dirct'	]	= 'is-down'			;
		}
		return $data ;
	}

	// 物件リクエスト数（前月比）を取得する
	public function getEstateRequestCountMoM( $type )
	{
		$count = $this->getContactCount( $this->_companyId, $this->_prevMonth, $this->_baseMonth, $type ) ;

		$data[ 'base-month-val'		] = $count[ $this->_baseMonth	] ;
		$data[ 'prev-month-val'		] = $count[ $this->_prevMonth	] ;
		$data[ 'prev-month-gap'		] = $data[ 'base-month-val' ] - $data[ 'prev-month-val' ] ;
		$data[ 'prev-month-gap-dirct'	]	= 'is-unchanged'	;
		if ( $data[ 'prev-month-gap'	] > 0 )
		{
			$data[ 'prev-month-gap-dirct'	]	= 'is-up'			;
		}
		if ( $data[ 'prev-month-gap'	] < 0 )
		{
			$data[ 'prev-month-gap-dirct'	]	= 'is-down'			;
		}
		return $data ;
	}

	
	private function getRowKey($srchVal, $resultMap){

		$rowKey = null;
		foreach($resultMap as $key=>$val){
			if($val == $srchVal){
				$rowKey = $key;
			}
		}
		return $rowKey;
	}
	
	private function getEstateContactCount( $companyId, $startDate, $endDate,$pageTypeCode = null )
	{
		$count			= array()											;
		$periodDateList	= $this->getPeriodDateList( $startDate, $endDate )	;
		
		foreach( $periodDateList as $periodDate )
		{
			$date	= new Carbon($periodDate) ;
			$start	= $date->ToString('yyyy-MM-01  00:00:00'	) ;
			$end	= $date->endOfMonth()->ToString('yyyy-MM-ddd 23:59:59'	) ;
			$count[ $date->format('Y-m') ] = App::make(EstateContactCountRepositoryInterface::class)->getCount($companyId, $start, $end, $pageTypeCode) ;
		}
		return $count ;
	}
	
	private function getContactCount($companyId,$startDate,$endDate,$pageTypeCode=null){
		$contactCount = array();
		$periodDateList = $this->getPeriodDateList($startDate,$endDate);
		
		foreach($periodDateList as $periodDate){
			$date = new Carbon($periodDate);
			$start	= $date->ToString('yyyy-MM-01  00:00:00') ;
			$end	= $date->endOfMonth()->ToString('yyyy-MM-ddd 23:59:59') ;
			$contactCount[$date->format('Y-m')] = App::make(ContactCountRepositoryInterface::class)->getCount($companyId, $start, $end, $pageTypeCode);
		}
		return $contactCount;
	}

	private function getPeriodDateList($startDate,$endDate){
		$dateList = array();
		for($idx=0; $idx<$this->_periodMonth; $idx++){
			$dateList[] = (new Carbon($startDate))->addMonth($idx)->format('Y-m');
		}
		return $dateList;
	}
}

