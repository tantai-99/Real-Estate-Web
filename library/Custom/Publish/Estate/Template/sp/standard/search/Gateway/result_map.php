<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class ResultMap extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/spatial-map';
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

        // from condition
        session_start();
        if($this->request->getPost(SearchPages::FROM_CONDITION)==true) {
            $params = [
                SearchCondition::CONDITION => $this->request->getPost('condition'),
            ];
            $this->session->setNamespace('condition');
            $this->session->set($params);

        // from select-city-on-map
        }elseif(($this->request->getPost(SearchPages::FROM_MAP_CITY_SELECT)==true) && ($this->request->getPost('for_change')==true)){
            $this->session->setNamespace('map_condition');
            $this->session->destroy();

        // from default
        }else{
            $this->session->setNamespace('condition');
            $this->session->destroy();
            $this->session->setNamespace('map_condition');
            $this->session->destroy();
        }

        // base
        $url = $this->apiSearchUrl();

        // city
        $url .= $this->addParam(ApiGateway::KEY_CITY, $this->request->getCityFromUrl(4));

        
        $url = str_replace('ken_ct=here','',$url);
        $url = str_replace('shikugun_ct=here','',$url);

        $view = $this->fetch($url);

        if($this->session->hasNamespace('map_condition')){
            $this->session->setNamespace('map_condition');
            $view->map_condition = true;
            $view->center = $this->session->get('center');
            $view->zoom   = $this->session->get('zoom');
        }

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}
