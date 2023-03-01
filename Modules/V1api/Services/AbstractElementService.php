<?php
namespace Modules\V1api\Services;

use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
require_once(base_path()."/library/phpQuery-onefile.php");
use Auth;

abstract class AbstractElementService
{
    // 共通
    const TEMPLATES_BASE         = 'Resources/templates';
    // SP用
    const TEMPLATE_SP_SEARCH_BREAD  = 'Resources/templates/breadcrumb.sp.tpl';
    
    const URL_SHUMOKU_TOP  = 'shumoku.html';
    const URL_RENT_TOP     = 'rent.html';
    const URL_PURCHASE_TOP = 'purchase.html';
    const BREAD_CRUMB_1ST_SHUMOKU = '種目選択';
    const BREAD_CRUMB_2ND_PREF    = '都道府県選択';

    protected $logger;
    protected $_config;

    public function __construct()
    {
        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }
    
    public final function execute(Params $params, Settings $settings, Datas $datas)
    {
    	$this->check($params, $settings, $datas);
    	$this->create($params, $settings, $datas);
    	// public変数を、変数名と値の連想配列にして返す。
    	return $this->getViewResult($this);
    }

    abstract function create(
    	Params $params,
    	Settings $settings,
    	Datas $datas);

    abstract function check(
    		Params $params,
    		Settings $settings,
    		Datas $datas);
        
    protected function getTemplateDoc($tpl) {
    	$template_file = module_path('V1api', static::TEMPLATES_BASE) .'/' . $tpl;
    	$html = file_get_contents($template_file);
    	return \phpQuery::newDocument($html);
    }

    protected function getViewResult ($instance)
    {
    	$viewResult = array();
    	$ref = new \ReflectionClass($instance);
    	$props = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
    	$isFirst = true;
    	foreach ($props as $prop)
    	{
    		$prop->setAccessible(true);
    		$val = $prop->getValue($instance);
    		if (! $val) {
    			continue;
    		}
    		$viewResult[$prop->getName()] = $val;
    	}
    	return $viewResult;
    }
    
    /**
     * 検索条件設定の物件種目から検索TOP画面のURLファイル名を判断して返します。
     *
     * @param Models\SearchCondSettings $searchCond
     * @return shumoku.html or rent.html or purchase.html
     */
    protected function getSearchTopFileName($searchCond)
    {
        if ($searchCond->containBothShumoku())
        {
            return $this::URL_SHUMOKU_TOP;
        }
        else if ($searchCond->containPurchaseShumoku())
        {
            return $this::URL_PURCHASE_TOP;
        }
        else
        {
            return $this::URL_RENT_TOP;
        }
    }

    /*
     * PC用パンくず
     */
    protected function createBreadCrum($doc, $levels)
    {
        // ０階層のホーム設定はしない
        // １階層の場合は、子階層を削除
        $doc['li[itemtype="http://data-vocabulary.org/Breadcrumb"]']->remove();
        $doc['ul']->append('<li><a href="/">ホーム</a></li>');
        foreach($levels as $url => $name)
        {
            if (empty($url))
            {
                // li を作成
                $doc['ul']->append("<li>${name}</li>");
            } else
            {
                // 子階層を追加
                $newElem = pq("<a>")->attr('href', $url)->text($name);
                $doc['ul']->append('<li>');
                $doc['ul li:last']->append($newElem);
            }
        }

        return $levels;
    }
    
    protected function createSpecialBreadCrum($doc, $levels, $specialTitle) {
    	// 第一階層に特集名を付与
    	foreach ($levels as $path => $level) {
    		$levels[$path] = $specialTitle.'：'.$levels[$path];
    		break;
    	}
    	return $this->createBreadCrum($doc, $levels);
    }

    /*
     * SP用パンくず作成
     */
    protected function createBreadCrumbSp($doc, $levels)
    {
        // テンプレートで統一されてなかったので一律削除
        $doc['li']->remove();
        // 子階層を追加
        $newElem = pq("<a>")->attr('href', '/')->text('ホーム');
        $doc['ul']->append('<li/>');
        $doc['ul li:last']->append($newElem);
        foreach($levels as $url => $name)
        {
            if (empty($url))
            {
                // 最後の階層li を作成
                $doc['ul']->append("<li>${name}</li>");
            } else
            {
                // 子階層を追加
                $newElem = pq("<a>")->attr('href', $url)->text($name);
                $doc['ul']->append('<li/>');
                $doc['ul li:last']->append($newElem);
            }
        }
        return $levels;
    }

