<?php
namespace Library\Custom\Analysis;

use App\Repositories\ContactCount\ContactCountRepositoryInterface;
use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use Carbon\Carbon;
/**
 * アクセスログ：サマリ
 *
 */
class Summary extends AbstractAnalysis
{
    public function getData($baseMonth) { 	

		// 取得月
		$baseMonth = (new Carbon($baseMonth))->format('Y-m'); 
		$prevMonth     = (new Carbon($baseMonth))->subMonth(1)->format('Y-m');
		$prevYearMonth = (new Carbon($baseMonth))->subMonth(12)->format('Y-m');
		$monthList = array($baseMonth,$prevMonth,$prevYearMonth);

		// 期間(基軸月の12ヶ月前の月初～基軸月の月末)
		$startDate = (new Carbon($baseMonth))->subMonth(12)->format('Y-m-01');
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
			'sessions',				// セッション数
			'newUsers',				// 新規ユーザー数
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

		//取得結果からサマリをつくる
		$summary = array();
		foreach( $gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey => $mapVal) {
				$date = $gaRow['dimensionValues'][0]['value']."-".$gaRow['dimensionValues'][1]['value'];
				if( in_array( $date, $monthList )){
					if( $date == $baseMonth )          $key = 'base-month';//当月
					else if( $date == $prevMonth )     $key = 'prev-month';//当月の前月
					else if( $date == $prevYearMonth ) $key = 'prev-year-month';//当月の前年月
					$summary['date'][$key] = $date;
					$value = $gaRow['metricValues'][$mapKey]['value'];
					switch ($mapVal) {
						case 'bounceRate':
							$value = $this->getBounceRate($value);
							break;
						case 'screenPageViewsPerSession':
							$value = $this->getPageviewsPerVisits($value);
							break;
					}
					$summary[$this->mapAnlytic($mapVal)][$key] = $value;
				}
			}
		}

		//メール情報件数を取得
		$contactCount = \App::make(ContactCountRepositoryInterface::class);
		$contRows[ 'mailCount'	] = $contactCount->getSummary( $this->_companyId, $baseMonth							) ;
		$contactCount = \App::make(EstateContactCountRepositoryInterface::class)	;
		$contRows[ 'mailCount'	] = $contactCount->getSummary( $this->_companyId, $baseMonth, $contRows[ 'mailCount' ]	) ;
		$summary = array_merge($summary, $contRows);
		
		
		// 前月比と前年月比
		foreach($summary as $rowKey=>$row){
			if($rowKey=='date'){ 
				continue;
			}
			
			// 当月と前月
			$prevMonth = isset($summary[$rowKey]['prev-month']) ? $summary[$rowKey]['prev-month'] : 0;
			$rate = $this->getRate($summary[$rowKey]['base-month'],$prevMonth );
			$summary[$rowKey]['prev-month-rate']['direction'] = $rate['direction'];
			$summary[$rowKey]['prev-month-rate']['value']     = $rate['value'];

			// 当月と前年月
			$prevYearMonth = isset($summary[$rowKey]['prev-year-month']) ? $summary[$rowKey]['prev-year-month'] : 0;
			$rate = $this->getRate($summary[$rowKey]['base-month'],$prevYearMonth );
			$summary[$rowKey]['prev-year-month-rate']['direction'] = $rate['direction'];
			$summary[$rowKey]['prev-year-month-rate']['value']     = $rate['value'];	
		}

		return $summary;
	}
}

