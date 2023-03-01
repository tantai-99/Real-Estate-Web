<?php

require_once(APPLICATION_PATH.'/../script/Theme/pc/_Abstract.php');

class Theme_Natural02CustomColor extends Theme_Abstract {

    public function run() {

        if($this->config['page_code']==SearchPages::RESULT_MAP ||
            $this->config['page_code']==SearchPages::SP_RESULT_MAP){
            $this->updateMapHeader();
        }else{
            $this->addLeaf();
        }

        $this->customTag();
        return $this->doc->htmlOuter();
    }

    /**
     * 地図検索用ヘッダに変える
     */
    protected function updateMapHeader()
    {
    	$pageHeader = $this->doc['.page-header'];
    	$pageHeader->wrap('<div class="maps-header"></div>');
    	$pageHeader->addClass('page-header-liquid')->addClass('close');
    	
    	$pageHeaderInner = $pageHeader->children('.page-header-inner')	;
    	$pageHeaderTopInner = $pageHeaderInner->children('.inner');
    	
    	// 地図用の小さいロゴ
    	$logoS=$pageHeaderTopInner->children('.logo')->clone();
    	$logoS->addClass('logo-s')->removeClass('logo');
    	$logoS->children('a')->append('<span class="company-link">トップページ</span>');
    	$logoS->children('a')->children('.company-img')->attr('style', 'display: none;');
    	if ($logoS->children('a')->children('.company-tx')->length<=0) {
    		$logoS->children('a')->prepend('<span class="company-tx"></span>');
    	}
    	$logoS->children('a')->children('.company-tx')->text($this->hp['outline']);
    	$pageHeaderInner->children('.page-header-top')->prepend( $logoS		) ;
    	
    	// 地図用の電話番号
    	$telS=$pageHeaderTopInner->children('.header-info')->children('.tel')->clone();
    	$telS->addClass('tel-s')->addClass('show')->removeClass('tel');
    	$pageHeaderInner->children('.page-header-top')->prepend( $telS		) ;
    	
    	// SNSボタン
			$pageHeaderTopInner->append($pageHeader->children('.header-sns'));
    	
    	$pageHeaderTopInner->children('.tx-explain')->attr('style', 'display: none;');
    	$pageHeaderTopInner->children('.link2')->attr('style', 'display: none;');
    	$pageHeaderTopInner->children('logo-s')->attr('style', 'display: block;');
    	
    	$this->doc['.maps-header']->append( $this->doc['.gnav']->attr( 'style','display:none;' ) ) ;
    	
    	$logoS=$pageHeader->children('.inner')->children('.logo')->children('a')->append('<span class="company-link">トップページ</span>');
    	
    	$pageHeader->children('.page-header-top,.inner')->wrapAll('<div class="page-header-inner">' ) ;
    }
    
    /**
     * 'header'
     *
     */
    private function addLeaf() {
        //$this->doc['body']->children('.page-header')->wrap('<div class="leaf">');
    	$this->doc['body']->children('.page-header')->children('.page-header-top,.inner')->wrapAll('<div class="page-header-inner">');
    }

}