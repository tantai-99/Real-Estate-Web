<?php
namespace library\Custom\Assessment;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Illuminate\Support\Facades\App;
use Library\Custom\Assessment;

use App\Models\Hp;
use App\Models\HpPage;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists\CmsPlan;
use Library\Custom\Plan;
/**
 * コンテンツ充実度
 * hp_page から page_type_code毎、状態毎にカウント
 *
 * 各ページの状態
 *  公開 = public_flg が 1
 *  下書き = new_flg が 0 且つ、public_flg が 0
 *  未作成 = nwe_flg が 1
 */
class Pages
{
    /**
     * @var App\Models\Hp
     */
    protected $hp;

    /**
     * 評価結果
     * @var array
     */
    protected $assess_data = array();

    protected $assess_article_data = array();

    public function __construct($hp)
    {
        $this->hp = $hp;
    }

    /**
     * page_type_code 毎に ページ数、更新日を集計する
     *
     * @return array
     */
    public function assess()
    {
        if (!empty($this->assess_data)) {
            return $this->assess_data;
        }

        $table = App::make(HpPageRepositoryInterface::class);
        $this->assess_data = $table->countPageStates($this->hp->id);

        $dates = $table->fetchPublishedDate($this->hp->id);
        $last_published = 0;
        foreach ($this->assess_data as $page_type_code => &$row) {
            $row['published_at'] = isset($dates[$page_type_code]) ? strtotime($dates[$page_type_code]) : 0;
            if ($row['published_at'] > $last_published) {
                $last_published = $row['published_at'];
            }
        }

        if ($last_published > 0) {
            $this->assess_data[config('constants.hp_page.TYPE_SITEMAP')] = [
                'public' => 1,
                'draft' => 0,
                'new' => 0,
                'published_at' => $last_published
            ];
        } else {
            $this->assess_data[config('constants.hp_page.TYPE_SITEMAP')] = [
                'public' => 0,
                'draft' => 0,
                'new' => 0,
                'published_at' => 0
            ];
        }

        return $this->assess_data;
    }

    public function assessArticle() {
        if (!empty($this->assess_article_data)) {
            return $this->assess_article_data;
        }

        $table = App::make(HpPageRepositoryInterface::class);
        $this->assess_article_data = $table->countPageStates($this->hp->id, true);
        $dates = $table->fetchPublishedDate($this->hp->id);
        $last_published = 0;
        foreach ($this->assess_article_data as $page_type_code => &$row) {
            $row['published_at'] = isset($dates[$page_type_code]) ? strtotime($dates[$page_type_code]) : 0;
        }

        return $this->assess_article_data;
    }

    public function assessPublishPage($page_type_code) {
        $table = App::make(HpPageRepositoryInterface::class);
        $dates = $table->fetchPublishedDatePage($this->hp->id, $page_type_code);
        return isset($dates['published_at']) ? strtotime($dates['published_at']) : 0;
    }

    /**
     * すべてのpage_tpe_codeを結果を合算する
     *
     * @return array
     */
    public function getTotalResult()
    {
        $result = ['public' => 0, 'draft' => 0, 'new' => 0];

        $last_published = 0;
        $assess_data = $this->assess();
        foreach ($assess_data as $page_type_code => $counts) {
            if (!Assessment::isTargetPageTypeCode($page_type_code)) {
                continue;
            }

            $result['new'] += $counts['new'];
            $result['public'] += $counts['public'];
            $result['draft'] += $counts['draft'];

            if ($counts['published_at'] > $last_published) {
                $last_published = $counts['published_at'];
            }
        }

        // ブログ詳細、お知らせ詳細は未作成時のカウント方法が異なる
        $dtail = $assess_data[config('constants.hp_page.TYPE_INFO_DETAIL')];
        if ($dtail['new'] === 0 && $dtail['public'] === 0 && $dtail['draft'] === 0){
            $result['new'] += 1;
        }

        $dtail = $assess_data[config('constants.hp_page.TYPE_BLOG_DETAIL')];
        if ($dtail['new'] === 0 && $dtail['public'] === 0 && $dtail['draft'] === 0){
            $result['new'] += 1;
        }

        $result['published_at'] = $last_published;

        return $result;
    }

