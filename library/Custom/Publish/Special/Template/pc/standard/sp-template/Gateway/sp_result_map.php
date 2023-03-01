<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class SpResultMap extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/spatial-map';
    protected $_namespace    = '';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_MAP;
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        // condition
        if(($this->request->getPost(SearchPages::FROM_CONDITION)==true)||
            (($this->request->getPost(SearchPages::FROM_MAP_CITY_SELECT)==true) && ($this->request->getPost('for_change')==true)))
        {
            $params = [
                SearchCondition::CONDITION => $this->request->getPost('condition'),
            ];
            $this->session->setNamespace('condition');
            session_start();
            $this->session->set($params);
        }else{
            $this->session->setNamespace('condition');
            $this->session->destroy();
        }

        // base
        $url = $this->apiSearchUrl();

        // city
        $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getCityFromUrl(4));

        // スマホの「現在地から探す」の場合は都道府県と市区郡パラメータを渡さない
        $url = str_replace('ken_ct=here','',$url);
        $url = str_replace('shikugun_ct=here','',$url);

        $view = $this->fetch($url);
        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}