    protected function createSpecialBreadCrumbSp($doc, $levels, $specialTitle) {
        // 第一階層に特集名を付与
        foreach ($levels as $path => $level) {
            $levels[$path] = $specialTitle.'：'.$levels[$path];
            break;
        }
        return $this->createBreadCrumbSp($doc, $levels);
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return ServiceUtils::getVal($name, $stdClass, $null);
    }
    protected $removeSearchFreeword = null;

    protected function removeSearchFreeword($shikugunWithLocateCd) {
        if (is_bool($this->removeSearchFreeword)) {
            return $this->removeSearchFreeword;
        }

        $locateGroups = $shikugunWithLocateCd['shikuguns'][0]['locate_groups'];
        foreach ($locateGroups as $locate) {

            $cnt = 0;
            foreach ($locate['shikuguns'] as $shikugun) {
                $cnt += $shikugun['count'];
            }

            if ($cnt > 0) {
                $this->removeSearchFreeword = false;
                return $this->removeSearchFreeword;
            }
        }
        $this->removeSearchFreeword = true;
        return $this->removeSearchFreeword;
    }

    protected function getPersonalSortVals() {
        return [
            'no01' => implode(",", [ Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no02' => implode(",", [ Params::SORT_KAKAKU_DESC, Params::SORT_EKI_KYORI ]),
            'no03' => implode(",", [ Params::SORT_ENSEN_EKI, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no04' => implode(",", [ Params::SORT_SHOZAICHI, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no05' => implode(",", [ Params::SORT_EKI_KYORI, Params::SORT_KAKAKU ]),
            'no06' => implode(",", [ Params::SORT_MADORI_INDEX, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no07' => implode(",", [ Params::SORT_SENYUMENSEKI_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no08' => implode(",", [ Params::SORT_TOCHI_MS_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no09' => implode(",", [ Params::SORT_CHIKUNENGETSU_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no10' => implode(",", [ Params::SORT_SHINCHAKU_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no11' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no12' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no13' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_KAKAKU_DESC, Params::SORT_EKI_KYORI ]),
            'no14' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_ENSEN_EKI, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no15' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_SHOZAICHI, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no16' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_EKI_KYORI, Params::SORT_KAKAKU ]),
            'no17' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_MADORI_INDEX, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no18' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_SENYUMENSEKI_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no19' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_TOCHI_MS_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no20' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_CHIKUNENGETSU_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
            'no21' => implode(",", [ Params::SORT_SHUMOKU, Params::SORT_SHINCHAKU_DESC, Params::SORT_KAKAKU, Params::SORT_EKI_KYORI ]),
        ];
    }

    /**
     * テストサイト・制作代行サイトの物件リクエストページが存在する場合は物件リクエストページのURLを返す
     *
     * @param $class
     * @param $settings
     * @param $params
     * @return array
     */
    protected function estateRequestPage($class, $settings, $params) {
        stream_context_set_default( [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $estateRequest = array();
        if ($params->isTestPublish()) {
            switch ($class) {
                case 1:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@test.' . $settings->company->getRow()->getSiteDomain() . '/request-kasi-kyojuu/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-kasi-kyojuu/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 2:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@test.' . $settings->company->getRow()->getSiteDomain() . '/request-kasi-jigyou/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-kasi-jigyou/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 3:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@test.' . $settings->company->getRow()->getSiteDomain() . '/request-uri-kyojuu/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-uri-kyojuu/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 4:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@test.' . $settings->company->getRow()->getSiteDomain() . '/request-uri-jigyou/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-uri-jigyou/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                default:
                    $estateRequest['url'] = '';
                    $estateRequest['requestUrl'] = '';
                    break;
            }
        } elseif ($params->isAgencyPublish()) {
            switch ($class) {
                case 1:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@substitute.' . $settings->company->getRow()->getSiteDomain() . '/request-kasi-kyojuu/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-kasi-kyojuu/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 2:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@substitute.' . $settings->company->getRow()->getSiteDomain() . '/request-kasi-jigyou/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-kasi-jigyou/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 3:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@substitute.' . $settings->company->getRow()->getSiteDomain() . '/request-uri-kyojuu/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-uri-kyojuu/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                case 4:
                    $estateRequest['url'] = 'https://' . $settings->company->getRow()->member_no . ':' . $settings->company->getHpRow()->test_site_password . '@substitute.' . $settings->company->getRow()->getSiteDomain() . '/request-uri-jigyou/edit/';
                    $estateRequest['requestUrl'] = "<a href='/request-uri-jigyou/edit/' target='_blank'>リクエストはこちらから</a>";
                    break;
                default:
                    $estateRequest['url'] = '';
                    $estateRequest['requestUrl'] = '';
                    break;
            }
        }
        return $estateRequest;
    }
}