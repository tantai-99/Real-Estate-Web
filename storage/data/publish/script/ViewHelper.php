<?php

/*
 * ビューヘルパー
 *
 */

class ViewHelper {

    public $protocol='http';

    public function __construct($view) {

        if(file_exists(APPLICATION_PATH.'/../setting/site.ini')) {
            $siteIni        =   glob(APPLICATION_PATH.'/../setting/site.ini')[0];
            $site           =   parse_ini_file($siteIni, true);
            $this->protocol =   $site['protocol'];
        }
        $this->_view = $view;
    }

    public function includeCommonFile($partial, $h1 = null, $head = null) {

        $sepa = '/';
        $file = '_'.$partial.'.html';
        $path = $this->_view->viewPath.$sepa."common".$sepa.$this->_view->device.$sepa.$file;
        return include($path);
    }

    public function includeSide($data) {
        $sepa = '/';
        $file = '_side.html';
        $path = $this->_view->viewPath.$sepa."common".$sepa.$this->_view->device.$sepa.$file;
        return include($path);
    }

    public function factory($className) {

        $sepa = '/';
        $file = $className.'.php';
        require_once $this->_view->scriptPath.$sepa.$file;
        return new $className($this->_view);
    }

    /**
     * setting内のファイルを取得
     *
     * @param $fileName
     *
     * @return string
     */
    public function getContentSettingFile($fileName) {

        $sepa = '/';
        return file_get_contents($this->pathToSetting().$sepa.$fileName);
    }

    /**
     * setting内のiniファイルを読み込む
     *
     * @param $fileName
     *
     * @return array
     */
    public function parseIniFileInSetting($fileName, $process_sections = false) {

        $sepa = '/';
        return parse_ini_file($this->pathToSetting().$sepa.$fileName, $process_sections);
    }

    private function pathToSetting() {

        $sepa = '/';
        return $this->_view->viewPath.$sepa.'..'.$sepa.'setting';
    }

    public function uri($path) {

        $length = strlen($path) - strlen('/index.html');
        return substr($path, 0, $length);
    }

    /**
     * hrefを出力
     * - リンクはtarget属性も合わせて出力
     *
     * @param $page
     *
     * @return string
     */
    public function hpHref($page) {

        // リンクページはURLを出力
        if ($page['page_type_code'] == 90) {
            $target = '';
            if ($page['link_target_blank']) {
                $target = 'target="_blank"';
            }
            return 'href="'.$page['link_url'].'" '.$target.' rel="nofollow"';
        }

        $target = '';

        $protocol = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';

        // target
        $target = '';

        if (41 <= $page['page_type_code'] && $page['page_type_code'] <= 47) {
            $target = ' target="_blank"';
        }

        // domain
        $domain = $_SERVER['HTTP_HOST'];

        // target
        if (($page['page_type_code'] == 91 || $page['page_type_code'] == 92 || $page['page_type_code'] == 93) && $page['link_target_blank'] == 1) {
            $target = ' target="_blank"';
        }

        // uri
        if ($page['page_type_code'] == 93) {
            if(is_string($page['link_house']) && is_array($jsonData = json_decode($page['link_house'], true)) && (json_last_error() == JSON_ERROR_NONE)) {
                $uri = $jsonData['url'];
                if (isset($jsonData['house_type']) && count($jsonData['house_type']) > 1) {
                    $uris = explode('/', $uri);
                    $uri = $this->getUrlLinkHouse($jsonData['house_type']).$uris[2].'/';
                }
            } else {
                $uri = $page['link_house'];
            }
        } else {
            $uri = '/'.substr($page['new_path'], 0, mb_strlen($page['new_path']) - strlen('index.html'));
        }

        return 'href="'.$protocol.$domain.$uri.'"'.$target;
    }

    /**
     * ファイル名（正確にはディレクトリ名ですが。。）を取得
     *
     * @param $parentDir
     *
     * @return mixed
     */
    public function getFileName($parentDir) {

        $contact = [
            'edit',
            'confirm',
            'complete',
            'error',
        ];

        $dirs     = explode('/', $parentDir);
        $filename = array_pop($dirs);

        // - ページネーションページ
        // - お問い合わせ
        // はもう一つ上の階層
        if (is_numeric($filename) || in_array($filename, $contact)) {
            $filename = array_pop($dirs);
        }

        return $filename;
    }

