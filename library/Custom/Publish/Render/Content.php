<?php
namespace Library\Custom\Publish\Render;

use Library\Custom\Logger;
use Library\Custom\Publish;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpImageUsed\HpImageUsedRepositoryInterface;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;
use App\Repositories\HpFile2Used\HpFile2UsedRepositoryInterface;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use App\Repositories\HpFileContent\HpFileContentRepositoryInterface;
use App\Repositories\HpHtmlContent\HpHtmlContentRepositoryInterface;
use App\Repositories\MTheme\MThemeRepository;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\View\Helper;
use Library\Custom\Hp\Page;
use Library\Custom\Model\Estate;
use Library\Custom\Model\Lists;
use Library\Custom\Model\Top;
use Modules\Api\Http\Form\Contact\ContactAbstract;
use Illuminate\Support\Carbon;

require_once(base_path().'/library/phpQuery-onefile.php');
require_once(base_path().'/library/template.inc');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

class Content extends AbstractRender {

    // 表示記事数
    const ARTICLE_PER_PAGE = 10;

    // 1-Zipファイルの格納上限(単位byte) * FTP転送に要する時間も考慮し500MB程度 
    const ZIPFILE_CAPACITY = 500000000;

    private $topTags;
    private $paramsKoma;
    private $session;
    private $page;
    private $parentPage;

    /**
     * ProgressのAdapter
     * 無通信が発生しないよう定期的にポーリングを行うために設定する。
     */
    private $adapter;

    /**
     * ATHOME_HP_DEV-4840 - Top用プロパティ -
     */
    private $contentPartCommonTop;
    private $contentSocialNetworkTop;

    /**
     * ATHOME_HP_DEV-4866
     * TOPオリジナル公開データの利用の有無および、公開データ保存先(method:setUsePubTop)
     */
    private $usePubTop = false;
    private $pubTopSrcPath = null;

    private $hpPageRepository;

    public function __construct($hpId, $publishType, $pageInstance) {

        parent::__construct($hpId, $publishType);
        $this->setDiff(new Diff($hpId, $publishType));
        $this->page = $pageInstance;
        if(app('request')->hasSession())
        {
            $this->session = app('request')->session()->get('publish');
        }
        $this->hpPageRepository = \App::make(HpPageRepositoryInterface::class);
    }

    /**
     * Library\Custom\ProgressBar\Adapte\JsPush $adapter
     */
    public function setProgressAdapter($adapter) {
        $this->adapter = $adapter;
    }

    /**
     * ATHOME_HP_DEV-4866  公開データ保存先設定
     */
    public function setUsePubTop($pubTopSrcPath) {
        if(!is_null($pubTopSrcPath) && is_dir($pubTopSrcPath)) {
            $this->usePubTop = true;
            $this->pubTopSrcPath = $pubTopSrcPath;
        } else {
            $this->usePubTop = false;
            $this->pubTopSrcPath = null;
        }
    }

    /**
     * プログレスバーのAdapterが設定されていたら、空文字をレスポンスに出力するpollingを行う。
     */
    private function pollingProgressBar() {
        if ($this->adapter) {
            $this->adapter->polling();
        }
    }

    public function setPages($pages) {

        parent::setPages($pages);
        $this->getDiff()->setPages($pages);
    }

    private $parameters;

    /**
     * プレビューのhtmlの中身を取得
     *
     * @param int                      $pageId
     * @param string                   $device
     * @param HpPageRepository $parentPage
     * @param array                    $parameters request parameters
     * @return mixed
     */
    public function preview($pageId, $device, $parentPage = null, array $parameters = null) {

        // サイドレイアウトパラメータがあれば設定
        if (isset($parameters['sidelayout'])) {
            $_sideCommon = new Page\Layout\SideCommon();
            $_sideCommon->sideLayout = $this->getHpRow()->getSideLayout();
            $_sideCommon->setValidSideLayout($parameters['sidelayout']);
            $this->getHpRow()->side_layout = json_encode($_sideCommon->sideLayout, JSON_FORCE_OBJECT);
        }

        if (isset($parameters['navigation']) && $parameters['navigation'] != '') {
            $this->getHpRow()->global_navigation = $parameters['navigation'];
        }

        $this->parameters = $parameters;
        $page = $this->getPage($pageId);

        if (!$page) {
            return;
        }

        $this->parentPage = $parentPage;

        // 一覧ページは別途パラメータ用意
        if ($this->hpPageRepository->hasPagination($page['page_type_code'])) {
            //previewは1ページ目のみ
            $num = isset($parameters['preview_page_num']) ? $parameters['preview_page_num'] + 0 : 1;
            // preview時はpublic_flgを無視する
            $childrenPageIds = $this->getChildrenPageIds($page, true);
            $count = $this->countIndexPage($childrenPageIds);

            return $this->getContent($page['id'], $device, $num, $count, $childrenPageIds);
        }

        return $this->getContent($pageId, $device);
    }

    /**
     * ヘッダーをセット
     * @param $extension
     */
    public function setHeader($extension) {

        switch (strtolower($extension)) {
            case 'js':
                header("Content-type: text/javascript");
                break;
            case 'css':
                header("Content-type: text/".$extension);
                break;
            case 'jpg':
                header("Content-type: image/jpeg");
                break;
            case 'png':
            case 'gif':
                header("Content-type: image/".$extension);
                break;
        }
    }

    /**
     * 公開中のhtmlファイルをローカルに設置
     *
     * @return null
     * @throws Exception
     */
    public function putHtmlFiles() {

        $ds = DIRECTORY_SEPARATOR;

        $hp = $this->getHpRow();

        // zipデータをdbから取得
        $zipContents = $hp->fetchHtmlContent();

        if (!$zipContents) {
            return null;
        }

        $serverPath = storage_path().$ds.'data'.$ds.'html'.$ds.'server';
        $htmlPath   = $serverPath.$ds.$hp->id;
        $zipPath    = $htmlPath.'.zip';

        // make zip file
        $this->mkdirRecursive($serverPath);
        file_put_contents($zipPath, $zipContents);

        // unzip
        exec("unzip -o {$zipPath} -d ".dirname($zipPath).$ds.$hp->id);
        exec("rm -rf {$zipPath}");

    }

    /**
     * viewファイルのレンダリング
     *
     */
    public function view() {

        // 公開中のhtmlを作業領域にコピー
        $this->copyHtmlFiles();

        // 非公開に変更になったファイルを削除
        $this->getDiff()->deletePrivateHtml();

        // パスの変更になったファイルを移動
        $this->getDiff()->movePathChangedHtml();

        $this->pollingProgressBar(); // browser polling
        // Koma Top Original .
        Logger\Publish::getInstance()->infoRender('view---renderContentsKoma::start');
        $this->renderContentsKoma();
        Logger\Publish::getInstance()->infoRender('view---renderContentsKoma::end');

        // info list top Original
        Logger\Publish::getInstance()->infoRender('view---renderInfoListTop::start');
        $this->renderInfoListTop();
        Logger\Publish::getInstance()->infoRender('view---renderInfoListTop::end');

        // 差分ページのレンダリング（通常ページ）
        Logger\Publish::getInstance()->infoRender('view---Pages::start');
        $this->renderPages();
        Logger\Publish::getInstance()->infoRender('view---Pages::end');
        $this->pollingProgressBar(); // browser polling

        // ATHOME_HP_DEV-5104
        if( $this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC')
            || $this->getPublishType() == config('constants.publish_type.TYPE_SUBSTITUTE') ) {
            foreach($this->getPages() as $page) {
                // 『公開 => 下書き』の場合 image,file,file2 にNULL設定
                if(!$page['public_flg'] && $page['public_flg'] !== 0) {
                    $row = $this->hpPageRepository->fetchRowById($page['id']);
                    $row->public_flg = 0;
                    $row->public_image_ids = null;
                    $row->public_file_ids  = null;
                    $row->public_file2_ids = null;
                    $row->save();
                }
            }
        }

        // 物件検索レンダリング
        Logger\Publish::getInstance()->infoRender('view---EstateSearch::start');
        $this->renderEstateSearch();
        Logger\Publish::getInstance()->infoRender('view---EstateSearch::end');
        $this->pollingProgressBar(); // browser polling
        
        // 特集レンダリング
        Logger\Publish::getInstance()->infoRender('view---Special::start');
        $this->renderSpecial();
        Logger\Publish::getInstance()->infoRender('view---Special::end');


        // サイトマップ
        Logger\Publish::getInstance()->infoRender('view---SitemapPage::start');
        $this->renderSitemapPage();
        Logger\Publish::getInstance()->infoRender('view---SitemapPage::end');


        // 404
        Logger\Publish::getInstance()->infoRender('view---404Page::start');
        $this->render404Page();
        Logger\Publish::getInstance()->infoRender('view---404Page::end');


        // 共通パーツのレンダリング
        Logger\Publish::getInstance()->infoRender('view---CommonParts::start');
        $this->renderCommonParts();
        Logger\Publish::getInstance()->infoRender('view---CommonParts::end');

       

        // フロントコントローラーをレンダリング
        Logger\Publish::getInstance()->infoRender('view---FrontController::start');
        $this->getFrontController();
        Logger\Publish::getInstance()->infoRender('view---FrontController::end');


        // sitemp.xml
        Logger\Publish::getInstance()->infoRender('view---sitemap_xml::start');
        $this->sitemap_xml();
        $this->robots_txt();
        Logger\Publish::getInstance()->infoRender('view---sitemap_xml::end');

    }

