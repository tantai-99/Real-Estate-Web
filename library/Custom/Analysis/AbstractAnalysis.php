<?php
namespace Library\Custom\Analysis;
use App\Repositories\Tag\TagRepositoryInterface;
use Illuminate\Support\Facades\App;
use Exception;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;

class AbstractAnalysis
{
	protected $_companyId;

    public function init($companyId, $accessToken = null) {

    	$this->_companyId = $companyId;
		$tag = App::make(TagRepositoryInterface::class)->getDataForCompanyId($companyId);
		if(is_null($tag)){ 
			 throw new Exception('Googleアナリティクス情報が設定されていません');
		}

		//サービスアカウント名（メールアドレス）
		$serviceAccountName = $tag->google_analytics_mail;

		//アナリティクスのビューID
		$viewId = $tag->google_analytics_view_id;

		$p12 = $tag->google_p12;

		$certs = array();
		if (!openssl_pkcs12_read($p12, $certs, 'notasecret')) {
			throw new Exception(
				"Unable to parse the p12 file.  " .
				"Is this a .p12 file?  Is the password correct?  OpenSSL error: " .
				openssl_error_string()
			);
		}
		if (!array_key_exists("pkey", $certs) || !$certs["pkey"]) {
			throw new Exception("No private key found in p12 file.");
		}
		$privateKey = $certs["pkey"];

		try {
			$creds = \Google\ApiCore\CredentialsWrapper::build([
				'keyFile' => [
					"type"=> "service_account",
					"private_key"=> $privateKey,
					"client_email"=> $serviceAccountName,
				]
			]);
			$client = new BetaAnalyticsDataClient( ['credentials' => $creds]);
			if (!is_null($accessToken)) {
				$client->setAccessToken($accessToken);
			}
		} catch (Exception $e) {
		    //echo $e->getMessage();
		    throw $e;
		}
		
		// アナリティクスクライアントを生成
		$this->analytics = $client;
		$this->analyticsViewId = $viewId;

	}

	protected function getGaData($startDate,$endDate,$dimensionsList, $sortList, $metricsList, $maxResults=5000) {

		// ga用の文字列を取得
		$dimensions = $this->gaSerialize($dimensionsList, '\Google\Analytics\Data\V1beta\Dimension');

		$metrics = $this->gaSerialize($metricsList, '\Google\Analytics\Data\V1beta\Metric');

		$sorts = $this->gaOrderBy($sortList);

		//オプションの設定
		// $optParams = array(
		// 	'dimensions'  => $dimensions,
		// 	'sort'        => $sort,
		// 	'quotaUser'   => $this->_companyId,
		// 	'max-results' => $maxResults
		// );
		$gaResult = array();
		try {
			//データ取得
			$gaResult = $this->analytics->runReport([
				'property' => 'properties/'.$this->analyticsViewId,
				'dateRanges' => [
					new DateRange([
						'start_date' => $startDate,
						'end_date' => $endDate,
					])
				],
				'dimensions' => $dimensions,
				'metrics' => $metrics,
				'orderBys' => $sorts,
				'limit' => $maxResults
			]);
		} catch (apiServiceException $e) {
			echo $e->getMessage();
		}
		return json_decode($gaResult->serializeToJsonString(), true);
	}

	protected function gaSort($sorts) {

		foreach($sorts as $key=>$sort) {

		}
	}

	protected function gaSerialize($list, $class) {
		$serialize = [];
		foreach( $list as $val) {
			$serialize[] = new $class([
				'name' => $val
			]);
		}
		return $serialize;
	}

	protected function gaOrderBy($list) {
		$result = [];
		foreach($list as $type=>$val) {
			switch ($type) {
				case 'dimension':
					$class = '\Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy';
					break;
				
				case 'metric':
					$class = '\Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy';
					break;
			}
			foreach($val as $v=>$sort) {
				$result[] = new OrderBy([
					$type => new $class([
						$type.'_name' => $v, 
					]),
					'desc' => $sort,
				]);
			}
		}
		return $result;
	}

	protected function getItemKyeFromGaMetricsKey($mapVal) {
		// メトリックキー先頭のga:を削除
		return substr( $mapVal, 3, strlen($mapVal)-3 );
	}

	// ページ/セッション
    protected function getPageviewsPerVisits($value) { 
        return $value == 0 ? 0 : round($value,2);
    }

	// 直帰率
    protected function getBounceRate($value) { 
        return $value == 0 ? 0 : round($value*100,2)."%";
    }

    protected function getRate($baseVal, $compVal) { 
    	$baseVal = (float)$baseVal;
    	$compVal = (float)$compVal;
    	
    	if($compVal == 0){
    		$direction = 'none';
    		$value = 0;
    		
    	}else if($baseVal == $compVal){
			$direction = 'same';
			$value = 0;

		}else{ 
			
			$value = (($compVal-$baseVal) / $compVal)*100;
			$direction = ($value<0) ? 'up':'down';
			$value = round(abs($value),2);
		}
		
		$rate = array();
		$rate['direction'] = $direction;
		$rate['value']     = $value;
    	return $rate;
	}
	
	public function mapAnlytic($key) {
		$mapVals = [
			'sessions' 					=> 'visits',
			'newUsers' 					=> 'newVisits',
			'totalUsers' 				=> 'visitors',
			'screenPageViews' 			=> 'pageviews',
			'screenPageViewsPerSession'	=> 'pageviewsPerVisits',
			'averageSessionDuration'	=> 'avgTimeOnPage',
			'keyword' 					=> 'googleAdsKeyword'
		];
		if (isset($mapVals[$key])) {
			return $mapVals[$key];
		}
		return $key;
	}


}

