<?php
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use Exception;
use Library\Custom\Registry;
use Modules\V1api\Services\BApi\Client;
use Library\Custom\Model\Estate;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Publish\Ftp;

class RequestForBapiActionId {
    function getControllerName() {
        return 'batch';
    }
    function getActionName() {
        return 'sitemapxml';
    }
}

/**
 * サイトマップXMLの生成とアップロード
 * php path_to_app/application/batch/index.php $env app SitemapXml [$companyIds [$noUpload]]
 * 例：php path_to_app/application/batch/index.php production app SitemapXml
 * 例：php path_to_app/application/batch/index.php production app SitemapXml 1,2,3
 * 例：php path_to_app/application/batch/index.php production app SitemapXml 1,2,3 true
 * 
 * @param string $env production | staging | testing | development | local
 * @param string $companyIds サイトマップXMLを生成するcompanyをカンマ区切りで指定（省略時は全てのcompany）
 * @param string $noUpload アップロードしない場合はtrueを指定
 */

class SitemapXml extends Command {

    const PARAM_COMPANY_IDS = 1;
    const PARAM_NO_UPLOAD = 2;

    const XML_MAX_URLS = 50000;
    // 10MB 10 * 1024 * 1024
    const XML_MAX_BYTES = 10485760;

    const SIITEMAP_B_REGEX = '/sitemap_b(_\d+)?\.xml$/';

    private $destination_dir;
    private $csv_dir;

    /**
     * @var App\Models\Company
     */
    private $companyRow;
    /**
     * @var App\Models\Hp
     */
    private $hpRow;
    /**
     * @var App\Models\HpEstateSetting
     */
    private $hpEstateSettingRow;
    /**
     * @var App\Collections\EstateClassSearchCollection
     */
    private $estateClassSearchRowset;
    /**
     * @var string
     */
    private $dest;

    /**
     * @var Library\Custom\Model\Estate\TypeList
     */
    private $estateTypeList;
    /**
     * @var Library\Custom\Model\Estate\PrefCodeList
     */
    private $prefCodeList;

    /**
     * サイトマップサフィックス
     * @var array
     */
    private $suffix = 1;

    /**
     * サイトマップXML出力先
     */
    private $fp;

    /**
     * 処理中のXMLの行数
     */
    private $currentXMLUrls = 0;

    /**
     * 処理中のXMLのバイト数
     */
    private $currentXMLBytes = 0;

    /**
     * @var V1api_Service_BApi_Client
     */
    private $bapiClient;

