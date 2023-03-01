<?php
namespace Library\Custom\Analysis;

use App\Repositories\ContactCount\ContactCountRepositoryInterface;
use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use Carbon\Carbon;
/**
 * アクセスログ：アクセス情報
 *
 */
class Access extends AbstractAnalysis
{
    public function getData($baseMonth) { 

		// 期間(基軸月の6ヶ月前の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$startDate = (new Carbon($baseMonth))->subMonth(5)->format('Y-m-01');
		$endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d');

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
			'sessions',					// セッション数
			'newUsers',					// 新規ユーザー数
			'totalUsers',		        // ユーザー数
			'screenPageViews',			// ページビュー数
			'bounceRate',				// 直帰数
			'screenPageViewsPerSession'
		);

		// Googleアナリティクスからデータを取得する
		$gaResult = $this->getGaData($startDate,$endDate,$dimensionsList, $sortList, $metricsList);
		if(!isset($gaResult['rows']) || is_null($gaResult['rows'])){
			return array();
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		$row = array();
		$date = null;
		foreach( $gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey => $mapVal) {
				$date = $gaRow['dimensionValues'][0]['value']."-".$gaRow['dimensionValues'][1]['value'];;
				$access['date'][$date] = $date;
				$value = $gaRow['metricValues'][$mapKey]['value'];
				switch ($mapVal) {
					case 'bounceRate':
						$value = $this->getBounceRate($value);
						break;
					case 'screenPageViewsPerSession':
						$value = $this->getPageviewsPerVisits($value);
						break;
				}
				$access[$this->mapAnlytic($mapVal)][$date] = $value;
			}
		}

		// 問い合わせ件数
		$periodDateList = $this->getPeriodDateList($startDate,$endDate);
		$contactCount = $this->getContactCount($this->_companyId,$startDate,$endDate);
		foreach($periodDateList as $date){
				$access['contactCount'][$date] = $contactCount[$date];
		}

		//  期間平均
		foreach($access as $rowKey=>$row){
			if($rowKey=='date'){ 
				continue;
			}
			$sam = 0;
			$cnt=0;
			foreach($row as $colKey=>$val){
				$sam += (int) $val;
				$cnt++;
			}
			$access[$rowKey]['average'] = round(($sam/$cnt),2);
			if($rowKey == 'bounceRate'){
				$access[$rowKey]['average'] = $access[$rowKey]['average']."%";
			}
		
		}
		return $access;
	}
	
	
	
	private function getContactCount($companyId,$startDate,$endDate){

		//6か月分
		$contactCount = array();
		$periodDateList = $this->getPeriodDateList($startDate,$endDate);
		
		foreach($periodDateList as $periodDate){
			$date = new Carbon($periodDate);
			$start = $date->format('Y-m-01  00:00:00'	) ;
			$end   = $date->endOfMonth()->format('Y-m-d 23:59:59'	) ;
			$month = $date->format('Y-m') ;
			$contactCount[ $month ]  = 0 ;
			$contactCount[ $month ] += \App::make(ContactCountRepositoryInterface::class)->getCount( $companyId, $start, $end ) ;
			$contactCount[ $month ] += \App::make(EstateContactCountRepositoryInterface::class)->getCount( $companyId, $start, $end ) ;
		}
		return $contactCount;
	}

	private function getPeriodDateList($startDate,$endDate){

		$dateList = array();
		for($idx=0; $idx<6; $idx++){
			$dateList[] = (new Carbon($startDate))->addMonth($idx)->format('Y-m');
		}
		return $dateList;
	}
}

