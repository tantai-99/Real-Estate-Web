<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
// require_once(APPLICATION_PATH.'/../script/SearchResult.php');

class ResultFreeword extends _AbstractGateway {

    protected $_page_api_url = '/v1api/search/result';
    protected $_namespace    = 'condition';
    protected $_s_type       = SearchPages::S_TYPE_RESULT_FREEWORD;

    public function run() {

        // must
        $url = $this->apiSearchUrl();

        $url .= $this->addParam(ApiGateway::KEY_F_TYPE, $this->request->directory(1));
        $url .= $this->addParam(ApiGateway::KEY_S_TYPE, $this->_s_type);
        // condition
        $params = [];
        if(!is_null($this->request->getPost('condition'))) {
            $params[ SearchCondition::CONDITION ] = $this->request->getPost('condition');
        }
        if(!is_null($this->request->getPost('fulltext'))) {
            $params[ SearchCondition::FULLTEXT ] = $this->request->getPost('fulltext');
            if(is_null($this->request->getPost('from_result')) && is_null($this->request->getPost('from_condition'))) {
                $params[ SearchCondition::CONDITION ] = null;
            }
        }
        session_start();
        $this->session->set($params);
        $data = [];
        $paramCondition      = $this->session->get(SearchCondition::CONDITION);
        $paramFulltext       = $this->session->get(SearchCondition::FULLTEXT);
        if ($paramCondition !== null || $paramFulltext !== null) {
            parse_str($paramCondition."&".$paramFulltext, $data);
        }

        $view = $this->post($url, $data);

        if (!$view) {
            return $view;
        }

        $view = $this->setNoindexIfNoEstate($view);

        return $view;
    }

}