    private $shikugunCache = [];
    private $ensenCache = [];
    private $ekiCache = [];
    private $chosonCache = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-sitemap-xml {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command sitemap xml';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        try {
            $arguments = $this->arguments();
            BatchAbstract::validParamater($arguments, $this);
            $args = array_slice($arguments, 3);

            $this->_info->info('/////////////// START ///////////////');

            // 作業ディレクトリ
            $this->destination_dir = storage_path('data/sitemapxml');
            $this->csv_dir = implode(DIRECTORY_SEPARATOR, [storage_path('data/bukkens_csv'), date('Ymd')]);

            // 物件APIアクションID設定の為のオブジェクト
            Registry::set('V1api_Request', new RequestForBapiActionId());

            // 物件APIクライアント
            $this->bapiClient = new Client();

            // 物件種目リスト
            $this->estateTypeList = Estate\TypeList::getInstance();
            // 都道府県コードリスト
            $this->prefCodeList = Estate\PrefCodeList::getInstance();

            // company毎に処理
            $where = [];
            if (isset($args[self::PARAM_COMPANY_IDS]) && $args[self::PARAM_COMPANY_IDS]) {
                $companyIds = explode(',', $args[self::PARAM_COMPANY_IDS]);
                $where = [
                    'whereIn' => [
                        'id',
                        $companyIds
                    ]
                ];
            }
            $companyRowset =App::make(CompanyRepositoryInterface::class)->fetchAll($where);
            foreach ($companyRowset as $companyRow) {
                $this->_info->info('---- com_id:' . $companyRow->id . ' ----');

                // 初期化
                if (false === $this->initializeXml($companyRow)) {
                    if (!$this->hpEstateSettingRow) {
                        $this->_info->info('物件検索設定が存在しない為、スキップ。');
                    } else {
                        $this->_info->info('物件種別毎の検索設定が存在しない為、サイトマップXML生成処理スキップ。');
                        // ファイルアップロード
                        if ($this->createRobotsTxt() && (!isset($args[self::PARAM_NO_UPLOAD]) || $args[self::PARAM_NO_UPLOAD] != 'true')) {
                            $this->syncXml();
                        }
                    }
                    continue;
                }

                $this->_info->info('サイトマップXML生成処理開始。');
                $this->openXML();

                // 物件種目全て取得
                $estateTypesAll = $this->estateClassSearchRowset->getEstateTypes();

                // 物件種目選択
                $this->writeURL('/shumoku.html');
                // 賃貸物件種目選択
                if ($this->estateTypeList->containsRent($estateTypesAll)) {
                    $this->writeURL('/rent.html');
                }
                // 売買物件種目選択
                if ($this->estateTypeList->containsPurchase($estateTypesAll)) {
                    $this->writeURL('/purchase.html');
                }

                // 物件種別毎の処理
                $shikugunCodes = [];
                $ekiCodes = [];
                $chosonSearchEnabled = [];
                $chosonCodes = [];
                foreach ($this->estateClassSearchRowset as $estateClassSearchRow) {
                    // 検索設定
                    $searchSetting = $estateClassSearchRow->toSettingObject();
                    // エリア検索設定
                    $areaSearchFilter = $searchSetting->area_search_filter;

                    $chosonSearchEnabled[$estateClassSearchRow->estate_class] = $areaSearchFilter->canChosonSearch();

                    // 都道府県毎に処理
                    foreach ($areaSearchFilter->area_1 as $prefCode) {
                        // 物件種別毎の市区郡コードを記憶
                        if ($areaSearchFilter->hasAreaSearchType()) {
                            if (isset($areaSearchFilter->area_2[$prefCode]) && is_array($areaSearchFilter->area_2[$prefCode])) {
                                foreach ($areaSearchFilter->area_2[$prefCode] as $shikugunCode) {
                                    $shikugunCodes[$estateClassSearchRow->estate_class][$shikugunCode] = $shikugunCode;

                                    // 町村コード
                                    if (
                                        isset($areaSearchFilter->area_5[$prefCode][$shikugunCode]) &&
                                        is_array($areaSearchFilter->area_5[$prefCode][$shikugunCode]) &&
                                        $areaSearchFilter->area_5[$prefCode][$shikugunCode]
                                    ) {
                                        $chosonCodes[$estateClassSearchRow->estate_class][$shikugunCode] = $areaSearchFilter->area_5[$prefCode][$shikugunCode];
                                    }
                                }
                            }
                        }

                        // 物件種別毎の駅コードを記憶
                        if ($areaSearchFilter->hasLineSearchType()) {
                            if (isset($areaSearchFilter->area_4[$prefCode]) && is_array($areaSearchFilter->area_4[$prefCode])) {
                                foreach ($areaSearchFilter->area_4[$prefCode] as $stationCode) {
                                    $ekiCodes[$estateClassSearchRow->estate_class][$stationCode] = $stationCode;
                                }
                            }
                        }
                    }

                    // 物件種目毎に処理
                    foreach ($estateClassSearchRow->getEnabledEstateTypeArray() as $estateType) {
                        // 物件種目URL
                        $estateUrl = $this->estateTypeList->getUrl($estateType);

                        // 物件種目TOP
                        $this->writeURL("/{$estateUrl}/");
                        // 物件種目検索・結果ページ
                        $this->writeSearchUrl("/{$estateUrl}", $areaSearchFilter);
                    }
                }

                // 特集
                foreach ($this->hpEstateSettingRow->getSpecialAll() as $specialRow) {
                    // 検索設定
                    $searchSetting = $specialRow->toSettingObject();
                    // エリア検索設定
                    $areaSearchFilter = $searchSetting->area_search_filter;

                    // 特集TOP
                    $this->writeURL("/{$specialRow->filename}/");

                    // 検索ページ有の場合
                    if ($areaSearchFilter->has_search_page) {
                        // 特集検索・結果ページ
                        $this->writeSearchUrl("/{$specialRow->filename}", $areaSearchFilter);
                    }
                }

                // 物件詳細
                $csvName = $this->csv_dir . DIRECTORY_SEPARATOR . "{$this->companyRow->member_no}.csv";
                // CSVがある場合に処理
                if (file_exists($csvName)) {
                    $csv = new SplFileObject($csvName, 'rb');
                    $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
                    foreach ($csv as $rowIndex => $row) {
                        // 空行SKIP
                        if (!$row) {
                            continue;
                        }

                        $estate_id    = $row[0];
                        $shumoku_cds  = $row[1];
                        $shikugun_cds = $row[2];
                        $eki_cds      = $row[3];
                        $kaiin_link_no = $row[4];
                        $shozaichi_cds = isset($row[5]) ? $row[5] : "";

                        // 種目コードがない場合skip
                        if (!$shumoku_cds) {
                            continue;
                        }

                        // 種目毎に処理
                        foreach (explode(',', $shumoku_cds) as $shumoku_cd) {
                            $estateType = $this->estateTypeList->getByShumokuCode($shumoku_cd);
                            // 種目が取得できない場合skip
                            if ($estateType === null) {
                                continue;
                            }
                            // 種目が未設定の場合skip
                            if (!in_array($estateType, $estateTypesAll)) {
                                continue;
                            }

                            // 物件種目URL
                            $estateUrl = $this->estateTypeList->getUrl($estateType);
                            // 物件種別を取得
                            $estateClass = $this->estateTypeList->getClassByType($estateType);

                            // エリア検索
                            if ($shozaichi_cds && isset($chosonSearchEnabled[$estateClass]) && $chosonSearchEnabled[$estateClass]) {
                                foreach (explode(',', $shozaichi_cds) as $shozaichi_cd) {
                                    list($shikugun_cd, $choson_cd) = explode(':', $shozaichi_cd);
                                    if (
                                        (isset($shikugunCodes[$estateClass][$shikugun_cd])) &&
                                        (
                                            !isset($chosonCodes[$estateClass][$shikugun_cd]) ||
                                            $this->isChosonIncluded($choson_cd, $chosonCodes[$estateClass][$shikugun_cd])
                                        )
                                    ) {
                                        // 物件詳細URL
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/");
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/map.html");
                                        continue 2;
                                    }
                                }
                            } else if ($shikugun_cds) {
                                foreach (explode(',', $shikugun_cds) as $shikugun_cd) {
                                    if (isset($shikugunCodes[$estateClass][$shikugun_cd])) {
                                        // 物件詳細URL
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/");
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/map.html");
                                        continue 2;
                                    }
                                }
                            }

                            // 駅検索
                            if ($eki_cds) {
                                foreach (explode(',', $eki_cds) as $eki_cd) {
                                    if (isset($shikugunCodes[$estateClass][$eki_cd])) {
                                        // 物件詳細URL
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/");
                                        $this->writeURL("/{$estateUrl}/detail-{$estate_id}/map.html");
                                        continue 2;
                                    }
                                }
                            }
                        }
                    }
                    unset($csv);
                }

                $this->closeXML();
                $this->_info->info('サイトマップXML生成処理終了。');

                // robots.txt作成
                if (!$this->createRobotsTxt()) {
                    continue;
                }

                // ファイルアップロード
                if (!isset($args[self::PARAM_NO_UPLOAD]) || $args[self::PARAM_NO_UPLOAD] != 'true') {
                    $this->syncXml();
                }
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (Exception $e) {
            $this->_error->error($e);
        }
    }

    /**
     * company毎の初期化処理
     * @param  App\Models\Company $companyRow
     */
    private function initializeXml($companyRow) {
        $this->companyRow              = $companyRow;
        $this->hpRow                   = null;
        $this->hpEstateSettingRow      = null;
        $this->estateClassSearchRowset = null;
        $this->dest                    = null;

        $this->suffix          = 1;
        $this->currentXMLUrls  = 0;
        $this->currentXMLBytes = 0;

        // 利用可能期間外、診断閲覧のみはskip
        if ($companyRow->contract_type != "0" || !$companyRow->isAvailable()) {
            return false;
        }

        // HPレコードを取得
        $this->hpRow = $companyRow->getCurrentHp();
        if (!$this->hpRow) {
            return false;
        }

        // 出力先ディレクトリの設定
        $this->dest = $this->getDestinationDir($companyRow);
        // ローカルの既存ファイル削除
        foreach (glob($this->dest.DIRECTORY_SEPARATOR.'*.xml') as $file) {
            unlink($file);
        }
        if (file_exists($this->dest.DIRECTORY_SEPARATOR.'robots.txt')) {
            unlink($this->dest.DIRECTORY_SEPARATOR.'robots.txt');
        }

        // 本番用の物件検索設定を取得する
        $this->hpEstateSettingRow = $this->hpRow->getEstateSettingForPublic();
        if (!$this->hpEstateSettingRow) {
            return false;
        }

        // 物件種目毎の設定を全て取得する
        $this->estateClassSearchRowset = $this->hpEstateSettingRow->getSearchSettingAll();
        if (!$this->estateClassSearchRowset->count()) {
            return false;
        }
    }

    private function fetchRobotsTxt() {
        return $this->fetchRobotsTxtHttp();
        // return $this->fetchRobotsTxtFtp();
    }

    private function fetchRobotsTxtHttp() {
        $this->_info->info('robots.txt取得開始。');
        $robotsTxt = @file_get_contents('http://www.' . $this->companyRow->domain . '/robots.txt', false,
            stream_context_create([
                'http' => [
                    'timeout' => 20,
                ],
            ]));
        if ($robotsTxt === false) {
            // エラー時にHTTPステータスコードを表示
            $status = 0;
            if (isset($http_response_header) && count($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/\d+(?:\.\d+)*\ (\d+)/', $header, $matched)) {
                        $status = $matched[1];
                    }
                }
            }

            $this->_info->info('robots.txt取得エラー。status:'.$status.' com_id:'.$this->companyRow->id);
            return false;
        }
        $this->_info->info('robots.txt取得終了。');

        // ログをFTPと合わせる
        $this->_info->info('robots.txt読み込み開始。');
        $this->_info->info('robots.txt読み込み終了。');

        $robotsTxt = preg_split('/\r?\n/', $robotsTxt);
        // Sitemap行を削除
        $robotsTxt = array_filter($robotsTxt, function ($line) {
            return !preg_match(self::SIITEMAP_B_REGEX, $line);
        });
        return trim(implode("\n", $robotsTxt)) . "\n";
    }

