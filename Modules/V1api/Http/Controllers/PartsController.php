<?php

namespace Modules\V1api\Http\Controllers;

use Modules\V1api\Models;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use App\Traits\JsonResponse;
class PartsController extends ApiAbstractController
{
    use JsonResponse;
    protected $params;
    protected $settings;

    public function preDispatch()
    {
        // パラメータ取得
        $params = (object) $this->_request->all();
        $this->params = new Models\Params($params);
        $this->settings = new Models\Settings($this->params);

        if ($this->params->getSpecialPath()) {
            $currentPagesSpecialRow = $this->settings->special->getCurrentPagesSpecialRow();
            if (!$currentPagesSpecialRow) {
                throw new \Exception('指定された特集は存在しません。', 404);
            }
            // 種目を設定
            $types = explode(',', $currentPagesSpecialRow->enabled_estate_type);
            $typeCts = [];
            foreach ($types as $type) {
                $typeCts[] = Estate\TypeList::getInstance()->getUrl($type);
            }
            if (count($typeCts) === 1) {
                $typeCts = $typeCts[0];
            }
            $this->params->setParam('type_ct', $typeCts);
        }
    }

    /**
     * 物件一覧画面API
     */
    public function modal()
    {
        $logic = new Models\Logic();
        $datas = $logic->modal($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            if ($this->params->getSpecialPath()) {
                $maker = new Services\Pc\SplResultHidden();
                $maker->isModal = true;
            } else {
                $maker = new Services\Pc\ResultHidden();
                $maker->isModal = true;
            }
        } else {
            throw new \Exception('not implemented yet.');
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    /**
     * 地図検索物件一覧画面API
     */
    public function estatelist()
    {
        $logic = new Models\Logic();
        if (is_null($this->params->getSpecialPath())) {
            $datas = $logic->spatialEstate($this->params, $this->settings, true);
        } else {
            $datas = $logic->spatialEstateSpl($this->params, $this->settings, true);
        }

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SpatialModal();
            $elements = $maker->execute($this->params, $this->settings, $datas);
            return $this->successV1api($elements);
        }
        else
        {
            $maker = new Services\Sp\SpatialModal();
            $elements = $maker->execute($this->params, $this->settings, $datas);
            return $this->successV1api($elements);
        }
    }

    public function koma()
    {
        $logic = new Models\Logic();
        $datas = $logic->koma($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\Koma();
        } else {
            $maker = new Services\Sp\Koma();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    public function count()
    {
        $logic = new Models\Logic();
        $datas = $logic->count($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\Count();
        } else {
            throw new \Exception('not implemented yet.');
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    public function komaTop()
    {
        $logic = new Models\Logic();
        $datas = $logic->koma($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\KomaTop();
        } else {
            $maker = new Services\Sp\KomaTop();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    public function suggest() {
        $logic = new Models\Logic();
        $datas = $logic->suggest($this->params, $this->settings);
        $maker = new Services\Pc\Suggest();
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    public function countBukken() {
        $logic = new Models\Logic();
        $datas = $logic->countBukken($this->params, $this->settings);
        $maker = new Services\Pc\CountBukken();
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    public function saveOperation()
    {
        // 不正なパラメータの場合は処理を行わない
        $func = $this->params->getOperation();
        $user_id = $this->params->getUserId();
        $bukken_id = $this->params->getBukkenId();
        $operationHistory = new Models\OperationHistory($this->params->getComId());
        if (empty($func) || !is_callable([$operationHistory, $func]) || empty($user_id) || empty($bukken_id)) {
            return;
        }
        $operationHistory->$func($user_id, $bukken_id);
    }
}
