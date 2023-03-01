<?php
namespace Library\Custom\Publish\Prepare;

use Carbon\Carbon;

abstract class PrepareAbstract {

    private $reserve;

    private $hpRow;
    private $pageRows;
    private $companyRow;

    private $namespace;

    private $params;

    protected function setCompanyRow($companyRow) {
        $this->companyRow = $companyRow;
    }

    protected function setHpRow($hpRow) {
        $this->hpRow = $hpRow;
    }

    protected function setPageRows($pageRows) {
        $this->pageRows = $pageRows;
    }

    protected function setParams($params) {
        $this->params = $params;
    }

    public function setNamespace($name, $namespace) {
        if(app('request')->hasSession())
        {
            app('request')->session()->put($name, $namespace);
        }
        $this->namespace = $namespace;
    }

    public function unsetNamespace($name) {
        if(app('request')->hasSession())
        {
            app('request')->session()->put($name, new \stdClass());
        }
        $this->namespace = null;
    }

    protected function setReserve($reserve) {
        $this->reserve = $reserve;
    }

    public function getHpRow() {
        return $this->hpRow;
    }

    public function getPageRows() {
        return $this->pageRows;
    }


    public function getCompanyRow() {
        return $this->companyRow;
    }

    public function getParam($key) {

        return $this->params->$key;
    }

    public function getParamsAll() {
        return $this->params;
    }

    public function getNamespace($name) {
        if(app('request')->hasSession())
        {
            return app('request')->session()->get($name);
        }
        return $this->namespace;
    }

    /**
     *
     *
     * @return Library\Custom\Publish\Prepare\Reserve
     */
    public function getReserve() {
        return $this->reserve;
    }

    // end of getter, setter

    /**
     * 日付日時をアプリケーションのフォーマットに変換する
     *
     * @param $datetime
     *
     * @return string
     */
    public function dateForApp($datetime) {
        return Carbon::parse($datetime)->format("Y年m月d日H時");
    }

    /**
     * 日付日時をDBのフォーマットに変換する
     *
     * @param $datetime
     *
     * @return string
     */
    public function dateForDb($datetime) {
        if($datetime == 0 || is_null($datetime)) {
            return 0;
        }

        $datetime = str_replace(' ', '', $datetime);

        $yyyyy = mb_substr($datetime, 0, 4);
        $mm = mb_substr($datetime, 5, 2);
        $dd = mb_substr($datetime, 8, 2);
        $hh = mb_substr($datetime, 11, 2);

        return $yyyyy.'-'.$mm.'-'.$dd.' '.$hh.':00:00';

    }

    /**
     * 新たに更新情報を設定したページ
     * @return array
     */
    public function getUpdatePageIds($params) {

        $pageIds = [];

        if (!isset($params['page'])) {
            return $pageIds;
        }

        foreach ($params['page'] as $pageId => $val) {

            if ($val['update']) {
                $pageIds[] = $pageId;
            }
        }

        return $pageIds;
    }

    public function getRequest() {
        return app('request');
    }
}

?>
