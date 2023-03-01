<?php
namespace Modules\V1api\Services\Sp;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use App\Repositories\HpPage\HpPageRepositoryInterface;

class SpatialModal extends Services\AbstractElementService
{
    public $head;
    public $header;
    public $content;
    public $info;

    public function create(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        $this->content = $this->content($params, $settings, $datas);
    }

    public function check(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
    }

    private function content(
            Params $params,
            Settings $settings,
            Datas $datas)
    {
        $doc = $this->getTemplateDoc("/spatial/result.sp.tpl");

        // 変数
        $comName = $settings->page->getCompanyName();
        $searchCond = $settings->search;
        $type_ct = $params->getTypeCt();
        
        if (is_array($type_ct)) {
            foreach ($type_ct as $key => $value) {
                $type_id[] = Estate\TypeList::getInstance()->getTypeByUrl($value);
            }
        }else{
            $type_id = Estate\TypeList::getInstance()->getTypeByUrl($type_ct);
        }

        //物件リクエストリンクの表示設定 #####
        $class = Estate\TypeList::getInstance()->getClassByType($type_id);
        $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
        $hpPageRow = null;
        $estateRequest = null;
        if ($params->isTestPublish() || $params->isAgencyPublish()) {
            $estateRequest = $this->estateRequestPage($class, $settings, $params);
            $get_header = @get_headers($estateRequest['url']);
            if ($get_header[0] != "HTTP/1.1 404 Not Found" && isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $doc['div.btn-request-txt']->append($estateRequest['requestUrl']);
            } else {
                $doc['div.btn-request-txt']->remove();
            }
        } else {
            if (isset($estateSettngRow) && $estateSettngRow->estate_request_flg == 1) {
                $hpPage = \App::make(HpPageRepositoryInterface::class);
                $hpPageRow = $hpPage->getRequestPageRow($settings->company->getHpRow()->id, $class);
                if ($hpPageRow) {
                    $requestUrl = "<a href='/" . $hpPageRow->public_path . "' target='_blank'>リクエストはこちらから</a>";
                    $doc['div.btn-request-txt']->append($requestUrl);
                } else {
                    $doc['div.btn-request-txt']->remove();
                }
            }
        }

        // こだわり条件
        $searchFilter = $datas->getSearchFilter();

        $resultMaker = new Element\Result();        //使いまわしています
        $resultMaker->createElement($type_ct, $doc, $datas, $params, $settings->special, !!$params->getSpecialPath(), $settings->page, $settings->search);

        return $doc->html();
    }
}