    /**
     * @throws Exception
     */
    public function rootTopOriginal(){
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-root::start');
        $outDir = $this->getTempPublicPath().'/top';
        $this->mkdirRecursive($outDir);
        $user = $this->getCompanyRow();

        // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
        if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
            $storeDir = $this->pubTopSrcPath;
        } else {
            $storeDir = Lists\Original::getOriginalImportPath($user->id);
        }
        $this->copyFilesInRoot($storeDir, $outDir);
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-root::end');
    }

    public function imgsTopOriginal(){
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-image::start');
        $outDir = $this->getTempPublicPath().'/top/images';
        $this->mkdirRecursive($outDir);
        $user = $this->getCompanyRow();

        // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
        if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
            $storeDir = $this->pubTopSrcPath . '/top_images';
        } else {
            $storeDir = Lists\Original::getOriginalImportPath($user->id, 'top_images');
        }
        $this->copyFilesInDir($storeDir, $outDir);
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-image::end');
    }

    public function jsTopOriginal(){
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-js::start');
        $outDir = $this->getTempPublicPath().'/top/js';
        $this->mkdirRecursive($outDir);
        $user = $this->getCompanyRow();

        // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
        if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
            $storeDir = $this->pubTopSrcPath . '/top_js';
        } else {
            $storeDir = Lists\Original::getOriginalImportPath($user->id, 'top_js');
        }
        self::labelTopOriginal($storeDir);
        $this->copyFilesInDir($storeDir, $outDir);
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-js::end');
    }

    public function cssTopOriginal(){
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-css::start');
        
        $outDir = $this->getTempPublicPath().'/top/css';
        $this->mkdirRecursive($outDir);
        $user = $this->getCompanyRow();

        // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
        if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
            $storeDir = $this->pubTopSrcPath . '/top_css';
        } else {
            $storeDir = Lists\Original::getOriginalImportPath($user->id, 'top_css');
        }
        $this->copyFilesInDir($storeDir, $outDir);
        Logger\Publish::getInstance()->infoRender('TopOriginal---copy-css::end');
    }

    /**
     * css fdp
     */
    function cssFDP() {
        foreach ($this->getDeviceList() as $device) {
            $outDir = $this->getTempPublicPath().'/'.$device.'/css/fdp';
            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->getBaseCssPath();
            // {$theme}/{$device}/{$fdp}/*
            $storeDir = $storeDirBase.'/standard/'.$device.'/fdp';
            $this->copyDirRecursive($storeDir, $outDir);

            $colorsetting = file_get_contents($outDir."/fdp_style.css");
            // if(!$colorsetting) {
            //     $msg = 'ファイルが存在しません。';
            //     throw new \Exception($msg);
            // }
            if(strpos($this->getThemeRow()->name, 'custom_color') !== false && $this->getColorCode() != "") {
                $rgbs = $this->conversionColorcodeToRbg($this->getColorCode());
                $color = ['rgba('.$rgbs["red"] .",". $rgbs["green"] .",". $rgbs["blue"].',1)', 'rgba('.$rgbs["red"] .",". $rgbs["green"] .",". $rgbs["blue"].',0.8)'];
            } else {
                $color = $this->listColorThemeFdp()[$this->getThemeRow()->name][$this->getColorRow()->name];
            }
            // 4601 render color ballon button contact fdp
            if (in_array($this->getThemeRow()->name, array('elegant','feminine','japanese','katame01','luxury01','retro01','simple'))) {
                $color[] = '#c60d30';
            } else {
                $color[] = '#ff5c02';
            }
            $colorsetting = str_replace(array('#025392', '#025393', '#025394'), $color, $colorsetting);
            file_put_contents($outDir."/fdp_style.css", $colorsetting);

            // 4622: Change css button popup contact FDP
            $colorContact = file_get_contents($outDir."/contact-fdp.css");
            if (in_array($this->getThemeRow()->name, array('elegant','feminine','japanese','katame01','simple','luxury01','retro01'))) {
                $color = Estate\FdpType::getInstance()->listColorContact()[$this->getThemeRow()->name];
            } else {
                $color = Estate\FdpType::getInstance()->listColorContact()['default'];
            }
            $colorContact = str_replace(array('#E37630', '#ff8542', '#ff5c02'), $color, $colorContact);
            file_put_contents($outDir."/contact-fdp.css", $colorContact);
            // end 4622
        }
    }

    /**
     * js FDP
     */
    public function jsFDP() {
        foreach ($this->getDeviceList() as $device) {
            $outDir = $this->getTempPublicPath().'/'.$device.'/js/fdp';
            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->{'getBase'.ucfirst(strtolower('js')).'Path'}();
            // {$theme}/{$device}/{$fdp}/*
            $storeDir = $storeDirBase.'/standard/'.$device.'/fdp';
            $this->copyDirRecursive($storeDir, $outDir);
        }
    }

    /**
     * img FDP
     */
    public function imgsFDP() {
        foreach ($this->getDeviceList() as $device) {

            $outDirFdp = $this->getTempPublicPath().'/'.$device.'/imgs/fdp';
            $this->mkdirRecursive($outDirFdp);

            $storeDirBase = $this->getBaseImgsPath();
            // {$theme}/{$device}/{$fdp}/*
            $storeDir = $storeDirBase.'/standard/'.$device.'/fdp';
            $this->copyFilesInDir($storeDir, $outDirFdp);
        }
    }


    /**
     * jsファイルを用意
     */
    public function js() {

        foreach ($this->getDeviceList() as $device) {

            $outDir = $this->getTempPublicPath().'/'.$device.'/js';
            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->{'getBase'.ucfirst(strtolower('js')).'Path'}();

            // common/{$device}/*
            $storeDir = $storeDirBase.'/common/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // standard/{$device}/*
            $storeDir = $storeDirBase.'/standard/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // {$theme}/{$device}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);
        }
    }

    /**
     * cssファイルを用意
     */
    public function css() {

        foreach ($this->getDeviceList() as $device) {

            $outDir = $this->getTempPublicPath().'/'.$device.'/css';

            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->getBaseCssPath();

            // common/{$device}/*
            $storeDir = $storeDirBase.'/common/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // {$theme}/{$device}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // {$theme}/{$device}/{$color}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device.'/'.$this->getColorRow()->name;
            $this->copyFilesInDir($storeDir, $outDir);

            if ($device == 'sp') {
                $storeDir = $storeDirBase.'/standard/'.$device.'/howtoinfo';
                $this->copyDirRecursive($storeDir, $outDir.'/howtoinfo');
            }

            //カスタムカラーの場合はカラー情報を上書きする
            if(strpos($this->getThemeRow()->name, 'custom_color') !== false && $this->getColorCode() != "") {
                $color_code = $this->getColorCode();
                $rgbs = $this->conversionColorcodeToRbg($color_code);
                $rgb = $rgbs["red"] .",". $rgbs["green"] .",". $rgbs["blue"];
                if(!$colorsetting = file_get_contents($outDir ."/color-setting.css")) {
                    $msg = 'ファイルが存在しません。';
                    throw new \Exception($msg);
                }
                $colorsetting = str_replace('2,83,146', $rgb, $colorsetting);
                file_put_contents($outDir ."/color-setting.css", $colorsetting);
            }
        }
    }

    /**
     * imgsファイル（デザイン）を用意
     */
    public function imgs() {

        foreach ($this->getDeviceList() as $device) {

            $outDir = $this->getTempPublicPath().'/'.$device.'/imgs';
            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->getBaseImgsPath();

            // common/{$device}/*
            $storeDir = $storeDirBase.'/common/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // {$theme}/{$device}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            $outDirColor = $outDir.'/'.$this->getColorRow()->name;
            $this->mkdirRecursive($outDirColor);

            // {$theme}/{$device}/{$color}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device.'/'.$this->getColorRow()->name;
            $this->copyFilesInDir($storeDir, $outDirColor);

        }
    }

    /**
     * fontsファイルを用意
     */
    public function fonts() {

        foreach ($this->getDeviceList() as $device) {

            $outDir = $this->getTempPublicPath().'/'.$device.'/fonts';
            $this->mkdirRecursive($outDir);

            $storeDirBase = $this->{'getBase'.ucfirst(strtolower('fonts')).'Path'}();

            // common/{$device}/*
            $storeDir = $storeDirBase.'/common/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // standard/{$device}/*
            $storeDir = $storeDirBase.'/standard/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);

            // {$theme}/{$device}/*
            $storeDir = $storeDirBase.'/'.$this->getThemeRow()->name.'/'.$device;
            $this->copyFilesInDir($storeDir, $outDir);
        }
    }

    /**
     * 公開領域直下のファイルを用意
     */
    public function directPublic() {

        //$this->copyFilesInDir($this->getBasePublicPath(), $this->getTempPublicPath());

        $this->htaccess();
        $this->index_php();
    }

    /**
     * imagesファイル（CMSで登録された画像）を生成
     */
    public function images() {

        return $this->addFile(self::IMAGES);
    }

    /**
     * file2sファイル（CMSで登録されたファイル）を生成
     */
    public function file2s()
    {
    	return $this->addFile( self::FILE2S ) ;
    }
    
    /**
     * ドキュメントファイルを生成
     *
     * @return string
     */
    public function files() {

        return $this->addFile(self::DOCUMENT);
    }


    public function qrcode() {

        $ds = DIRECTORY_SEPARATOR;

        $outPath = $this->getTempImagesPath().$ds.'qr';

        // 全ページ共通 or 個別
        $commonQr = $this->getHpRow()->qr_code_type == 1 ? true : false;
        $publicFiles = [];
        $isTopQrSetting = $this->getHpRow()->hasCommonSidePartsQr();
        $updatePageIds = $this->page->getUpdatedPageIds();
        $close   = isset($updatePageIds['close']) ? $updatePageIds['close'] : [];
        $release = isset($updatePageIds['release']) ? $updatePageIds['release'] : [];
        foreach ($this->getPagesFilterDraft() as $page) {

            // 全ページ共通QRの場合はトップだけレンダリング
            if ($commonQr && $page['page_type_code'] != HpPageRepository::TYPE_TOP) {
                continue;
            }

            $length = strlen($page['new_path']) - strlen('index.html');
            $url = AbstractRender::protocol($this->getPublishType()).$this->getCompanyRow()->domain.$ds.substr($page['new_path'], 0, $length);
            $file = $page['id'].'.png';

            $old = $this->touchTempFile(\Library\Custom\Qr::pngBinary($url), $file);
            $new = $outPath.$ds.$file;
            $this->moveFileRecursive($old, $new);

            // 個別かつTOP＞サイドコンテンツにQRコード設定がある場合は、全てが公開対象
            if (!$commonQr && $isTopQrSetting) {
                $publicFiles[] = $file;
                continue;
            }

            // ATHOME_HP_DEV-5104 apply QR code
            $where = [
                ['hp_id', $this->getHpRow()->id],
                ['parts_type_code', HpSidePartsRepository::PARTS_QR],
                ['display_flg', 1]
            ];
            if (!$commonQr) {
                $where[] = ['page_id', $page['id']];
            }
            $rows = \App::make(HpSidePartsRepositoryInterface::class)->fetchAll($where);
            if (count($rows) > 0) {
                $display = false;
                foreach($rows as $row) {
                    $pageRow = $this->getPage($row['page_id']);
                    if ($pageRow['public_flg']) {
                        if (!in_array($row['page_id'], $close)) {
                            $display = true;
                            break;
                        }
                    } else {
                        if (in_array($row['page_id'], $release)) {
                            $display = true;
                            break;
                        }
                    }
                }
                if ($display) {
                    $publicFiles[] = $file;
                }
            }
		}

        // 公開ファイル一覧の出力
        $publicTxt = 'public_files.txt';
        file_put_contents($outPath.$ds.$publicTxt, implode("\n", $publicFiles));
    }

    public function logo() {

        if ($this->getHpRow()->logo_pc) {
            $this->createSiteImage($this->getTempImagesPath(), config('constants.hp_site_image.TYPE_SITELOGO_PC'), $this->getHpRow()->logo_pc);
        }

        if ($this->getHpRow()->logo_sp) {
            $this->createSiteImage($this->getTempImagesPath(), config('constants.hp_site_image.TYPE_SITELOGO_SP'), $this->getHpRow()->logo_sp);
        }
    }

    public function favicon() {

        if ($this->getHpRow()->favicon) {
            $this->createSiteImage($this->getTempImagesPath(), config('constants.hp_site_image.TYPE_FAVICON'), $this->getHpRow()->favicon);
        }
    }

    public function webclip() {

        if ($this->getHpRow()->webclip) {
            $this->createSiteImage($this->getTempImagesPath(), config('constants.hp_site_image.TYPE_WEBCLIP'), $this->getHpRow()->webclip);
        }
    }


    public function script() {

        $ds = DIRECTORY_SEPARATOR;

        $dir = $this->getTempFilesPath().$ds.Publish\Ftp::getPublishName($this->getPublishType()).$ds.'script';
        $this->mkdirRecursive($dir);
        $this->copyFilesInDir($this->getBaseScriptPath(), $this->getTempScriptPath());
        $this->copyDirRecursive($this->getBaseScriptPath().'/Theme', $this->getTempScriptPath().'/Theme');

        // $this->getTempScriptPath() のパスが作成されるタイミングはここ。
        $logFrom = "{$this->getBaseSettingPath()}/log.ini";
        $logTo   = "{$this->getTempScriptPath()}/../setting/log.ini";
        if (file_exists($logFrom) && file_exists($this->getTempScriptPath())) {
            $settingDir = "{$this->getTempScriptPath()}/../setting";
            if(!file_exists($settingDir)) {
                $this->mkdirRecursive($settingDir);
            }
            copy($logFrom, $logTo);
        }
        
        // $lang = Helper\Translate::$language;
        // if (!$lang || $lang === 'japanese') {
        //     return;
        // }

        // // Contact.php を翻訳
        // $contents = file_get_contents($path = $dir.DIRECTORY_SEPARATOR.'Contact.php');

        // $class = 'Custom_Filter_Language_'.ucfirst(strtolower($lang)).'_Page_Contact';
        // foreach ($class::getAll() as $japanese => $foreign) {
        //     $contents = str_replace($japanese, $foreign, $contents);
        // };

        // $this->moveFileRecursive($this->touchTempFile($contents, 'Contact.php'), $path);
    }

    /**
     * 4139 edit file setting
     */
    public function getDataPublish($file, $data, $page = true, $isPageAutoLink = false) {
        $ds = DIRECTORY_SEPARATOR;
        $settingFile = $this->getBackupHtmlPath().$ds.'setting'.$ds.$file;
        if (file_exists($settingFile) && !$isPageAutoLink) {
            $content = unserialize(file_get_contents($settingFile));
        } else {
            return $data;
        }
        $result = $data;
        foreach ($data as $key=>$item) {
            if ($page && $this->isReleasePage($item['id'])) {
                continue;
            }
            if (!$page && $this->isReleasePage($item['page_id'])) {
                continue;
            }
            $oldItem = array_filter($content, function($value) use ($item, $page) {
                if ($page) {
                    return $value['id'] == $item['id'];
                } else {
                    return $value['page_id'] == $item['page_id'];
                }
                 
            });
            if (count($oldItem) > 0) {
                // ATHOME_HP_DEV-5145 お知らせ（一覧のみリンクあり）のリンクが外れる原因を調査する
                if ($page) {
                    $result[$key] = $oldItem[$key];
                } else {
                    $valueOldItem = array_values($oldItem);
                    $result[$key] = $valueOldItem[0];
                }
            }
        }
        return $result;
    }

    public function setting() {

        $ds = DIRECTORY_SEPARATOR;

        // 出力先
        $outDir = $this->getTempFilesPath().$ds.Publish\Ftp::getPublishName($this->getPublishType()).$ds.'setting';
        $this->mkdirRecursive($outDir);

        $file = 'pages.txt';
        $this->serializedTxt($outDir, $file, $this->getPagesFilterDraft());

        $file = 'page_info.txt';
        $pagesInfo = array_filter($this->getPagesFilterDraft(), function($page) {
            return $page['page_type_code'] == HpPageRepository::TYPE_INFO_INDEX 
                    || $page['page_type_code'] == HpPageRepository::TYPE_INFO_DETAIL; 
        });
        $pages = $this->getDataPublish($file, $pagesInfo);
        $this->serializedTxt($outDir, $file, $pages);

        $file = 'page_auto_link.txt';
        $table = $this->hpPageRepository;
        $pagesAuto = [];
        foreach ($this->getPagesFilterDraft() as $key => $page) {
            if (in_array($page['page_type_code'], $table->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE))) {
                $acticle = \App::make(HpMainPartsRepositoryInterface::class)->fetchAll([
                    ['hp_id', $page['hp_id']],
                    ['page_id', $page['id']],
                    'whereIn' => ['parts_type_code', array(HpMainPartsRepository::PARTS_ARTICLE_TEMPLATE,
                        HpMainPartsRepository::PARTS_ORIGINAL_TEMPLATE,
                        HpMainPartsRepository::PARTS_SELL,
                        HpMainPartsRepository::PARTS_REPLACEMENT_AHEAD_SALE,
                        HpMainPartsRepository::PARTS_BUILDING_EVALUATION,
                        HpMainPartsRepository::PARTS_PURCHASING_REAL_ESTATE,
                        HpMainPartsRepository::PARTS_BUYER_VISITS_DETACHEDHOUSE,
                        HpMainPartsRepository::PARTS_POINTS_SALE_OF_CONDOMINIUM,
                        HpMainPartsRepository::PARTS_CHOOSE_APARTMENT_OR_DETACHEDHOUSE,
                        HpMainPartsRepository::PARTS_NEWCONSTRUCTION_OR_SECONDHAND,
                        HpMainPartsRepository::PARTS_LIFE_PLAN,
                        HpMainPartsRepository::PARTS_BUY,
                        HpMainPartsRepository::PARTS_PURCHASE_BEST_TIMING,
                        HpMainPartsRepository::PARTS_ERECTIONHOUSING_ORDERHOUSE,
                        HpMainPartsRepository::PARTS_FUNDING_PLAN,
                        HpMainPartsRepository::PARTS_TYPES_MORTGAGE_LOANS,
                        HpMainPartsRepository::PARTS_REPLACEMENTLOAN_MORTGAGELOAN,
                        HpMainPartsRepository::PARTS_CONSIDERS_LAND_UTILIZATION_OWNER,
                        HpMainPartsRepository::PARTS_UTILIZING_LAND,
                        HpMainPartsRepository::PARTS_MEASURES_AGAINST_VACANCIES,
                        HpMainPartsRepository::PARTS_HOUSE_REMODELING,
                        HpMainPartsRepository::PARTS_LEASING_MANAGEMENT_MENU,
                        HpMainPartsRepository::PARTS_TROUBLED_LEASING_MANAGEMENT,
                        HpMainPartsRepository::PARTS_LEND,
                        HpMainPartsRepository::PARTS_PURCHASE_INHERITANCE_TAX,
                        HpMainPartsRepository::PARTS_SHOP_SUCCESS_BUSINESS_PLAN,
                        HpMainPartsRepository::PARTS_STORE_SEARCH,
                        HpMainPartsRepository::PARTS_SQUEEZE_CANDIDATE,
                        HpMainPartsRepository::PARTS_UPPER_LIMIT,
                        HpMainPartsRepository::PARTS_PREVIEW,
                        HpMainPartsRepository::PARTS_RENTAL_INITIAL_COST,
                        HpMainPartsRepository::PARTS_RENT,
                        HpMainPartsRepository::PARTS_MOVING,
                        HpMainPartsRepository::PARTS_UNUSED_ITEMS_AND_COARSEGARBAGE,
                        HpMainPartsRepository::PARTS_COMFORTABLELIVING_RESIDENT_RULES,
                    ),
                ]])->toArray();
                if ($acticle) {
                    $_helperHpImage = new Helper\HpImage();
                    $acticle[0]['id'] = $page['link_id'];
                    switch ($acticle[0]['parts_type_code']) {
                        case HpMainPartsRepository::PARTS_ARTICLE_TEMPLATE:
                        case HpMainPartsRepository::PARTS_ORIGINAL_TEMPLATE:
                            $acticle[0]['attr_1'] = $_helperHpImage->hpImage($acticle[0]['attr_1']);
                            $acticle[0]['len'] = mb_strlen(strip_tags($acticle[0]['attr_3']));
                            break;
                        case HpMainPartsRepository::PARTS_SELL:
                        case HpMainPartsRepository::PARTS_BUY:
                        case HpMainPartsRepository::PARTS_LEND:
                        case HpMainPartsRepository::PARTS_PREVIEW:
                        case HpMainPartsRepository::PARTS_RENT:
                        case HpMainPartsRepository::PARTS_MOVING:
                            $description = $acticle[0]['attr_1'];
                            if ($acticle[0]['attr_2']) {
                                $acticle[0]['attr_1'] = $_helperHpImage->hpImage($acticle[0]['attr_2']);
                            } else {
                                $acticle[0]['attr_1'] = null;
                            }
                            $acticle[0]['attr_2'] = $acticle[0]['attr_3'];
                            $acticle[0]['attr_3'] = $description;
                            $acticle[0]['len'] = mb_strlen(strip_tags($acticle[0]['attr_3']));
                            break;
                        default:
                            if ($acticle[0]['attr_2']) {
                                $acticle[0]['attr_1'] = $_helperHpImage->hpImage($acticle[0]['attr_2']);
                            }
                            $acticle[0]['attr_2'] = $acticle[0]['attr_3'];
                            $acticle[0]['attr_3'] = $acticle[0]['attr_4'];
                            $acticle[0]['len'] = mb_strlen(strip_tags($acticle[0]['attr_3']));
                            break;
                    }
                    $pagesAuto[$page['link_id']] = $acticle[0];
                }
            }
        }
        $pages = $this->getDataPublish($file, $pagesAuto, true, true);
        $pages['larges'] = $table->getPageArticleByCategory(HpPageRepository::CATEGORY_LARGE);
        $pages['larges']['id'] = '0023';
        $pages['smalls'] = $table->getPageArticleByCategory(HpPageRepository::CATEGORY_SMALL);
        $pages['smalls']['id'] = '0024';
        $this->serializedTxt($outDir, $file, $pages);

        $file = 'hp.txt';
        $hpArray = $this->getHpRow()->toArray();
        $hpArray["theme_name"] = MThemeRepository::getThemeName($hpArray['theme_id']);
        $this->serializedTxt($outDir, $file, $hpArray);
        
        $file = 'info_detail_link.txt';
        $specialSetting = array();
        $file2 = array();
        $infoDetailLink = \App::make(HpInfoDetailLinkRepositoryInterface::class)->getDataByHp($this->getHpRow()->id)->toArray();
        $infoDetailLink = $this->getDataPublish($file, $infoDetailLink, false);
        foreach ($infoDetailLink as $key=>$detailLink) {
            if (preg_match("/^estate_special_/", $detailLink['link_page_id'])) {
                $setting = $this->getHpRow()->getEstateSetting();
                if ($setting) {
                    $specialEstate = $setting->getSpecialByOriginId(str_replace('estate_special_', '', $detailLink['link_page_id']));
                    if ($specialEstate) {
                        $specialSetting[] = $specialEstate->toArray();
                    } else {
                        $infoDetailLink[$key]['link_page_id'] = null;
                    }
                }
            }

            if ($detailLink['file2']) {
                $file2[] = \App::make(HpFile2RepositoryInterface::class)->fetchFile2Information($detailLink['file2']);
            }
        }
        $this->serializedTxt($outDir, $file, $infoDetailLink);

        if (!empty($specialSetting)) {
            $file = 'special_setting.txt';
            $this->serializedTxt($outDir, $file, $specialSetting);
        }

        if (!empty($file2)) {
            $file = 'file2.txt';
            $this->serializedTxt($outDir, $file, $file2);
        }

        if ($this->getCompanyRow()->checkTopOriginal()) {
            $classInfo = \App::make(HpMainPartsRepositoryInterface::class)->getNotificationClassDetail($this->getHpRow()->id);
            $classInfo = json_decode(json_encode($classInfo),true);
            $file = 'category_class_info.txt';
            $this->serializedTxt($outDir, $file, $this->getDataPublish($file, $classInfo, false));
        }

        $this->memberOnly($outDir);

        $this->htpasswd($outDir);

        $this->contactPageIdList($outDir);

        $this->makeApiIni($outDir);

        $view = $this->getViewInstance();
        $protocol = config('constants.company_agreement_type.CONTRACT_TYPE_DEMO') ==  $view->company->contract_type? 'http':'https';
        
        $this->makeSiteIni($outDir,$protocol);

        $specialSetting = array();
        $searchSetting = array();
        $setting = $this->getHpRow()->getEstateSetting();
        if ($setting) {
            $specialSetting = $setting->getSpecialAllWithPubStatus()->toArray();
            $searchSetting = $setting->getSearchSettingAll()->toArray();
        }
        if (!empty($specialSetting)) {
            $file = 'special_setting_all.txt';
            $this->serializedTxt($outDir, $file, $specialSetting);
        }
        if (!empty($searchSetting)) {
            $file = 'search_setting.txt';
            $this->serializedTxt($outDir, $file, $searchSetting);
        }

    }

    public function getZip() {
    	$this->dotEnd()						;
        $targetDir = $this->getTempPath();

        /**
          ATHOME_HP_DEV-5278
          delete.txt:all 以外はzip圧縮する
         */
        $fileTypes = [ self::IMAGES, self::FILE2S ];
        foreach ($fileTypes as $type) {
            $filePath = $this->{'getTemp'.ucfirst($type).'Path'}();

            // delete.txt 存在チェック
            $deleteTxtFile = sprintf("%s/delete.txt", $filePath);

            if(is_file($deleteTxtFile)) {
                $content = file_get_contents($deleteTxtFile);

                if($content != 'all') {
                    // 出力したフォルダを_tmpにリネームし、新たに空のフォルダを作成する
                    $filePathTmp = $filePath . "_tmp";
                    rename($filePath, $filePathTmp);
                    mkdir($filePath);

                    // delete.txt だけ移動。ファイルが残っていればzipを出力領域に作成
                    rename(sprintf("%s/delete.txt", $filePathTmp), sprintf("%s/delete.txt", $filePath));

                    if(count(glob("$filePathTmp/*")) > 0) {

                        $zipCmd = sprintf("cd %s && zip -r %s/%s.zip .", $filePathTmp, $filePath, $type);
                        exec($zipCmd, $out1, $out2);
                        if($out2 != 0) {
                            throw new \Exception("ファイルの圧縮に失敗しました");
                        }
                        exec("rm -rf " . $filePathTmp, $out1, $out2);
                        if($out2 != 0) {
                            throw new \Exception("ファイルの圧縮に失敗しました");
                        }
                    } else {
                        rmdir($filePathTmp);
                    }
                }
            }
        }

        $localFile = [];
        $this->deleteEmptyDirRecursive($targetDir);

        $zip = new \ZipArchive();

        $totalSize = 0; // Byte
        $file = null;
        $root = "";

        $baseLen = mb_strlen($targetDir);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
                        $targetDir,
                        \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO),
                        \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $pathname => $info) {
            $localpath = $root.mb_substr($pathname, $baseLen);

            // 直下にあるzipファイルは対象外
            if(preg_match("/^\/[^\/]{1,}zip$/", $localpath)) {
                continue;
            }

            $new_file = sprintf("%s/update_%d.zip", $targetDir, intval($totalSize / self::ZIPFILE_CAPACITY));
            if($new_file != $file) {
                if(!is_null($file)) {
                    $zip->close();
                    $this->pollingProgressBar();
                }
                $file = $new_file;
                $localFile[] = $file;

                $res = $zip->open($file, \ZipArchive::CREATE);
                if(!$res) {
                    throw new \Exception("ファイルの圧縮に失敗しました");
                }
            }
            if ($info->isFile()) {
                $zip->addFile(mb_convert_encoding($pathname, "UTF-8", "auto"), mb_convert_encoding($localpath, "UTF-8", "auto"));
                $totalSize = $totalSize + $info->getSize();
            }
            else {
                $res = $zip->addEmptyDir(mb_convert_encoding($localpath, "UTF-8", "auto"));
            }
        }
        $zip->close();

        return $localFile;
    }

    /**
     * 公開中HTMLを上書き
     */
    public function updateHtmlFiles() {

        exec("rm -rf {$this->getBackupHtmlPath()}");

        // #4139 Copy to save setting 
        $baseDirSetting = $this->getBackupHtmlPath().DIRECTORY_SEPARATOR.'setting';
        $newDir = $this->getTempScriptPath().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'setting';
        $this->mkdirRecursive($baseDirSetting);
        $this->copyDirRecursive($newDir, $baseDirSetting);
        
        foreach ($this->getDeviceList() as $device) {

            $baseDir = $this->getBackupHtmlPath().DIRECTORY_SEPARATOR.$device;
            $newDir = $this->getTempViewPath().DIRECTORY_SEPARATOR.$device;
            $this->mkdirRecursive($baseDir);
            $this->copyDirRecursive($newDir, $baseDir);
        }

        $this->deleteEmptyDirRecursive($this->getBackupHtmlPath());
    }

    /**
     * htmlファイルをzipに圧縮、保存
     *
     */
    public function zipHtml() {

        $ds = DIRECTORY_SEPARATOR;

        $hp = $this->getHpRow();

        $serverHtmlDir = storage_path().$ds.'data'.$ds.'html'.$ds.'server';
        $fileName = $hp->id;
        $compressDir = $serverHtmlDir.$ds.$fileName;

        if (file_exists($compressDir)) {

            $zipFileSavePath = $serverHtmlDir.$ds;

            $command = 'cd '.$compressDir.';'.'zip -r '.$zipFileSavePath.$fileName.'.zip .';
            exec($command);

            $path = $zipFileSavePath.$fileName;

            \App::make(HpHtmlContentRepositoryInterface::class)->save($hp->id, file_get_contents($path.'.zip'));

            // zipとhtml削除
            unlink($path.'.zip');
            $command = 'rm -rf '.$path;
            exec($command);

        }

    }

    /**
     * 各ページのHTMLをレンダリング
     */
    private function renderPages() {
        $table = $this->hpPageRepository;
        $category = $table->getCategoryMap();

        // ATHOME_HP_DEV-5220
        $contact_page = null;
        $form_pages   = [];

        // 通常ページ
        foreach ($this->getPagesFilterDraft() as $page) {


            // 実体のないページはcontinue
            if (!$table->hasEntity($page['page_type_code'])) {
                continue;
            }

            // 更新対象でないページはcontinue
            /*
            if(!$this->isReleasePage($page['id'])){
                continue;
            }
            */

            // ATHOME_HP_DEV-5220
            if($page['page_type_code'] == HpPageRepository::TYPE_FORM_CONTACT) {
                // サイト問い合わせ
                $contact_page = $page;
            } else {
                // 物件リクエスト
                switch($page['page_type_code']) {
                    case HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE:
                    case HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE:
                    case HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY:
                    case HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY:
                    case HpPageRepository::TYPE_FORM_DOCUMENT:
                    case HpPageRepository::TYPE_FORM_ASSESSMENT:
                        $form_pages[ $page['page_type_code'] ] = $page;
                        break;
                    default:
                        break;
                }
            }

            // - 更新対象
            // - 全上げ
            // - ページネーションあり
            if (($this->isReleasePage($page['id'])) || $this->getHpRow()->all_upload_flg || $table->hasPagination($page['page_type_code']) /*|| $table->isDetailPageType($page['page_type_code'])*/) {


                foreach ($this->getDeviceList() as $device) {

                    Logger\Publish::getInstance()->infoRender('view------renderPage(page_type_code='.str_pad($page['page_type_code'],2,0,STR_PAD_LEFT).", page_id=".$page['id'].", device=".$device.")");

                    $this->pollingProgressBar(); // browser polling


                    // 問い合わせレンダリング
                    // 物件検索は回避
                    if (in_array($page['page_type_code'], $category[HpPageRepository::CATEGORY_FORM]) &&
                        !in_array($page['page_type_code'], $this->hpPageRepository->estateContactPageTypeCodeList())
                    ) {

                        $this->renderContactSetting($page, $device);
                        $this->renderContactPage($page, $device);

                        // ATHOME_HP_DEV-5220 : iniファイルを生成したらリセットする
                        if($page['page_type_code'] == HpPageRepository::TYPE_FORM_CONTACT) {
                            $contact_page = null;
                        } else if(isset($form_pages[ $page['page_type_code'] ])) {
                            unset($form_pages[ $page['page_type_code'] ]);
                        } 
                        continue;
                    }

                    // 一覧ページレンダリング
                    if ($table->hasPagination($page['page_type_code'])) {
                        $this->renderPageWithPagination($page, $device);

                        // ブログはさらに
                        // - 月別の一覧ページ
                        // - 月別のサイドパーツ
                        if ($page['page_type_code'] == HpPageRepository::TYPE_BLOG_INDEX) {
                            $this->pollingProgressBar(); // browser polling
                            $this->renderBlogIndexByMonth($page, $device);
                        }
                        continue;
                    }

                    if (!$this->hpPageRepository->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                        // その他ページレンダリング
                        $this->renderMonoPage($page, $device);
                    }

                }

                // ATHOME_HP_DEV-5104
                if( $this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC')
                    || $this->getPublishType() == config('constants.publish_type.TYPE_SUBSTITUTE') ) {
                    // 差分公開となったページは公開フラグを設定し、image, file, file2 のIDリストを設定
                    $row = $this->hpPageRepository->fetchRowById($page['id']);

                    $row->public_flg = 1;

                    // 利用画像一覧を hp_image_usedテーブルより取得
                    $public_image_ids = \App::make(HpImageUsedRepositoryInterface::class)->usedImageIdsInPage($row->hp_id, $row->id);
                    $row->public_image_ids = (count($public_image_ids)) ? implode(",", $public_image_ids) : null;

                    // 利用File一覧を hp_main_elementテーブルより取得
                    $public_file_ids = \App::make(HpMainElementRepositoryInterface::class)->usedFileIdsInPage($row->hp_id, $row->id);
                    $row->public_file_ids = (count($public_file_ids)) ? implode(",", $public_file_ids): null;

                    // 利用File2一覧を hp_file2_usedテーブルより取得
                    $public_file2_ids = \App::make(HpFile2UsedRepositoryInterface::class)->usedFile2IdsInPage($row->hp_id, $row->id);
                    $row->public_file2_ids = (count($public_file2_ids)) ? implode(",", $public_file2_ids) : null;

                    $row->save();
                }
            }
        }

        // ATHOME_HP_DEV-5220
        if(!is_null($contact_page) || !empty($form_pages)) {
            $ds = '/';

            $outDir = $this->getTempFilesPath().$ds.Publish\Ftp::getPublishName($this->getPublishType()).$ds.'setting';
            $this->mkdirRecursive($outDir);

            if(!is_null($contact_page)) {
                $settingPath = storage_path().$ds.'data'.$ds.'html'.$ds.'server'.$ds.$contact_page['hp_id'].$ds.'setting';
                $iniFile = 'contact_'.$contact_page['filename'].'_'.$contact_page['id'].'.ini';
                if(is_file($settingPath.$ds.$iniFile) && !is_file($outDir.$ds.$iniFile)) {
                    copy($settingPath.$ds.$iniFile, $outDir.$ds.$iniFile);
                }
            }
            if(!empty($form_pages)) {
                foreach($form_pages as $request_code => $form_page) {
                    $settingPath = storage_path().$ds.'data'.$ds.'html'.$ds.'server'.$ds.$form_page['hp_id'].$ds.'setting';
                    $iniFile = 'contact_'.$form_page['filename'].'_'.$form_page['id'].'.ini'; 
                    if(is_file($settingPath.$ds.$iniFile) && !is_file($outDir.$ds.$iniFile)) {
                        copy($settingPath.$ds.$iniFile, $outDir.$ds.$iniFile);
                    }
                }
            }
        }
    }

    /**
     * 共通部分のHTMLをレンダリング
     */
    private function renderCommonParts() {

        $ds = DIRECTORY_SEPARATOR;

        $publishType = Publish\Ftp::getPublishName($this->getPublishType());

        foreach ($this->getDeviceList() as $device) {

            // 出力先
            $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
            $this->mkdirRecursive($outDir);

            // パーツごとにレンダリング
            foreach ($this->getIncludeHtmlList() as $name) {

                if ($name === 'info_list') {
                    $contents = $this->getContentsInfoList($device);
                }
                else {
                    $contents = $this->getContentCommonParts($name, $device);
                }
                $file = '_'.$name.'.html';
                file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
            }
            if($this->topTags instanceof Top\TagTopOriginal){
                $file = '_sidenavscript.html';
                $contents = $this->getContentCommonParts('sidenavscript', $device);
                file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
                $file = '_sidearticlelinkoriginal.html';
                $contents = $this->getContentCommonParts('sidearticlelinkoriginal', $device);
                file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
            }

        }
    }

    /**
     * render info list top oroginal
     *
     */
    private function renderInfoListTop() {
        $pageRow = $this->getHpRow()->findPagesByType(HpPageRepository::TYPE_TOP, false);
        $view = $this->getViewInstance();
        if ($pageRow->count() < 1) {
            return;
        }
        if($view->isTopOriginal){
            $ds = DIRECTORY_SEPARATOR;
            $publishType = Publish\Ftp::getPublishName($this->getPublishType());
            $user = $this->getCompanyRow();
            $prefix = $this->getBasePartialsPath().$ds;
            $parentDir = $this->getThemeRow()->name;

            // ATHOME_HP_DEV-4866 View-Helper作成時に、TopOriginalコピー元を指定
            $_helperTags = Helper\Tags::getInstance($this->getCompanyRow()->id, $this->usePubTop, $this->pubTopSrcPath);
            $_helperHpLink = new Helper\HpLink();
            $_helperHpLink->setViewParams($view);
            $all_pages = $this->getDataPublish('page_info.txt', $this->getPagesFilterDraft());
            foreach ($this->getDeviceList() as $device) {
                $suffix = $ds.$device;
                $contents = '';
                $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
                $this->mkdirRecursive($outDir);
                $page = $this->loadHpPage($pageRow->first()->id, null, true);
                if ($page->form->getSubForm('main')) {
                    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
                        foreach ($area->getPartsByColumn() as $column) {
                            foreach ($column as $parts) {
                                if ($parts instanceof \Library\Custom\Hp\Page\Parts\InfoList) {
                                    $name = 'info_list'.$parts->getValue('notification_type');
                                    $tag_name = "";
                                    if (1 == $parts->getValue('notification_type')) {
                                        $tag_name = Top\TagTopOriginal::TAG_NEWS_1;
                                        
                                    } else if  (2 == $parts->getValue('notification_type')) {
                                        $tag_name = Top\TagTopOriginal::TAG_NEWS_2;
                                    }
                                    $filter = new Helper\FilterCollection();
                                    $news = $this->hpPageRepository->fetchRowByLinkId($parts->getValue('page_id'),$this->getHpRow()->id);
                                    $pages = $filter->filterCollectionTop($all_pages, array('page_type_code', HpPageRepository::TYPE_INFO_DETAIL, 'public_flg', 1, 'parent_page_id', (int)$news->id), array(['date', 'id'], ['DESC', 'DESC']), (int)$parts->getValue('page_size'));
                                    $contents = '<?php $viewHelper = new ViewHelper($this->_view);?>';
                                    $contents .= '<?php $pageIndex = $viewHelper->getPageByLinkId('.$parts->getValue('page_id').');?>';
                                    if (count($pages) > 0) {
                                        foreach ($pages as $page) {
                                            $_helperTags->newBlock($this->filesNewsTop()[$device][$tag_name]);
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_TITLE, '<?php echo $title;?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DETAIL, '<?php echo $url?>');
                                            if ($tag_name == Top\TagTopOriginal::TAG_NEWS_1) {
                                                $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_LIST1, $_helperHpLink->linkList($page['parent_page_id']));
                                            } else {
                                                $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_LIST2, $_helperHpLink->linkList($page['parent_page_id']));
                                            }
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_TEXT, '<?php echo $text;?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DATE, '<?php echo $dateJP;?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_YEAR, '<?php echo date("Y", $date);?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_MONTH, '<?php echo (int) date("m", $date);?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DAILY, '<?php echo (int) date("d", $date);?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK1, '<?php echo $dayNames[date("w", $date)];?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK2, '<?php echo $dayNamesShort[date("w", $date)];?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK3, '<?php echo date("l", $date);?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK4, '<?php echo date("D", $date);?>.');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_CATEGORY, '<?php echo $category;?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_CATEGORY_CLASS, '<?php echo $category_class;?>');
                                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_NEW_MARK, '<?php if($newMark): ?>'.Lists\NewMark::NEW_MARK.'<?php endif; ?>');
                                            if ($this->hpPageRepository->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                                                $script = $this->infoListTopScriptDetailList($page['id']);
                                            } else {
                                                $script = $this->infoListTopScript($device, $_helperHpLink->hpLink($page['link_id']));
                                            }
                                            
                                            $contents .= $script.$_helperTags->getOutput();
                                        }
                                        if($contents){
                                            file_put_contents($outDir.          $ds.'_'.$name.'.html', $contents    , LOCK_EX);
                                        }
                                    }
                                }
                                
                            } 
                        }
                    }
                }
            }
        }
    }

    /**
     * ページをレンダリング
     *
     * @param $page
     * @param $device
     * @param $ChildrenPageIds
     */
    private function renderMonoPage($page, $device, $ChildrenPageIds = null) {
        $tempPath = $this->touchTempFile($this->getContent($page['id'], $device, $ChildrenPageIds));
        $newPath = $this->getTempViewPath().DIRECTORY_SEPARATOR.$device.DIRECTORY_SEPARATOR.$page['new_path'];

        $this->moveFileRecursive($tempPath, $newPath);
    }

    /**
     * 問い合わせページをレンダリング
     *
     * @param $page
     * @param $device
     */
    private function renderContactPage($page, $device) {

        $ds = DIRECTORY_SEPARATOR;
        $parent = str_replace('index.html', '', $page['new_path']);

        // お問いせに必要なHTMLファイルを生成する
        foreach (AbstractRender::getContactFileList() as $class) {


            $content = $this->getContentContact($page, $class, $device);
            $controller = '';
            if ($class == 'edit' || $class == 'confirm') {
                $controller = $this->getContentContact($page, 'controller-'.$class, $device);
            }
            if ($class != 'index' && $class != 'validate') {
                $content = $this->getContent($page['id'], $device, null, null, null, null, $content);
                $content = $controller.$content;
            }
            $tempPath = $this->touchTempFile($content);
            $newPath = ($class == 'index') ? $this->getTempViewPath().$ds.$device.$ds.$page['new_path'] : $this->getTempViewPath().$ds.$device.$ds.$parent.$class.$ds.'index.html';

            $this->moveFileRecursive($tempPath, $newPath);
        }
    }

    private function renderContactSetting($page, $device) {

        $ds = DIRECTORY_SEPARATOR;

        // ページ情報取得する
        $where = array(
            ['id', (int)$page['id']], ['hp_id', $page['hp_id']],
        );

        if (!$pageRow = $this->hpPageRepository->fetchRow($where)) {
            $msg = '公開処理に失敗しました。';
            throw new \Exception($msg);
        }

        // ページのForm
        $pageForm = \Library\Custom\Hp\Page::factory($this->getHpRow(), $pageRow);
        $pageForm->init();
        $pageForm->load();
        $contactForm = $pageForm->form->form; 

        // ユーザーサイトとの連携用Form
        $apiContactForm = new ContactAbstract;
        $apiContactForm->init();

        $view = $this->getViewInstance();
        $view->contactForm = $contactForm;
        $view->pageForm = $pageForm;
        $view->apiContactForm = $apiContactForm;
        $view->page = $page;
        $view->companyAccount = \App::make(CompanyAccountRepositoryInterface::class)->getDataForCompanyId($view->company->id);
        $view->hankyo_plus_use_flg = $this->getHpRow()->hankyo_plus_use_flg;
        $contactCnfs = getConfigs('api.contact');
        // 物件問い合わせ        
        if( $this->hpPageRepository->isEstateContactPageType($page['page_type_code']) ) {
            if ($contactCnfs->contact->api->estateurl){
                $view->api_url = $contactCnfs->contact->api->estateurl;
            }else{
                $view->api_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER['SERVER_NAME'].'/api/estate-contact';
            }

        // 通常問い合わせ
        }else{
            if ($contactCnfs->contact->api->url){
                $view->api_url = $contactCnfs->contact->api->url;
            }else{
                if($this->hpPageRepository->isEstateRequestPageType($page['page_type_code'])) {
                    $view->api_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER['SERVER_NAME'].'/api/estate-request';                    
                }else{
                    $view->api_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER['SERVER_NAME'].'/api/contact';                    
                }
            }
        }


        // APIキーを設定する
        try {
            // 加盟店アカウントのIDを取得する。
            $comAccIds = array();
            foreach ($view->companyAccount as $comAcc) {
                $comAccIds[] = $comAcc->id;
            }
            //加盟店アカウントにAPIキーをセットする
            $crypApikey = new \Library\Custom\Crypt\ApiKey();
            $data = array();
            $data['api_key'] = $crypApikey->encrypt($view->company->id);
            $where = array( 'whereIn' => ["id", $comAccIds]);
            $table = \App::make(CompanyAccountRepositoryInterface::class);
            if ($this->getCompanyRow()->companyAccount()->first()->api_key !== $data['api_key']) {
                $table->update($where, $data);
            }

        } catch (\Exception $e) {
            throw $e;
        }


        // 設定ファイルのテンプレートを読む
        $path = $this->getBaseSettingPath();
        $filename = 'contact_setting.blade.php';
        $view->setViewPath([$path], $filename);
        
        // if(Helper\Translate::$language && Helper\Translate::$language !== 'japanese'){
        //     $filename = 'contact_setting_foreign.tmpl.ini';
        // }

        if (file_exists($path)) {
            $content = $view->render($filename);
        }

        // iniファイルを生成
        $outDir = $this->getTempFilesPath().$ds.Publish\Ftp::getPublishName($this->getPublishType()).$ds.'setting';

        $this->mkdirRecursive($outDir);
        $file = 'contact_'.$page['filename'].'_'.$page['id'].'.ini';
        $oldPath = $this->touchTempFile($content, $file);
        $this->moveFileRecursive($oldPath, $outDir.$ds.$file);
    }

    /**
     * ページネーションのあるページのレンダリング
     *
     * @param $page
     * @param $device
     */
    private function renderPageWithPagination($page, $device) {

        $ds = DIRECTORY_SEPARATOR;

        // html
        $parent = str_replace('index.html', '', $page['new_path']);
        if ($page['page_type_code'] == HpPageRepository::TYPE_INFO_INDEX) {
            $childrenPageIds = $this->getChildrenPageInfoIds($page);
        } else {
            $childrenPageIds = $this->getChildrenPageIds($page);
        }
        $count = $this->countIndexPage($childrenPageIds);

        for ($num = 1; $num <= $count; $num++) {

            $contents = $this->getContent($page['id'], $device, $num, $count, $childrenPageIds);
            $tempPath = $this->touchTempFile($contents);

            $base = $this->getTempViewPath().$ds.$device.$ds;
            $newPath = $num == 1 ? $base.$page['new_path'] : $base.$parent.$num.$ds.'index.html';

            $this->moveFileRecursive($tempPath, $newPath);

            // ATHOME_HP_DEV-5256 indexページ1ファイルごとにpolling
            $this->pollingProgressBar();
        }
    }

    /**
     * ブログの月別一覧ページレンダリング
     *
     */
    private function renderBlogIndexByMonth($page, $device) {

        $ds = DIRECTORY_SEPARATOR;

        $childrenPageIds = $this->getChildrenPageIds($page);
        $list = $this->listMonth($childrenPageIds);
        $listAll = $this->listMonth($childrenPageIds, false);
        $countValues = array_count_values($listAll);

        foreach ($list as $i => $yyyymm) {
        	$count = ceil($countValues[$yyyymm] / self::ARTICLE_PER_PAGE);
        	for ($j=1; $j <= $count; $j++) {
        		$contents = $this->getContent($page['id'], $device, $j, $count, $childrenPageIds, $yyyymm);

        		$tempPath = $this->touchTempFile($contents);
        		$base = $this->getTempViewPath().$ds.$device.$ds.
        		    str_replace('index.html', '', $page['new_path']).
        		    $yyyymm.$ds;
        		$newPath = $j == 1 ?
        		    $base.'index.html' :
        		    $base.$j.$ds.'index.html';
                $this->moveFileRecursive($tempPath, $newPath);
        	}
        }

        $this->renderSideBlog($page, $device, $childrenPageIds);
    }

    /**
     * ブログ サイドの月別一覧表示
     *
     * @param $page
     * @param $device
     * @param $list
     */
    private function renderSideBlog($page, $device, $childrenPageIds) {

        if ($device != 'pc') {
            return;
        }

        $ds = DIRECTORY_SEPARATOR;

        $publishType = Publish\Ftp::getPublishName($this->getPublishType());

        // 出力先
        $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
        $this->mkdirRecursive($outDir);

        $name = 'sideblog';

        $file = '_'.$name.'_'.$page['id'].'.html';
        $contents = $this->getContentsSideBlog($childrenPageIds, $device);
        file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
    }

    /**
     * サイトマップページをレンダリング
     */
    private function renderSitemapPage() {

        $ds = DIRECTORY_SEPARATOR;

        $this->initPageId();

        foreach ($this->getDeviceList() as $device) {

            $file = '_frame.blade.php';
            $prefix = $this->getBasePartialsPath().$ds;
            $suffix = $ds.$device;
            $parentDir = $this->getThemeRow()->name;

            $view = $this->getViewInstance();
            $view->isSitemap = true;
            $view->contents = $this->getContentSitemap($device);

            // 全てを纏める
            $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);

            $tempPath = $this->touchTempFile($contents);
            $newPath = $this->getTempViewPath().$ds.$device.$ds.'sitemap'.$ds.'index.html';
            $this->moveFileRecursive($tempPath, $newPath);
        }
    }

    private function render404Page() {

        $ds = DIRECTORY_SEPARATOR;

        foreach ($this->getDeviceList() as $device) {

            $file = '_frame.blade.php';
            $prefix = $this->getBasePartialsPath().$ds;
            $suffix = $ds.$device;
            $parentDir = $this->getThemeRow()->name;

            $view = $this->getViewInstance();
            $view->isSitemap = true;
            $view->is404 = true;
            $view->contents = $this->getContentSitemap($device, $is404 = true);

            // 全てを纏める
            $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);

            $tempPath = $this->touchTempFile($contents);
            $newPath = $this->getTempViewPath().$ds.$device.$ds.'404notFound'.$ds.'index.html';
            $this->moveFileRecursive($tempPath, $newPath);
        }
    }


    /**
     * ページHTMLの中身を取得
     *
     * @param int    $pageId
     * @param string $device
     * @param int    $listNumber      （一覧ページのページ番号）
     * @param int    $listCount       （一覧ページのページ数）
     * @param array  $childrenPageIds （詳細ページのページID）
     * @param int    $yyyymm          （ブログ一覧の月別）
     * @return mixed
     * @throws Exception
     */
    private function getContent($pageId, $device, $listNumber = null, $listCount = null, $childrenPageIds = null, $yyyymm = null, $contactContent = null) {
Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent1----');
        $this->setPageId($pageId);

        $file = '_frame.blade.php';
        $prefix = $this->getBasePartialsPath().DIRECTORY_SEPARATOR;
        $suffix = DIRECTORY_SEPARATOR.$device;
        $parentDir = $this->getThemeRow()->name;
Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent2----');

        $view = $this->getViewInstance();
        if ($view->isTopOriginal) {
            // ATHOME_HP_DEV-4866 View-Helper作成時に、TopOriginalコピー元を指定
            $_helperTags = Helper\Tags::getInstance($this->getCompanyRow()->id, $this->usePubTop, $this->pubTopSrcPath);

            // get all list tags
            $page = $this->getPage($pageId);

            // get instance of TagTopOriginal, do not re-new class
            if(!$this->topTags instanceof Top\TagTopOriginal){
                $this->topTags = new Top\TagTopOriginal();
            }

            $tagTop = $this->topTags;

            $tagTop->setTagList($view->hp, $view->mode, $page, $device, $this->getPages());
            $tagTop->setPartCommon($this->getContentPartCommonTop($device));
            $tagTop->setTagSocialNetwork($this->getContentSocialNetworkTop($device));

            $index_files = [
                'pc' => 'pctop_index.html',
                'sp' => 'sptop_index.html'
            ];
            if ($view->isTop && $this->hasIssetTopHtml($index_files[$device])) {
                $tagTop->setTagKoma($this->getParamsKomaTop($view->hp, $pageId, $device),$view->isPreview);
                $tagTop->setTagInfoList($this->getContentNewsCommonTop($pageId, $device));
                // $view->tagTopOriginal = $tagTop;
                $_helperTags->newBlock($index_files[$device]);
                $_helperTags->assignGroup($tagTop->getListTags());
                $output = $this->addScriptTagTop($_helperTags->getOutput(),$view->isPreview);
                return $output;
            }
        }
        
Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent3----');

        $view->all_pages = $this->getPages(); // $this->getPagesFilterDraft() こっち？

Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent4----');

        $view->page = $this->loadHpPage($pageId, null, true);

Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent5----');

        $page_list = array();
        if ($childrenPageIds && count($childrenPageIds)) {
            if ($yyyymm) {
                $yyyymm = (string)$yyyymm;
                $year = substr($yyyymm, 0, 4);
                $month = substr($yyyymm, 4, 2);
                $table = $this->hpPageRepository;
                $childrenPageIds = $table->filterPageIdsByDate($this->getHpRow()->id, $childrenPageIds, $year, $month);
                $view->blog_yyyymm = $yyyymm;
            }

            $childrenPageIds = array_chunk($childrenPageIds, self::ARTICLE_PER_PAGE)[$listNumber - 1];
            foreach ($childrenPageIds as $id) {
                $page_list[] = $this->loadHpPage($id, $view->page->getParentRow());
            }
            $view->page_list = $page_list;
            $view->listNumber = $listNumber;
            $view->listCount = $listCount;

        }
Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent6----');

        if (!is_null($contactContent)) {
            $view->contactContent = $contactContent;
        }

        // 各ページのメインコンテンツをレンダリング
        $view->contents = $this->content('_main_content.blade.php', $prefix, $suffix, $parentDir, $view);

        // 各ページのサイドコンテンツをレンダリング
        $view->sideunique = trim($this->content('_side_content.blade.php', $prefix, $suffix, $parentDir, $view));

        // ヘッダーなど共通部分をレンダリング
        $view->page = $view->page->getRow();
        if ($this->getPublishType() == config('constants.publish_type.TYPE_PREVIEW')) {
            foreach ($this->getIncludeHtmlList() as $name) {
                $view->{$name} = $this->getContentCommonParts($name, $device);
            }
            
            // ブログ一覧と詳細はサイドの月別一覧
            if ($view->page->page_type_code == HpPageRepository::TYPE_BLOG_INDEX
             || $view->page->page_type_code == HpPageRepository::TYPE_COLUMN_INDEX) {
                $view->{'sideblog_'.$pageId} = $this->getContentsSideBlog($childrenPageIds, $device);
            }
            elseif ($view->page->page_type_code == HpPageRepository::TYPE_BLOG_DETAIL 
                || $view->page->page_type_code == HpPageRepository::TYPE_COLUMN_DETAIL) {
                $childrenPageIds = $this->getChildrenPageIds($this->getPage($view->page->parent_page_id), true);
                $view->{'sideblog_'.$view->page->parent_page_id} = $this->getContentsSideBlog($childrenPageIds, $device);
            }
        }

Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent7----');

        // 全てを纏める
        $contetn = $this->content($file, $prefix, $suffix, $parentDir, $view);

Logger\Publish::getInstance()->infoRenderGetContent('--------------getContent8----');

        return $contetn;

    }

    private $tmp      = array();
    private $contents = array();
    private $tmpArticle      = array();
    private $contentsArticle = array();

    private function getContentSitemap($device, $is404 = false) {

        $this->tmp = array();
        $this->contents = array();
        $this->tmpArticle = array();
        $this->contentsArticle = array();

        $ds = DIRECTORY_SEPARATOR;
        $table = $this->hpPageRepository;
        $category = $table->getCategoryMap();
        $file = 'sitemap.blade.php';

        $view = $this->getViewInstance();
        $view->is404 = $is404;
        $view->contentsList = array();
        $view->contactList = array();
        $view->articleList = array();

        foreach ($this->getPagesFilterDraft() as $page) {

            // 非表示
            if (!$table->isDisplayInSitemap($page)) {
                continue;
            }

            // 問い合わせ
            if (in_array($page['page_type_code'], $category[HpPageRepository::CATEGORY_FORM])) {
                $view->contactList[] = $page;
                continue;
            }

            // article
            if (in_array($page['page_category_code'], $table->getCategoryCodeArticle())) {
                $this->tmpArticle[] = $page;
                continue;
            }

            // コンテンツ
            $this->tmp[] = $page;
        }

        // ソート
        foreach ($this->tmp as $i => $page) {
            if ($page['level'] == 1) {
                $this->contents[] = $page;
                unset($this->tmp[$i]);
                $this->lowerPage($page, $this->tmp, false);
            }
        }
        $view->contentsList = $this->contents;

        foreach ($this->tmpArticle as $i => $page) {
            if ($page['level'] == 1) {
                $this->contentsArticle[] = $page;
                unset($this->tmpArticle[$i]);
                $this->lowerPage($page, $this->tmpArticle, true);
            }
        }

        $view->contentsArticle = $this->contentsArticle;

        // device theme
        $path = $this->sitemapViewPath($device, false);

        // device standard
        if (!file_exists($path.$ds.$file)) {
            $path = $this->sitemapViewPath($device, true);
        }

        // pc theme
        if (!file_exists($path.$ds.$file)) {
            $device = 'pc';
            $path = $this->sitemapViewPath($device, false);
        }

        // pc standard
        if (!file_exists($path.$ds.$file)) {
            $path = $this->sitemapViewPath($device, true);
        }

        $view->setViewPath([$path], $file);
        if (file_exists($path.$ds.$file)) {
            return $view->render($file);
        }
    }

    /**
     * 共通HTMLの中身を取得
     *
     * @param $name
     * @param $device
     * @return mixed
     */
    private function getContentCommonParts($name, $device) {

        $file = 'common'.DIRECTORY_SEPARATOR.'_'.$name.'.blade.php';
        $prefix = $this->getBasePartialsPath().DIRECTORY_SEPARATOR;
        $suffix = DIRECTORY_SEPARATOR.$device;
        $parentDir = $this->getThemeRow()->name;
        $view = $this->getViewInstance();
        // サイドの共通パーツは別途viewにパラメータを渡す
        if ($name == 'sidecommon') {
            $this->setViewParamForSideCommon($view, $device);
        }
        if  ($view->isTopOriginal) {

            $topPartials = $this->getCommonPartialsByDevice($device);
            /** @var Helper\Tags $_helperTags */
            // ATHOME_HP_DEV-4866 View-Helper作成時に、TopOriginalコピー元を指定
            $_helperTags = Helper\Tags::getInstance($this->getCompanyRow()->id, $this->usePubTop, $this->pubTopSrcPath);
            $sitemapOutput = $this->content( '_footernav.blade.php', $prefix, $suffix, $parentDir, $view);
            $this->topTags->setTag(Top\TagTopOriginal::TAG_SITEMAP, $sitemapOutput);
            if (!$view->isPreview) {
                $sidebarArticleOutput = '<?php $sidearticlelinkoriginal_error = $viewHelper->includeCommonFile("sidearticlelinkoriginal");?>';
            } else {
                $sidebarArticleOutput = $this->content( '_sidearticlelinkoriginal.blade.php', $prefix, $suffix, $parentDir, $view);
            }
            $this->topTags->setTag(Top\TagTopOriginal::TAG_ARTICLELINK, $sidebarArticleOutput);
            if($name == 'header' && !$view->isPreview && $this->hasIssetTopHtml($topPartials[$name], $device, $name)){
                $_helperTags->newBlock($topPartials[$name]);
                $this->topTags->setHeaderPublish();
                if ($device == 'sp') {
                    $gnavOutput = $this->content( '_gnav.blade.php', $prefix, $suffix, $parentDir, $view);
                    $this->topTags->setTagSpGlonavi($gnavOutput);
                }
                $_helperTags->assignGroup($this->topTags->getListTags());
                return $this->topTags->addScriptHeader().$_helperTags->getOutput().$this->topTags->addScriptAfterHeader();
            }
            if($name == 'footer' && !$view->isPreview && $this->hasIssetTopHtml($topPartials[$name], $device, $name)){
                $_helperTags->newBlock($topPartials[$name]);
                $_helperTags->assignGroup($this->topTags->getListTags());
                return $this->topTags->addScriptBeforeFooter().$_helperTags->getOutput().$this->topTags->addScriptAfterFooter();
            }
            if ($name == 'company_info' && $this->hasIssetTopHtml($topPartials['footer'], $device, 'footer')) {
                return '';
            }
            if (array_key_exists($name, $topPartials) && $this->hasIssetTopHtml($topPartials[$name], $device, $name)) {
                $_helperTags->newBlock($topPartials[$name]);
                if ($name == 'header' && $device == 'sp') {
                    $gnavOutput = $this->content( '_gnav.blade.php', $prefix, $suffix, $parentDir, $view);
                    $this->topTags->setTagSpGlonavi($gnavOutput);
                }
                $_helperTags->assignGroup($this->topTags->getListTags());
                $output = $_helperTags->getOutput();

                // if header and is form page/ inquiry page, filter header
                if($name == 'header' && $this->isInquiry()){
                    $output = $this->topTags->filterHeaderPreview($output);
                }

                // #3936 add footer
                if($name == 'footer' && $this->isInquiry()){
                    $output = $this->topTags->filterFooterPreview($output);
                }
                return $output;
            }
        }
        return $this->content($file, $prefix, $suffix, $parentDir, $view);
    }

    private function getTemplateNewsTop()
    {
        return "
        <section>
            <h2 class='heading-lv1'><span>NEWS</span></h2>
            <div class='elemen telement-news'>
                <dl>
                <dt>yyyy年mm月dd日</dt>
                <dd>
                <a href='(詳細へのURL)'>(お知らせタイトル)</a>
                </dd>
                <dt>yyyy年mm月dd日</dt>
                <dd>
                <a href='(詳細へのURL)'>(お知らせタイトル)</a>
                </dd>
                <dt>yyyy年mm月dd日</dt>
                <dd>
                <a href='(詳細へのURL)'>(お知らせタイトル)</a>
                </dd>
                </dl>
                <p class='link-past news'>
                    <a href='https://www.(ドメイン)/news2'>過去のお知らせをすべて見る>
                </p>
            </div>
        </section>";
    }
    
    public function getContentSocialNetworkTop($device)
    {        
        // ATHOME_HP_DEV-4840 : Top用プロパティ確認
        if(isset($this->contentSocialNetworkTop[$device]) && !is_null($this->contentSocialNetworkTop[$device])) {
            return $this->contentSocialNetworkTop[$device];
        }

        $tags = [
            // Top\TagTopOriginal::TAG_GOOGLE_MAP    => '',
            Top\TagTopOriginal::TAG_TWITTER       => 'tweetbtn',
            Top\TagTopOriginal::TAG_FACEBOOK      => 'fblike',
            Top\TagTopOriginal::TAG_LINE          => 'linebtn',
        ];
        
        foreach ($tags as $tag=>$file_name) {
            if ($file_name == "linebtn" && $device == "pc") {
                $tags[$tag] = '';
            } else {
                $file = 'common' . DIRECTORY_SEPARATOR . '_' .$file_name . '.blade.php';
                $prefix = $this->getBasePartialsPath() . DIRECTORY_SEPARATOR;
                $suffix = DIRECTORY_SEPARATOR . $device;
                $parentDir = 'standard';
                $view = $this->getViewInstance();

                $tags[$tag] = $this->content($file, $prefix, $suffix, $parentDir, $view);
            }
        }
        
        // ATHOME_HP_DEV-4840 : Top用プロパティ設定
        $this->contentSocialNetworkTop[$device] = $tags;

        return $tags;
    }
    
    public function getContentNewsCommonTop($page_id, $device)
    {
        $tags = [
            Top\TagTopOriginal::TAG_NEWS_1 => '',
            Top\TagTopOriginal::TAG_NEWS_2 => '',
        ];
        
        if ($this->getPublishType() == config('constants.publish_type.TYPE_PREVIEW')) {
            // ATHOME_HP_DEV-4866 View-Helper作成時に、TopOriginalコピー元を指定
            $_helperTags = Helper\Tags::getInstance($this->getCompanyRow()->id, $this->usePubTop, $this->pubTopSrcPath);
            $_helperHpLink = new Helper\HpLink();
            $_helperHpLink->setViewParams($this->getViewInstance());
            $type = Lists\Original::$EXTEND_INFO_LIST['notification_type'];
            $attr = Lists\Original::$EXTEND_INFO_LIST['page_id'];
            $category = Lists\Original::$CATEGORY_COLUMN['title'];
            $category_class = Lists\Original::$CATEGORY_COLUMN['class'];

            $settingParts = \App::make(HpMainPartsRepositoryInterface::class)->getAllNotificationSettings($page_id);
            foreach($settingParts as $part){
            
                $news = $this->hpPageRepository->fetchRowByLinkId($part->{$attr},$part->hp_id);

                $tag_name = '';
                if (1 == $part->{$type}) {
                    $tag_name = Top\TagTopOriginal::TAG_NEWS_1;
                    
                } else if  (2 == $part->{$type}) {
                    $tag_name = Top\TagTopOriginal::TAG_NEWS_2;
                }
                
                if ('' != $tag_name) {
                    $html_file = $this->filesNewsTop()[$device][$tag_name];
                    
                    $categories = $news->fetchNewsCategories();
                    $categoryList = array();
                    foreach($categories as $cate){
                        $categoryList[$cate->id] = [
                            'name' => $cate->{$category},
                            'class' => $cate->{$category_class}
                        ];
                    }
                    $pageSize = null;
                    if (isset($this->parameters['main'])) {
                        foreach ($this->parameters['main'] as $main) {
                            if (isset($main['parts'])) {
                                $parts = $main['parts'][0];
                                if ($parts['id'] == $part->id && $parts['parts_type_code'] == HpMainPartsRepository::PARTS_INFO_LIST) {
                                    $pageSize = $parts['page_size'];
                                    break;
                                }
                            }
                        }
                    }
                    if (isset($this->parameters['notifications'])) {
                        foreach ($this->parameters['notifications'] as $notifications) {
                            if ($notifications['id'] == $part->id) {
                                $pageSize = $notifications['page_size'];
                                break;
                            }
                        }
                    }

                    $childs = $news->fetchAllPublishChild($pageSize);
                    foreach ($childs as $child) {

                        $hpMainPart = \App::make(AssociatedHpPageAttributeRepositoryInterface::class)->fetchRowByHpId($child->link_id,$child->hp_id);
                        $date = Carbon::parse($child->date)->locale('en');
                        $dateJP = Carbon::parse($child->date)->locale('ja_JP');
                        $category_name = '';
                        $category_class_name = '';
                        if ($hpMainPart) {
                            $category_id = $hpMainPart->hp_main_parts_id;

                            if(isset($categoryList[$category_id])){
                                $category_name = $categoryList[$category_id]['name'];
                                $category_class_name = $categoryList[$category_id]['class'];
                            }
                        }
                        
                        $_helperTags->newBlock($html_file);
                        $text = '';
                        $elements = \App::make(HpMainElementRepositoryInterface::class)->getElement($child->id);
                        if ($elements) {
                            $_helperHpImage = new Helper\HpImage();
                            foreach ($elements as $key => $el) {
                                if ($el->type === 'image') {
                                    $image = \App::make(HpImageRepositoryInterface::class)->fetchImageInformation($el->attr_1);
                                    $text .= '<p class="element-tx tac"><img src="'.$_helperHpImage->hpImage($el->attr_1).'" alt="'.$image['title'].'"/></p>';
                                } else {
                                    $text .= '<p class="element-tx">'.$el->attr_1.'</p>';
                                }
                            }
                        }

                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_TITLE, empty($child->list_title) ? $child->title : $child->list_title);
                        $url = '#top_a_cancel';
                        if (!$this->hpPageRepository->notIsPageInfoDetail($child['page_type_code'], $child['page_flg'])) {                                                        
                            $url = $_helperHpLink->hpLink($child->link_id);
                        } else {
                            $linkDetail = \App::make(HpInfoDetailLinkRepositoryInterface::class)->getData($child->id, $child->hp_id);
                            if ($linkDetail['link_page_id'] || $linkDetail['link_url'] || $linkDetail['file2'] || $linkDetail['link_house']) {
                                switch ( $linkDetail['link_type'] )
                                {
                                    case config('constants.link_type.PAGE')	:
                                        $url = $_helperHpLink->hpLink(	$linkDetail['link_page_id']	) ;
                                        break ;
                                    case config('constants.link_type.URL')		:
                                        $url =					$linkDetail['link_url']		  ;
                                        break ;
                                    case config('constants.link_type.FILE')	:
                                        $file2 = new Helper\HpFile2();
                                        $url = $file2->hpFile2( $linkDetail['file2']			) ;
                                        break ;
                                    case config('constants.link_type.HOUSE')	:
                                        $house = new Helper\HpLinkHouse();
                                        $url = $house->hpLinkHouse( $linkDetail['link_house']			) ;
                                        break;
                                }
                            }
                        }
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DETAIL, $url);
                        if ($tag_name == Top\TagTopOriginal::TAG_NEWS_1) {
                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_LIST1, $_helperHpLink->linkList($child->parent_page_id));
                        } else {
                            $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_LIST2, $_helperHpLink->linkList($child->parent_page_id));
                        }
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_TEXT, $text);
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DATE, $dateJP->translatedFormat('Y年m月d日'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_YEAR, $date->format('Y'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_MONTH, $date->format('n'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_DAILY, $date->format('j'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK1, $dateJP->translatedFormat('l'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK2, $dateJP->translatedFormat('D'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK3, $date->format('l'));
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_WEEK4, $date->format('D').'.');
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_CATEGORY, $category_name);
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_CATEGORY_CLASS, $category_class_name);
                        $newMark = $this->hpPageRepository->checkNewMark($news->new_mark, $child->date);
                        $_helperTags->assign(Top\TagTopOriginal::TAG_NEWS_NEW_MARK, $newMark ? Lists\NewMark::NEW_MARK : "");

                        $tags[$tag_name] .= $_helperTags->getOutput();
                    }
                }
            }
        } else {
            $tags = [
                Top\TagTopOriginal::TAG_NEWS_1 => '<?php $info_list1_error = $this->viewHelper->includeCommonFile("info_list1");?>',
                Top\TagTopOriginal::TAG_NEWS_2 => '<?php $info_list2_error = $this->viewHelper->includeCommonFile("info_list2");?>',
            ];
        }
        
        return $tags;
    }
    
    /**
     * get HTML part common tag top
     *
     * @param $device
     * @return mixed
     */
    private function getContentPartCommonTop($device) {

        // ATHOME_HP_DEV-4840 : Top用プロパティ確認
        if(isset($this->contentPartCommonTop[$device]) && !is_null($this->contentPartCommonTop[$device])) {
            return $this->contentPartCommonTop[$device];
        }

        $result = [];
        $ds = DIRECTORY_SEPARATOR;
        $publishType = Publish\Ftp::getPublishName($this->getPublishType());
        foreach ($this->getCommonHtmlListTop() as $key=>$name) {
            $file = 'common'.DIRECTORY_SEPARATOR.'_'.$name.'.blade.php';
            $prefix = $this->getBasePartialsPath().DIRECTORY_SEPARATOR;
            $suffix = DIRECTORY_SEPARATOR.$device;
            $parentDir = $this->getThemeRow()->name;
            $view = $this->getViewInstance();
            if ($name == 'sidesearchtop') {
                if ($key == Top\TagTopOriginal::TAG_CHINTAI) {
                    $view->rent_or_purchase = Estate\ClassList::RENT;
                } else {
                    $view->rent_or_purchase = Estate\ClassList::PURCHASE;
                }
            }
            
            if ($name == 'sidecommon') {
                $this->setViewParamForSideCommon($view, $device);
            }
            if ($name == 'gnav' && $device == 'pc') {
                continue;
            }
            if ($name == 'gnav' && $device == 'sp' && $this->getPublishType() != config('constants.publish_type.TYPE_PREVIEW')) {
                $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
                $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);
                $file = '_gnav_top_page.html';
                file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
                $result[$key] = '<?php $gnav = $this->viewHelper->includeCommonFile("gnav_top_page");?>';
                continue;
            }
            if ($name == 'footernav' && $this->getPublishType() != config('constants.publish_type.TYPE_PREVIEW')) {
                $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
                $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);
                $file = '_sitemap_top.html';
                file_put_contents($outDir.$ds.$file, $contents, LOCK_EX);
                $result[$key] = '<?php $sitemap_top = $this->viewHelper->includeCommonFile("sitemap_top");?>';
                continue;
            }
            if ($name == 'sidenav' && $this->getPublishType() != config('constants.publish_type.TYPE_PREVIEW')) {
                $result[$key] = '<?php $sidenav_error = $this->viewHelper->includeCommonFile("sidenav");?>';
                continue;
            }
            if ($name == 'sidenavscript' && $this->getPublishType() != config('constants.publish_type.TYPE_PREVIEW')) {
                $result[$key] = '<?php $sidenav_error_script = $this->viewHelper->includeCommonFile("sidenavscript");?>';
                continue;
            }
            if ($name == 'sidearticlelinkoriginal' && $this->getPublishType() != config('constants.publish_type.TYPE_PREVIEW')) {
                $result[$key] = '<?php $sidearticlelinkoriginal_error = $this->viewHelper->includeCommonFile("sidearticlelinkoriginal");?>';
                continue;
            }
            $result[$key] = $this->content($file, $prefix, $suffix, $parentDir, $view);
        }

        // ATHOME_HP_DEV-4840 : Top用プロパティ設定
        $this->contentPartCommonTop[$device] = $result;

        return $result;
    }



    /**
     * お問い合わせ系の共通HTMLの中身を取得
     *
     * @param $name
     * @param $device
     * @return mixed
     */
    private function getContentContact($page, $class, $device) {
        $ds = DIRECTORY_SEPARATOR;

        $content = null;

        $file = 'main-parts/contact/contact-'.$class.'.blade.php';
        $prefix = $this->getBasePartialsPath().DIRECTORY_SEPARATOR;
        $parent_dir = $this->getThemeRow()->name;
        $suffix = '/'.$device;

        // standard(pc)に存在しないファイルはスルー
        if (!file_exists("{$prefix}standard/pc/{$file}")) {
            return null;
        }

        $base_dirs = array(
            $prefix.$parent_dir, $prefix.'standard'
        );

        $paths = array();
        if ($suffix === '/sp') {
            foreach ($base_dirs as $dir) {
                $paths[] = $dir.'/sp';
            }
        }
        foreach ($base_dirs as $dir) {
            $paths[] = $dir.'/pc';
        }

        /* HTMLファイルがあるか調べていく
         * 調べる順番
         *  PCの場合
         *    (1)PCの選択テーマ
         *    (2)PCのスタンダードテーマ
         *  SPの場合
         *    (1)SPの選択テーマ
         *    (2)SPのスタンダードテーマ
         *    (3)PCの選択テーマ
         *    (4)PCのスタンダードテーマ
         */
        $filePath = null;
        foreach ($paths as $path) {
            $filePath = $path.$ds.$file;
            if (file_exists($filePath)) {
                break;
            }
        }
        $content = file_get_contents($filePath);
        $content = str_replace('[[pagename]]', "'".$page['filename']."'", $content);


        // PCの場合はお問い合わせ入力画面にCMSで作成したプライバシーポリシーを埋め込む
        if ($device == 'pc' && $class == 'edit') {
            $path = null;
            $privacypolicy = "main-parts/contact/_contact-privacypolicy.blade.php";
            foreach ($paths as $_path) {
                if (file_exists($_path.$ds.$privacypolicy)) {
                    $path = $_path;
                    break;
                }
            }
            $privacypolicyContent = "";
            if (!is_null($path)) {
                $view = $this->getViewInstance();
                $view->setViewPath([$path], $file);
                $privacypolicyContent = $view->render($privacypolicy);
            }
            $content = str_replace('[[privacypolicy]]', $privacypolicyContent, $content);
        }
        // SPの場合はお問い合わせ入力画面にプライバシーポリシーリンク用のコードを埋め込む
        else if ($device == 'sp' && $class == 'edit') {

            $url =
            <<< 'EOD'
<?php
            $url = '';
            foreach ($viewHelper->getPages() as $page) {

                // プライバシーポリシー
                if ($page['page_type_code'] == 16) {
                    break;
                }
            }
            $url = (empty($_SERVER['HTTPS']) ? 'http://':'https://').$_SERVER["HTTP_HOST"].DIRECTORY_SEPARATOR.$page['new_path'];
            $url =substr($url, 0, strlen($url) - strlen('index.html'));
            echo $url;
            ?>
EOD;

            $privacypolicyLink = '<p class="link-form-privacy">お問い合わせを行う前に、<a href="'.$url.'" target="_blank">プライバシーポリシー</a>を必ずお読みください。<br> プライバシーポリシーに同意いただいた場合は「上記にご同意の上 確認画面へ進む」のボタンをクリックしてください。</p>';
            $content = str_replace('[[privacypolicy]]', $privacypolicyLink, $content);
        }

        return $content;
    }

    private function getContentsSideBlog($childrenPageIds, $device) {

        $name = 'sideblog';

        $file = '_'.$name.'.blade.php';
        $prefix = $this->getBasePartialsPath().DIRECTORY_SEPARATOR;
        $suffix = DIRECTORY_SEPARATOR.$device;
        $parentDir = $this->getThemeRow()->name;
        $view = $this->getViewInstance();

        $view->yyyymm_list = $this->listMonth($childrenPageIds, false);
        return $this->content($file, $prefix, $suffix, $parentDir, $view);
    }


    /**
     * トップページのお知らせ一覧を生成
     *
     * @param $device
     * @return mixed|string|void
     */
    private function getContentsInfoList($device) {

        $ds = DIRECTORY_SEPARATOR;

        // トップページ取得
        $pageRow = $this->getHpRow()->findPagesByType(HpPageRepository::TYPE_TOP, false);
        if ($pageRow->count() < 1) {
            return;
        }

        // ページIDセット
        $this->setPageId($pageRow->first()->id);

        // ファイル名
        $file = 'main-parts/include/info-list.blade.php';
        $prefix = $this->getBasePartialsPath().$ds;
        $parentDir = $this->getThemeRow()->name;
        $suffix = $ds.$device;

        // ビュー
        $view = $this->getViewInstance();

        // ページのパーツ取得
        $page = $this->loadHpPage($pageRow->first()->id, null, true);
        $view->all_pages = $this->getDataPublish('page_info.txt', $this->getPagesFilterDraft());

        $contents = '';
        // お知らせ一覧のみレンダリング
        if ($page->form->getSubForm('main')) {
            foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
                foreach ($area->getPartsByColumn() as $column) {
                    foreach ($column as $parts) {
                        if ($parts instanceof \Library\Custom\Hp\Page\Parts\InfoList) {
                            $view->element = $parts;
                            $view->area = $area;
                            $view->page = $page;
                            $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);
                            if ($view->isTopOriginal) {
                                return $contents;
                            }
                            break;
                        }
                    }
                }
            }
        }
        return $contents;
    }

    private function renderContentsKoma() {
        $pageRow = $this->getHpRow()->findPagesByType(HpPageRepository::TYPE_TOP, false);
        $view = $this->getViewInstance();
        if ($pageRow->count() < 1) {
            return;
        }
        if($view->isTopOriginal){
            $ds = DIRECTORY_SEPARATOR;
            $publishType = Publish\Ftp::getPublishName($this->getPublishType());
            $user = $this->getCompanyRow();
            $file = 'main-parts/include/estate-koma-top-original.blade.php';
            $prefix = $this->getBasePartialsPath().$ds;
            $parentDir = $this->getThemeRow()->name;
            $page = $this->loadHpPage($pageRow->first()->id, null, true);
            $this->paramsKoma=array();
            foreach ($this->getDeviceList() as $device) {
                $suffix = $ds.$device;
                $contents = '';
                $outDir = $this->getTempPath().''.$ds.'files'.$ds.$publishType.$ds.'view'.$ds.'common'.$ds.$device;
                $outDirtemplate = $outDir.$ds.'themeKoma';
                $this->mkdirRecursive($outDir);
                $this->mkdirRecursive($outDirtemplate);
                if ($page->form->getSubForm('main')) {
                    foreach ($page->form->getSubForm('main')->getSubForms() as $area) {
                        foreach ($area->getPartsByColumn() as $column) {
                            foreach ($column as $parts) {
                                if ($parts instanceof \Library\Custom\Hp\Page\Parts\EstateKoma) {
                                    $view->element = $parts;
                                    if($view->element->getValue(\Library\Custom\Hp\Page\Parts\EstateKoma::CMS_DISABLE)==0){
                                        $contents = '';
                                    }
                                    else{
                                        $name='special'.$view->element->getValue('special_id');
                                        if($device=='pc'){
                                            $this->paramsKoma[]=['id'=>$view->element->getValue('special_id')];
                                        }
                                        $contents = $this->content($file, $prefix, $suffix, $parentDir, $view);
                                        if($contents){
                                            // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
                                            if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
                                                $files = $this->pubTopSrcPath . '/bukken_koma/'.$name.'_'.$device.'.html';
                                            } else {
                                                $files = Lists\Original::getOriginalImportPath($user->id, 'bukken_koma/'.$name.'_'.$device.'.html');
                                            }
                                            $themeKoma = '';
                                            if (is_file($files)) {
                                                $themeKoma = file_get_contents($files);
                                            }
                                            file_put_contents($outDir.          $ds.'_'.$name.'.html', $contents    , LOCK_EX);
                                            file_put_contents($outDirtemplate.  $ds.'_'.$name.'.html', $themeKoma   , LOCK_EX);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * ブログの月一覧を取得
     *
     * @param $childrenPageIds
     * @return array
     */
    private function listMonth($childrenPageIds, $isUnique = true) {

        $year_month = array();
        foreach ($childrenPageIds as $id) {

            $yyyymm = substr(str_replace('-', '', $this->getPage($id)['date']), 0, 6);

            if (!$yyyymm) {
                continue;
            }

            $year_month[] = $yyyymm;
        }
        arsort($year_month);

        if ($isUnique) {
            return array_unique($year_month);
        }

        return $year_month;
    }


    /**
     * htaccess
     *
     */
    private function htaccess() {

        $ds = DIRECTORY_SEPARATOR;

        // ベーシック認証
        $fileName = '.htaccess';
        $search = '<<<--basic authentication-->>>';
        $replace = $this->htaccessContents();
        $subject = file_get_contents($this->getBasePublicPath().$ds.$fileName);
        $subject = str_replace($search, $replace, $subject);

        // IP制限
        $search = '<<<--ip restriction-->>>';
        $replace = $this->ipRestriction();
        $contents = str_replace($search, $replace, $subject);

        // ファイル２制限
        $search		= '<<<--file2 restriction-->>>' ;
        $replace	= $this->file2Restriction() ;
        $contents	= str_replace( $search, $replace, $contents ) ;
        
        $search = '<<<--redirect to https-->>>';
        if ($this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC') && !$this->isSalesDemo()) {
            $replace	=  <<<EOD
RewriteCond %{HTTPS} off
RewriteCond %{REQUEST_URI} robots.txt [NC,OR]
RewriteCond %{REQUEST_URI} sitemap.xml [NC,OR]
RewriteCond %{REQUEST_URI} sitemap_b.xml [NC,OR]
RewriteCond %{REQUEST_URI} sitemap_b_[0-9]+.xml [NC]
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
EOD;
        } else {
            $replace = '';
        }
        $contents	= str_replace( $search, $replace, $contents ) ;
   
        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $this->getTempPublicPath().$ds.$fileName);
    }

    /**
     * .end
     */
    private function dotEnd()
    {
        $ds			= DIRECTORY_SEPARATOR	;
        $fileName	= '.end'				;
        $contents	= "UNZIPの成功"			;
        
        $this->moveFileRecursive( $this->touchTempFile( $contents, $fileName ), $this->getTempPublicPath().$ds.$fileName ) ;
    }

    private function file2Restriction()
    {
    	$result	= ""	;
    	$pages	= $this->page->fetchPages() ;
    	
		$model_hp_file2			= \App::make(HpFile2RepositoryInterface::class)	;
		$model_hp_file2_content	= \App::make(HpFile2ContentRepositoryInterface::class)	;
		$select = $model_hp_file2_content->model()->select("extension") ;
		$select->from( $model_hp_file2_content->model()->getTable())	;

    	$table	= \App::make(HpFile2UsedRepositoryInterface::class);
    	$file2s	= $table->fetchAll( array( ['hp_id', $pages[ 0 ][ 'hp_id' ]]) ) ;

    	foreach ( $file2s as $file2 )
    	{
    		$hp_page_id	= $file2->hp_page_id	;
    		foreach ( $pages as $key => $page )
    		{
    			if ( $page[ 'id' ] == $hp_page_id )
    			{
                    $fetchPages = $this->page->fetchPages();
    				$topParentPage = $this->getTopParentPage( $fetchPages , $key ) ;
    				if ( $topParentPage[ 'member_only_flg' ] == 1 )
    				{
						$hp_file2_row			= $model_hp_file2->find($file2->hp_file2_id)	;
						$hp_file2_content_id	= $hp_file2_row->hp_file2_content_id								;
						$hp_file2_title			= $hp_file2_row->title												;
						$select->where( 'id',	$hp_file2_content_id		) ;
						$extension	=& $select->first()->extension	;
    					$filename	=& $topParentPage[ 'filename' ]				;
    					$result		.= $this->fileDirective( $hp_file2_title	, $extension, $filename )	;
    				}
    			}
    		}
    	}

        return $result ;
    }
    
    private function getTopParentPage( &$pages, $no )
    {
        $parent_page_id = $pages[ $no ][ 'parent_page_id' ]	;
    	if ( !$parent_page_id )
    	{
    		return $pages[ $no ] ;
    	}
    	foreach ( $pages as $key => $page )
    	{
    		if ( $page[ 'id' ] == $parent_page_id )
    		{
    			return $this->getTopParentPage( $pages, $key ) ;
    		}
    	}
    }

    private function fileDirective( $file2id, $extension, $pageName )
    {
    	return "
<Files {$file2id}.{$extension}>
	SetEnvIf Referer '/{$pageName}/' ref_ok
	order deny,allow
	deny from all
	allow from env=ref_ok
</Files>
		" ;
    }
    
    private function ipRestriction() {

        $config = getConfigs('ip');
    	if ($config->ip === null) {
    	    // ATHOME_HP_DEV-5419 本番環境で攻撃元のIPアドレス制限をする
          $res  = 'order allow,deny'."\n";
          $res .= 'allow from all'."\n";
          $res .= 'deny from 45.41.181.0/24'."\n";
          //   ATHOME_HP_DEV-6265 add IP deny
          $res .= 'deny from 168.138.202.105'."\n";
          $res .= 'deny from 140.238.35.233'."\n";
          $res .= 'deny from 140.238.60.173'."\n";
          $res .= 'deny from 168.138.208.218'."\n";
          $res .= 'deny from 140.238.42.233'."\n";
          $res .= 'deny from 140.238.34.4'."\n";
          $res .= 'deny from 138.2.11.135'."\n";
          $res .= 'deny from 138.2.5.153'."\n";
          $res .= 'deny from 158.101.157.30'."\n";
          $res .= 'deny from 158.101.157.137'."\n";
          $res .= 'deny from 155.248.173.236'."\n";
          $res .= 'deny from 138.3.216.49'."\n";
          $res .= 'deny from 158.101.92.186'."\n";
          $res .= 'deny from 150.230.206.85'."\n";
          $res .= 'deny from 150.230.206.117'."\n";
          //   ATHOME_HP_DEV-6450 add IP deny
          $configIpDeny = getConfigs('ip_deny');
          if ($configIpDeny->ip !== null) {
            foreach ($configIpDeny->ip as $ip_deny) {
                $res .= 'deny from '.$ip_deny."\n";
            }
          }
          return $res;
    	}
    	
    	$res = 'order deny,allow'."\n";
    	$res .= 'deny from all'."\n";
    	foreach ($config->ip as $ip) {
    		$res .= 'allow from '.$ip."\n";
    	}
    	return $res;
    }
    
    /**
     * 営業デモ用か？
     */
    private function isSalesDemo() {
    	$result	= false	;

    	$domain = AbstractRender::www($this->getPublishType()).AbstractRender::prefix($this->getPublishType()).$this->getCompanyRow()->domain;
        $config = getConfigs('sales_demo');
    	if ( strpos( $domain, $config->demo->domain ) ) {
    		$result = true	;
    	}

    	return $result ;
    }

    /**
     *
     * htpasswd
     *
     */
    private function htpasswd($outDir) {

        $ds = DIRECTORY_SEPARATOR;
        $fileName = '.htpasswd';

        $publishType	= $this->getPublishType()	;
        if ( $this->isSalesDemo() ) {
        	$publishType	= config('constants.publish_type.TYPE_TESTSITE')	;
        }
        switch ( $publishType ) {
            case config('constants.publish_type.TYPE_SUBSTITUTE'):
            case config('constants.publish_type.TYPE_TESTSITE'):
                $id = $this->getCompanyRow()->companyAccount()->first()->login_id;
                $pass = $this->getHpRow()->test_site_password;
                break;
            default:
                return;
        }

        $contents = $this->htpasswdContents($id, $pass);
        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $outDir.$ds.$fileName);
    }

    /**
     * お問い合わせ系ページのID一覧
     *
     * @param $outDir
     *
     */
    private function contactPageIdList($outDir) {

        $ds = DIRECTORY_SEPARATOR;
        $fileName = 'contact_page_list.txt';

        $table = $this->hpPageRepository;
        $category = $table->getCategoryMap();

        $contents = '';
        foreach ($this->getPagesFilterDraft() as $page) {
            if (in_array($page['page_type_code'], $category[HpPageRepository::CATEGORY_FORM])) {
                $contents .= $page['id'].',';
            }
        };

        // なければ生成しない
        if ($contents == '') {
            return;
        }
        $contents = substr($contents, 0, -1);

        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $outDir.$ds.$fileName);
    }

    /**
     * Create file setting site.ini.
     * @param $outDir string
     * @param $protocol string
     * @return return void
     */
    private function makeSiteIni($outDir, $protocol='https')
    {

        $ds = DIRECTORY_SEPARATOR;
        $fileName   = 'site.ini';
        $contents   = <<<EOD
protocol = $protocol
EOD;
        $this->moveFileRecursive($this->touchTempFile($contents, $fileName),$outDir.$ds.$fileName);
    }

    private function makeApiIni($outDir) {

        $fileName = 'api.ini';

        // com_id
        $com_id = $this->getCompanyRow()->id;

        // api_key
        $api_key = '"'.$this->getCompanyRow()->fetchCompnayAccountRow()->api_key.'"';

        // publish
        switch ($this->getPublishType()) {
            case config('constants.publish_type.TYPE_PUBLIC'):
                $publish = 1;
                break;
            case config('constants.publish_type.TYPE_TESTSITE'):
                $publish = 2;
                break;
            case config('constants.publish_type.TYPE_SUBSTITUTE'):
                $publish = 3;
                break;
            default:
                $publish = 0;
        }
        // domain
        $domain = '"'.getConfigs('api')->api->domain.'"';

        // dev or not
        $config = getConfigs('console_log');
        $dev = $config->dev;

        // googlemap client id
        $gmap_api_id      = '"' . \Library\Custom\Hp\Map::getGooleMapKeyForUserSite( $this->getCompanyRow() ) . '"' ;
        
        $apiChannel = \Library\Custom\Hp\Map::getGoogleMapChannel($this->getCompanyRow());
        $gmap_api_channel = '"'.$apiChannel.'"';

        // appliation env
        $app_env = '"'.\App::environment().'"';


        $contents = <<<EOD
[api]
com_id = $com_id
api_key = $api_key
publish = $publish
domain = $domain
dev = $dev
gmap_api_id = $gmap_api_id
gmap_api_channel = $gmap_api_channel
app_env = $app_env
EOD;

        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $outDir.DIRECTORY_SEPARATOR.$fileName);
    }

    /**
     *
     * index.php
     *
     */
    private function index_php() {

        $ds = DIRECTORY_SEPARATOR;

        $fileName = 'index.php';
        $search = '<<<--publish_type-->>>';
        $replace = Publish\Ftp::getPublishName($this->getPublishType());
        $subject = file_get_contents($this->getBasePublicPath().$ds.$fileName);
        $contents = str_replace($search, $replace, $subject);

        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $this->getTempPublicPath().$ds.$fileName);
    }

    /**
     * htaccessの中身
     *
     * @return string
     */
    private function htaccessContents() {

        if ($this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC')) {
        	if ( $this->isSalesDemo() == false ) {
        		return '' ;
        	}
        }

        $company = $this->getCompanyRow();
        if (!$company->full_path) {
            $company = \App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($this->getHpRow()->id);
        }

        $base = $company->full_path;;
        $publishName = Publish\Ftp::getPublishName($this->getPublishType());
        $AuthUserFile = $base.'/files/'.$publishName.'/setting/.htpasswd';

        return <<<EOD
AuthName 'Please enter user name and password'
AuthType Basic
AuthUserFile $AuthUserFile
AuthGroupFile /dev/null
require valid-user
<Files ~ '^.(htpasswd|htaccess)$'>
deny from all
</Files>

satisfy all
EOD;
    }

    /**
     * htpasswdの中身
     *
     * @param $id
     * @param $pass
     * @return string
     */
    private function htpasswdContents($id, $pass) {

        return $id.':'.crypt($pass, '$1$'.substr(md5($id), -2));
    }


    const DOCUMENT = 'document';
    const IMAGES   = 'images';
    const FILE2S   = 'file2s';
    
    /**
     * imagesファイル、ファイル２、ドキュメントファイルを取得
     *
     * - 本番反映は差分、テストサイト、代行作成は常に全上げ
     * - 新規に追加された画像IDをreturn
     * - delete.txtに記載された画像をGMOサーバーから削除
     * -- allの場合はすべての画像を削除
     *
     * @param $type
     * @return array
     */
    private function addFile($type) {

        $ds = DIRECTORY_SEPARATOR;

        // 全上げフラグ
        $isAllUpload = true;
        if ($this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC') && !$this->getHpRow()->all_upload_flg) {
            $isAllUpload = false;
        }

        // 出力先
        $outPath = $this->{'getTemp'.ucfirst($type).'Path'}();
        $this->mkdirRecursive($outPath);

        // 公開中ファイルID
        $publicIds = $this->getHpRow()->{$this->columnPublicId($type)} ? explode(',', $this->getHpRow()->{$this->columnPublicId($type)}) : array();
        if ($isAllUpload) {
            $publicIds = array();
        }

        // 更新後ファイルID
        $newIds = $this->getNewIds($type);

        // ATHOME_HP_DEV-5104
        $pubFileIds = null;
        if( $this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC')
            || $this->getPublishType() == config('constants.publish_type.TYPE_SUBSTITUTE') ) {
            $pubFileIds = $this->getNewPubIds($type);
        }

        // 差分
        $add = array_diff($newIds, $publicIds);
        $delete = array_diff($publicIds, $newIds);

        // 追加
        foreach ($add as $id) {

            $row = $this->getContentRow($type, $id);
            $old = $this->touchTempFile($row->content, $id.'.'.strtolower($row->extension));
            $new = $outPath.$ds.$id.$this->filename($type, $row);
            if ($type == self::FILE2S) {
                $hpFile2 = $this->getContentHpFile2($id, $this->getHpRow()->id);
                $new = $outPath.$ds.$hpFile2->title.$this->filename($type, $row);
            }
            $this->moveFileRecursive($old, $new);
            
            $this->pollingProgressBar();
        }

        // 削除
        $contents = '';
        $file = 'delete.txt';
        if (count($delete) > 0) {

            $contents = implode(',', $delete);

            // ATHOME_HP_DEV-3571 「ファイル管理」のファイルが差し変わらない不具合を解消する
            if ($type == self::FILE2S) {
                $file2DeleteTitleContents = array();

                // ATHOME_HP_DEV-5104 削除ファイル2はCMSから削除でなく、公開サイトからの削除に意味あいが変わったので修正
                $table = \App::make(HpFile2RepositoryInterface::class);
                $table->setAutoLogicalDelete(false);

                foreach ($delete as $id) {
                    $hpFile2Delete = $table->fetchRow(array(
                        ['id', $id],
                        ['hp_id' => $this->getHpRow()->id]
                    ));
                    array_push($file2DeleteTitleContents, $hpFile2Delete->title);
                }
                $contents = implode(',', $file2DeleteTitleContents);
            }
        }
        if ($isAllUpload) {
            $contents = 'all';
        }
        $old = $this->touchTempFile($contents, $file);
        $new = $outPath.$ds.$file;
        $this->moveFileRecursive($old, $new);

        // ATHOME_HP_DEV-5104
        // 本番公開のみ
        if(!is_null($pubFileIds)) {
            $table = null;
            switch($type) {
                case self::IMAGES:
                    $table = \App::make(HpImageRepositoryInterface::class);
                    break;
                case self::FILE2S:
                    $table = \App::make(HpFile2RepositoryInterface::class);;
                    break;
                case self::DOCUMENT:
                    $table = \App::make(HpFileContentRepositoryInterface::class);;
                    break;
                default:
                    break;
            }
            if(!is_null($table)) {
                $table->setAutoLogicalDelete(false);

                $publicFiles = []; // 公開ファイルリスト初期化
                foreach ($pubFileIds as $id) {
                    $pubFileRow = $table->fetchRow(array( ['id', $id], ['hp_id', $this->getHpRow()->id] ));
                    if(!empty($pubFileRow)) {
                        switch($type) {
                            case self::IMAGES:
                                $publicFiles[] = $id.$this->filename($type, $pubFileRow->getContent());
                                break;
                            case self::FILE2S:
                                $publicFiles[] = $pubFileRow->title.$this->filename($type, $pubFileRow->getContent());
                                break;
                            case self::DOCUMENT:
                                $publicFiles[] = $id.$this->filename($type, $pubFileRow);
                                break;
                            default:
                                break;
                        }
                    }
                }
                // 公開ファイル一覧の出力
                $publicTxt = 'public_files.txt';
                file_put_contents($outPath.$ds.$publicTxt, implode("\n", $publicFiles));

                // .htaccessを生成する
                $haFile = '.htaccess';
                $haLines = [];
                $haLines[] = sprintf("<Files %s>", $publicTxt);
                $haLines[] = 'Deny from all';
                $haLines[] = '</Files>';
                $haLines[] = 'RewriteEngine On';
                if ($type == self::IMAGES) {
                    $haLines[] = 'RewriteBase /images/';
                } elseif ($type == self::FILE2S) {
                    $haLines[] = 'RewriteBase /file2s/';
                } elseif ($type == self::DOCUMENT) {
                    $haLines[] = 'RewriteBase /files/';
                }
                $haLines[] = ( $type == self::IMAGES ) ? 'RewriteRule ([0-9](.*))\.(.*)$ restrict.php' : 'RewriteRule (.*)\.(.*)$ restrict.php';
                file_put_contents($outPath.$ds.$haFile, implode("\n", $haLines));
                // restrict.php コピー
                $from = sprintf("%s/data/publish/execution/restrict.php", storage_path());
                $to = $outPath.$ds.'restrict.php';
                exec("cp -a $from $to");
                chmod($to, 0755);
            }
        }

        return $newIds;

    }

    private function filename($type, $row) {

        if ($type == self::DOCUMENT) {

            return DIRECTORY_SEPARATOR.$row->filename;
        }
        if ($type == self::IMAGES) {
            return '.'.$row->extension;
        }
        if ( $type == self::FILE2S ) {
        	return ".{$row->extension}" ;
        }
    }

    private function getNewIds($type) {

        $ids = array();

        $rows = $this->getTable($type)->fetchAll($this->getWhere($type));
        foreach ($rows as $row) {
            $ids[] = $row->{$this->columnId($type)};
        }

        return $ids;
    }

    /**
     * 公開中のページ各々の利用ファイルのIDを連結する: ATHOME_HP_DEV-5104
     * @param int $type
     * @return array
     */
    private function getNewPubIds($type) {
        $ids = array();
        $colName = $this->columnPublicId($type);
        $where = [
            ['hp_id', $this->getHpRow()->id],
            ['public_flg', 1],
            'whereNotNull' => $colName
        ];
        // $where[ sprintf("%s IS NOT NULL", $colName) ] = 1;
        // カラム特定のうえhp_pageを検索
        $rows = $this->hpPageRepository->fetchAll($where);
        foreach($rows as $row) {
            if(!is_null($row->{ $colName })) {
                $ids = array_merge($ids, explode(",", $row->{ $colName }));
            }
        }
        $ids = array_unique($ids); // 重複の排除
        sort($ids, SORT_NUMERIC);  // IDの昇順に並び替え
        return $ids;
    }

    private function columnPublicId($type) {

        if ($type == self::DOCUMENT) {

            return 'public_file_ids';
        }
        if ($type == self::IMAGES) {
            return 'public_image_ids';
        }
        if ( $type == self::FILE2S ) {
        	return 'public_file2_ids' ;
        }
    }

    private function columnId($type) {

        if ($type == self::DOCUMENT) {

            return 'attr_2';
        }
        if ($type == self::IMAGES) {
            return 'id';
        }
        if ( $type == self::FILE2S ) {
        	return 'id' ;
        }
    }

    private function getWhere($type) {
        if ($type == self::IMAGES) {
            return array(['hp_id', $this->getHpRow()->id]);
        }

        if ( $type == self::FILE2S ) {
        	return array(['hp_id', $this->getHpRow()->id]);
        }
        
        if ($type == self::DOCUMENT) {
            return array(['type', 'file'], ['hp_id', $this->getHpRow()->id]);
        }
    }

    private function getTable($type) {

        if ($type == self::IMAGES) {
            return \App::make(HpImageRepositoryInterface::class);
        }
        if ( $type == self::FILE2S ) {
        	return \App::make(HpFile2RepositoryInterface::class);
        }
        if ($type == self::DOCUMENT) {
            return \App::make(HpMainElementRepositoryInterface::class);
        }

    }

    private function getContentRow($type, $id) {

        $where = [
            ['id', $id],
            ['hp_id', $this->getHpRow()->id]
        ];
        if ($type == self::IMAGES) {
            return $this->getTable($type)->fetchRow($where)->getContent();
        }
        if ( $type == self::FILE2S ) {
        	return $this->getTable( $type )->fetchRow($where)->getContent() ;
        }
        if ($type == self::DOCUMENT) {
            return \App::make(HpFileContentRepositoryInterface::class)->fetchRow($where);
        }
    }

    private function getContentHpFile2($id, $hp_id) {
        return \App::make(HpFile2RepositoryInterface::class)->fetchRow(array(
          ['id', $id], ['hp_id', $hp_id]
        ));
    }


    private function serializedTxt($outDir, $file, $content) {

        $ds = DIRECTORY_SEPARATOR;

        $serialized = serialize($content);
        $oldPath = $this->touchTempFile($serialized, $file);
        $this->moveFileRecursive($oldPath, $outDir.$ds.$file);
    }

    private function memberOnly($outDir) {

        $ds = DIRECTORY_SEPARATOR;

        $view = $this->getViewInstance();
        $file = 'member_only.blade.php';
        $path = $this->getBaseSettingPath();

        $memberOnly = array();
        foreach ($this->getPagesFilterDraft() as $page) {
            if ($page['page_type_code'] == HpPageRepository::TYPE_MEMBERONLY) {
                $page['redirect_to'] = $this->redirectTo($page);
                $memberOnly[] = $page;
            }
        }
        $view->memberOnly = $memberOnly;


        $view->setViewPath([$path], $file);
        if (file_exists($path.$ds.$file)) {
            $contents = $view->render($file);
            $file = 'member_only.ini';
            $this->moveFileRecursive($this->touchTempFile($contents, $file), $outDir.$ds.str_replace('tmpl.', '', $file));
        }
    }

    private function redirectTo($parentPage) {

        foreach ($this->getPagesFilterDraft() as $page) {

            if ($page['parent_page_id'] == $parentPage['id'] && $page['sort'] == 0) {

                return $page['new_path'];
            }
        }

        return '';
    }


    /**
     * 公開中のHTMLを作業領域にコピー
     */
    private function copyHtmlFiles() {

        foreach ($this->getDeviceList() as $device) {

            $baseDir = $this->getBackupHtmlPath().DIRECTORY_SEPARATOR.$device;
            $newDir = $this->getTempViewPath().DIRECTORY_SEPARATOR.$device;
            $this->mkdirRecursive($newDir);
            $this->copyDirRecursive($baseDir, $newDir);
        }
    }


    /**
     * コントローラーを取得
     */
    private function getFrontController() {

        $ds          = DIRECTORY_SEPARATOR;
        $publishType = Publish\Ftp::getPublishName($this->getPublishType());

        $fileName = 'FrontController.php';
        $storeDir = $this->getBasePublicPath() . $ds . '..' . $ds . 'view' . $ds . $fileName;
        $outDir   = $this->getTempPath() . $ds . 'files' . $ds . $publishType . $ds . 'view' . $ds . $fileName;

        // お問い合わせページのファイル名書き換え
        $search   = '<<<--contact page filename-->>>';
        $replace  = $this->contactPageFilename();
        $subject  = file_get_contents($storeDir);
        $contents = str_replace($search, $replace, $subject);
        
        // 営業デモ用ドメインの書き換え
        $search		= '<<<--sales demo domain-->>>'								;
        $config = getConfigs('sales_demo');
        $contents	= str_replace( $search, $config->demo->domain, $contents )	;
        
        $this->moveFileRecursive($this->touchTempFile($contents, $fileName), $outDir);
    }

    private function contactPageFilename() {

        $res = '';
        foreach ($this->getPagesFilterDraft() as $page) {
            if ($page['page_type_code'] == HpPageRepository::TYPE_FORM_CONTACT ||
                $page['page_type_code'] == HpPageRepository::TYPE_FORM_DOCUMENT ||
                $page['page_type_code'] == HpPageRepository::TYPE_FORM_ASSESSMENT ) {
                $res .= "'".$page['filename']."',";
            }
        }
        return $res;
    }

    protected function loadHpPage($page_id, $parentPageRow = null, $load_from_request = false) {
        if ($page_id == 0 && $this->parentPage) {
            $pageRow = $this->parentPage->createDetailRow();
        }
        else {
            $pageRow = $this->hpPageRepository->find($page_id);
        }

        if ($pageRow === null) {
            throw new \Exception('ページの取得に失敗しました');
        }

        if (!$parentPageRow && $pageRow->parent_page_id) {
            $parentPageRow = $this->hpPageRepository->find($pageRow->parent_page_id);
            if ($parentPageRow === null) {
                throw new \Exception('親ページの取得に失敗しました');
            }
        }

        $page = \Library\Custom\Hp\Page::factory($this->getHpRow(), $pageRow, $parentPageRow);
        $page->init();
        $page->load($load_from_request);
        $page->setFiltersForPublish();

        return $page;
    }


    private function sitemapViewPath($device, $isStandard) {

        $ds = DIRECTORY_SEPARATOR;

        $theme = $this->getThemeRow()->name;
        if ($isStandard) {
            $theme = 'standard';
        }

        return $this->getBasePartialsPath().$ds.$theme.$ds.$device.''.$ds.'main-parts';
    }

    private function lowerPage($parentPage, $tmp, $isArticle) {

        foreach ($tmp as $i => $page) {

            if ($parentPage['id'] == $page['parent_page_id']) {
                if ($isArticle) {
                    $this->contentsArticle[] = $page;
                } else {
                    $this->contents[] = $page;
                }
                unset($tmp[$i]);
                $this->lowerPage($page, $tmp, $isArticle);
            }
        }
    }


    /**
     * HTMLの中身を生成
     *
     * @param           $file
     * @param           $prefix
     * @param           $suffix
     * @param           $parent_dir
     * @param Zend_View $view
     * @return mixed
     */
    private function content($file, $prefix, $suffix, $parent_dir, $view) {

        // standard(pc)に存在しないファイルはスルー
        // device直下とcommon直下をチェック
        if (!file_exists("{$prefix}standard/pc/{$file}") && !file_exists("{$prefix}standard/pc/common/{$file}")) {
            return null;
        }

        // - spの場合、pcも参照
        // - standard以外の場合、standardも参照
        // - standard以外のspの場合、standardのpcも参照
        $base_dirs = array(
            $prefix.$parent_dir, $prefix.'standard'
        );
        $base_dirs = array_unique($base_dirs);

        $paths = array();
        if ($suffix === DIRECTORY_SEPARATOR .'sp') {
            foreach ($base_dirs as $dir) {
                $paths[] = $dir.'/sp';
                $paths[] = $dir.'/sp/common';
            }
        }
        foreach ($base_dirs as $dir) {
            $paths[] = $dir.'/pc';
            $paths[] = $dir.'/pc/common';
        }

        $view->setViewPath($paths, $file);
        return $view->render($file);
    }

    /**
     * サイド共通パーツ作成用にviewのパラメータをセット
     *
     * @param Zend_View $view
     * @param string    $device
     * @return void
     */
    private function setViewParamForSideCommon($view, $device) {
        $view->all_pages = $this->getPages();

        // TOPページにて共通設定を行うため、TOPページのデータを読み込む
        if ($this->getPublishType() === config('constants.publish_type.TYPE_PREVIEW')) {
            $page = $this->loadHpPage($this->page->getParam('id'), null, true);
            $view->page = $page;
            if ($page instanceof Page\Top) {
                $view->top_page = $page;
                return;
            }
        }

        $pageRow = $this->getHpRow()->findPagesByType(HpPageRepository::TYPE_TOP, false);
        if ($pageRow->count() < 1) {
            return;
        }

        $view->top_page = $this->loadHpPage($pageRow->first()->id);
    }

    /**
     * グローバルナビ格納
     * @var null
     */
    private $tempGnav = null;

    /**
     * グローバルナビの項目を取得
     *
     * @return array|null
     */
    private function getGnavList() {

        if ($this->tempGnav) {
            return $this->tempGnav;
        }

        $gnav = array();
        $cnt = 1;
        $pages = $this->getPages();

        $globalMenuNumber = null;

        foreach ($pages as $i => $page) {

            // check if is inquiry
            $pages[$i]['is_inquiry'] = false;
            if(in_array($page['page_type_code'],$this->hpPageRepository->getCategoryMap()[HpPageRepository::CATEGORY_FORM])){
                $pages[$i]['is_inquiry'] = true;
            }

            // 第一階層以外continue
            if ($page['level'] != 1 || is_null($page['parent_page_id'])) {
                $pages[$i]['is_gnav'] = false; // グローバルナビ判定
                continue;
            }

            // - 公開中（ + プレビューは作成中を表示）
            $company = $this->getCompanyRow();
            $hp = $this->getHpRow();

            if(is_null($globalMenuNumber)) {
                $globalMenuNumber = $company->checkTopOriginal() ? $hp->global_navigation : 6;
            }

            if (($page['public_flg'] || ($this->getPublishType() == config('constants.publish_type.TYPE_PREVIEW') && !$page['new_flg'])) && $cnt <= $globalMenuNumber) {

                switch ($page['page_type_code']) {
                    case HpPageRepository::TYPE_LINK:
                        $page['new_path'] = $page['link_url'];
                        $gnav[$page['sort']] = $page;
                        break;
                    case HpPageRepository::TYPE_LINK_HOUSE:
                        $page['new_path'] = $page['link_house'];
                        $gnav[$page['sort']] = $page;
                        break;
                    default:
                        $gnav[$page['sort']] = $page;
                        break;
                }
                $pages[$i]['is_gnav'] = true;
            }

            $cnt++;
        }
        ksort($gnav);
        $this->tempGnav = $gnav;
        $this->setPages($pages);
        return $gnav;
    }

    private $tempGnavSp = null;

    private function getGnavListSp() {


        if ($this->tempGnavSp) {
            return $this->tempGnavSp;
        }

        $gnav = array();
        foreach ($this->getPages() as $page) {

            // 第一階層以外continue
            if ($page['level'] != 1 || is_null($page['parent_page_id'])) {
                continue;
            }

            // - 公開中（ + プレビューは作成中を表示）
            if (($page['public_flg'] || ($this->getPublishType() == config('constants.publish_type.TYPE_PREVIEW') && !$page['new_flg']))) {

                switch ($page['page_type_code']) {
                    case HpPageRepository::TYPE_LINK:
                        $page['new_path'] = $page['link_url'];
                        $gnav[$page['sort']] = $page;
                        break;
                    case HpPageRepository::TYPE_LINK_HOUSE:
                        $page['new_path'] = $page['link_house'];
                        $gnav[$page['sort']] = $page;
                    default:
                        $gnav[$page['sort']] = $page;
                        break;
                }
            }
        }
        ksort($gnav);
        $this->tempGnavSp = $gnav;
        return $gnav;
    }

    /**
     * フッターナビ格納
     * @var null
     */
    private $tempFnav = null;
    private $tempFnavLevel = null;

    /**
     * フッターナビの項目を取得
     *
     * @param $footerLinkLevel
     * @return array|null
     */
    private function getFnavList($footerLinkLevel=null) {

        if(!is_null($footerLinkLevel) && $footerLinkLevel == 0) {
            return [];
        }

        if(is_null($footerLinkLevel) && !is_null($this->tempFnav)) {
            return $this->tempFnav;
        } else if (!is_null($this->tempFnavLevel)) {
            return $this->tempFnavLevel;
        }

        $res = array();

        $pages = $this->getPagesFilterDraft();

        foreach ($pages as $i => $page) {

            if (is_null($page['parent_page_id']) || $page['page_type_code'] == HpPageRepository::TYPE_TOP) {
                unset($pages[$i]);
                continue;
            }


            if ($page['level'] == 1) {
                unset($pages[$i]);

                // 取得階層制限の有無による分岐
				if(is_null($footerLinkLevel)) {
                $res[$page['id']] = $this->getFnavChildren($pages, $page);
                } else if($footerLinkLevel == 2) {
                    // 下位階層チェック不要
                    $res[$page['id']] = $page['id'];
                } else {
                    // 3階層目(以降)を組み込む
                    $res[$page['id']] = $this->getFnavChildren($pages, $page, $footerLinkLevel, 3);
                }
            }
        }

        if(is_null($footerLinkLevel)) {
            $this->tempFnav = $res;
        } else {
            $this->tempFnavLevel = $res;
        }
     
        return $res;
    }

    /**
     * フッターナビの下層を取得
     *
     * @param $pages
     * @param $parentPage
     * @param $footerLinkLevel
     * @param $currentLevel
     * @return array
     */
    private function getFnavChildren($pages, $parentPage, $footerLinkLevel=null, $currentLevel=null) {

        $res = array();

        // ブログ詳細、会員専用ページ配下を非表示
        if ($parentPage['page_type_code'] == HpPageRepository::TYPE_BLOG_INDEX || $parentPage['page_type_code'] == HpPageRepository::TYPE_MEMBERONLY) {
            return $res;
        }

        foreach ($pages as $i => $page) {

            if (is_null($page['parent_page_id']) || $page['page_type_code'] == HpPageRepository::TYPE_TOP) {
                unset($pages[$i]);
                continue;
            }

            if ($page['parent_page_id'] == $parentPage['id']) {
                unset($pages[$i]);

				if(is_null($footerLinkLevel)) {
                    $res[$page['id']] = $this->getFnavChildren($pages, $page);
                } else if($footerLinkLevel == $currentLevel) {
                    $res[$page['id']] = $page['id'];
                } else {
                    $res[$page['id']] = $this->getFnavChildren($pages, $page, $footerLinkLevel, ($currentLevel + 1));
                }
            }
        }

        if (count($res) == 0) {
            $res = $parentPage['id'];
        }

        return $res;
    }

    /**
     * サイドナビを取得
     *
     * @return string
     */
    private function getSnavList() {

        return json_encode($this->getFnavList());
    }

    /**
     * viewインスタンスの取得
     *
     * @param array $data
     * @return Zend_View
     */
    private function getViewInstance($data = array()) {

        $view = new \Library\Custom\View();
        $view->addHelperPath(array('library/Custom/View/Helper/'), '\Library\Custom\View\Helper\\');
        $view->addFilterPath(array('library/Custom/View/Filter/'), '\Library\Custom\View\Filter\\');
        
        $hpRow = $this->getHpRow();


        foreach ($data as $key => $val) {
            $view->{$key} = $val;
        }
        $view->mode = $this->getPublishType();
        $view->company = $this->getCompanyRow();

        $view->fetch_tag = $this->getCompanyFetchTag();

        $view->hp = $this->getHpRow();
        $view->theme = $this->getThemeRow();
        $view->layout = $this->getLayoutRow();
        $view->color = $this->getColorRow();
        $view->gnav = $this->getGnavList();
        $view->gnav_sp = $this->getGnavListSp();
        $view->footernav = $this->getFnavList($hpRow->footer_link_level);

        $view->sidenav = $this->getSnavList();
        $view->siteImagePc = $this->getSiteImage(config('constants.hp_site_image.TYPE_SITELOGO_PC'));
        $view->siteImageSp = $this->getSiteImage(config('constants.hp_site_image.TYPE_SITELOGO_SP'));
        $view->siteImageWebclip = $this->getSiteImage(config('constants.hp_site_image.TYPE_WEBCLIP'));
        $view->keywords = $this->filterNullKeywords();

        if ($this->getPublishType() === config('constants.publish_type.TYPE_PREVIEW')) {
            $view->isPreview = true;
            $view->pages = $this->getPages();
        }
        else {
            $view->isPreview = false;
            $view->pages = $this->getPagesFilterDraft();
            $view->all_pages = $this->getPagesFilterDraft();
        }
        Helper\HpPageTitle::setPreview($view->isPreview);
        Helper\HpLinkHouse::setPreview($view->isPreview);

        // $view->isTopOriginal = $view->company->checkTopOriginal();
        $view->isTopOriginal = $this->getIsTopOriginal();

        if ($view->isTopOriginal) {
            $view->layoutTop = $this->getLayoutTop();
        }
        if(app('request')->hasSession())
        {
            $this->session->company = $this->getCompanyRow();// sessionにもcompany
        }

        $view->page = '';
        if ($this->getPageId() || $this->parentPage) {
            $page_id = $this->getPageId();
            if (!$page_id && $this->parentPage) {
                $page = $this->parentPage->createDetailRow();
            }
            else {
                $page = $this->hpPageRepository->fetchRowById($this->getPageId());
            }
            $view->pageId = $page_id;
            $view->page = $page;
        }

        $view->isTop = false;
        $view->isMemberOnly = false;
        $view->isSitemap = false;
        $view->isBlogIndex = false;
        if ($view->page) {
            $view->isTop = $view->page->page_type_code == HpPageRepository::TYPE_TOP ? true : false;
            $view->isMemberOnly = $view->page->page_type_code == HpPageRepository::TYPE_MEMBERONLY ? true : false;
            $view->isBlogIndex = $view->page->page_type_code == HpPageRepository::TYPE_BLOG_INDEX ? true : false;
        }
        // ATHOME_HP_DEV-4866 各ヘッダーの js, cssを埋め込む際必要
        $view->usePubTop = $this->usePubTop;
        $view->pubTopSrcPath = $this->pubTopSrcPath;

        // フッターで使用するページ
        $view->pageCompany = array();
        foreach ($view->pages as $page) {
            switch ($page['page_type_code']) {
                case HpPageRepository::TYPE_FORM_CONTACT:
                    $view->pageContact = $page;
                    break;
                case HpPageRepository::TYPE_COMPANY:
                    $view->pageCompany[] = $page;// すべて表示
                    break;
                case HpPageRepository::TYPE_PRIVACYPOLICY:
                    $view->privacypolicy = $page;
                    break;
                case HpPageRepository::TYPE_SITEPOLICY:
                    $view->sitepolicy = $page;
                    break;
                // case HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION:
                //     $view->usefulRealEstateInformation = $page;
                //     break;
            }
        }

        // 共通サイドコンテンツにデータがあるか
        // プレビュー && トップページ -> ポスト値から判定
        if ($this->getPublishType() === config('constants.publish_type.TYPE_PREVIEW') && $view->page && $view->page->page_type_code == HpPageRepository::TYPE_TOP) {

            list($view->hasCommonSideParts, $view->hasCommonSidePartsSp) = array(false, false);

            if (isset($this->parameters['side'])) {

                // pc
                $view->hasCommonSideParts = false;
                if (\App::make(HpSidePartsRepositoryInterface::class)::isDisplayCommonSideParts($this->parameters['side'], $view->hp)) {
                    $view->hasCommonSideParts = true;
                }

                // sp
                foreach ($this->parameters['side'] as $side) {
                    if (isset($side['parts_type_code']) && $side['parts_type_code'] != HpSidePartsRepository::PARTS_QR) {
                        continue;
                    }elseif(isset($side['parts_type_code']) && $side['parts_type_code'] != HpSidePartsRepository::PARTS_LINE_AT_QR) {
                        continue;
                    }elseif(isset($side['parts_type_code']) && $side['parts_type_code'] != HpSidePartsRepository::PARTS_TW) {
                        continue;
                    }elseif(isset($side['parts_type_code']) && $side['parts_type_code'] != HpSidePartsRepository::PARTS_FB) {
                        continue;
                    }elseif(isset($side['parts_type_code']) && $side['parts_type_code'] != HpSidePartsRepository::PARTS_LINE_AT_BTN) {
                        continue;
                    }
                    $view->hasCommonSidePartsSp = false;
                }
            }
        }
        // DBの値から判定
        else {
            $view->hasCommonSideParts = $this->getHasCommonSideParts();
            $view->hasCommonSidePartsSp = $this->getHasCommonSidePartsSp();
        }

        return $view;
    }

    /**
     * 詳細ページのページID一覧を取得
     *
     * @param      $indexPage
     * @param bool $isPreview
     * @return array
     */
    private function getChildrenPageIds($indexPage, $isPreview = false) {
        $table = $this->hpPageRepository;

        if (!in_array($indexPage['page_type_code'], $table->getHasDetailPageTypeList())) {
            return null;
        }

        $childrenPageIds = array();
        foreach ($this->getPages() as $page) {

            if (!$page['public_flg'] && !$isPreview) {
                continue;
            }

            if ($page['parent_page_id'] == $indexPage['id']) {
                $childrenPageIds[] = $page['id'];
            }
        };

        if (count($childrenPageIds) < 1) {
            return $childrenPageIds;
        }

        // DBを使ってソートする
        return $table->sortChildPageId($indexPage['page_type_code'], $childrenPageIds);
    }

    /**
     * 詳細ページのページID一覧を取得
     *
     * @param      $indexPage
     * @return array
     */
    private function getChildrenPageInfoIds($indexPage) {

        $pageInfo = $this->getDataPublish('page_info.txt', $this->getPagesFilterDraft());
        $childrenPages = array();
        foreach ($this->getPages() as $page) {

            if (!$page['public_flg']) {
                continue;
            }

            if ($page['parent_page_id'] == $indexPage['id'] && $this->isReleasePage($page['id'])) {
                $childrenPages[] = $page;
            }
            if ($page['parent_page_id'] == $indexPage['id'] && !$this->isReleasePage($page['id'])) {
                $childrenPages[] = $pageInfo[$page['id']];
            }
        };

        if (count($childrenPages) < 1) {
            return $childrenPages;
        }
        $filter = new Helper\FilterCollection();
        $pages = $filter->filterCollection($childrenPages, array('page_type_code', HpPageRepository::TYPE_INFO_DETAIL, 'public_flg', 1), array(['date', 'id'], ['DESC', 'DESC']));
        return array_map(function($page) {
            return $page['id'];
        }, $pages);
    }

    /**
     *
     * 一覧ページのページ数をカウント
     *
     */
    private function countIndexPage($childrenPageIds) {

        return ceil(count($childrenPageIds) / self::ARTICLE_PER_PAGE);
    }

    /**
     * キーワードのフィルタリング
     *
     * @return string
     */
    private function filterNullKeywords() {

        $keywordPage = '';
        if ($this->getPageId()) {
            $keywordPage = $this->getPage($this->getPageId())['keywords'];
        }

        return implode(',', array_filter(explode(',', $keywordPage.','.$this->getHpRow()->keywords), 'strlen'));

    }


    /**
     * 画像を作成
     *
     * @param $outDir
     * @param $type
     * @param $id
     */
    private function createSiteImage($outDir, $type, $id) {

        $row = \App::make(HpSiteImageRepositoryInterface::class)->fetchRowByType($id, $this->getHpRow()->id, $type);
        $file = $this->siteImageName($type).'.'.$row->extension;

        $old = $this->touchTempFile($row->content, $file);
        $new = $outDir.DIRECTORY_SEPARATOR.$file;
        $this->moveFileRecursive($old, $new);

        if($this->getCompanyRow()->checkTopOriginal() && $type == config('constants.hp_site_image.TYPE_WEBCLIP') && $row->extension !== 'png'){
                $news = $outDir.DIRECTORY_SEPARATOR.'apple-touch-icon.png';
                copy($new, $news);
        }
    }

    /**
     * 画像のファイル名を判定
     *
     * @param $type
     * @return string
     */
    private function siteImageName($type) {

        switch ($type) {
            case config('constants.hp_site_image.TYPE_SITELOGO_PC'):
                return 'logo_pc';
            case config('constants.hp_site_image.TYPE_SITELOGO_SP'):
                return 'logo_sp';
            case config('constants.hp_site_image.TYPE_FAVICON'):
                return 'favicon';
            case config('constants.hp_site_image.TYPE_WEBCLIP'):
                return 'apple-touch-icon';
        }
    }

    /**
     * 更新対象ページか確認
     *
     * @param $pageId
     * @return bool
     */
    private function isReleasePage($pageId) {
        $updatePageIds = $this->page->getUpdatedPageIds();
        if (!isset($updatePageIds['release'])) {
            return false;
        }
        return in_array($pageId, $updatePageIds['release']);
    }

    private function robots_txt() {

        $ds = DIRECTORY_SEPARATOR;

        $fileName = 'robots.txt';

        $domain = AbstractRender::protocol($this->getPublishType()).AbstractRender::www($this->getPublishType()).AbstractRender::prefix($this->getPublishType()).$this->getCompanyRow()->domain;
        $sitemap = $domain.'/sitemap.xml';

        $table = $this->hpPageRepository;

        $content = 'User-Agent:*'."\n";
        foreach ($this->getPagesFilterDraft() as $page) {

            // 固定メニューはcontinue
            if ($table->isFixedMenuType($page['page_type_code'])) {
                continue;
            }

            // ブログ詳細は親が階層外かチェック
            if ($page['page_type_code'] == HpPageRepository::TYPE_BLOG_DETAIL) {
                $parent = $this->getPage($page['parent_page_id']);
                if (is_null($parent['parent_page_id'])) {

                    $content .= 'Disallow:'.$this->disallowUrl($page)."\n";
                    continue;
                }
            }

            // 階層外
            if (is_null($page['parent_page_id'])) {

                $content .= 'Disallow:'.$this->disallowUrl($page)."\n";
                continue;
            }

        }
        $content .= 'Sitemap:'.$sitemap."\n";

        $path = $this->getTempPublicPath().$ds.$fileName;
        $this->moveFileRecursive($this->touchTempFile($content, $fileName), $path);

    }

    private function disallowUrl($page) {

        $str = '/'.$page['new_path'];
        $cut = 10;//index.html
        return substr($str, 0, strlen($str) - $cut);
    }

    private function sitemap_xml() {

        $ds = DIRECTORY_SEPARATOR;

        $fileName = 'sitemap.xml';

        $content = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n\n";

        foreach ($this->getPagesFilterDraft() as $page) {

            if (!$this->hpPageRepository->hasEntity($page['page_type_code'])) {
                continue;
            }

            if ($this->hpPageRepository->notIsPageInfoDetail($page['page_type_code'], $page['page_flg'])) {
                continue;
            }

            if (is_null($page['parent_page_id']) && $page['page_type_code'] != HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) {
                continue;
            }

            $loc = $this->sitemap_xml_loc($page);

            if ($page['page_type_code'] == HpPageRepository::TYPE_TOP) {
                $priority = '1.0';
            }
            else {
                $priority = 1 - ($page['level'] / 10);
            }

            $content .= '<url>'."\n";
            $content .= '<loc>'.$loc.'</loc>'."\n";
            $content .= '<changefreq>daily</changefreq>'."\n";
            $content .= '<priority>'.$priority.'</priority>'."\n";
            $content .= '<xhtml:link rel="alternate" media="only screen and (max-width: 640px)" href="'.$loc.'" />';
            $content .= '</url>'."\n\n";
        }

        $content .= '</urlset>'."\n";

        $path = $this->getTempPublicPath().$ds.$fileName;
        $this->moveFileRecursive($this->touchTempFile($content, $fileName), $path);
    }

    private function sitemap_xml_loc($page) {

        // http: or https:
        $map = $this->hpPageRepository->getCategoryMap();
        $protocol = AbstractRender::protocol($this->getPublishType());
        if (in_array($page['page_type_code'], $map[HpPageRepository::CATEGORY_FORM])) {
            $protocol = 'https://';
        }

        // domain
        $domain = AbstractRender::www($this->getPublishType()).AbstractRender::prefix($this->getPublishType()).$this->getCompanyRow()->domain;

        // uri
        $uri = DIRECTORY_SEPARATOR.substr($page['new_path'], 0, strlen($page['new_path']) - strlen('index.html'));

        return $protocol.$domain.$uri;
    }

    /**
     * 物件検索ファイルのレンダリング
     *
     */
    private function renderEstateSearch() {

        $name         = 'Estate';
        $templatePath = base_path().DS.'library'.DS.'Custom'.DS.'Publish'.DS.$name.DS.'Template';

        // 物件検索情報なければreturn
        if (!$this->getHpRow()->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS')) instanceof \App\Models\HpEstateSetting) {
            return;
        }

        /**
         * 物件お問い合わせのファイル名取得
         *
         * @param $pageTypeCode
         * @return null|string
         */
        $getFilename = function ($pageTypeCode) {

            switch ($pageTypeCode) {
                case HpPageRepository::TYPE_FORM_LIVINGBUY:
                    return 'uri_kyojuu.php';
                case HpPageRepository::TYPE_FORM_LIVINGLEASE:
                    return 'kasi_kyojuu.php';
                case HpPageRepository::TYPE_FORM_OFFICEBUY:
                    return 'uri_jigyou.php';
                case HpPageRepository::TYPE_FORM_OFFICELEASE:
                    return 'kasi_jigyou.php';
                default:
                    return null;
            }
        };

        /**
         * 物件お問い合わせのエレメントのHTML取得
         *
         * @param $id
         * @param $device
         */
        $generateHtml = function ($id, $device) {

            $content = $this->getContent($id, $device, null, null, null, null, '');

            // remove php tag
            $from    = '<\?php';
            $end     = '\?>';
            $content = preg_replace("/{$from}.+?{$end}/", '', $content);

            $doc = \phpQuery::newDocument($content);

            if ($device === 'pc') {
                $html = $doc->find(':header:contains("物件のお問い合わせ")')->siblings()->htmlOuter();
            }
            else {
                $html = $doc->find('.contents')->children()->htmlOuter();
            }

            return $html;
        };

        $view = new \Library\Custom\View();
        $estateContactRows = $this->hpPageRepository->fetchEstateContactPageAll($this->getHpRow()->id);

        $contactPageIds = [];    // ATHOME_HP_DEV-5104

        foreach ($this->getDeviceList() as $device) {

            $path = "";
            if($this->getColorRow()->theme_name) $path = "{$templatePath}/{$device}/{$this->getColorRow()->theme_name}";
            if ($path == "" || !file_exists($path)) {
                $path = "{$templatePath}/{$device}/standard";
            }

            // copy
            $from = "{$path}/search";
            $to   = "{$this->getTempViewPath()}/{$device}";
            exec("cp -r $from $to");

            // common parts
            $view->setViewPath(["{$from}/View/header"]);

            // view object
            $view->theme       = $this->getThemeRow();
            $view->color       = $this->getColorRow();
            $view->color_code  = $this->getColorCode();
            $view->tag         = $this->getCompanyRow()->fetchTag();
            $view->publishType = $this->getPublishType();
            $view->isFDP       = Estate\FdpType::getInstance()->isFDP($this->getCompanyRow());

            // stylesheet
            $content = $view->render($filename = 'stylesheet.blade.php');
            // add css Top Original
            if($this->getCompanyRow()->checkTopOriginal()){
                foreach ($this->getLayoutTop()[$device] as $layout) {
                    $folder = 'top_css';

                    // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
                    if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
                        $dir = $this->pubTopSrcPath . '/' . $folder;
                    } else {
                        $dir = Lists\Original::getOriginalImportPath($this->getCompanyRow()->id, $folder);
                    }
                    if (file_exists($dir.'/'.$layout.'.css')) {
                        $content .= '<link rel="stylesheet" href="/top/css/'.$layout.'.css" media="all"/>';
                    }
                }
            }
            if ($view->isFDP && $this->getCompanyRow()->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')) {
                $content.= '<link rel="stylesheet" href="/'.$device.'/css/fdp/Chart.css">';
                $content.= '<link rel="stylesheet" href="/'.$device.'/css/fdp/fdp_style.css" media="all"/>';
                $content.= '<link rel="stylesheet" href="/'.$device.'/css/fdp/contact-fdp.css" media="all"/>';
            }
            file_put_contents("{$to}/search/View/header/{$filename}", $content);

            // tag
            $content = $view->render($filename = 'tag.blade.php');
            file_put_contents("{$to}/search/View/header/{$filename}", $content);

            // tag under body tag
            $content = $view->render($filename = 'tag_under_body_tag.blade.php');
            file_put_contents("{$to}/search/View/header/{$filename}", $content);

            // tag above close body tag
            $content = $view->render($filename = 'tag_above_close_body_tag.blade.php');
            file_put_contents("{$to}/search/View/header/{$filename}", $content);

            // add js Top Original
            if($this->getCompanyRow()->checkTopOriginal()){
                $filename = 'script.blade.php';
                $content = file_get_contents("{$from}/View/header/{$filename}");
                foreach ($this->getLayoutTop()[$device] as $layout) {
                    $folder = 'top_js';

                    // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
                    if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
                        $dir = $this->pubTopSrcPath . '/' . $folder;
                    } else {
                        $dir = Lists\Original::getOriginalImportPath($this->getCompanyRow()->id, $folder);
                    }
                    if (file_exists($dir.'/'.$layout.'.js')) {
                        $content .= '<script src="/top/js/'.$layout.'.js"></script>';
                    }
                }
                file_put_contents("{$to}/search/View/header/{$filename}", $content);
            }

            if($view->isFDP && $this->getCompanyRow()->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')){
                $filename = 'script.blade.php';
                // 4782: Check Top original before add js FDP
                if (!$this->getCompanyRow()->checkTopOriginal()) {
                    $content = file_get_contents("{$from}/View/header/{$filename}");
                }
                $content .= '<script src="/'.$device.'/js/fdp/Chart.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/Chart.bundle.min.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/chartjs-plugin-labels.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/chartjs-plugin-annotation.min.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/owl.carousel.min.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/fdp_map.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/fdp_town.js"></script>';
                $content .= '<script src="/'.$device.'/js/fdp/contact-fdp.js"></script>';
                file_put_contents("{$to}/search/View/header/{$filename}", $content);
            }

            // 20160518 -> $this->script() へ移動。 @TODO
            // log
            $logFrom = "{$this->getBaseSettingPath()}/log.ini";
            $logTo   = "{$this->getTempScriptPath()}/../setting/log.ini";
            if (file_exists($logFrom) && file_exists($this->getTempScriptPath())) {
                copy($logFrom, $logTo);
            }
            
            // estate contact
            foreach ($estateContactRows as $row) {

                $view->page_type_code = $row->page_type_code;
                // ini
                $this->renderContactSetting($row->toArray(), $device);

                $contactPageIds[] = $row->id;

                // element
                $filename = $getFilename($row->page_type_code);
                $content  = $generateHtml($row->id, $device);
                file_put_contents("{$to}/search/View/contact/{$filename}", $content);

                //各物件問い合わせの追加タグの設定
                if ($view->publishType == config('constants.publish_type.TYPE_PUBLIC')) {
                    // 物件問い合わせ 居住用賃貸物件フォーム
                    if ($row->page_type_code == HpPageRepository::TYPE_FORM_LIVINGLEASE) {
                        //edit画面 tag
                        // </head>直上タグ情報_居住用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_residential_rental_input.blade.php", $view->tag->above_close_head_tag_residential_rental_input);

                        // ＜body>直下タグ情報_居住用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_residential_rental_input.blade.php", $view->tag->under_body_tag_residential_rental_input);

                        // </body>直上タグ情報_居住用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_residential_rental_input.blade.php", $view->tag->above_close_body_tag_residential_rental_input);

                        //comp画面 tag
                        // </head>直上タグ情報_居住用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_residential_rental_thanks.blade.php", $view->tag->above_close_head_tag_residential_rental_thanks);

                        // ＜body>直下タグ情報_居住用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_residential_rental_thanks.blade.php", $view->tag->under_body_tag_residential_rental_thanks);

                        // </body>直上タグ情報_居住用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_residential_rental_thanks.blade.php", $view->tag->above_close_body_tag_residential_rental_thanks);

                    }
                    // 物件問い合わせ 事務所用賃貸物件フォーム
                    else if ($row->page_type_code == HpPageRepository::TYPE_FORM_OFFICELEASE) {
                        //edit画面 tag
                        // </head>直上タグ情報_事業用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_business_rental_input.blade.php", $view->tag->above_close_head_tag_business_rental_input);

                        // ＜body>直下タグ情報_事業用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_business_rental_input.blade.php", $view->tag->under_body_tag_business_rental_input);

                        // </body>直上タグ情報_事業用賃貸物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_business_rental_input.blade.php", $view->tag->above_close_body_tag_business_rental_input);

                        //comp画面 tag
                        // </head>直上タグ情報_事業用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_business_rental_thanks.blade.php", $view->tag->above_close_head_tag_business_rental_thanks);

                        // ＜body>直下タグ情報_事業用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_business_rental_thanks.blade.php", $view->tag->under_body_tag_business_rental_thanks);

                        // </body>直上タグ情報_事業用賃貸物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_business_rental_thanks.blade.php", $view->tag->above_close_body_tag_business_rental_thanks);
                    }
                    // 物件問い合わせ 居住用売買物件フォーム
                    else if ($row->page_type_code == HpPageRepository::TYPE_FORM_LIVINGBUY) {
                        //edit画面 tag
                        // </head>直上タグ情報_居住用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_residential_sale_input.blade.php", $view->tag->above_close_head_tag_residential_sale_input);

                        // ＜body>直下タグ情報_居住用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_residential_sale_input.blade.php", $view->tag->under_body_tag_residential_sale_input);

                        // </body>直上タグ情報_居住用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_residential_sale_input.blade.php", $view->tag->above_close_body_tag_residential_sale_input);

                        //comp画面 tag
                        // </head>直上タグ情報_居住用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_residential_sale_thanks.blade.php", $view->tag->above_close_head_tag_residential_sale_thanks);

                        // ＜body>直下タグ情報_居住用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_residential_sale_thanks.blade.php", $view->tag->under_body_tag_residential_sale_thanks);

                        // </body>直上タグ情報_居住用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_residential_sale_thanks.blade.php", $view->tag->above_close_body_tag_residential_sale_thanks);
                    }
                    // 物件問い合わせ 事務所用売買物件フォーム
                    else if ($row->page_type_code == HpPageRepository::TYPE_FORM_OFFICEBUY) {
                        //edit画面 tag
                        // </head>直上タグ情報_事業用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_business_sale_input.blade.php", $view->tag->above_close_head_tag_business_sale_input);

                        // ＜body>直下タグ情報_事業用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_business_sale_input.blade.php", $view->tag->under_body_tag_business_sale_input);

                        // </body>直上タグ情報_事業用売買物件フォーム(入力フォームページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_business_sale_input.blade.php", $view->tag->above_close_body_tag_business_sale_input);

                        //comp画面 tag
                        // </head>直上タグ情報_事業用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_head_tag_business_sale_thanks.blade.php", $view->tag->above_close_head_tag_business_sale_thanks);

                        // ＜body>直下タグ情報_事業用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/under_body_tag_business_sale_thanks.blade.php", $view->tag->under_body_tag_business_sale_thanks);

                        // </body>直上タグ情報_事業用売買物件フォーム(サンクスページ)
                        file_put_contents("{$to}/search/View/header/above_close_body_tag_business_sale_thanks.blade.php", $view->tag->above_close_body_tag_business_sale_thanks);
                    }
                }
            }
        }

        // ATHOME_HP_DEV-5104
        if( $this->getPublishType() == config('constants.publish_type.TYPE_PUBLIC')
            || $this->getPublishType() == config('constants.publish_type.TYPE_SUBSTITUTE') ) {
            foreach(array_unique($contactPageIds) as $contactPageId) {
                $row = $this->hpPageRepository->fetchRowById($contactPageId);

                $row->public_flg = 1;

                // 利用画像一覧を hp_image_usedテーブルより取得
                $public_image_ids = \App::make(HpImageUsedRepositoryInterface::class)->usedImageIdsInPage($row->hp_id, $row->id);
                $row->public_image_ids = (count($public_image_ids)) ? implode(",", $public_image_ids) : null;

                // 利用File一覧を hp_main_elementテーブルより取得
                $public_file_ids = \App::make(HpMainElementRepositoryInterface::class)->usedFileIdsInPage($row->hp_id, $row->id);
                $row->public_file_ids = (count($public_file_ids)) ? implode(",", $public_file_ids): null;

                // 利用File2一覧を hp_file2_usedテーブルより取得
                $public_file2_ids = \App::make(HpFile2UsedRepositoryInterface::class)->usedFile2IdsInPage($row->hp_id, $row->id);
                $row->public_file2_ids = (count($public_file2_ids)) ? implode(",", $public_file2_ids) : null;

                $row->save();
            }
        }
    }

    private function renderSpecial() {

        $name         = 'Special';
        $templatePath = base_path().DS.'library'.DS.'Custom'.DS.'Publish'.DS.$name.DS.'Template';

        $special = Publish\Special\Make\Rowset::getInstance();

        // NHP-5167 詳細公開時は hp.all_upload_flg=1 もテンプレート更新対象にする
        $allUploadFlg = $this->getHpRow()->all_upload_flg;

        // 全特集共通セット(_sp-common)を作成
        // 特集の有無にかかわらずPC,SP版を用意する
        foreach ($this->getDeviceList() as $DEIVCE) {

            $themeName = $this->getColorRow()->theme_name;
            $path = $templatePath.DS.$DEIVCE.DS.$themeName;
            if (!$themeName || !file_exists($path)) {
                $path = $templatePath.DS.$DEIVCE.DS.'standard';
            }
            // とりあえずまるっとコピー
            $from = $path.DS.'sp-template'.DS.'.'; // 中身だけコピー
            $to   = $this->getTempViewPath().DS.$DEIVCE.DS."_sp-common";

            exec("cp -r $from $to");
        }

        foreach ($special->rowsetCms as $row) {

            // NHP-5167 $row->updateNowと、$all_upload_flg とのORをとるようにする
            $updateNow = $row->updateNow;
            if(!$updateNow) {
                $updateNow = $allUploadFlg;
            }
            if (!$updateNow || !$row->is_public) {
                continue;
            }

            foreach ($this->getDeviceList() as $DEIVCE) {
                $to   = $this->getTempViewPath().DS.$DEIVCE.DS.$row->filename;
                if(is_dir($to)) {
                    exec("rm -rf $to");
                }
                $this->mkdirRecursive($to);
                touch($to.DS.".sp-dummy.txt");
            }
        }
    }

    private function getParamsKomaTop($hp, $pageId, $device) {
        $params = array();
        $setting = $hp->getEstateSetting();
        if ($setting) {
            if ($this->getPublishType() === config('constants.publish_type.TYPE_PREVIEW')) {
                if (isset($this->parameters['main'])) {
                    foreach ($this->parameters['main'] as $main) {
                        if (isset($main['parts']) && $main['parts'][0]['parts_type_code'] == HpMainPartsRepository::PARTS_ESTATE_KOMA) {
                            $special = $setting->getSpecial((int)$main['parts'][0]['special_id']);

                            $params[] = array (
                                'id'            => $main['parts'][0]['special_id'],
                                'special_path'  => isset($special['filename']) ? $special['filename'] : Null,
                                'rows'          => $main['parts'][0][$device.'_rows'],
                                'columns'       => $main['parts'][0][$device.'_columns'],
                                'device'        => $device,
                            );
                        }
                    }
                }
                if (isset($this->parameters['koma'])) {
                    foreach ($this->parameters['koma'] as $id=>$koma) {
                        $special = $setting->getSpecialByOriginId((int)$koma['special_id']);
                        $params[] = array (
                            'id'            => $koma['special_id'],
                            'special_path'  => isset($special['filename']) ? $special['filename'] : Null,
                            'rows'          => $koma[$device.'_rows'],
                            'columns'       => $koma[$device.'_columns'],
                            'device'        => $device,
                        );
                    }
                }
            }
            else{
                $params=$this->paramsKoma;
            }
        }
        return $params;
    }
     
    private function addScriptTagTop($output,$review) {
        $scriptHeadTags = array(
            Top\TagTopOriginal::TAG_TWITTER => '<script id="twitter-wjs" async="" src="https://platform.twitter.com/widgets.js"></script>',
            Top\TagTopOriginal::TAG_FACEBOOK => '<script id="facebook-jssdk" async="" src="//connect.facebook.net/ja_JP/sdk.js#xfbml=1&amp;version=v2.3"></script> ',
        );
        $patternHead        ='~<head[^>]*>([\s\S]*)<\/head>~iU';
        preg_match($patternHead, $output, $match);
        $head = $match[1];
        if($review){
            $patternOldCssHead  ='~((href)=["\'])(/pc/css?)/~iU';
            $head = preg_replace($patternOldCssHead,'$1css/',$head);
            $head .= '<script type="application/javascript">window.onload = function(){var links = document.getElementsByTagName("a");if(links.length){for(i=0;i<links.length;i++){links[i].onclick = function(e){e.preventDefault(); return false;};}};window.app.isPreview = true;}</script>';
        }
        foreach ($scriptHeadTags as $tag=>$script) {
            if (strpos($output, $this->topTags->getTag($tag)) > -1) {
                $head .= $script;
            }
        }

        return preg_replace($patternHead,'<head>'.$head.'</head>',$output);
    }

    public function hasIssetTopHtml($file, $device = null, $name = null) {
        $user = $this->getCompanyRow();

        // ATHOME_HP_DEV-4866 TopOriginalコピー元判定
        if($this->usePubTop && !is_null($this->pubTopSrcPath) && is_dir($this->pubTopSrcPath)) {
            $dir    = $this->pubTopSrcPath;
        } else {
            $dir    = Lists\Original::getOriginalImportPath($user->id);
        }

        $files =  file_exists($dir) ? scandir($dir) : array();
        switch ($name) {
            case 'gnav':
                if (in_array($device.'_header.html', $files)) {
                    return true;
                }
                break;
            case 'footernav':
                if (in_array($device.'_footer.html', $files)) {
                    return true;
                }
                break;
            
            default:
                if (in_array($file, $files)) {
                    return true;
                }
                break;
        }
        return false;
    }

    public function infoListTopScript($device, $url) {
        $html = <<< 'EOD'
<?php

        $url = "{detailInfoUrl}";
        // パス
        $path = APPLICATION_PATH.'/{device}/'.parse_url($url, PHP_URL_PATH).'index.html';

        // dom取得
        $dom = new DOMDocument();
        $html = str_replace('&nbsp;', '&ensp;', file_get_contents($path)); // android chrome 文字化け対策
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // タイトル
        $node = $xpath->query('//div[@class="element-list-title"]')->item(0);
        $title = "";
        if ($node !== null) {  
            foreach ($node->childNodes as $key=>$childNode) {
                if ($node->childNodes->length == 1) {
                    $title .= $dom->saveHTML($childNode);
                } else {
                    if ($key == 0 || $key == $node->childNodes->length - 1 ) continue;
                    $ensp = html_entity_decode("&ensp;");
                    $nodeValue = html_entity_decode($childNode->nodeValue);
                    $nodeValue = str_replace($ensp, "", $nodeValue);
                    if (trim($nodeValue) != '') {
                        $title .= $dom->saveHTML($childNode);
                    } else {
                        if ($key != $node->childNodes->length - 2 ) {
                            $title .= '<br>';
                        }
                    }
                }
            }
        }
        if ($title == "") {
            $node = $xpath->query('//h2[@class="heading-lv1"]/span')->item(0);
            $title = $node !== null ? $node->nodeValue : '';
        }
        if ($title == "") {
            $node = $xpath->query('//h2[@class=" heading-lv1-1column"]/span')->item(0);
            $title = $node !== null ? $node->nodeValue : '';
        }
        if ($title == "") {
            $node = $xpath->query('//h2[@class=" heading-lv1 info"]/span')->item(0);
            $title = $node !== null ? $node->nodeValue : '';
        }

        // 日付
        $node = $xpath->query('//p[@class="element-date"]')->item(0); 
        $date = $node !== null ? $node->nodeValue : '';
        $dateJP = '';
        if ($date != '') {
            $dateJP = $date;
            $dayNames = array('日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日');
            $dayNamesShort = array('日', '月', '火', '水', '木', '金', '土');
            $date = strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $date));
        }

        $node = $xpath->query('//div[@class="element element-info-content element-news-top"]')->item(0);
        $text = $node !== null ? $node->ownerDocument->saveHTML($node) : '';
        $node = $xpath->query('//div[@class="element element-top-news"]')->item(0);
        $category = $node !== null ? $node->getAttribute('data-category') : '';
        $category_class = $node !== null ? $node->getAttribute('data-category-class') : '';
        $newMark = $viewHelper->checkNewMark($pageIndex, date('Y-m-d', $date));
        ?>
EOD;
        $html = str_replace('{detailInfoUrl}', $url, $html);
        $html = str_replace('{device}', $device, $html);
        if ($device == 'sp') {
            $html = str_replace('element element-top-news', 'element element-info-date element-top-news', $html);
        }
        return $html;

    }

    public function infoListTopScriptDetailList($pageId) {
        $html = <<< 'EOD'
<?php

        $pageDetail = $viewHelper->getPageById({pageId});

        $linkDetail = $viewHelper->getLinkInfoDetail({pageId});

        $url	= "#top_a_cancel" ;
        if ( $linkDetail['link_page_id'] || $linkDetail['link_url'] || $linkDetail['file2'] || $linkDetail['link_house']) {
            switch ( $linkDetail['link_type'] )
            {
                case 1	:
                    $url = $viewHelper->hpLink(	$linkDetail['link_page_id']	) ;
                    break ;
                case 2		:
                    $url =					$linkDetail['link_url']		  ;
                    break ;
                case 3	:
                    $url = $viewHelper->hpFile2( $linkDetail['file2']			) ;
                    break ;
                case 4	:
                    $url = $viewHelper->hpLinkHouse( $linkDetail['link_house']			) ;
                    break ;
            }
            if ($linkDetail['link_target_blank']) {
                $url = implode('"', array($url, ' target=', '_blank'));
            }
        }
        $title = $pageDetail['list_title'];
        $title = preg_replace('/((<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)$)/', '', $title);
        $title = preg_replace('/(<p[^>]*>(&nbsp;|&nbsp; )*<\/p>)/', '<br>', $title);
        $newMark = $viewHelper->checkNewMark($pageIndex, $pageDetail['date']);
        if ($pageDetail['date'] != '') {
            $dateJP = date('Y年m月d日', strtotime($pageDetail['date']));
            $dayNames = array('日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日');
            $dayNamesShort = array('日', '月', '火', '水', '木', '金', '土');
            $date = strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $pageDetail['date']));
        }
        $text = '';
        $dataCategory = $viewHelper->getCategoryClassInfo($pageDetail['link_id']);
        $category = isset($dataCategory['attr_2']) ? $dataCategory['attr_2'] : '';
        $category_class = isset($dataCategory['attr_3']) ? $dataCategory['attr_3'] : '';
        $newMark = $viewHelper->checkNewMark($pageIndex, date('Y-m-d', $date));

        ?>
