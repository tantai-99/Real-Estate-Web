<?php
namespace Library\Custom\Publish\Render;

use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use Library\Custom\Publish\Ftp;
use Library\Custom\Model\Top;
use Library\Custom\Model\Lists;

abstract class AbstractRender {

    // 共通HTMLパーツ名
    /**
     * @var Library\Custom\Publish\Render\Diff
     */
    private $diff;

    /**
     * @var int
     */
    protected $pageId;

    /**
     * @var int
     */
    private $publishType;

    /**
     * @var App\Models\Hp
     */
    private $hpRow;

    /**
     * @var App\Models\Company
     */
    private $companyRow;

    /**
     * @var App\Models\Hp::fetchTag
     */
    private $companyFetchTag;

    /**
     * @var App\Models\Company::checkTopOriginal
     */
    private $isTopOriginal;

    private $hasCommonSideParts;
    private $hasCommonSidePartsSp;

    private $themeRow;

    private $layoutRow;

    private $colorRow;

    /**
     * @var array
     */
    private $pages;

    public function __construct($hpId, $publishType = null) {
        if (!$publishType) {
            $publishType = config('constants.publish_type.TYPE_PREVIEW');
        }

        $this->publishType = $publishType;
        $this->hpRow = \App::make(HpRepositoryInterface::class)->find($hpId);

        if (!file_exists($this->getDataHtmlPath())) {
            $this->mkdirRecursive($this->getDataHtmlPath());
        }
    }

    //getter

    public static function getContactFileList() {

        return array(
            'index', 'edit', 'confirm', 'complete', 'error','validate'
        );
    }


    private function getDataHtmlPath() {
        return storage_path().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'html';
    }

    // remote html files
    protected function getBackupHtmlPath() {
        return $this->getDataHtmlPath().DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.$this->getHpRow()->id;
    }

    // temp space
    protected function getTempPath() {

        return $this->getDataHtmlPath().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getHpRow()->id;
    }

    public function getTempFilesPath() {

        return $this->getTempPath().DIRECTORY_SEPARATOR.'files';
    }


    public static function protocol($publishType) {

        $isDemo     = false                                         ;
        $profile    = getInstanceUser('cms')->getProfile()  ;
        if ( $profile )
        {
            $isDemo = $profile->isDemo( false   ) ;
        }
        $protocol = 'https://';
        if ($publishType == config('constants.publish_type.TYPE_SUBSTITUTE') || $publishType == config('constants.publish_type.TYPE_TESTSITE') || $isDemo) {
            $protocol = 'http://';
        }
        return $protocol;
    }

    public static function prefix($publishType) {

        $prefix = '';
        if ($publishType == config('constants.publish_type.TYPE_SUBSTITUTE') || $publishType == config('constants.publish_type.TYPE_TESTSITE')) {
            $prefix = Ftp::getPublishName($publishType).'.';
        }
        return $prefix;
    }

    public static function www($publishType) {

        $www = 'www.';
        if ($publishType == config('constants.publish_type.TYPE_SUBSTITUTE') || $publishType == config('constants.publish_type.TYPE_TESTSITE') ) {
            $www = '';
        }
        return $www;
    }

    public function getTempPublicPath() {

        return $this->getTempPath().DIRECTORY_SEPARATOR.self::prefix($this->getPublishType()).$this->getCompanyRow()->domain;
    }

    public function getTempViewPath() {

        return $this->getTempPath().DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.Ftp::getPublishName($this->getPublishType()).DIRECTORY_SEPARATOR.'view';
    }

    public function getTempScriptPath() {

        return $this->getTempPath().DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.Ftp::getPublishName($this->getPublishType()).DIRECTORY_SEPARATOR.'script';
    }

    public function getTempImagesPath() {

        return $this->getTempPublicPath().DIRECTORY_SEPARATOR.'images';

    }

    public function getTempFile2sPath()
    {
    	return $this->getTempPublicPath() . DIRECTORY_SEPARATOR . 'file2s' ;
    }
    
    public function getTempDocumentPath() {

        return $this->getTempPublicPath().DIRECTORY_SEPARATOR.'files';
    }