    /**
     * count article bay page_type_code
     * 
     * @return array
     */
    public function countArticlePage($nowPlan)
    {
        $result = array();
        $topPlan		= Plan::factory(CmsPlan::getCmsPLanName(config('constants.cms_plan.TOP_PLAN')	) ) ;
        $_category_map	= $topPlan->pageMapArticle;
        $_category_map_nowPlan = $nowPlan->pageMapArticle;
        $table = App::make(HpPageRepositoryInterface::class);
        $article_data = $table->countChildPageStates($this->hp->id)->all();
        $article_result = array('public' => 0, 'new' => 0, 'draft' => 0);
        foreach($article_data as $page) {
            if (is_null($page['parent_page_id'])) {
                continue;
            }
            $type = null;
            if ($page['page_category_code'] == config('constants.hp_page.CATEGORY_LARGE')) {
                $result[$page['page_type_code']] = array();
            }
            if ($page['page_category_code'] == config('constants.hp_page.CATEGORY_SMALL')) {
                $largePage = array_values(array_filter($article_data, function($item) use ($page) {
                    return $item['id'] == $page['parent_page_id'];
                }));
                if (count($largePage) > 0) {
                    $new = isset($_category_map_nowPlan[$page['page_type_code']]) ? count($_category_map_nowPlan[$page['page_type_code']]) : 0;
                    $result[$largePage[0]['page_type_code']][$page['page_type_code']] = array(
                        'public' => 0,
                        'new' => $new,
                        'draft' => 0,
                    );
                }
            }
            if ($page['page_category_code'] == config('constants.hp_page.CATEGORY_ARTICLE')) {
                $smallPage = array_values(array_filter($article_data, function($item) use ($page) {
                    return $item['id'] == $page['parent_page_id'];
                }));
                if (count($smallPage) > 0) {
                    $typeSmall = $smallPage[0]['page_type_code'];
                    $largePage = array_values(array_filter($article_data, function($item) use ($smallPage) {
                        return $item['id'] == $smallPage[0]['parent_page_id'];
                    }));
                    if (count($largePage) > 0) {
                        $typeLarge = $largePage[0]['page_type_code'];
                    }
                }
                if ($page['page_type_code'] == config('constants.hp_page.TYPE_ARTICLE_ORIGINAL')) {
                    $typeSmall = config('constants.hp_page.TYPE_SMALL_ORIGINAL');
                }
                if (isset($typeLarge) && isset($typeSmall)) {
                    if (!isset($result[$typeLarge][$typeSmall])) {
                        $result[$typeLarge][$typeSmall] = array(
                            'public' => 0,
                            'new' => 0,
                            'draft' => 0,
                        );
                    }
                    if ($page['public_flg'] === 1) {
                        $result[$typeLarge][$typeSmall]['public'] = $result[$typeLarge][$typeSmall]['public'] + 1;
                    } else {
                        $result[$typeLarge][$typeSmall]['draft'] = $result[$typeLarge][$typeSmall]['draft'] + 1;
                    }
                    if ($result[$typeLarge][$typeSmall]['new'] != 0) {
                        $result[$typeLarge][$typeSmall]['new'] = $result[$typeLarge][$typeSmall]['new'] - 1;
                    }
                    if ($typeSmall == config('constants.hp_page.TYPE_SMALL_ORIGINAL')) {
                        $article_result['public'] += $result[$typeLarge][$typeSmall]['public'];
                        $article_result['draft'] += $result[$typeLarge][$typeSmall]['draft'];
                        $article_result['new'] += $result[$typeLarge][$typeSmall]['new'];
                        unset($result[$typeLarge][$typeSmall]);
                    }
                }
            }
        }
        $table = App::make(HpPageRepositoryInterface::class);
        foreach($table->getPageArticleByCategory(config('constants.hp_page.CATEGORY_LARGE')) as $large) {
            if (isset($_category_map[$large])) {
                $_category_map[$large][] = config('constants.hp_page.TYPE_SMALL_ORIGINAL');
            } else {
                $_category_map[$large] = [config('constants.hp_page.TYPE_SMALL_ORIGINAL')];
            }
            foreach($_category_map[$large] as $small) {
                if (!isset($result[$large][$small])) {
                    $new = isset($_category_map_nowPlan[$small]) ? count($_category_map_nowPlan[$small]) : 0;
                    $result[$large][$small] = ['public' => 0, 'draft' => 0, 'new' => $new];
                }
            }
        }
        $result[config('constants.hp_page.TYPE_LARGE_ORIGINAL')][config('constants.hp_page.TYPE_SMALL_ORIGINAL')] = $article_result;
        return $result;
    }

    /**
     * すべてのpage_tpe_codeを結果を合算する
     *
     * @return array
     */
    public function getTotalResultArticle()
    {
        $result = ['public' => 0, 'draft' => 0, 'new' => 0];

        $last_published = 0;
        $assess_data = $this->assessArticle();
        foreach ($assess_data as $page_type_code => $counts) {
            // if (!Assessment::isTargetPageTypeCode($page_type_code)) {
            //     continue;
            // }

            $result['new'] += $counts['new'];
            $result['public'] += $counts['public'];
            $result['draft'] += $counts['draft'];

            if ($counts['published_at'] > $last_published) {
                $last_published = $counts['published_at'];
            }
        }
        $result['published_at'] = $last_published;

        return $result;
    }
}