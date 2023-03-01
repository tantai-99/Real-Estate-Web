<?php
namespace Modules\V1api\Services\Sp\Element;
use Modules\V1api\Services;
use Modules\V1api\Models\PageInitialSettings;
use Modules\V1api\Models\Params;
use Library\Custom\Model\Estate;

use phpQuery;
class Detail
{
    const TEMPLATES_BASE         = '/../../../Resources/templates';

    protected $logger;
    protected $_config;

    public function __construct()
    {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);

        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }

    /**
     * 物件詳細の要素を作成して返します。
     *
     * @return 物件詳細の要素
     */
    public function createElement($shumoku, $kaiinNo, $bukken, Params $params, $codeList, PageInitialSettings $pageInitialSettings, $searchCond)
    {
        $dispModel = (object) $bukken['display_model'];
        // 種目情報の取得
        $shumokuCt = Services\ServiceUtils::getShumokuCtByCd($shumoku);

        // 物件種目ごとのテンプレートは、ここで取得する。
        $template_file = dirname(__FILE__) . static::TEMPLATES_BASE . "/detail/${shumokuCt}.sp.tpl";
        $html = file_get_contents($template_file);
        $doc = phpQuery::newDocument($html);

        $contentElem = $doc;

        switch ($shumoku)
        {
            case Services\ServiceUtils::TYPE_CHINTAI:
                $maker = new Detail\Chintai();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_KASI_TENPO:
            case Services\ServiceUtils::TYPE_KASI_OFFICE:
                $maker = new Detail\KasiTenpoOffice();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_PARKING:
                $maker = new Detail\Parking();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_KASI_TOCHI:
                $maker = new Detail\KasiTochi();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_KASI_OTHER:
                $maker = new Detail\KasiOther();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_MANSION:
                $maker = new Detail\Mansion();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_KODATE:
                $maker = new Detail\Kodate();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_URI_TOCHI:
                $maker = new Detail\UriTochi();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_URI_TENPO:
            case Services\ServiceUtils::TYPE_URI_OFFICE:
                $maker = new Detail\UriTenpoOffice();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            case Services\ServiceUtils::TYPE_URI_OTHER:
                $maker = new Detail\UriOther();
                $maker->createElement($kaiinNo, $contentElem, $bukken, $shumoku, $params, $codeList, $pageInitialSettings, $searchCond);
                break;
            default:
                throw new \Exception('Illegal Argument.');
                break;
        }

        return $contentElem;
    }

}