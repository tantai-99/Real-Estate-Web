<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class ResultFreeword extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_FREEWORD;

    public function run() {

        // must
        $url = $this->apiUrl();

        $url .= $this->addParam(ApiGateway::KEY_F_TYPE, $this->request->directory(1));
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->_s_type);
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