    private function fetchRobotsTxtFtp() {
        $this->_info->info('robots.txt取得開始。');

        // robots.txt
        $robotsTxtPath = $this->dest.DIRECTORY_SEPARATOR.'robots.txt';

        $ftp = new Ftp($this->hpRow->id, config('constants.publish_type.TYPE_PUBLIC'));
        try {
            $ftp->login();
            // robots.txtダウンロード
            if (!$ftp->downloadPublic('robots.txt', $robotsTxtPath)) {
                throw new Exception('FTPダウンロード処理に失敗。');
            }
            $ftp->close();
        } catch (Exception $e) {
            $this->_info->info('robots.txt取得エラー。com_id:'.$this->companyRow->id);

            if ($ftp->isLogin) {
                try {
                    $ftp->close();
                } catch (Exception $e) {
                    $this->_error->error($e, true);
                }
            }
            return false;
        }
        $this->_info->info('robots.txt取得終了。');

        $this->_info->info('robots.txt読み込み開始。');
        $robotsTxt = @file_get_contents($robotsTxtPath);
        @unlink($robotsTxtPath);
        if ($robotsTxt === false) {
            $this->_info->info('robots.txtの読み込みに失敗。com_id:'.$this->companyRow->id);
            return false;
        }
        $this->_info->info('robots.txt読み込み終了。');

        $robotsTxt = preg_split('/\r?\n/', $robotsTxt);
        // Sitemap行を削除
        $robotsTxt = array_filter($robotsTxt, function ($line) {
            return !preg_match(self::SIITEMAP_B_REGEX, $line);
        });
        return trim(implode("\n", $robotsTxt)) . "\n";
    }

