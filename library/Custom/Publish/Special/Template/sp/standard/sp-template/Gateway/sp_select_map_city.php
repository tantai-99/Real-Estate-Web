<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectMapCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/spatial-city';
    protected $_namespace    = 'redirect';
    protected $_seo_tags     = [
        SeoTags::NOINDEX,
        SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        // SeoTags::CANONICAL,
        // SeoTags::REL,
        // SeoTags::ALTERNATE,
    ];

    public function run() {

        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // redirect from select prefecture
        session_start();
        if ($this->session->get('redirect')) {
            $url .= $this->addParam(ApiGateway::KEY_DIRECT_ACCESS, (string)true);
            $this->session->destroy();
        }

        // �����ꗗ��ʂ���J�ڂ��Ă����ꍇ�A�������@�^�u�𖳌��ɂ��A������������ǉ�����
        $data = [];
        if ($this->request->getPost(SearchPages::FROM_RESULT) !== null) {
            $url .= $this->addParam('disable_s_type_tab', (string)true);

            // condition
            $this->session = new Session('condition');
            if ($this->session->get(SearchCondition::CONDITION) !== null) {
                parse_str($this->session->get(SearchCondition::CONDITION), $data);
            }
        }

        //$view = $this->fetch($url);
        $view = $this->post($url, $data);

        return $view;
    }
}