EOD;
        $html = str_replace('{pageId}', $pageId, $html);
        return $html;
    }

    /**
     * トップオリジナル用のラベルを作業用ディレクトリに追記する（ATHOME_HP_DEV-4792）
     *
     * @param string $storeDir
     */
    private static function labelTopOriginal($storeDir) {
        $cmsini = getConfigs('cms');
        switch (\App::environment()) {
            case 'local':
                $style = 'style="position:fixed;top:10px;left:-72px;transform: rotate(-30deg);z-index:99999; width:226px;line-height:33px;background-color:#ed7777;font-size:19px;color:#FFF;text-align:center;cursor:pointer"';
                $label = $cmsini->header->mark->label;
                break;
            case 'testing':
                if ($cmsini->header->mark->label === '検証HP2') {
                    $style = 'style="position:fixed;top:10px;left:-72px;transform: rotate(-30deg);z-index:99999; width:226px;line-height:33px;background-color:orange;font-size:19px;color:#FFF;text-align:center;cursor:pointer"';
                } else {
                    $style = 'style="position:fixed;top:10px;left:-72px;transform: rotate(-30deg);z-index:99999; width:226px;line-height:33px;background-color:yellowgreen;font-size:19px;color:#FFF;text-align:center;cursor:pointer"';
                }
                $label = $cmsini->header->mark->label;
                break;
            case 'staging':
                $style = 'style="position:fixed;top:10px;left:-72px;transform: rotate(-30deg);z-index:99999; width:226px;line-height:33px;background-color:#77B2ED;font-size:19px;color:#FFF;text-align:center;cursor:pointer"';
                $label = $cmsini->header->mark->label;
                break;
            default:
                return;
        }

        $class = 'class="h-mark-top-original"';
        $onClickRemoveContent = '$(function() {$(".h-mark-top-original").on("click", function () {$(".h-mark-top-original").hide();});});';
        if (isset($style) && file_exists($storeDir."/pctop_index.js")) {
            $content = "$(function() { $('body').prepend('<p {$class} {$style}>{$label}</p>')});{$onClickRemoveContent}";
            $js = file_get_contents($storeDir."/pctop_index.js");
            if (strpos($js, $content) === false) {
                file_put_contents($storeDir."/pctop_index.js", $content, FILE_APPEND);
            }
        }
        if (isset($style) && file_exists($storeDir."/sptop_index.js")) {
            $content = "$(function() { $('body').prepend('<p {$class} {$style}>{$label}</p>')});{$onClickRemoveContent}";
            $js = file_get_contents($storeDir."/sptop_index.js");
            if (strpos($js, $content) === false) {
                file_put_contents($storeDir."/sptop_index.js", $content, FILE_APPEND);
            }
        }
        if (isset($style) && file_exists($storeDir."/pc_second_layer.js")) {
            $content = "$(function() { $('body').prepend('<p {$class} {$style}>{$label}</p>')});{$onClickRemoveContent}";
            $js = file_get_contents($storeDir."/pc_second_layer.js");
            if (strpos($js, $content) === false) {
                file_put_contents($storeDir."/pc_second_layer.js", $content, FILE_APPEND);
            }
        }
        if (isset($style) && file_exists($storeDir."/sp_second_layer.js")) {
            $content = "$(function() { $('body').prepend('<p {$class} {$style}>{$label}</p>')});{$onClickRemoveContent}";
            $js = file_get_contents($storeDir."/sp_second_layer.js");
            if (strpos($js, $content) === false) {
                file_put_contents($storeDir."/sp_second_layer.js", $content, FILE_APPEND);
            }
        }
    }
}

?>
