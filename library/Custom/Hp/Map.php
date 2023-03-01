<?php
namespace Library\Custom\Hp;
use Library\Custom\User\Cms;
use Library\Custom\User\UserAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;

class Map {

	/**
	 * CMS上で使うキーを返す
	 */
    public static function getGooleMapKey() {
        $google = getConfigs('google');
        return $google->map->api->key;
    }

    /**
     * ユーザサイト（GMOサーバ）で使用するGoogleMapのMapKeyを返します。
     */
    public static function getGooleMapKeyForUserSite( $company = null )
    {
    	$google = getConfigs('google');
    	$result	= $google->map->api->id->usersite	;
    	if ( $company == null )
    	{
        	$com_id			= $_REQUEST[ 'com_id' ]							;
			$tableCompany	= \App::make(CompanyRepositoryInterface::class)			;
        	$company		= $tableCompany->getDataForId(	$com_id		)	;
    	}
		$apiKey			= $company[ 'google_map_api_key' ]	;
		if ( $apiKey != "" )
		{
			$result		= 'key=' . $apiKey		;
		}
    	
    	return $result	;
    }

    /**
     * GoogleMapのAPIキーを返します。（プレビューなどCMS上で使う）
     */
    public static function getGoogleMapApiKey()
    {
    	$google			 = getConfigs('google');
    	$result			 = $google->map->api->key					;
    	
    	return $result	;
    }

    /**
     * GoogleMapのChannelパラメータを返します。
     */
    public static function getGoogleMapChannel($company=null) {

        $isDemo = false;
        if ( $company && $company->contract_type === config('constants.company_agreement_type.CONTRACT_TYPE_DEMO') ) {
            $isDemo=true;
        }
        return self::_getGoogleMapChannel($isDemo);
    }

    public static function getGoogleMapChannelByProfile() {

        $isDemo = false;
        if (Cms::getInstance()->getProfile()){
            $isDemo = Cms::getInstance()->getProfile()->isDemo();
        }
        return self::_getGoogleMapChannel($isDemo);
    }


    private static function _getGoogleMapChannel($isDemo){
        $google = getConfigs('google');
        $apiChannel = ($isDemo) ? $google->map->api->channel_for_demo : $google->map->api->channel ;
        return $apiChannel;

    }

        /**
     * 会社所在地の県庁所在地の座標を取得
     * @return array
     */
    public function getSelfPref(){

    	$hp = UserAbstract::factory('default')->getCurrentHp();
    	if ($hp) {
        	foreach (Map::getPref() as $key => $pref) {
            	if ((strstr($hp->adress, $pref))) {
                	return Map::getPrefLl($pref);
            	}
        	}
        }
        return Map::getPrefLl('東京都');
    }

    /**
     * 都道府県一覧
     * @return array[
     */
    public static function getPref() {

        $pref = array(
            '北海道',
            '青森県',
            '岩手県',
            '宮城県',
            '秋田県',
            '山形県',
            '福島県',
            '茨城県',
            '栃木県',
            '群馬県',
            '埼玉県',
            '千葉県',
            '東京都',
            '神奈川県',
            '新潟県',
            '富山県',
            '石川県',
            '福井県',
            '山梨県',
            '長野県',
            '岐阜県',
            '静岡県',
            '愛知県',
            '三重県',
            '滋賀県',
            '京都府',
            '大阪府',
            '兵庫県',
            '奈良県',
            '和歌山県',
            '鳥取県',
            '島根県',
            '岡山県',
            '広島県',
            '山口県',
            '徳島県',
            '香川県',
            '愛媛県',
            '高知県',
            '福岡県',
            '佐賀県',
            '長崎県',
            '熊本県',
            '大分県',
            '宮崎県',
            '鹿児島県',
            '沖縄県',
        );
        return $pref;
    }

