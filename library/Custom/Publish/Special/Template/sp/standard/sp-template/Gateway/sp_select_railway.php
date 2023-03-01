<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');

class SpSelectRailway extends _AbstractGateway {

    protected $_page_api_url = '/v1api/special/line';
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

        $this->resetSessionCondition();

        // fetch
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_PREFECUTURE, $this->request->directory(2));

        // �����ꗗ��ʂ���J�ڂ��Ă����ꍇ�A�������@�^�u�𖳌��ɂ��A���������ǉ�����
        $data = [];
        session_start();
        if($this->request->getPost('condition')){
            $params = [
                SearchCondition::CONDITION => $this->request->getPost('condition'),
            ];
            $this->session->set($params);
            if ($this->session->get(SearchCondition::CONDITION) !== null) {
                parse_str($this->session->get(SearchCondition::CONDITION), $data);
            }
        }
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
        if (isset($condition['railway'])) {

            $content = $view->api->content;
            $doc     = phpQuery::newDocument($content);

            $railwayList = explode(',', $condition['railway']);
            foreach ($railwayList as $railway) {
                $doc["input[value=\"{$railway}\"]"]->attr('checked', 'checked');
            }

            $view->api->content = $doc->htmlOuter();
        }

        return $view;
    }
}



