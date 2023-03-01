<?php
namespace Modules\V1api\Services\Cms\Element;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;

class SortTable
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;
    
    private $isPicBukken;

    public function __construct()
    {
    
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    public function createElement($params)
    {
        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/houselist/sorttable.tpl";
        $html = file_get_contents($template_file);
        $doc = \phpQuery::newDocument($html);

        if ($params->getLinkPage()) {
            $doc['th span.js-estate-select-group-check']->html('');
        }

        $sort = $params->getSortCMS();
        $doc['th span']->removeClass('active');
        $doc['th.cell1 span:eq(0)']->attr('data-value', Params::SORT_CMS_MANAGEMENT_NO);
        if ($sort == Params::SORT_CMS_MANAGEMENT_NO) {
            $doc['th.cell1 span:eq(0)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_MANAGEMENT_NO_DESC);
        }
        if ($sort == Params::SORT_CMS_MANAGEMENT_NO_DESC) {
            $doc['th.cell1 span:eq(0)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_MANAGEMENT_NO);
        }

        $doc['th.cell1 span:eq(1)']->attr('data-value', Params::SORT_CMS_HOUSE_NO);
        if ($sort == Params::SORT_CMS_HOUSE_NO) {
            $doc['th.cell1 span:eq(1)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_HOUSE_NO_DESC);
        }
        if ($sort == Params::SORT_CMS_HOUSE_NO_DESC) {
            $doc['th.cell1 span:eq(1)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_HOUSE_NO);
        }

        $doc['th.cell2 span:eq(0)']->attr('data-value', Params::SORT_CMS_SHUMOKU);
        if ($sort == Params::SORT_CMS_SHUMOKU) {
            $doc['th.cell2 span:eq(0)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_SHUMOKU_DESC);
        }
        if ($sort == Params::SORT_CMS_SHUMOKU_DESC) {
            $doc['th.cell2 span:eq(0)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_SHUMOKU);
        }

        $doc['th.cell2 span:eq(1)']->attr('data-value', Params::SORT_CMS_KAKAKU);
        if ($sort == Params::SORT_CMS_KAKAKU) {
            $doc['th.cell2 span:eq(1)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_KAKAKU_DESC);
        }
        if ($sort == Params::SORT_CMS_KAKAKU_DESC) {
            $doc['th.cell2 span:eq(1)']->removeClass('descend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_KAKAKU);
        }

        $doc['th.cell2 span:eq(2)']->attr('data-value', Params::SORT_CMS_SHINCHAKU);
        if ($sort == Params::SORT_CMS_SHINCHAKU) {
            $doc['th.cell2 span:eq(2)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_SHINCHAKU_DESC);
        }
        if ($sort == Params::SORT_CMS_SHINCHAKU_DESC) {
            $doc['th.cell2 span:eq(2)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_SHINCHAKU);
        }

        $doc['th.cell3 span:eq(1)']->attr('data-value', Params::SORT_CMS_SHOZAICHI);
        if ($sort == Params::SORT_CMS_SHOZAICHI) {
            $doc['th.cell3 span:eq(1)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_SHOZAICHI_DESC);
        }
        if ($sort == Params::SORT_CMS_SHOZAICHI_DESC) {
            $doc['th.cell3 span:eq(1)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_SHOZAICHI);
        }

        $doc['th.cell3 span:eq(2)']->attr('data-value', Params::SORT_CMS_ENSEN_EKI);
        if ($sort == Params::SORT_CMS_ENSEN_EKI) {
            $doc['th.cell3 span:eq(2)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_ENSEN_EKI_DESC);
        }
        if ($sort == Params::SORT_CMS_ENSEN_EKI_DESC) {
            $doc['th.cell3 span:eq(2)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_ENSEN_EKI);
        }

        $doc['th.cell5 span:eq(0)']->attr('data-value', Params::SORT_CMS_HOUSE_CATEGORY);
        if ($sort == Params::SORT_CMS_HOUSE_CATEGORY) {
            $doc['th.cell5 span:eq(0)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_HOUSE_CATEGORY_DESC);
        }
        if ($sort == Params::SORT_CMS_HOUSE_CATEGORY_DESC) {
            $doc['th.cell5 span:eq(0)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_HOUSE_CATEGORY);
        }

        $doc['th.cell5 span:eq(1)']->attr('data-value', Params::SORT_CMS_RECOMMENDED);
        if ($sort == Params::SORT_CMS_RECOMMENDED) {
            $doc['th.cell5 span:eq(1)']->removeClass('descend')->addClass('ascend active')
            	->attr('data-value', Params::SORT_CMS_RECOMMENDED_DESC);
        }
        if ($sort == Params::SORT_CMS_RECOMMENDED_DESC) {
            $doc['th.cell5 span:eq(1)']->removeClass('ascend')->addClass('descend active')
            	->attr('data-value', Params::SORT_CMS_RECOMMENDED);
        }

        return $doc;
    }

    protected function getVal($name, $stdClass, $null = false)
    {
        return Services\ServiceUtils::getVal($name, $stdClass, $null);
    }

}