    /**
     * ファイル名からページを判定
     * - トップページ、リンク、エイリアスは非対応（ページ名の設定がないため）
     *
     * @param $filename
     *
     * @return array
     */
    public function getPageByFileName($filename) {

        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['filename'] == $filename) {
                return $page;
            }
        }
    }

    public function getPages() {

        return unserialize($this->getContentSettingFile('pages.txt'));
    }

    public function getPageByType($page_type_code) {
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['page_type_code'] == $page_type_code) {
                return $page;
            }
        }
    }

    public function getPageById($id) {
        foreach (unserialize($this->getContentSettingFile('page_info.txt')) as $page) {
            if ($page['id'] == $id) {
                return $page;
            }
        }
    }

    public function getPageByLinkId($id) {
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['link_id'] == $id) {
                return $page;
            }
        }
    }

    public function getPageByIdPage($id) {
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['id'] == $id) {
                return $page;
            }
        }
    }

    public function getChildByLinkId($linkId) {
        $list = [];
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['parent_page_id'] == $linkId) {
                $list[] = $page;
            }
        }
        return $list;
    }

    public function getChildWithoutPageTypeCode($pageTypecode, $categories) {
        foreach ($categories as $key => $category) {
            if ($category == $pageTypecode) {
                unset($categories[$key]);
            }
        }
        return $categories;
    }

    public function getChildWithoutPageId($pageId, $category) {
        $list = [];
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['page_category_code'] == $category && $page['id'] != $pageId) {
                $list[] = $page;
            }
        }
        return $list;
    }

    public function getLargesCategory() {
        $pages = unserialize($this->getContentSettingFile('page_auto_link.txt'));
        $larges = [];
        if (isset($pages['larges'])) {
            $larges = $pages['larges'];
            unset($larges['id']);
        }
        return $larges;
    }

    public function getSmallsCategory() {
        $pages = unserialize($this->getContentSettingFile('page_auto_link.txt'));
        $smalls = [];
        if (isset($pages['smalls'])) {
            $smalls = $pages['smalls'];
            unset($smalls['id']);
        }
        return $smalls;
    }

    public function getArticle($linkId) {
        $pages = unserialize($this->getContentSettingFile('page_auto_link.txt'));
        $article = null;
        if (isset($pages[$linkId])) {
            return $pages[$linkId];
        }
        return $article;
    }

    public function getChildByArrayId($arrLink) {
        $list = [];
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if (in_array($page['parent_page_id'], $arrLink)) {
                $list[] = $page;
            }
        }
        return $list;
    }

    public function getChildByPageTypeCode($pageTypecode) {
        $list = [];
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if (in_array($page['page_type_code'], $pageTypecode)) {
                $list[] = $page;
            }
        }
        return $list;
    }

    public function getLinkInfoDetail($pageId) {
        foreach (unserialize($this->getContentSettingFile('info_detail_link.txt')) as $link) {
            if ($link['page_id'] == $pageId) {
                return $link;
            }
        }
    }

    public function hpLinkHouse($link) {
        if(is_string($link) && is_array($jsonData = json_decode($link, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $link = $jsonData['url'];
            if (isset($jsonData['house_type']) && count($jsonData['house_type']) > 1) {
                $links = explode('/', $link);
                $link = $this->getUrlLinkHouse($jsonData['house_type']).$links[2].'/';
            }
        }
        $domain = $this->protocol.'://'.$_SERVER['HTTP_HOST'];
        return $domain.$link;
    }

    public function getUrlLinkHouse($houseTypes) {
        foreach (unserialize($this->getContentSettingFile('search_setting.txt')) as $searchSetting) {
            $estateTypes = explode(',', $searchSetting['enabled_estate_type']);
            foreach($houseTypes as $type) {
                if (in_array($type, $estateTypes)) {
                    $types = array_intersect($estateTypes, $houseTypes);
                    if (count($types) > 0) {
                        $links = explode('/', $link);
                        return $this->urlEstate($types[0]);
                    }
                }
            }
        }
    }

    public function hpLink($page_link_id) {
        $domain = $this->protocol.'://'.$_SERVER['HTTP_HOST'];
        if (is_numeric($page_link_id)) {
            $page = $this->getPageByLinkId($page_link_id);
            if (!is_null($page) && $page['new_path']) {
                return $domain.'/'.substr($page['new_path'], 0, -10);
            }
            return $domain . '/404notFound';
        } else {
            if (preg_match("/^estate_top/", $page_link_id)) {
                return $domain.'/shumoku.html';
            }

            if (preg_match("/^estate_rent/", $page_link_id)) {
                return $domain.'/rent.html';
            }

            if (preg_match("/^estate_purchase/", $page_link_id)) {
                return $domain.'/purchase.html';
            }

            if (preg_match("/^estate_type_/", $page_link_id)) {
                return $this->urlEstate($page_link_id);
            }

            if (preg_match("/^estate_special_/", $page_link_id)) {
                return $this->urlSpecial($page_link_id);
            }
        }
    }

    public function hpFile2($file2Id) {
        foreach (unserialize($this->getContentSettingFile('file2.txt')) as $file2) {
            if ($file2['id'] == $file2Id) {
                return "/file2s/{$file2['title']}.{$file2['extension']}" ;
            }
        }
        
    }

    public function hpPageTitle($page_link_id, $withFilename = false) {
        $filename = '';
        if (is_numeric($page_link_id)) {
            $page = $this->getPageByLinkId($page_link_id);
            if ($withFilename) {
                $filename = '（'.$page['filename'].'）';
            }
            return $page['title'].$filename;
        }
        else if (preg_match("/^estate_special_/", $page_link_id)) {
            foreach (unserialize($this->getContentSettingFile('special_setting_all.txt')) as $special) {
                $id = str_replace('estate_special_', '', $page_link_id);
                if ($special['origin_id'] == $id) {
                    if ($withFilename) {
                        $filename = '（'.$special['filename'].'）';
                    }
                    return $special['title'].$filename;
                }
            }
        }
    }

    public function checkNewMark($page, $date) {
        if ($page['new_mark'] == 0 || is_null($page['new_mark']) || strtotime($date) > time())
            return false;
        return time() <= strtotime("+".$page['new_mark']." day", strtotime($date));
    }

    public function urlEstate($page_link_id) {
        $estate = array(
            1       => "chintai",
            2       => "kasi-tenpo",
            3       => "kasi-office",
            4       => "parking",
            5       => "kasi-tochi",
            6       => "kasi-other",
            7       => "mansion",
            8       => "kodate",
            9       => "uri-tochi",
            10      => "uri-tenpo",
            11      => "uri-office",
            12      => "uri-other",
            1001    => "chintai-jigyo-1",
            1002    => "chintai-jigyo-2",
            1003    => "chintai-jigyo-3",
            1101    => "baibai-kyoju-1",
            1102    => "baibai-kyoju-2",
            1201    => "baibai-jigyo-1",
            1202    => "baibai-jigyo-2"
        );
        return '/'.$estate[str_replace('estate_type_', '', $page_link_id)].'/';
    }

    public function urlSpecial($page_link_id) {
        foreach (unserialize($this->getContentSettingFile('special_setting.txt')) as $special) {
            $id = str_replace('estate_special_', '', $page_link_id);
            if ($special['origin_id'] == $id) {
                return '/'.$special['filename'];
            }
        }
    }

    public function getCategoryClassInfo($pageId) {
        $result = array();
        foreach (unserialize($this->getContentSettingFile('category_class_info.txt')) as $category) {
            if ($category['link_id'] == $pageId) {
                $result = $category;
            }
        }
        return $result;
    }

    /** render Image Resize use skitter slider.
     * @param $param
     */
    public function outImage($param){

      $filename   =  realpath('images/'.$param['src']);
      if(!$filename || !$param['src'] || !is_numeric($param['width']) || !is_numeric($param['height']) ){
        return;
      }
      $etag=md5($filename);
      $last_modified_time=filemtime($filename);
      $image_info = getimagesize($filename);
      $image_type = $image_info[2];
      header('Content-type: image/png');
      $dWidth  = $param['width'];
      $dHeight = $param['height'];
      if( $image_type == IMAGETYPE_JPEG ) {
        $image = imagecreatefromjpeg($filename);
      } elseif( $image_type == IMAGETYPE_GIF ) {
        $image = imagecreatefromgif($filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
        $image = imagecreatefrompng($filename);
      }
      $new_image = imagecreatetruecolor($dWidth, $dHeight);
      $rHeight = $dHeight;
      $rWidth  = $dWidth;
      $sHeight = imagesy($image);
      $sWidth  = imagesx($image);
      $dRate =  $dWidth/$dHeight;
      $sRate =  $sWidth/$sHeight;
      $dx = 0;
      $dy = 0;
          $rate = $dWidth / $sWidth;
          $dHeight = $sHeight * $rate;
      
      imagealphablending($new_image, false);
      imagesavealpha($new_image, true);
      $background = imagecolorallocatealpha($new_image, 255,255, 255,127);
      $background = imagecolortransparent($new_image,$background);
      imagefilledrectangle($new_image, 0, 0, $rWidth, $rHeight, $background);
      imagecopyresampled($new_image, $image,$dx,$dy,0, 0,  $dWidth, $dHeight,  $sWidth, $sHeight);
      imagepng($new_image);
      $patch_new='images/customize-image-auto-resize/'.$param['width'].'/'.$param['height'].'/'.$param['src']; 
      if(!file_exists(realpath('images/customize-image-auto-resize'))){
        mkdir('images/customize-image-auto-resize');
        mkdir('images/customize-image-auto-resize/'.$param['width']);
        mkdir('images/customize-image-auto-resize/'.$param['width'].'/'.$param['height']);
      }
      if(!file_exists(realpath($patch_new))){
        imagepng($new_image,$patch_new);
        chmod($patch_new,0755);
      }
      imagedestroy($new_image);
      exit;
            
    }

    public function getPathPublic($parentDir, $device) {
        $paths = explode($device.'/', $parentDir);
        return $paths[1].'/index.html';
    }

    public function getPageByPathPublic($path, $filename) {
        foreach (unserialize($this->getContentSettingFile('pages.txt')) as $page) {
            if ($page['new_path'] == $path && $page['filename'] == $filename) {
                return $page;
            }
        }
    }

    /*
    public function canonical() {

        $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];

        if (preg_match("/index.html$/", $url)) {

            $cut = str_length('index.html');
            $url = substr($url, 0, strlen($url) - $cut);
        }

        if (!preg_match("/\/$/", $url)) {
            $url .= '/';
        }

        return $url;
    }
    */
}