<?php
namespace Library\Custom;

use App\Models\AssociatedCompanyHp;
use App\Models\Hp;
use App\Models\HpPage;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\Hp\HpRepository;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpAssessment\HpAssessmentRepositoryInterface;
use Illuminate\Support\Facades\App;

use Library\Custom\Assessment\Features as Custom_Assessment_Features;
use Library\Custom\Assessment\Pages;
use Library\Custom\Model\Lists\CmsPlan;
use Library\Custom\Plan;
/**
 * 評価/分析 の集計などを行う
 */
class Assessment
{
    const UPDATE_INDEX_NUMBER = 10;

	private static $PAGE_INDEX_NUMBERS		= [] ;

    private static $FEATURE_INDEX_NUMBERS = [
		Custom_Assessment_Features::FEATURE_FAVICON			=> [ 'importance' =>  1, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_COMPANY_NAME	=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_ADDRESS			=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_TEL				=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_OFFICE_HOUR		=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_LOGO			=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_LOGO_SP			=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_COPYRIGHT		=> [ 'importance' =>  3, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_FACEBOOK_BUTTON	=> [ 'importance' =>  1, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_TWITTER_BUTTON	=> [ 'importance' =>  1, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_LINE_BUTTON		=> [ 'importance' =>  1, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_COMPANY_MAP		=> [ 'importance' =>  1, 'limit' =>  1 ],
		Custom_Assessment_Features::FEATURE_TOP_IMAGES		=> [ 'importance' =>  1, 'limit' =>  1 ],
        // 4174 CMS内のFDP項目を設定している会員の出力
        Custom_Assessment_Features::FEATURE_WEB_CLIP		=> [ 'importance' =>  0, 'limit' =>  0 ],
        Custom_Assessment_Features::FEATURE_FOOTER_LINK		=> [ 'importance' =>  0, 'limit' =>  0 ],
	] ;

    /**
     * @var App\Models\Hp
     */
    private $hp;

    /**
     * @var library\Custom\Assessment\Pages
     */
    private $pages;

    /**
     * @var library\Custom\Assessment\Features
     */
    private $features;

	/**
	 * @var Library\Custom\Plan
	 */
	protected	$nowPlan	;
	
    /**
     * page_type_codeが集計対象か否か
     *
     * @param int $page_type_code
     * @return bool
     */
    public static function isTargetPageTypeCode($page_type_code)
    {
        return isset(self::$PAGE_INDEX_NUMBERS[$page_type_code]);
    }

    /**
     * @param App\Models\Hp $hp
     */
    function __construct($hp)
    {
        $this->hp = $hp;
        $this->pages = new Pages($hp);
        $this->features = new Custom_Assessment_Features($hp);
		$row		= App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCurrentHpId(	$hp->id		) ;
		$planVal	= config('constants.cms_plan.TOP_PLAN')	;
        if ( $row !== null )
        {
			$row		= App::make(CompanyRepositoryInterface::class)->getDataForId(	$row->company_id	) ;
			if ( CmsPlan::getCmsPLanName( $row->cms_plan ) != 'unknown' )
			{
				$planVal	= $row->cms_plan	;
			}
        }
		$this->nowPlan	= Plan::factory( CmsPlan::getCmsPLanName(	$planVal	)	) ;
		self::$PAGE_INDEX_NUMBERS	= $this->nowPlan->pageIndexNumbers	;
        // // ATHOME_HP_DEV-5460 評価分析（ツール評価）に不動産お役立ち情報（200記事）の項目を追加する
        // $this->updatePageIndexNumberArticle();
    }

    public function assess()
    {
        $this->pages->assess();
        $this->features->assess($this->getTargetFeatures());
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getPageCategoryMap()
    {
		$topPlan		= Plan::factory( CmsPlan::getCmsPLanName( config('constants.cms_plan.TOP_PLAN')	) ) ;
		$_category_map	= $topPlan->categoryMap		;
		$_categories = App::make(HpPageRepositoryInterface::class)->getCategories();
        $category_map = [];
        foreach ($_category_map as $category => $pages) {
            if (!isset($_categories[$category]) || empty($pages)) {
                continue;
            }
            $category_map[$category] = [
                'label' => $_categories[$category],
                'pages' => $pages
            ];
        }

        // サイトマップを追加
        $category_map[config('constants.hp_page.CATEGORY_SITEMAP')] = [
            'label' => 'サイトマップ',
            'pages' => [config('constants.hp_page.TYPE_SITEMAP')]
        ];

        return $category_map;
    }

	public function getDisablePages()
	{
		$topPlan		= Plan::factory(CmsPlan::getCmsPLanName(config('constants.cms_plan.TOP_PLAN')	) ) ;
		$disablePages	= $topPlan->getDiffPages( $this->nowPlan )[ 'del' ] ;
		
		return $disablePages	;
	}
	/**
	 * Disable With Standard
	 * @reuturn array
	 */
    public function getDisableWithStandardPages()
    {
        $standardPlan   = Plan::factory( CmsPlan::getCmsPLanName( config('constants.cms_plan.CMS_PLAN_STANDARD')    ) ) ;
        $disablePages   = $standardPlan->getDiffPages( $this->nowPlan )[ 'del' ] ;
		
        return $disablePages    ;
    }
	
    public function getFeatures()
    {
        return $this->features;
    }

    public function getTargetFeatures()
    {
        return array_keys(self::$FEATURE_INDEX_NUMBERS);
    }

    public function getTargetFeatureNames()
    {
        $r = [];
        foreach ($this->getTargetFeatures() as $feature) {
            $r[$feature] = $this->features->getFeatureName($feature);
        }

        return $r;
    }

    /**
     * 上限値を計算
     * @return int
     */
    public function getMaxPoint()
    {
        $max = self::UPDATE_INDEX_NUMBER;

        foreach (self::$PAGE_INDEX_NUMBERS as $config) {
            $max += $config['importance'] * $config['limit'];
        }

        foreach (self::$FEATURE_INDEX_NUMBERS as $config) {
            $max += $config['importance'] * $config['limit'];
        }

        return $max;
    }

    /**
     * 総合評価による指数を算出
     *
     * @return int
     */
    public function calculateTotalPoint()
    {
        return $this->calculateFeaturePoint() + $this->calculatePagePoint() + $this->calculateUpdatePoint();
    }

    /**
     * 機能活用度による指数を算出
     *
     * @return int
     */
    public function calculateFeaturePoint()
    {
        $points = 0;
        foreach ($this->features->assess() as $feature => $utilized) {
            if ($utilized && isset(self::$FEATURE_INDEX_NUMBERS[$feature])) {
                $points += self::$FEATURE_INDEX_NUMBERS[$feature]['importance'];
            }
        }

        return $points;
    }


    /**
     * ページ充実による指数を算出
     *
     * @return int
     */
    public function calculatePagePoint()
    {
        $hasTopOriginal = getInstanceUser('cms')->getInstance()->checkHasTopOriginal();
        
        $points = 0;
        foreach ($this->pages->assess() as $page_type_code => $counts) {
            if (!isset(self::$PAGE_INDEX_NUMBERS[$page_type_code])) {
                continue;
            }
            
            $config = self::$PAGE_INDEX_NUMBERS[$page_type_code];
            if (config('constants.hp_page.TYPE_TOP') == $page_type_code && $hasTopOriginal) {
                $points += $config['importance'] * $config['limit'];
            } else {
                $points += min($config['importance'] * $config['limit'], $config['importance'] * $counts['public']);    
            }
        }

        return $points;
    }

    /**
     * 更新状況による指数を算出
     *
     * @return int
     */
    public function calculateUpdatePoint()
    {
        $total = $this->pages->getTotalResult();
        if ($total['published_at'] >= strtotime('-30 days')) {
            return self::UPDATE_INDEX_NUMBER;
        }

        return 0;
    }

    /**
     * @return int 1..5
     */
    public function calculateFeaturePoint5steps()
    {
        $enabled_counts = $this->features->countUtilized();

        $steps = [
            5 => 10,
            4 => 8,
            3 => 6,
            2 => 4
        ];

        foreach ($steps as $rank => $required) {
            if ($enabled_counts >= $required) {
                return $rank;
            }
        }

        return 1;
    }

    /**
     * @return int 1..5（ページ作成）
     */
    public function calculatePagePoint5steps()
    {
        $point = $this->calculatePagePoint();

        $steps = [
            5 => 500,
            4 => 300,
            3 => 200,
            2 => 100
        ];

        foreach ($steps as $rank => $required) {
            if ($point >= $required) {
                return $rank;
            }
        }

        return 1;
    }

    /**
     * @return int 1..5
     */
    public function calculateUpdatePoint5steps()
    {
        $published_ts = $this->pages->getTotalResult()['published_at'];

        $steps = [
            5 => '-5 days',
            4 => '-10 days',
            3 => '-20 days',
            2 => '-30 days'
        ];

        foreach ($steps as $rank => $required) {
            if ($published_ts >= strtotime($required)) {
                return $rank;
            }
        }

        return 1;
    }

    /**
     * @param string $min_month 'YYYY-MM'
     * @param string $max_month 'YYYY-MM'
     * @return array
     */
    public function fetchTotalPointsIn($min_month, $max_month)
    {
        $min_date = $min_month . '-01';
        $max_date = date('Y-m-t', strtotime($max_month . '-01'));

        $table = App::make(HpAssessmentRepositoryInterface::class);
        $rowset = $table->fetchMonthlyAverageInRange($this->hp->id, $min_date, $max_date);

        $data = [];
        foreach ($rowset as $row) {
            $data[$row->month] = round($row->point);
        }

        if ($max_month === date('Y-m')) {
            // "今月"は現在のデータを基に取得する
            $data[$max_month] = $this->calculateTotalPoint();
        }

        // 集計データが存在しない場合は0で補完
        $max_ts = strtotime($max_month . '-01');
        for ($ts = strtotime($min_date); $ts < $max_ts; $ts += 86400) {
            $m = date('Y-m', $ts);
            if (!isset($data[$m])) {
                $data[$m] = 0;
            }
        }

        return $data;
    }

    /**
     * 集計結果をDBに保存する
     * @param string $target_date
     */
    public function saveAs($target_date)
    {
        $table = \App::make(HpAssessmentRepositoryInterface::class);
        $row = $table->create([
            'hp_id' => $this->hp->id,
            'date' => $target_date,
            'point' => $this->calculateTotalPoint()
        ]);

        return $row;
    }

    public function getPageArticleCategoryMap() {
        $table = App::make(HpPageRepositoryInterface::class);
        $topPlan		= Plan::factory( CmsPlan::getCmsPLanName( config('constants.cms_plan.TOP_PLAN')	) ) ;
		$_category_map	= $topPlan->pageMapArticle		;
		$_categories = $table->getTypeListJp();
        $category_map = [];
        foreach ($_category_map as $type => $pages) {
            if (!isset($_categories[$type]) || empty($pages)) {
                continue;
            }
            if (in_array($type, $table->getPageArticleByCategory(config('constants.hp_page.CATEGORY_TOP_ARTICLE')))) {
                $pages[] = config('constants.hp_page.TYPE_LARGE_ORIGINAL');
            }
            $category_map[$type] = [
                'label' => $_categories[$type],
                'pages' => $pages
            ];
        }
        $category_map[config('constants.hp_page.TYPE_LARGE_ORIGINAL')] = [
            'label' => $_categories[config('constants.hp_page.TYPE_ARTICLE_ORIGINAL')],
            'pages' => [config('constants.hp_page.TYPE_SMALL_ORIGINAL')]
        ];
        $category_map[config('constants.hp_page.TYPE_SMALL_ORIGINAL')] = [
            'label' => $_categories[config('constants.hp_page.TYPE_ARTICLE_ORIGINAL')],
            'pages' => [config('constants.hp_page.TYPE_ARTICLE_ORIGINAL')]
        ];
        return $category_map;
    }

    public function getCountArticlePage() {
        return $this->pages->countArticlePage($this->nowPlan);
    }

    public function updatePageIndexNumberArticle() {
        $pageMapArticle = $this->nowPlan->pageMapArticle;
        foreach($pageMapArticle as $key=>$types) {
            if (!isset(self::$PAGE_INDEX_NUMBERS[$key])) {
                self::$PAGE_INDEX_NUMBERS[$key] = [ 'importance' =>  5, 'limit' =>  1 ];
            }
            foreach($types as $type) {
                if (!isset(self::$PAGE_INDEX_NUMBERS[$type])) {
                    self::$PAGE_INDEX_NUMBERS[$type] = [ 'importance' =>  5, 'limit' =>  1 ];
                }
            }
        }
        self::$PAGE_INDEX_NUMBERS[HpPageRepository::TYPE_LARGE_ORIGINAL] = [ 'importance' => 5, 'limit' => 1];
        self::$PAGE_INDEX_NUMBERS[HpPageRepository::TYPE_SMALL_ORIGINAL] = [ 'importance' =>  5, 'limit' => 1];
        self::$PAGE_INDEX_NUMBERS[HpPageRepository::TYPE_ARTICLE_ORIGINAL] = [ 'importance' =>  5, 'limit' => 1];
    }


}