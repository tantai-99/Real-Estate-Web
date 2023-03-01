<?php

require_once(APPLICATION_PATH.'/../script/Request.php');
require_once(APPLICATION_PATH.'/../script/Session.php');
require_once(APPLICATION_PATH.'/../script/UserAgent.php');
require_once(APPLICATION_PATH.'/../script/ApiGateway.php');
require_once(APPLICATION_PATH.'/../script/ApiConfing.php');
require_once(APPLICATION_PATH.'/../script/SearchShumoku.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');
require_once(APPLICATION_PATH.'/../script/_ApiControllerInterface.php');
require_once(APPLICATION_PATH.'/../script/SearchCondition.php');
require_once(APPLICATION_PATH.'/../script/Path.php');
require_once(APPLICATION_PATH.'/../script/Log.php');

abstract class AbstractApiController implements ApiControllerInterface {

    /**
     * 連携サーバーからデータ取得
     */
    protected $request;
    protected $ua;
    protected $session;
    protected $apiGateway;
    protected $apiConfig;
    protected $logger;

    protected $apiHost;

    protected $name      = '';
    protected $searchUrl = '';
    protected $modalUrl  = '/v1api/parts/modal';
    protected $facetUrl  = '/v1api/parts/count';
    protected $suggestUrl= '/v1api/parts/suggest';
    protected $countUrl  = '/v1api/parts/count-bukken';

    public function __construct(viewHelper $viewHelper, array $config) {

        $this->viewHelper = $viewHelper;
        $this->request    = new Request();
        $this->ua         = new UserAgent();
        $this->apiGateway = new ApiGateway($config);
        $this->apiConfig  = new ApiConfing();
        $this->logger     = new Log();

        $this->apiHost = 'http://'.$this->apiConfig->get('domain');

        session_write_close();
    }

    /**
     * API 実行
     */
    public function run() {

        $methodName = $this->methodName();
        if (!method_exists($this, $methodName)) {
            // error response
            exit;
        }

        $this->$methodName();
        exit;
    }

    /**
     * 物件検索
     */
    protected function searchAction() {

        $this->searchAbstract($this->searchUrl);
    }

    /**
     * モーダル更新
     */
    protected function modalAction() {

        $this->searchAbstract($this->modalUrl);
    }

    /**
     * こだわり条件 ファセット更新
     */
    protected function fetchKodawariFacetAction() {

        $this->searchAbstract($this->facetUrl, 'json');
    }

    /**
     * アクセスカウンター
     */
    protected function accesscountAction() {
        if(empty($this->request->directory(4))) {
            // error response
            exit;
        }
        if(method_exists($this, 'accessCount')) {
            $target = $this->request->directory(4);
            $this->accessCount($target);
        }
    }
   
    /**
     * メソッド判定
     *
     * @return string
     */
    protected function methodName() {

        $name = '';

        if(preg_match('/^detail-/', $this->request->directory(2))) {
            if(!empty($this->request->directory(3))) {
                switch($this->request->directory(3)) {
                    case 'accesscount':
                        $name = 'accesscount';
                        return $name.'Action';
                        break;
                    default:
                        break;
                }
            }
        }

        $search = !preg_match('/^sp-/', $this->request->directory(2));

        foreach ($this->request->parse['path_array'] as $i => $val) {

            if ($search) {
                $i++;
            }

            if ($i < 2) {
                continue;
            }

            if ($i === 2) {
                $name .= strtolower($val);
                continue;
            }

            $name .= ucfirst(strtolower($val));
        };
        return $name.'Action';
    }

    /**
     * URLパラメータセット
     *
     * @param      $key
     * @param      $value
     * @param bool $first
     * @return string
     */
    protected function addParam($key, $value, $first = false) {

        $and = '&';
        if ($first) {
            $and = '?';
        }

        if (is_array($value)) {

            $value = implode(',', $value);
        }

        return $and.(string)$key.'='.urlencode((string)$value);
    }

    /**
     * エラーレスポンス
     *
     * @param string $msg
     */
    protected function error($msg = 'System error') {

        $res = [
            'success' => false,
            'message' => $msg,
        ];
        echo json_encode($res);
    }

    /**
     * テーマごとのHTMLの差分を反映
     *
     * @param $html
     * @return mixed
     */
    protected function moveElementByTheme($html) {

        $theme = ucfirst($this->getTheme());
        //デザインパターン追加（カラー自由版）
        if(strpos($theme, '_custom_color') !== false) {
            $theme = str_replace('_custom_color', 'CustomColor', $theme);
        }
        //デザインパターン追加（カラー自由版）
        $class = "Theme_{$theme}";
        require_once(APPLICATION_PATH."/../script/Theme/pc/{$theme}.php");
        $hp = unserialize($this->viewHelper->getContentSettingFile('hp.txt'));
        return (new $class($hp,$html))->run();
    }

    /**
     * 相対パスを絶対パスに変換
     *
     * @param $html
     * @return String
     */
    protected function setAbsolutePath($html) {

        $path = new Path();
        return $path->setAbsolutePath($html);
    }


    /**
     * テーマ名を取得
     *
     * @return string
     */
    private function getTheme() {

        $html = file_get_contents(APPLICATION_PATH."/pc/search/View/header/stylesheet.blade.php");
        $doc  = phpQuery::newDocument($html);
        return $doc['.css-theme-color']->attr('data-theme');
    }

    /**
     * suggest action
     */
    protected function suggestAction() {

        $this->suggestCountAbstract($this->suggestUrl, 'suggest', 'json');
    }

    /**
     * counter action
     */
    protected function countAction() {

        $this->suggestCountAbstract($this->countUrl, 'count', 'json');
    }



}