    private function createRobotsTxt() {
        $this->_info->info('robots.txt作成開始。');
        // robots.txtをリモートから取得
        $robotsTxt = $this->fetchRobotsTxt();
        if ($robotsTxt === false) {
            return false;
        }

        // 作業ディレクトリのXMLファイルを取得
        $files = glob($this->dest.DIRECTORY_SEPARATOR.'*.xml');
        foreach ($files as $file) {
            $basename = basename($file);
            // robots.txtに追記
            $robotsTxt .= 'Sitemap:https://www.'.$this->companyRow->domain.'/'.$basename."\n";
        }

        // 書き出し
        $robotsTxtPath = $this->dest.DIRECTORY_SEPARATOR.'robots.txt';
        file_put_contents($robotsTxtPath, $robotsTxt);

        $this->_info->info('robots.txt作成終了。');
        return true;
    }

    private function syncXml() {

        $this->_info->info('サイトマップXML同期処理開始。');

        // robots.txt
        $robotsTxtPath = $this->dest.DIRECTORY_SEPARATOR.'robots.txt';

        $ftp = new Ftp($this->hpRow->id, config('constants.publish_type.TYPE_PUBLIC'));
        try {
            $ftp->login();
            // リモートのXMLファイルを取得
            $remoteXmls = $ftp->nlistPublic('*.xml');
            // 物件系サイトマップXMLを削除対象リストに追加
            $removeXmls = [];
            if ($remoteXmls) {
                foreach ($remoteXmls as $xmlName) {
                    if (preg_match(self::SIITEMAP_B_REGEX, $xmlName)) {
                        $removeXmls[basename($xmlName)] = true;
                    }
                }
            }

            // XMLファイルのアップロード
            // 作業ディレクトリのXMLファイルを取得
            $files = glob($this->dest.DIRECTORY_SEPARATOR.'*.xml');
            foreach ($files as $file) {
                $basename = basename($file);
                // 削除対象から除外
                unset($removeXmls[$basename]);
                // アップロード
                $ftp->uploadToPublicRecursive($file, $basename);
            }

            // robots.txtのアップロード
            $ftp->uploadToPublicRecursive($robotsTxtPath, basename($robotsTxtPath));

            // リモート不要XMLファイルの削除
            $ftp->deletePublic(array_keys($removeXmls));

            $ftp->close();
        } catch (Exception $e) {
            $this->_info->info('サイトマップXML同期処理エラー。com_id:'.$this->companyRow->id);

            if ($ftp->isLogin) {
                try {
                    $ftp->close();
                } catch (Exception $e) {
                    $this->_error->error($e, true);
                }
            }
            return;
        }
        $this->_info->info('サイトマップXML同期処理終了。');
    }

