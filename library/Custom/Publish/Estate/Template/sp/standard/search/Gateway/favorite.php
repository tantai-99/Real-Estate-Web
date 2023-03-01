<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class Favorite extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/favorite';
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

        // favorite bukken id
        $favoriteList = $this->request->getFavoriteAll();;
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $favoriteList !== null ? implode(',', $favoriteList) : '');

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



