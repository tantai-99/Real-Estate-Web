<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class History extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/history';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // url
        $url = $this->apiUrl();

        // history bukken id
        $histories = $this->request->getHistoriesAll();
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $histories !== null ? implode(',', $histories) : '');

        $searchtab = $this->request->getPost('searchtab');
        if(!is_null($searchtab)) {
            $url .= $this->addParam('searchtab', $searchtab);
        }
        $checklisttab = $this->request->getPost('checklisttab');
        if(!is_null($searchtab)) {
            $url .= $this->addParam('checklisttab', $checklisttab);
        }
        $sort = $this->request->getPost('sort');
        if(!is_null($sort)) {
            $url .= $this->addParam('sort', $sort);
        } else {
            $url .= $this->addParam('sort', 'asc');
        }

        return $this->fetch($url);
    }

}



