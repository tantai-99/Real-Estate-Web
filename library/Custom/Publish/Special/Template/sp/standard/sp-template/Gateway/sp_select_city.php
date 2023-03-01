<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/phpQuery-onefile.php');

class SpSelectCity extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/city';
    protected $_namespace    = 'condition';
    protected $_seo_tags     = [
        // SeoTags::NOINDEX,
        // SeoTags::NOFOLLOW,
        SeoTags::NOARCHIVE,
        SeoTags::CANONICAL,
        // SeoTags::REL,
        SeoTags::ALTERNATE,
    ];

    public function run() {

        // reset condition (GET only)
        $this->resetSessionCondition();

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // �����ꗗ��ʂ���J�ڂ��Ă����ꍇ�A�������@�^�u�𖳌��ɂ��A������������ǉ�����
        session_start();
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


        if (!$view) {
            return $view;
        }

        // base url
        $view->baseUrl = $this->request->getMobileSelectBaseUrl();

        // populate
        $condition = $this->session->getAll();
        if (isset($condition['city'])) {

            $content = $view->api->content;
            $doc     = phpQuery::newDocument($content);

            $cityList = explode(',', $condition['city']);
            foreach ($cityList as $city) {
                $doc["input[value=\"{$city}\"]"]->attr('checked', 'checked');
            }

            $view->api->content = $doc->htmlOuter();
        }

        return $view;
    }
}



