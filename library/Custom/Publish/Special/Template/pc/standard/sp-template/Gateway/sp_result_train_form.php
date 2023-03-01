<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class SpResultTrainForm extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/result';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];
    protected $_s_type       = SearchPages::S_TYPE_RESULT_STATION_FORM;

    public function run() {

        // must
        $url = $this->apiSearchUrl();

        // station
        if ($this->request->getPost('station')) {
            $url .= $this->addParam(ApiGateway::KEY_STATION, $this->request->getPost('station'));
        }

        // condition
        $data = [];
        if ($this->request->getPost('detail')) {
            parse_str($this->request->getPost('detail'), $data);
        }

        $view = $this->post($url, $data);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}

