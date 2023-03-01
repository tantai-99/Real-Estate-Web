<?php
namespace Library\Custom\Analysis;
use Carbon\Carbon;
/**
 * アクセスログ：メディア別データ
 *
 */
class AccessByMedia extends AbstractAnalysis
{
    public function getData($baseMonth) { 


		// 期間(基軸月の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$startDate = (new Carbon($baseMonth))->subMonth(5)->format('Y-m-01');
		$endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d'); 

		$dateList = array();
		for($idx=0; $idx<6; $idx++){
			$dateList[] = (new Carbon($startDate))->addMonth($idx)->format('Y-m');
		}

		//ディメンション(dimensions)
		$dimensionsList = array(
			'source',
			'medium',
			'year',
			'month',
		);

		//ソート(sort)
		$sortList = array(
			'metric' => [
				'sessions' => true
			],
			'dimension'=> [
				'source' => false,
				'medium' => false,
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
		$gaResult = $this->getGaData($startDate,$endDate,$dimensionsList,$sortList,$metricsList);
		if(!isset($gaResult['rows']) || is_null($gaResult['rows'])){
			$access = array();
			$access['date']=$dateList;
			return $access;
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		$access['date']=$dateList;
		$row = array();
		$idx = 0;
		foreach($gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey=>$mapVal){
				$sourceMedia = $gaRow['dimensionValues'][1]['value'];
				$date = $gaRow['dimensionValues'][2]['value']."-".$gaRow['dimensionValues'][3]['value'];

				$access[$sourceMedia]['sourceMedia'] = $sourceMedia;
				if(!isset($access[$sourceMedia][$this->mapAnlytic($mapVal)][$date])) $access[$sourceMedia][$this->mapAnlytic($mapVal)][$date] = 0;
				$value = $gaRow['metricValues'][$mapKey]['value'];
				switch ($mapVal) {
					case 'bounceRate':
						$value = $this->getBounceRate($value);
						break;
					case 'screenPageViewsPerSession':
						$value = $this->getPageviewsPerVisits($value);
						break;
				}
				$access[$sourceMedia][$this->mapAnlytic($mapVal)][$date] = $access[$sourceMedia][$this->mapAnlytic($mapVal)][$date]+$value;
				$idx++;
			}
		}

		return $access;
	}
}
