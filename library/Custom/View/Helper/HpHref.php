<?php
namespace Library\Custom\View\Helper;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Library\Custom\Publish\Render\AbstractRender;

class HpHref extends  HelperAbstract{

    public function hpHref($page) {

        // リンクはURLそのまま出力
        if ($page['page_type_code'] == HpPageRepository::TYPE_LINK) {

            $target = '';
            if ($page['link_target_blank']) {
                $target = 'target="_blank"';
            }
            return 'href="'.$page['link_url'].'" '.$target.' rel="nofollow"';
        }

        // http: or https:
        $map = \App::make(HpPageRepositoryInterface::class)->getCategoryMap();

        $target   = '';
        if (in_array($page['page_type_code'], $map[HpPageRepository::CATEGORY_FORM])) {
            $target   = ' target="_blank"';
        }

        // domain
        $protocol = AbstractRender::protocol($this->_view->mode)	;
        $domain  = AbstractRender::www($this->_view->mode).AbstractRender::prefix($this->_view->mode).$this->_view->company->domain;

        // 営業デモ用サイトだと、HTTPSは、使わない
        $config 	= getConfigs('sales_demo');
        if ( strpos( $domain, $config->demo->domain ) ) {
        	$protocol	= 'http://'	;
        }
        
        if (($page['page_type_code'] == HpPageRepository::TYPE_LINK_HOUSE || $page['page_type_code'] == HpPageRepository::TYPE_ALIAS || $page['page_type_code'] == HpPageRepository::TYPE_ESTATE_ALIAS) && $page['link_target_blank'] == 1) {
            $target = ' target="_blank"';
        }

        // urielse {
        if ($page['page_type_code'] == HpPageRepository::TYPE_LINK_HOUSE) {
            if(is_string($page['link_house']) && is_array($jsonData = json_decode($page['link_house'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                $uri = $jsonData['url'];
            } else {
                $uri = $page['link_house'];
            }
        } else {
            $uri = DIRECTORY_SEPARATOR.substr($page['new_path'], 0, strlen($page['new_path']) - strlen('index.html'));
        }

        return 'href="'.$protocol.$domain.$uri.'"'.$target;
    }
    
    protected function dotConvert( $domain, $baseDomain )
    {
    	$target	= substr( $domain, 0, ( strlen( $domain ) - strlen( $baseDomain ) - 1 ) )	;
    	$target = str_replace( '.', '_', $target )	;
    	return "{$target}.{$baseDomain}"	;
    }
}