    /**
     * 都道府県の県庁所在地の座標一覧
     * @param $str
     *
     * @return array
     */
    public static function getPrefLl($str) {

        $llAll = array(
            array(
                '北海道' => array(
                    43.063968,
                    141.347899
                )
            ),
            array(
                '青森県' => array(
                    40.824623,
                    140.740593
                )
            ),
            array(
                '岩手県' => array(
                    39.703531,
                    141.152667
                )
            ),
            array(
                '宮城県' => array(
                    38.268839,
                    140.872103
                )
            ),
            array(
                '秋田県' => array(
                    39.7186,
                    140.102334
                )
            ),
            array(
                '山形県' => array(
                    38.240437,
                    140.363634
                )
            ),
            array(
                '福島県' => array(
                    37.750299,
                    140.467521
                )
            ),
            array(
                '茨城県' => array(
                    36.341813,
                    140.446793
                )
            ),
            array(
                '栃木県' => array(
                    36.565725,
                    139.883565
                )
            ),
            array(
                '群馬県' => array(
                    36.391208,
                    139.060156
                )
            ),
            array(
                '埼玉県' => array(
                    35.857428,
                    139.648933
                )
            ),
            array(
                '千葉県' => array(
                    35.605058,
                    140.123308
                )
            ),
            array(
                '東京都' => array(
                    35.689521,
                    139.691704
                )
            ),
            array(
                '神奈川県' => array(
                    35.447753,
                    139.642514
                )
            ),
            array(
                '新潟県' => array(
                    37.902418,
                    139.023221
                )
            ),
            array(
                '富山県' => array(
                    36.69529,
                    137.211338
                )
            ),
            array(
                '石川県' => array(
                    36.594682,
                    136.625573
                )
            ),
            array(
                '福井県' => array(
                    36.065219,
                    136.221642
                )
            ),
            array(
                '山梨県' => array(
                    35.664158,
                    138.568449
                )
            ),
            array(
                '長野県' => array(
                    36.651289,
                    138.181224
                )
            ),
            array(
                '岐阜県' => array(
                    35.391227,
                    136.722291
                )
            ),
            array(
                '静岡県' => array(
                    34.976978,
                    138.383054
                )
            ),
            array(
                '愛知県' => array(
                    35.180188,
                    136.906565
                )
            ),
            array(
                '三重県' => array(
                    34.730283,
                    136.508591
                )
            ),
            array(
                '滋賀県' => array(
                    35.004531,
                    135.86859
                )
            ),
            array(
                '京都府' => array(
                    35.021004,
                    135.755608
                )
            ),
            array(
                '大阪府' => array(
                    34.686316,
                    135.519711
                )
            ),
            array(
                '兵庫県' => array(
                    34.691279,
                    135.183025
                )
            ),
            array(
                '奈良県' => array(
                    34.685333,
                    135.832744
                )
            ),
            array(
                '和歌山県' => array(
                    34.226034,
                    135.167506
                )
            ),
            array(
                '鳥取県' => array(
                    35.503869,
                    134.237672
                )
            ),
            array(
                '島根県' => array(
                    35.472297,
                    133.050499
                )
            ),
            array(
                '岡山県' => array(
                    34.661772,
                    133.934675
                )
            ),
            array(
                '広島県' => array(
                    34.39656,
                    132.459622
                )
            ),
            array(
                '山口県' => array(
                    34.186121,
                    131.4705
                )
            ),
            array(
                '徳島県' => array(
                    34.06577,
                    134.559303
                )
            ),
            array(
                '香川県' => array(
                    34.340149,
                    134.043444
                )
            ),
            array(
                '愛媛県' => array(
                    33.84166,
                    132.765362
                )
            ),
            array(
                '高知県' => array(
                    33.559705,
                    133.53108
                )
            ),
            array(
                '福岡県' => array(
                    33.606785,
                    130.418314
                )
            ),
            array(
                '佐賀県' => array(
                    33.249367,
                    130.298822
                )
            ),
            array(
                '長崎県' => array(
                    32.744839,
                    129.873756
                )
            ),
            array(
                '熊本県' => array(
                    32.789828,
                    130.741667
                )
            ),
            array(
                '大分県' => array(
                    33.238194,
                    131.612591
                )
            ),
            array(
                '宮崎県' => array(
                    31.91109,
                    131.423855
                )
            ),
            array(
                '鹿児島県' => array(
                    31.560148,
                    130.557981
                )
            ),
            array(
                '沖縄県' => array(
                    26.212401,
                    127.680932
                )
            ),
        );

        foreach ($llAll as $ll) {

            if (key($ll) == $str) {

                return array_combine(array('lat', 'lng'), $ll[$str]);
            }
        }
    }

}