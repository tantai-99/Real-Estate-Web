<?php

namespace Modules\V1api\Http\Controllers;

use Modules\V1api\Models;
use Modules\V1api\Services;
use Library\Custom\Model\Estate;
use App\Traits\JsonResponse;
use Modules\V1api\Models\Logger\CLogger;

class SpecialController extends ApiAbstractController
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

        $currentPagesSpecialRow = $this->settings->special->getCurrentPagesSpecialRow();
        if (!$currentPagesSpecialRow) {
            throw new \Exception('指定された特集は存在しません。', 404);
        }
    }

    /**
     * 特集TOP 都道府県選択 or 検索結果一覧
     */
    public function pref()
    {
        // 直接一覧判定用パラメータ
        $this->view->directResult = false;

        // 検索画面なし設定の場合
        $specialSetting = $this->settings->special->getCurrentPagesSpecialRow()->toSettingObject();

        $areaSearchFilter = $specialSetting->area_search_filter;

        if (!$specialSetting->area_search_filter->has_search_page) {
            return $this->result();
        }
        // 都道府県がひとつの場合
        else if (count($areaSearchFilter->area_1) == 1 && $this->params->getAllowRedirect() === 'true') {
            $ken_cd = $areaSearchFilter->area_1[0];
            $ken_ct = Estate\PrefCodeList::getInstance()->getUrl($ken_cd);
            $this->params->setParam('ken_ct', $ken_ct);

            // 市区郡設定がある場合
            if ($specialSetting->area_search_filter->hasAreaSearchType()) {
                $this->view->redirectUrl = "/{$specialSetting->filename}/{$ken_ct}/";
                $redirectUrl = $this->view->redirectUrl;
            } else if ($specialSetting->area_search_filter->hasLineSearchType()) {
                // 沿線がひとつの場合
                if (count($areaSearchFilter->area_3->getAll()) == 1) {
                    $ensen_cd = $areaSearchFilter->area_3->getAll()[0];
                    $ensen_ct = Services\ServiceUtils::getEnsenCtByCd($ken_cd, $ensen_cd);
                    $this->params->setParam('ensen_ct', $ensen_ct);
                    $this->view->redirectUrl = "/{$specialSetting->filename}/{$ken_ct}/{$ensen_ct}-line/";
                    $redirectUrl = $this->view->redirectUrl;
                } else {
                    $this->view->redirectUrl = "/{$specialSetting->filename}/{$ken_ct}/line.html";
                    $redirectUrl = $this->view->redirectUrl;
                }
            } else if ($specialSetting->area_search_filter->hasSpatialSearchType()) {
                $this->view->redirectUrl = "/{$specialSetting->filename}/{$ken_ct}/map.html";
                $redirectUrl = $this->view->redirectUrl;
            }
            return $this->successV1api(['redirectUrl' => $redirectUrl]);
        }

        // データ取得
        $logic = new Models\Logic();
        $datas = $logic->prefSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplPref();
        } else {
            $maker = new Services\Sp\SplPref();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 市区郡選択
     */
    public function city()
    {
        // データ取得
        $logic = new Models\Logic();
        $datas = $logic->citySpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplCity();
        } else {
            $maker = new Services\Sp\SplCity();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 町名検索
     */
    public function choson()
    {
        $logic = new Models\Logic();
        $datas = $logic->chosonSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplChoson();
        } else {
            $maker = new Services\Sp\SplChoson();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 沿線選択
     */
    public function line()
    {
        $specialSetting = $this->settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        if (count($specialSetting->area_search_filter->area_3->getAll()) == 1) {
            $ken_cd = $specialSetting->area_search_filter->area_1[0];
            $ken_ct = Estate\PrefCodeList::getInstance()->getUrl($ken_cd);
            $this->params->setParam('ken_ct', $ken_ct);

            $ensen_cd = $specialSetting->area_search_filter->area_3->getAll()[0];
            $ensen_ct = Services\ServiceUtils::getEnsenCtByCd($ken_cd, $ensen_cd);
            $this->params->setParam('ensen_ct', $ensen_ct);
            return $this->successV1api(['redirectUrl' => "/{$specialSetting->filename}/{$ken_ct}/{$ensen_ct}-line/"]);
        }

        // データ取得
        $logic = new Models\Logic();
        $datas = $logic->lineSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplLine();
        } else {
            $maker = new Services\Sp\SplLine();
        }

        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }

    /**
     * 駅選択
     */
    public function eki()
    {
        // データ取得
        $logic = new Models\Logic();
        $datas = $logic->ekiSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplEki();
        } else {
            $maker = new Services\Sp\SplEki();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    /**
     * SP用こだわり条件から探す画面API
     */
    public function condition()
    {
        $logic = new Models\Logic();
        $datas = $logic->conditionSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            throw new \Exception('Illegal Access.');
        } else {
            $maker = new Services\Sp\SplCondition();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }


    /**
     * 結果一覧
     */
    public function result()
    {
        $logic = new Models\Logic();
        $specialSetting = $this->settings->special->getCurrentPagesSpecialRow()->toSettingObject();
        $areaSearchFilter = $specialSetting->area_search_filter;
        $methodSetting = Estate\SpecialMethodSetting::getInstance();

        // 検索画面が無い場合はS_TYPEを上書きする
        if (!$specialSetting->area_search_filter->has_search_page) {
            $search_type = $specialSetting->area_search_filter->search_type[0];
            switch ($search_type) {
                case '1':    // 地域から探す(市区郡から抽出する) or 地域から探す(町名から抽出する)
                    if (count($areaSearchFilter->area_2) == 0) {
                        $this->params->setParam('s_type', 6);    // S_TYPE_RESULT_PREF
                    } else if (count($areaSearchFilter->area_5) == 0) {
                        $this->params->setParam('s_type', 7);    // S_TYPE_RESULT_CITY_FORM
                    } else {
                        $this->params->setParam('s_type', 11);    // S_TYPE_RESULT_CHOSON_FORM
                    }
                    break;
                case '2':    // 沿線・駅から探す（沿線・駅で抽出する）
                    $this->params->setParam('s_type', 1);    // S_TYPE_RESULT_STATION_FORM
                    break;
                default:    // dead-route
                    $this->params->setParam('s_type', 6);    // S_TYPE_RESULT_PREF
                    break;
            }
            $this->params->setParam('no_search_page', 1);
        }

        if ($this->params->getDirectAccess() || !$specialSetting->area_search_filter->has_search_page) {
            $datas = $logic->resultDirectSpl($this->params, $this->settings);
            if ($this->params->isPcMedia()) {
                $maker = new Services\Pc\SplDirectResult();
                $elements = $maker->execute($this->params, $this->settings, $datas);

                $hiddenMaker = new Services\Pc\SplResultHidden();
                $hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);
                $elem = array_merge($elements, $hiddenElements);
            } else {
                $maker = new Services\Sp\SplDirectResult();
                $elements = $maker->execute($this->params, $this->settings, $datas);
                $elem = $elements;
            }
        } else {
            $datas = $logic->resultSpl($this->params, $this->settings);
            if ($this->params->isPcMedia()) {
                $maker = new Services\Pc\SplResult();
                $elements = $maker->execute($this->params, $this->settings, $datas);

                // PC版の検索動線あり物件一覧のみHidden（モーダル）要素が必要
                // ↑修正になりました
                $hiddenMaker = new Services\Pc\SplResultHidden();
                $hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);

                $elem = array_merge($elements, $hiddenElements);
            } else {
                $maker = new Services\Sp\SplResult();
                $elements = $maker->execute($this->params, $this->settings, $datas);
                $elem = $elements;
            }
        }

        /*
         *　評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        if ($this->params->isProdPublish()) {
            // 検索結果０件はログ出力しない
            $bukkenList = $datas->getBukkenList();
            if (!$bukkenList['total_count'] == 0) {
                $cts = (array)$this->params->getTypeCt();
                $class = Estate\TypeList::getInstance()->getClassByUrl($cts[0]);
                $special_id = $specialSetting->id;
                CLogger::logResult(
                    $this->params,
                    $class,
                    $special_id
                );
            }
        }

        return $this->successV1api($elem);
    }
    /**
     * 市区郡選択
     */
    public function spatialCity()
    {
        // データ取得
        $logic = new Models\Logic();
        $datas = $logic->citySpl($this->params, $this->settings, true);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplSpatialCity();
        } else {
            $maker = new Services\Sp\SplSpatialCity();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);
        return $this->successV1api($elements);
    }
    /**
     * 地図検索画面API
     */
    public function spatialMap()
    {
        $datas = new Models\Datas();
        $datas->setParamNames(new Models\ParamNames($this->params));
        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SplSpatialMap();
            $elements = $maker->execute($this->params, $this->settings, $datas);
        } else {
            $maker = new Services\Sp\SplSpatialMap();
            $elements = $maker->execute($this->params, $this->settings, $datas);
        }

        return $this->successV1api($elements);
    }
    /**
     * 地図検索中心点API
     */
    public function spatialMapcenter()
    {
        $logic = new Models\Logic();
        $coordinate = $logic->mapcenterSpl($this->params, $this->settings);
        // $this->view->assign(['coordinate' => $coordinate]);
        return $this->successV1api(['coordinate' => $coordinate]);
    }
    /**
     * 地図検索アサイドと物件情報API
     */
    public function spatialEstate()
    {
        $logic = new Models\Logic();
        $datas = $logic->spatialEstateSpl($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SpatialEstate();
            $elements = $maker->execute($this->params, $this->settings, $datas);

            $hiddenMaker = new Services\Pc\SplResultHidden();
            $hiddenElements = $hiddenMaker->execute($this->params, $this->settings, $datas);
            $elem = array_merge($elements, $hiddenElements);
        } else {
            // 通常を検索を使い回し
            $maker = new Services\Sp\SpatialEstate();
            $elements = $maker->execute($this->params, $this->settings, $datas);
            $elem = $elements;
        }

        /*
         * 評価分析ログ
         */
        // 本番サイトじゃなければログは出さない。
        if ($this->params->isProdPublish()) {
            $specialSetting = $this->settings->special->getCurrentPagesSpecialRow()->toSettingObject();
            $spatialEstate = $datas->getSpatialEstate();
            if ($spatialEstate['total_count'] != 0) {
                $cts = (array)$this->params->getTypeCt();
                $class = Estate\TypeList::getInstance()->getClassByUrl($cts[0]);
                $special_id = $specialSetting->id;
                CLogger::logMap($this->params, $class, $special_id);
            }
        }

        return $this->successV1api($elem);
    }
    /**
     * 地図検索物件一覧（右カラム）
     * いらないかもしれない
     */
    public function spatialEstatelist()
    {
        $logic = new Models\Logic();
        $datas = $logic->spatialMapEstatelist($this->params, $this->settings);

        if ($this->params->isPcMedia()) {
            $maker = new Services\Pc\SpatialEstatelist();
        } else {
            $maker = new Services\Sp\SpatialEstatelist();
        }
        $elements = $maker->execute($this->params, $this->settings, $datas);

        return $this->successV1api($elements);
    }

    public function counter()
    {
        $logic = new Models\Logic();
        $datas = $logic->count($this->params, $this->settings);
        $bukkenList = $datas->getBukkenList();
        // $this->view->total = $bukkenList['total_count'];
        return $this->successV1api(['total' => $bukkenList['total_count']]);
    }
}
