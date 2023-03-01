<?php
namespace Modules\V1api\Http\Controllers;

use Modules\V1api\Models;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use App\Traits\JsonResponse;

class AccessCountController extends ApiAbstractController
{
    protected $params;
    protected $settings;

	public function preDispatch()
	{
		// パラメータ取得
		$params = (object) $this->_request->all();
		$this->params = new Models\Params($params);
		$this->settings = new Models\Settings($this->params);
	}

    /**
     * パノラマAccess-Counter API
     */
    public function panorama()
    {
        $logic = new Models\Logic();
        $datas = $logic->detail($this->params, $this->settings);

        /*
         *　評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        if (! $this->params->isProdPublish()) return;
        // Controllerで物件情報をAPIから取得
        $bukken = $datas->getBukken();
        if (is_null($bukken)) return;

        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];

        // パノラマログ

        // 物件番号
        $bukken_no = Services\ServiceUtils::getVal('bukken_no', $dispModel, true);
        // 会員番号
        $member_no = $this->settings->company->getRow()->member_no;
        // パノラマコンテンツID
        $panorama_contents_id = Services\ServiceUtils::getVal('panorama_contents_id', $dataModel, true);
        // PC/モバイル区分
        $agent = $this->params->isPcMedia() ? '00' : '02';
        // 物件ID
		$bukken_id = Services\ServiceUtils::getVal('id', $dispModel, true);
        // 物件バージョン番号
        $version_no = Services\ServiceUtils::getVal('version_no', $dataModel, true);

        Models\Logger\CLogger::logPanorama(
            $this->params, $bukken_no, $member_no, $panorama_contents_id, $agent, $bukken_id, $version_no);
    }
}