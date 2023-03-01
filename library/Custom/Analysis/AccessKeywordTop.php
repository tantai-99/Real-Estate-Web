<?php
namespace Library\Custom\Analysis;
use Carbon\Carbon;
/**
 * アクセスログ：月間キーワードTOP20
 *
 */
class AccessKeywordTop extends AbstractAnalysis
{
    public function getData($baseMonth, $limit) { 
		// 期間(基軸月の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$startDate = (new Carbon($baseMonth))->format('Y-m-01');
		$endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d'); 
		
		//ディメンション(dimensions)
		$dimensionsList = array(
			'googleAdsKeyword',
		);

		//ソート(sort)
		$sortList = array(
			'metric' => [
				'sessions' => true
			],
			'dimension'=> [
				'googleAdsKeyword' => false,
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
		$gaResult = $this->getGaData($startDate,$endDate,$dimensionsList,$sortList,$metricsList, $limit);
		if(!isset($gaResult['rows']) || is_null($gaResult['rows'])){
			return array();
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		foreach( $gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey=>$mapVal){
				$value = $gaRow['metricValues'][$mapKey]['value'];
				switch ($mapVal) {
					case 'bounceRate':
						$value = $this->getBounceRate($value);
						break;
					case 'screenPageViewsPerSession':
						$value = $this->getPageviewsPerVisits($value);
						break;
				}
				$access[$gaRowKye][$this->mapAnlytic($mapVal)] = $value;
			}
			foreach($dimensionsList as $mapKey=>$mapVal){
				$value = $gaRow['dimensionValues'][$mapKey]['value'];
				$access[$gaRowKye][$this->mapAnlytic($mapVal)] = $value;
			}
		}
		return $access;
    }
}
