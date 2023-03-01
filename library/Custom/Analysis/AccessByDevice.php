<?php
namespace Library\Custom\Analysis;
use Carbon\Carbon;
/**
 * アクセスログ：デバイス別アクセス情報
 *
 */
class AccessByDevice extends AbstractAnalysis
{
    public function getData($baseMonth) { 

		// 期間(基軸月の月初～基軸月の月末)
		$baseMonth = (new Carbon($baseMonth))->format('Y-m');
		$startDate = (new Carbon($baseMonth))->format('Y-m-01');
		$endDate   = (new Carbon($baseMonth))->endOfMonth()->format('Y-m-d');
		
		//ディメンション(dimensions)
		$dimensionsList = array(
			'deviceCategory',
		);

		//ソート(sort)
		$sortList = array(
			'dimension' => [
				'deviceCategory' => false,
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


		//Googleアナリティクスからデータを取得する
		$gaResult = $this->getGaData($startDate,$endDate,$dimensionsList,$sortList,$metricsList);
		if(!isset($gaResult['rows']) || is_null($gaResult['rows'])){
			return array();
		}

		//取得結果からアクセス情報をつくる
		$access = array();
		$deviceCategoryList = array(); 
		foreach( $gaResult['rows'] as $gaRowKye => $gaRow){
			foreach($metricsList as $mapKey => $mapVal) {
				$deviceCategory = $gaRow['dimensionValues'][0]['value'];
				$deviceCategoryList[] = $deviceCategory;
		
				$value = $gaRow['metricValues'][$mapKey]['value'];
				switch ($mapVal) {
					case 'bounceRate':
						$value = $this->getBounceRate($value);
						break;
					case 'screenPageViewsPerSession':
						$value = $this->getPageviewsPerVisits($value);
						break;
				}
				$access[$this->mapAnlytic($mapVal)][$deviceCategory] = $value;
			}
		}

		return $access;
	}
}
