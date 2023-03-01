<?php
namespace Library\Custom\Assessment;
use App\Models\Hp;
class Features
{
    const FEATURE_FAVICON = 'Favicon';
    const FEATURE_COMPANY_NAME = 'CompanyName';
    const FEATURE_ADDRESS = 'Address';
    const FEATURE_TEL = 'Tel';
    const FEATURE_OFFICE_HOUR = 'OfficeHour';
    const FEATURE_LOGO = 'Logo';
    const FEATURE_LOGO_SP = 'LogoSP';
    const FEATURE_COPYRIGHT = 'Copyright';
    const FEATURE_FACEBOOK_BUTTON = 'FacebookButton';
    const FEATURE_TWITTER_BUTTON = 'TwitterButton';
    const FEATURE_LINE_BUTTON = 'LineButton';
    const FEATURE_COMPANY_MAP = 'CompanyMap';
    const FEATURE_TOP_IMAGES = 'TopImages';
    const FEATURE_WEB_CLIP = 'WebClip';
    const FEATURE_FOOTER_LINK = 'FooterLink';

    /**
     * @var App\Models\Hp
     */
    protected $hp;

    /**
     * 評価結果
     * @var array
     */
    protected $assess_data = [];

    protected static $FEATURE_NAMES = [
        self::FEATURE_FAVICON => 'ファビコン',
        self::FEATURE_COMPANY_NAME => '会社名',
        self::FEATURE_ADDRESS => '住所',
        self::FEATURE_TEL => '電話番号',
        self::FEATURE_OFFICE_HOUR => '営業時間',
        self::FEATURE_LOGO => 'サイトロゴPC',
        self::FEATURE_LOGO_SP => 'サイトロゴスマホ',
        self::FEATURE_COPYRIGHT => 'コピーライト',
        self::FEATURE_FACEBOOK_BUTTON => 'Facebook',
        self::FEATURE_TWITTER_BUTTON => 'Twitter',
        self::FEATURE_LINE_BUTTON => 'LINE',
        self::FEATURE_COMPANY_MAP => '会社地図',
        self::FEATURE_TOP_IMAGES => 'TOP画像',
    ];

    public function __construct($hp)
    {
        $this->hp = $hp;
    }

    /**
     * 対象機能の活用状態を確認
     *
     * @param array $target
     * @return array
     */
    public function assess($target = null)
    {
        if (!empty($this->assess_data)) {
            return $this->assess_data;
        }

        if (is_null($target)) {
            $target = array_keys(self::$FEATURE_NAMES);
        }
        if (!is_array($target)) {
            $target = [$target];
        }

        $result = [];
        foreach ($target as $function_name) {
            $class_name = '\Library\Custom\Assessment\Features\\'.$function_name;
            /** @var $u Library\Custom\Assessment\Features\AbstractFeatures */
            $u = new $class_name($this->hp);
            $result[$function_name] = $u->isUtilized();
        }

        $this->assess_data = $result;
        return $this->assess_data;
    }

    /**
     * 対象機能数
     *
     * @return int
     */
    public function countTargetFunctions()
    {
        return count($this->assess_data);
    }

    /**
     * 活用済機能数
     *
     * @return int
     */
    public function countUtilized()
    {
        if (empty($this->assess_data)) {
            $this->assess();
        }

        return count(array_filter($this->assess_data));
    }

    /**
     * 未活用機能数
     *
     * @return int
     */
    public function countUnUtilized()
    {
        return $this->countTargetFunctions() - $this->countUtilized();
    }

    public function getFeatureName($key = null)
    {
        if (is_null($key)){
            return self::$FEATURE_NAMES;
        }else{
            return isset(self::$FEATURE_NAMES[$key]) ? self::$FEATURE_NAMES[$key] : null;
        }
    }

    public static function getFeatureNameAll() {
         return self::$FEATURE_NAMES;
    }
}