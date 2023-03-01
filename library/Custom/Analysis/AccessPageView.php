<?php
namespace Library\Custom\Analysis;
use Carbon\Carbon;
/**
 * アクセスログ：ページ別ページビューTOP20
 *
 */
class AccessPageView extends AbstractAnalysis
{
    public function getData($baseMonth,  $limit) { 
		// 期間(基軸月の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$startDate = (new Carbon($baseMonth))->format('Y-m-01');
		$endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d');  
		
		//ディメンション(dimensions)
		$dimensionsList = array(
			'pagePath',
		);

		//ソート(sort)
		$sortList = array(
			'metric' => [
				'screenPageViews' => true
			],
			'dimension'=> [
				'pagePath' => false,
			]
			
		);
		//メトリクス(metrics)
		$metricsList = array(
			'screenPageViews',			// ページビュー数
			'sessions',				// 訪問数
			'averageSessionDuration',	// 滞在時間
			// 'exits',					// 離脱数
		);

		// Googleアナリティクスからデータを取得する
		$gaResult = $this->getGaData($startDate,$endDate,$dimensionsList,$sortList,$metricsList);

		if(is_null($gaResult['rows'])){
			return array();
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		foreach( $gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey=>$mapVal){
				$value = $gaRow['metricValues'][$mapKey]['value'];
				if ($mapVal == "averageSessionDuration"){
					$value = $this->cnvSec($value);
				}
				$access[$gaRowKye][$this->mapAnlytic($mapVal)] = $value;
			}
			foreach($dimensionsList as $mapKey=>$mapVal){
				$value = $gaRow['dimensionValues'][$mapKey]['value'];
				$access[$gaRowKye][$this->mapAnlytic($mapVal)] = $value;
			}
		}
		// 離脱率算出
		// foreach($access as $rowKey=>$row){
		// 	$access[$rowKey]['exitsRate'] =$this->getExitsRate($access[$rowKey]['exits'],$access[$rowKey]['pageviews']); 
		// }
		return $access;
	}
	
	// 秒を"00:00:00"形式に変換する
	private function cnvSec($seconds) {
	
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds / 60) % 60);
		$seconds = $seconds % 60;
		
		$hms = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
		
		return $hms;
	
	}
	// 離脱率
	private function getExitsRate($exits, $pageviews) { 
		if($exits == 0 || $pageviews == 0) {
			$rate = 0;
		}else{
			$rate = round((($exits/$pageviews)*100), 2);
		}
		return $rate;
	}
	
}