    // local view base files
    public function getBaseViewPath() {
        return app_path().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'Custom'.DIRECTORY_SEPARATOR.'Publish'.DIRECTORY_SEPARATOR.'view';
    }

    protected function getBasePartialsPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'partials';
    }

    public function getBaseCssPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'css';
    }

    public function getBaseJsPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'js';
    }

    public function getBaseImgsPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'imgs';
    }
    public function getBaseFontsPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'fonts';
    }

    public function getBaseWoffPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'fonts';
    }
    public function getBaseSvgPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'fonts';
    }
    public function getBaseTtfPath() {
        return $this->getBaseViewPath().DIRECTORY_SEPARATOR.'fonts';
    }

    public function getBasePublicPath() {
        return storage_path().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'publish'.DIRECTORY_SEPARATOR.'public';
    }

    public function getBaseScriptPath() {
        return storage_path().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'publish'.DIRECTORY_SEPARATOR.'script';
    }

    public function getBaseSettingPath() {
        return storage_path().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'publish'.DIRECTORY_SEPARATOR.'setting';
    }

    protected function getIncludeHtmlList() {

        return [
            'header',
            'footer',
            'gnav',
            'footernav',
            'sidenav',
            'sidesearch',
            'sidecommon',
            'side',
            'breadcrumb',
            'breadcrumb_ld_json',
            'script_before_head',
            'info_list',
            'company_info', // sp only
            'sidearticlelink', // 5444
            'sidesearcharcticle', // 5444
        ];
    }

    protected function getTagCommonHtmlList() {

        return [
            Top\TagTopOriginal::TAG_BODY_ABOVE => 'above_close_body',
            Top\TagTopOriginal::TAG_HEAD_ABOVE => 'above_close_head',
            Top\TagTopOriginal::TAG_BODY_UNDER => 'under_body',
        ];
    }

    protected function getCommonHtmlListTop() {

        return [
            Top\TagTopOriginal::TAG_SITEMAP => 'footernav',
            Top\TagTopOriginal::TAG_CHINTAI => 'sidesearchtop',
            Top\TagTopOriginal::TAG_BUY => 'sidesearchtop',
            Top\TagTopOriginal::TAG_SIDELINK => 'sidenav',
            Top\TagTopOriginal::TAG_CUSTOMIZE => 'sidecommon',
            Top\TagTopOriginal::SP_GLONAVI => 'gnav',
            Top\TagTopOriginal::TAG_SIDELINK_SCRIPT => 'sidenavscript',
            // Top\TagTopOriginal::TAG_BUY => 'under_body',
            Top\TagTopOriginal::TAG_ARTICLELINK => 'sidearticlelinkoriginal',
        ];
    }
    
    protected function getCommonPartialsByDevice($device)
    {
        $_list = [
            'sp' => [
                'header' => 'sp_header.html',
                'footer' => 'sp_footer.html',
                'gnav' => '',
                'footernav' => '',
            ],
            'pc' => [
                'header' => 'pc_header.html',
                'footer' => 'pc_footer.html',
                'gnav' => '',
                'footernav' => '',
            ],
        ];
        
        return $_list[$device];
    }

    protected function getLayoutTop() {
        return [
            'sp' => [
                'sp_header',
                'sp_footer',
                'sp_second_layer',
            ],
            'pc' => [
                'pc_header',
                'pc_footer',
                'pc_second_layer',
            ]
        ];
    }

    protected function filesNewsTop() {
        return [
            'pc' => [
                Top\TagTopOriginal::TAG_NEWS_1 => 'pc_news1.html',
                Top\TagTopOriginal::TAG_NEWS_2 => 'pc_news2.html'
                ],
            'sp' => [
                Top\TagTopOriginal::TAG_NEWS_1 => 'sp_news1.html',
                Top\TagTopOriginal::TAG_NEWS_2 => 'sp_news2.html'
            ]
        ];
    }

    static public function getDeviceList() {

        return array(
            'pc', 'sp'
        );
    }

    public function getDiff() {
        return $this->diff;
    }

    protected function setDiff($diff) {
        $this->diff = $diff;
    }

    protected function getHpRow() {
        return $this->hpRow;
    }

    public function getCompanyRow() {

        if ($this->companyRow) {
            return $this->companyRow; 
        }

        $this->companyRow = \App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($this->getHpRow()->id);

        // ATHOME_HP_DEV-2197	本番以外の公開の時に必要
        $config 		= getConfigs('sales_demo')                                      ;
        $demoDomain		= $config->demo->domain											;
        $contractType	= $this->companyRow->contract_type 								;
        $isPublic		= ( $this->publishType == config('constants.publish_type.TYPE_PUBLIC') )					;
        $isDemoSite		= ( strpos( $this->companyRow->domain, $demoDomain ) > 0 )		;
        if ( !$isPublic && !$isDemoSite ) {
        	$ftpUserName						= $this->companyRow->member_no			;
        	$this->companyRow->domain			= "{$ftpUserName}.{$demoDomain}"		;
        	$this->companyRow->ftp_directory	= "{$ftpUserName}.{$demoDomain}"		;
        	$this->companyRow->ftp_server_name	= "ftp.{$demoDomain}"					;
        	$this->companyRow->ftp_user_id		= "{$ftpUserName}"						;
        }
        
        return $this->companyRow ;
    }

    public function getCompanyFetchTag() {

        if ($this->companyFetchTag) {
            return $this->companyFetchTag;
        }

        $compnay = $this->getCompanyRow();
        $this->companyFetchTag = $compnay->fetchTag();

        return $this->companyFetchTag;
    }

    public function getIsTopOriginal() {

        if (!is_null($this->isTopOriginal)) {
            return $this->isTopOriginal;
        }

        $compnay = $this->getCompanyRow();
        $this->isTopOriginal = $compnay->checkTopOriginal();

        return $this->isTopOriginal;
    }

    public function getHasCommonSideParts() {

        if (!is_null($this->hasCommonSideParts)) {
            return $this->hasCommonSideParts;
        }

        $this->hasCommonSideParts = $this->getHpRow()->hasCommonSideParts();

        return $this->hasCommonSideParts;
    }

    public function getHasCommonSidePartsSp() {

        if (!is_null($this->hasCommonSidePartsSp)) {
            return $this->hasCommonSidePartsSp;
        }

        $this->hasCommonSidePartsSp = $this->getHpRow()->hasCommonSideParts(false);

        return $this->hasCommonSidePartsSp;
    }

    public function getThemeRow() {

        if ($this->themeRow) {
            return $this->themeRow;
        }
        return $this->themeRow = $this->getHpRow()->mtheme();
    }

    public function getLayoutRow() {

        if ($this->layoutRow) {
            return $this->layoutRow;
        }
        return $this->layoutRow = $this->getHpRow()->mlayout();
    }

    public function getColorRow() {

        if ($this->colorRow) {
            return $this->colorRow;
        }
        $colorRow = $this->getHpRow()->mcolor();
        if($colorRow){
            return $this->colorRow = $colorRow;
        }else{
            $obj = new \stdClass;
            $obj->name = false;
            $obj->theme_name = false;
            return $this->colorRow = $obj;
        }
    }

    public function getColorCode() {
        return $this->hpRow->color_code;
    }

    public function getPageId() {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     */
    protected function setPageId($pageId) {
        $this->pageId = $pageId;
    }

    protected function initPageId() {
        $this->pageId = null;
    }


    protected function getPage($pageId) {

       $pages = $this->getPages();

       if (isset($pages[$pageId])){
           return $pages[$pageId];
       }

       $table = \App::make(HpPageRepositoryInterface::class);
       $row = $table->find($pageId);

       // 物件検索お問い合わせのみDB直接参照
       if ($row && in_array($row->page_type_code, $table->estateContactPageTypeCodeList())){
           return $row->toArray();
       }

       return [];
   }

public function setPages($pages) {

        $array = array();

        $list = array();

        // sort でソート
        foreach ($pages as $i => $page) {

            // ブログは日付逆順
            if ($page['page_type_code'] == HpPageRepository::TYPE_BLOG_DETAIL) {
                $list[$i] = (int)mb_ereg_replace('[^0-9]', '', $page['date']) * -1;
                continue;
            }

            $list[$i] = $page['sort'];
        }
        array_multisort($list, SORT_ASC, $pages);

        // keyにページIDを設定
        foreach ($pages as $page) {
            $array[$page['id']] = $page;
        }

        $this->pages = $array;
    }

    protected function getPages() {
        return $this->pages;
    }

    protected function getPagesFilterDraft() {

        $res = array();
        foreach ($this->getPages() as $page) {
            if ($page['public_flg']) {
                $res[$page['id']] = $page;
            }
        }
        return $res;
    }

    protected function getPublishType() {
        return $this->publishType;
    }

    private $siteImagePc = '';
    private $siteImageSp = '';
    private $siteImageWebclip = '';

    protected function getSiteImage($type) {

        $table = \App::make(HpSiteImageRepositoryInterface::class);

        if ($type == config('constants.hp_site_image.TYPE_SITELOGO_PC') && $this->getHpRow()->logo_pc) {

            $res = $this->siteImagePc ? $this->siteImagePc : $table->fetchRowByType($this->getHpRow()->logo_pc, $this->getHpRow()->id, config('constants.hp_site_image.TYPE_SITELOGO_PC'));
            return $this->siteImagePc = $res;

        }
        elseif ($type == config('constants.hp_site_image.TYPE_SITELOGO_SP') && $this->getHpRow()->logo_sp) {

            $res = $this->siteImageSp ? $this->siteImageSp : $table->fetchRowByType($this->getHpRow()->logo_sp, $this->getHpRow()->id, config('constants.hp_site_image.TYPE_SITELOGO_SP'));
            return $this->siteImageSp = $res;

        }
        elseif ($type == config('constants.hp_site_image.TYPE_WEBCLIP') && $this->getHpRow()->webclip) {

            $res = $this->siteImageWebclip ? $this->siteImageWebclip : $table->fetchRowByType($this->getHpRow()->webclip, $this->getHpRow()->id, config('constants.hp_site_image.TYPE_WEBCLIP'));
            return $this->siteImageWebclip = $res;
        }

    }

    // end of getter, setter

    public function init() {

        // 作業ディレクトリの初期化
        exec("rm -rf {$this->getTempPath()}");
        // $this->deleteDirRecursive($this->getTempPath());
    }

    public function afterPublish() {

        // 作業ディレクトリの初期化
        exec("rm -rf {$this->getTempPath()}");
        // $this->deleteDirRecursive($this->getTempPath());
    }

    protected function touchTempFile($contents, $filename = 'index.html') {

        if (!file_exists($this->getTempPath())) {
            $this->mkdirRecursive($this->getTempPath());
        }

        $localFile = $this->getTempPath().DIRECTORY_SEPARATOR.$filename;
        file_put_contents($localFile, $contents, LOCK_EX);
        return $localFile;
    }

    protected function mkdirRecursive($dir) {

        if (file_exists($dir)) {
            return;
        }

        mkdir($dir, 0777, true);
    }

    protected function getFilePathRecursive($dir) {

        if (!file_exists($dir)) {
            return array();
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        $list = array();
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $list[] = $fileinfo->getPathname();
            }
        }

        return $list;
    }

    protected function getFilePathList($dir) {

        $list = array();
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') as $file) {
            if (is_file($file)) {
                $list[] = $file;
            }
        }
        return $list;
    }

    protected function deleteEmptyDirRecursive($dir) {

        exec("find {$dir} -type d -empty -delete");
    }

    protected function moveFileRecursive($oldPath, $newPath) {

        if (!file_exists($oldPath)) {
            return;
        }

        $this->mkdirRecursive(dirname($newPath));
        rename($oldPath, $newPath);
    }

    protected function copyFilesInDir($storeDir, $outDir) {

        $this->mkdirRecursive($outDir);

        foreach ($this->getFilePathRecursive($storeDir) as $path) {
            if (is_file($path)) {
                copy($path, $outDir.DIRECTORY_SEPARATOR.basename($path));
            };
        }
    }

    /**
     * @param $storeDir
     * @param $outDir
     * @throws Exception
     */
    protected function copyFilesInRoot($storeDir,$outDir){
        $this->mkdirRecursive($outDir);
        $di = new \Library\Custom\DirectoryIterator();
        $di->setExtensions(Lists\Original::$MEDIA_FILES);
        foreach ($this->getFilePathRecursive($storeDir) as $path) {
            if (is_file($path)) {
                $isAllow = $di->checkIsAllowExtension(pathinfo($path,PATHINFO_BASENAME));
                if(!$isAllow) continue;
                copy($path, $outDir.DIRECTORY_SEPARATOR.basename($path));
            };
        }
    }

    protected function copyDirRecursive($dirName, $newDir) {

        $this->mkdirRecursive($dirName);
        $this->mkdirRecursive($newDir);

        if (is_dir($dirName)) {
            if ($dh = opendir($dirName)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    if (is_dir($dirName.DIRECTORY_SEPARATOR.$file)) {
                        $this->copyDirRecursive($dirName.DIRECTORY_SEPARATOR.$file, $newDir.DIRECTORY_SEPARATOR.$file);
                    }
                    else {
                        copy($dirName.DIRECTORY_SEPARATOR.$file, $newDir.DIRECTORY_SEPARATOR.$file);
                    }
                }
                closedir($dh);
            }
        }
        return true;
    }

    protected function zipDir($dir, $file, $root = "") {

        $zip = new \ZipArchive();
        $res = $zip->open($file, \ZipArchive::CREATE);

        if ($res) {
            // $rootが指定されていればその名前のフォルダにファイルをまとめる
            if ($root != "") {
                $zip->addEmptyDir($root);
                $root .= DIRECTORY_SEPARATOR;
            }

            $baseLen = mb_strlen($dir);

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO), RecursiveIteratorIterator::SELF_FIRST);

            $list = array();
            foreach ($iterator as $pathname => $info) {
                $localpath = $root.mb_substr($pathname, $baseLen);

                if ($info->isFile()) {
                    $zip->addFile(mb_convert_encoding($pathname, "UTF-8", "auto"), mb_convert_encoding($localpath, "UTF-8", "auto"));
                }
                else {
                    $res = $zip->addEmptyDir(mb_convert_encoding($localpath, "UTF-8", "auto"));
                }
            }

            $zip->close();
        }
        else {
            return false;
        }
    }

    /**
     * １６進数のカラーコードをRGBに変換する
     */
    public function conversionColorcodeToRbg($colorCode) {
        $colorCode = preg_replace("/#/", "", $colorCode);
        $rgb["red"] = hexdec(substr($colorCode, 0, 2));
        $rgb["green"] = hexdec(substr($colorCode, 2, 2));
        $rgb["blue"] = hexdec(substr($colorCode, 4, 2));
        return $rgb;
    }

    /**
     * check if is form page / inquiry page
     * @return bool
     */
    public function isInquiry(){
        $page = $this->getPage($this->pageId);
        if(in_array($page["page_type_code"], \App::make(HpPageRepositoryInterface::class)->getCategoryMap()[HpPageRepository::CATEGORY_FORM]) ){
            return true;
        }
        return false;
    }

    public function listColorThemeFdp() {
        // color [link, hover]
        return [
            'standard' => [
                'blue' => ['#057286', '#04939a'], 
                'gray' => ['#5e5e5e', '#000'], 
                'green' => ['#04790d', '#4fa155'], 
                'orange' => ['#9b3700', '#b9734c'], 
                'red' => ['#790000', '#a14c4c'],
            ],
            'elegant' => [
                'gold' => ['#534227', '#938b80'],
                'navy' => ['#163d5c', '#7a8998'],
                'red' => ['#7d483c', '#a14c4c'],
            ],
            'japanese' => [
                'black' => ['#727272', '#939393'], 
                'navy' => ['#486472', '#5594b3'], 
                'green' => ['#7b8a57', '#a8bc77'], 
                'red' => ['#934141', '#b65151'], 
                'yellow' => ['#8a723a', '#c2a052'],
            ],
            'natural' => [
                'green' => ['#21574a', '#307d6a'], 
                'beige' => ['#2c4f12', '#41751c'], 
                'pink' => ['#274557', '#3c6a86'],
            ],
            'feminine' => [
                'blue' => ['#4d5e93', '#647abe'], 
                'green' => ['#366634', '#4f944d'], 
                'lavender' => ['#a55a84', '#cd70a4'], 
                'orange' => ['#884a12', '#b16018'], 
                'pink' => ['#a55656', '#cc6a6a'],
            ],
            'colorful' => [
                'blue' => ['#657b95', '#55b8dc'], 
                'orange' => ['#877a21', '#ffba55'], 
                'pink' => ['#a46464', '#ff7272'],
            ],
            'simple' => [
                'blue' => ['#585858', '#225396'],
                'green' => ['#585858', '#117b18'],
                'red' => ['#585858', '#b9191d'],
                'yellow' => ['#585858', '#f6bf00'],
            ],
            'sawayaka01' => [
                'blue' => ['#06c', '#4D94DB'], 
                'bluegreen' => ['#06c', '#4D94DB'], 
                'yellowgreen' => ['#06c', '#4D94DB'],
            ],
            'luxury01' => [
                'blue' => ['#205880', '#4D7999'],
                'brown' => ['#604543', '#806A69'],
                'green' => ['#284428', '#536953'],
                'navy' => ['#202c49', '#4D566D'],
                'purple' => ['#49213f', '#6D4D65'],
            ],
            'cute01' => [
                'beige' => ['#CA392C', '#E06F00'],
                'green' => ['#00664C', '#E06F00'],
                'pink' => ['#CC3A24', '#B20000'],
                'yellow' => ['#D96D00', '#D96D00'],
            ],
            'vivid01' => [
                'blue' => ['#00698C', '#00A3D9'], 
                'bluered' => ['#00698C', '#00A3D9'], 
                'red' => ['#D90000', '#FF7373'], 
                'yellow' => ['#D96D00', '#B20000'], 
                'yellowblue' => ['#D96D00', '#B20000'],
            ],
            'pop01' => [
                'blue' => ['#009bfe', '#4db9fe'], 
                'green' => ['#0a8f17', '#51b05a'], 
                'orange' => ['#fc8302', '#fda84e'], 
                'pink' => ['#eb1f6d', '#f16399'],
            ],
            'retro01' => [
                'blue' => ['#2048b6', '#275ae4'], 
                'brown' => ['#2048b6', '#275ae4'], 
                'green' => ['#2048b6', '#275ae4'], 
                'red' => ['#2048b6', '#275ae4'],
            ],
            'house01' => [
                'blue' => ['#0d73db', '#3D8FE2'], 
                'green' => ['#42b0a5', '#68C0B7'], 
                'orange' => ['#ff7200', '#FF8E33'], 
                'red' => ['#e82d54', '#ED5776'],
            ],
            'colorful02' => [
                'blue' => ['#3d5c78', '#5f8eba'], 
                'brown' => ['#936920', '#c68d2b'], 
                'green' => ['#3d6935', '#58964c'],
            ],
            'tegaki01' => [
                'blue' => ['#003399', '#6685c2'], 
                'green' => ['#003399', '#6685c2'], 
                'pink' => ['#003399', '#6685c2'],
            ],
            'unique01' => [
                'blue' => ['#005d93', '#007dc6'], 
                'green' => ['#006404', '#009906'], 
                'pink' => ['#8a1449', '#bd1b64'], 
                'yellow' => ['#ca7700', '#f89200'],
            ],
            'cool01' => [
                'blue' => ['#1e549e', '#6187bb'], 
                'green' => ['#1e549e', '#6187bb'], 
                'red' => ['#1e549e', '#6187bb'],
            ],
            'katame01' => [
                'adzuki' => ['#2048b6', '#718bcb'], 
                'blue' => ['#2048b6', '#718bcb'], 
                'green' => ['#2048b6', '#718bcb'],
            ],
            'sawayaka02' => [
                'blue' => ['#3b9ab5', '#44b1cf'], 
                'green' => ['#8b904f', '#969b55'], 
                'orange' => ['#ce8e29', '#eca32f'], 
                'pink' => ['#de6eb0', '#fd7dc9'], 
                'purple' => ['#af79e1', '#c588fd'],
            ],
        ];
    }

}