    /**
     * 検索・結果ページのURLをXMLに書き込む
     * @param  string $baseUrl
     * @param  Library\Custom\Estate\Setting\AreaSearchFilter\Basic $searchFilter
     */
    private function writeSearchUrl($baseUrl, $searchFilter) {
        // 都道府県毎に処理
        foreach ($searchFilter->area_1 as $prefCode) {
            // 都道府県URL
            $prefUrl = $this->prefCodeList->getUrl($prefCode);

            // 都道府県物件一覧
            $this->writeURL("{$baseUrl}/{$prefUrl}/result/");

            // 市区選択
            if ($searchFilter->hasAreaSearchType()) {
                $this->writeURL("{$baseUrl}/{$prefUrl}/");
                // エリアの物件一覧
                if (isset($searchFilter->area_2[$prefCode]) && is_array($searchFilter->area_2[$prefCode])) {
                    // ロケート情報を取得
                    $locateData = $this->getLocateDataByPrefCode($prefCode);
                    // 市区群が所属するロケート情報を初期化
                    $locates = [];

                    foreach ($searchFilter->area_2[$prefCode] as $shikugunCode) {
                        // 市区群情報を取得
                        if (!isset($locateData['shikuguns'][$shikugunCode])) {
                            continue;
                        }
                        $shikugun = $locateData['shikuguns'][$shikugunCode];

                        // 市区群が所属するロケートを記憶
                        if (isset($locateData['locates'][ $shikugun['locate_cd'] ])) {
                            $locates[ $shikugun['locate_cd'] ] = $locateData['locates'][ $shikugun['locate_cd'] ];
                        }

                        $shikugunUrl = $shikugun['roman'];
                        $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$shikugunUrl}-city.html");

                        // 町村検索
                        if ($searchFilter->canChosonSearch()) {
                            // 町名選択画面URL
                            $this->writeURL("{$baseUrl}/{$prefUrl}/{$shikugunUrl}-city/");

                            $chosons = $this->getChosonDataByShikugunCode($shikugunCode);
                            if (
                                !isset($searchFilter->area_5[$prefCode][$shikugunCode]) ||
                                !is_array($searchFilter->area_5[$prefCode][$shikugunCode]) ||
                                !$searchFilter->area_5[$prefCode][$shikugunCode]
                            ) {
                                // 町村設定がない場合はすべての町村検索可
                                foreach ($chosons as $choson) {
                                    $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$shikugunUrl}-{$choson['code']}.html");
                                }
                            } else {
                                // 町村設定がある場合は設定されている町村のみ検索可
                                $allowedChosons = $searchFilter->area_5[$prefCode][$shikugunCode];
                                foreach ($chosons as $choson) {
                                    if (in_array($choson['code'], $allowedChosons)) {
                                        $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$shikugunUrl}-{$choson['code']}.html");
                                    }
                                }
                            }
                        }
                    }

                    // 政令指定都市からの物件一覧
                    foreach ($locates as $locate) {
                        if (!$locate['seirei_fl']) {
                            continue;
                        }
                        $locateUrl = $locate['roman'];
                        $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$locateUrl}-mcity.html");
                    }
                }
            }

            // 沿線選択
            if ($searchFilter->hasLineSearchType()) {
                $this->writeURL("{$baseUrl}/{$prefUrl}/line.html");
                if (isset($searchFilter->area_3[$prefCode]) && is_array($searchFilter->area_3[$prefCode]) && $searchFilter->area_3[$prefCode]) {
                    // 沿線情報を取得
                    $ensenData = $this->getEnsenDataByPrefCode($prefCode);

                    foreach ($searchFilter->area_3[$prefCode] as $lineCode) {
                        // 沿線情報を取得
                        if (!isset($ensenData[$lineCode])) {
                            continue;
                        }
                        $ensen = $ensenData[$lineCode];

                        $lineUrl = $ensen['roman'];
                        // 駅選択
                        $this->writeURL("{$baseUrl}/{$prefUrl}/{$lineUrl}-line/");
                        // 沿線の物件一覧
                        $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$lineUrl}-line.html");
                    }

                    // 駅からの物件一覧
                    if (isset($searchFilter->area_4[$prefCode]) && is_array($searchFilter->area_4[$prefCode])) {
                        $ekiData = $this->getEkiDataByPrefCode($prefCode, $searchFilter->area_3[$prefCode]);
                        foreach ($searchFilter->area_4[$prefCode] as $stationCode) {
                            // 駅情報を取得
                            if (!isset($ekiData[$stationCode])) {
                                continue;
                            }
                            $eki = $ekiData[$stationCode];

                            $stationUrl = $eki['roman'];
                            $this->writeURL("{$baseUrl}/{$prefUrl}/result/{$stationUrl}-eki.html");
                        }
                    }
                }
            }

        }
    }

    /**
     * 町村コード一覧に検索対象が含まれているかチェックする
     * @param $choson_cd 検索対象
     * @param $chosonCodes 町村コード一覧
     * @return bool
     */
    private function isChosonIncluded($choson_cd, $chosonCodes) {
        foreach ($chosonCodes as $code) {
            if (strpos($choson_cd, $code) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param  string $path 公開領域からの絶対パス
     */
    private function writeURL($path) {
        $priority = 1;
        $dirs = count(explode('/', $path)) - 2;
        $priority -= (0.1 * $dirs);
        $url = 'https://www.'.$this->companyRow->domain.$path;

        $data  = '<url>'."\n";
        $data .= '<loc>'.$url.'</loc>'."\n";
        $data .= '<changefreq>daily</changefreq>'."\n";
        $data .= sprintf('<priority>%.1f</priority>', $priority)."\n";
        $data .= '<xhtml:link rel="alternate" media="only screen and (max-width: 640px)" href="'.$url.'" /></url>' . "\n";
        $data .= "\n";

        $bytes = strlen($data);

        // 上限を超える場合は新しいXMLに書き出し
        if (
            $this->currentXMLUrls + 1 > self::XML_MAX_URLS ||
            $this->currentXMLBytes + $bytes > self::XML_MAX_BYTES
        ) {
            $this->closeXML();
            $this->openXML();
        }

        fwrite($this->fp, $data);

        $this->currentXMLUrls++;
        $this->currentXMLBytes += $bytes;
    }

    private function openXML() {
        $suffix = $this->suffix++;
        $filename = $this->dest . DIRECTORY_SEPARATOR . 'sitemap_b';
        if ($suffix > 1) {
            $filename .= "_{$suffix}";
        }
        $filename .= '.xml';

        $this->fp = fopen($filename, 'w');
        if ($this->fp === false) {
            throw new Exception('サイトマップXML出力ファイルのオープンに失敗しました。('.$filename.')');
        }

        $data = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">


EOD;

        $this->currentXMLUrls = 0;
        $this->currentXMLBytes = strlen($data);

        // 閉じタグ分を追加
        $close = <<<EOD
</urlset>
EOD;
        $this->currentXMLBytes += strlen($close);

        $this->writeXML($data);
    }

    private function closeXML() {
        $data = <<<EOD
</urlset>
EOD;
        $this->writeXML($data);
        if (false === fclose($this->fp)) {
            throw new Exception('サイトマップXML出力ファイルのクローズに失敗しました。');
        }
    }

    private function writeXML($data) {
        if (false === fwrite($this->fp, $data)) {
            throw new Exception('サイトマップXMLデータの書き込みに失敗しました。');
        }
    }

    private function getDestinationDir($companyRow) {
        $dir = $this->destination_dir . DIRECTORY_SEPARATOR . $companyRow->id;
        if (!file_exists($dir)) {
            // ディレクトリ作成
            if (!mkdir($dir, 0777, true)) {
                throw new Exception('サイトマップXML出力ディレクトリの作成に失敗しました。(' . $dir . ')');
            }
        }
        return $dir;
    }

    private function getLocateDataByPrefCode($prefCode) {
        if (!isset($this->shikugunCache[$prefCode])) {
            $params = [
                'media'=>'pc',
                'grouping'=>'locate_cd',
                'ken_cd'=>$prefCode
            ];
            $response = $this->bapiClient->get('/shikugun/list.json', $params);
            $response->ifFailedThenThrowException();
            $data = [
                'shikuguns' => [],
                'locates' => [],
            ];
            if (isset($response->data['shikuguns']) && is_array($response->data['shikuguns'])) {
                foreach ($response->data['shikuguns'] as $prefData) {
                    if ($prefData['ken_cd'] != $prefCode) {
                        continue;
                    }
                    foreach ($prefData['locate_groups'] as $locate) {
                        $data['locates'][$locate['locate_cd']] = [
                            // 必要なデータだけコピー
                            'roman'     => $locate['locate_roman'],
                            'seirei_fl' => $locate['seirei_fl'],
                        ];
                        foreach ($locate['shikuguns'] as $shikugun) {
                            $data['shikuguns'][$shikugun['code']] = [
                                // 必要なデータだけコピー
                                'locate_cd' => $locate['locate_cd'],
                                'roman' => $shikugun['shikugun_roman']
                            ];
                        }
                    }
                }
            }
            $this->shikugunCache[$prefCode] = $data;
        }
        return $this->shikugunCache[$prefCode];
    }

    /**
     * @param $shikugunCode
     * @return mixed
     */
    private function getChosonDataByShikugunCode($shikugunCode) {
        if (!isset($this->chosonCache[$shikugunCode])) {
            $params = [
                'shikugun_cd'=>$shikugunCode,
                'oaza_fl'=>1,
            ];
            $response = $this->bapiClient->get('/choson/list.json', $params);
            $response->ifFailedThenThrowException();
            $data = [];
            if (isset($response->data['shikuguns']) && is_array($response->data['shikuguns'])) {
                foreach ($response->data['shikuguns'][0]['chosons'] as $choson) {
                    // 必要なコードだけコピー
                    $data[] = [
                        'code' => $choson['code']
                    ];
                }
            }
            $this->chosonCache[$shikugunCode] = $data;
        }
        return $this->chosonCache[$shikugunCode];
    }

    private function getEnsenDataByPrefCode($prefCode) {
        if (!isset($this->ensenCache[$prefCode])) {
            $params = [
                'media'=>'pc',
                'ken_cd'=>$prefCode
            ];
            $response = $this->bapiClient->get('/ensen/list.json', $params);
            $response->ifFailedThenThrowException();
            $data = [];
            if (isset($response->data['ensens']) && is_array($response->data['ensens'])) {
                foreach ($response->data['ensens'] as $prefData) {
                    if ($prefData['ken_cd'] != $prefCode) {
                        continue;
                    }
                    foreach ($prefData['ensens'] as $ensen) {
                        $data[$ensen['code']] = [
                            // 必要なデータだけコピー
                            'roman' => $ensen['ensen_roman'],
                        ];
                    }
                }
            }
            $this->ensenCache[$prefCode] = $data;
        }
        return $this->ensenCache[$prefCode];
    }

    private function getEkiDataByPrefCode($prefCode, $ensenCodes) {
        if (!isset($this->ekiCache[$prefCode])) {
            $params = [
                'media'=>'pc',
                'ken_cd'=>$prefCode,
                'ensen_cd'=>implode(',', array_keys($this->getEnsenDataByPrefCode($prefCode))),
            ];
            $response = $this->bapiClient->get('/eki/list.json', $params);
            $response->ifFailedThenThrowException();
            $data = [];
            if (isset($response->data['ensens']) && is_array($response->data['ensens'])) {
                foreach ($response->data['ensens'] as $ensen) {
                    foreach ($ensen['ekis'] as $eki) {
                        $data[$eki['code']] = [
                            // 必要なデータだけコピー
                            'roman' => $eki['eki_roman'],
                        ];
                    }
                }
            }
            $this->ekiCache[$prefCode] = $data;
        }
        return $this->ekiCache[$prefCode];
    }
}
// php artisan command:batch-sitemap-xml development app SitemapXml >> /var/www/html/storage/logs/SitemapXml.log 2>&1