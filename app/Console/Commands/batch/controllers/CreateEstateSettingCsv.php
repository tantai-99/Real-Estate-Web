<?php
/**
 *物件設定情報をCSVにして吐き出す
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Model\Estate;

class CreateEstateSettingCsv extends Command
{
    private $fileName;
    private $header_name;
    private $stream;
    private $prefMaster;
    private $searchTypeMaster;
    private $searchClassMaster;
    private $secondEnabledMaster;
    private $secondSearchTypeMaster;
    private $_config;
    private $data_key_for_bukken;
    private $data_key_for_second;
    private $data_key_for_second_chintai;
    private $data_key_for_second_kashitenpo;
    private $data_key_for_second_kashijimusyo;
    private $data_key_for_second_kashityusyajo;
    private $data_key_for_second_kashitochi;
    private $data_key_for_second_kashisonota;
    private $data_key_for_second_urimansion;
    private $data_key_for_second_urikodate;
    private $data_key_for_second_uritochi;
    private $data_key_for_second_uritenpo;
    private $data_key_for_second_urijimusyo;
    private $data_key_for_second_urisonota;


    // 出力ファイル名をセット
    private function setFileName()
    {
        $this->fileName = 'HPAD_ESTATE_SETTING.CSV';
    }


    public function __construct()
    {
        parent::__construct();

        // コンフィグ取得
        // $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/v1api/configs/api.ini', APPLICATION_ENV);

        //マスタ取得
        $this->searchTypeMaster = Estate\SearchTypeList::getInstance()->getAll();
        $this->searchClassMaster = Estate\ClassList::getInstance()->getKeyConst();
        $this->secondEnabledMaster = Estate\SecondEstateEnabledList::getInstance()->getAll();
        $this->secondSearchTypeMaster = Estate\SecondSearchTypeList::getInstance()->getAll();

    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-estate-setting-csv {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create estate setting csv';

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

            //csvにファイル名、カラム名を書き込む
            $this->initCsv();

            $this->_info->info('/////////////// START ///////////////');

            //全会員を取得する
            $companyRows = $this->getAllCompany();

            //CSV登録情報
            $this->setBukkenDataKey();

            //二次広告自動公開CSV登録情報
            $this->setSecondDataKey();

            //二次広告自動公開絞り込み設定のためのCSV登録情報
            $this->setSecondDataKeyForCond();


            //全会員でループ処理
            foreach ($companyRows as $companyRow) {

                $this->debugLog('memberNo: '.(string)$companyRow->member_no);

                //会員のホームページ情報を取得
                $hp = $companyRow->getCurrentHp();
                $secondEstate = $companyRow->getSecondEstate();
                if (!$hp) {
                    $this->debugLog('  skip');
                    continue;
                }

                //全ての物件設定を取得
                $setting = $hp->getEstateSetting();

                //公開中の物件設定を取得
                $setting_for_pub = $hp->getEstateSettingForPublic();

                //二次広告自動公開設定を取得
                $second_estate_setting = $hp->getSecondSearchSettingRow();

                $this->debugLog('  bukken-setting-start');

                //物件検索設定の取得
                $bukken_data = $this->getBukkenSearchSetting($setting_for_pub,$this->data_key_for_bukken);

                $this->debugLog('  bukken-setting-stop');

                $this->debugLog('  2nd-setting-start');

                //2次広告自動公開設定の取得
                $second_estate_data = $this->getSecondEstateSetting($hp,$this->data_key_for_second);

                $this->debugLog('  2nd-setting-end');

                $this->debugLog('  special-setting-start');

                //物件特集の作成/更新情報の取得
                $tokusyu_data = array();
                if ($this->getSpecial($setting,$setting_for_pub)) {
                    $tokusyu_data = $this->getSpecial($setting,$setting_for_pub);
                }
                $this->debugLog('  special-setting-end');

                $this->debugLog('  csv-output-start');

                //初期化
                $bukken_csv_row = array();
                $second_csv_row = array();
                $second_cond_csv_row = array();

                //クラスごとに設定情報を取得
                foreach ($this->searchClassMaster as $class) {
                    //整形済み物件検索設定の取得
                    $bukken_csv_row = array_merge($bukken_csv_row,$this->createCsv($bukken_data,$this->data_key_for_bukken,$class));

                    //整形済み二次広告自動公開設定の取得
                    $second_csv_row = array_merge($second_csv_row,$this->createCsv($second_estate_data,$this->data_key_for_second,$class));

                    //整形済み二次広告の絞り込み条件の取得
                    $second_cond_csv_row = array_merge($second_cond_csv_row,$this->createCsvForSecondCond($second_estate_data, $class));
                }

                $memberNo = [(string)$companyRow->member_no];
                $csv_row = array_merge($memberNo,$bukken_csv_row, $second_csv_row,$second_cond_csv_row,$tokusyu_data);

                //csvへの書き出し
                $this->encfputscv($csv_row, ',', '"');
                $this->debugLog('  csv-output-end');
            }
            fclose($this->stream);

            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    //csvにファイル名、カラム名を書き込む
    private function initCsv()
    {
        $this->setFileName();
        $this->setHeaderName();
        $this->stream = fopen($this->_path_data .$this->fileName, 'w');
        $csv_row_name = array();
        foreach($this->header_name as $name) {
            mb_convert_variables('SJIS-win', 'UTF-8', $name);
            $csv_row_name[] = $name;
        }
        $this->encfputscv($csv_row_name, ',', '"');
    }

    //csvデータを作成
    private function createCsv($data, $data_key, $class)
    {
        $csv_row = array();
        foreach($data_key as $name) {
            if (!array_key_exists($name,$data)) {
                continue;
            }
            if (!array_key_exists($class,$data[$name])) {
                $csv_row[] = '';
                continue;
            }
            $string = (string)$data[$name][$class];
            mb_convert_variables('SJIS-win', 'UTF-8', $string);
            $csv_row[] = (string)$string;
        }
        return $csv_row;
    }

    //二次広告の絞り込み条件用csvデータを作成
    private function createCsvForSecondCond($second_estate_data,$class)
    {
        $csvCond = array();
        $cond = $second_estate_data['cond'];
        if (isset($cond)) {

            switch ($class) {
                case '1':
                    $csvCond = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_chintai, $class);
                    break;

                case '2':
                    $cond_kashitenpo    = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_kashitenpo, $class);
                    $cond_kashijimusyo  = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_kashijimusyo, $class);
                    $cond_kashityusyajo = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_kashityusyajo, $class);
                    $cond_kashitochi    = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_kashitochi, $class);
                    $cond_kashisonota   = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_kashisonota, $class);
                    $csvCond = array_merge($cond_kashitenpo,$cond_kashijimusyo,$cond_kashityusyajo,$cond_kashitochi, $cond_kashisonota);
                    break;

                case '3':
                    $cond_urimansion = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_urimansion, $class);
                    $cond_urikodate  = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_urikodate, $class);
                    $cond_uritochi   = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_uritochi, $class);
                    $csvCond = array_merge($cond_urimansion,$cond_urikodate,$cond_uritochi);
                    break;

                case '4':
                    $cond_uritenpo    = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_uritenpo, $class);
                    $cond_urijimusyo  = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_urijimusyo, $class);
                    $cond_urisonota   = $this->createCsvDataForSecondCond($cond, $this->data_key_for_second_urisonota, $class);
                    $csvCond = array_merge($cond_uritenpo,$cond_urijimusyo,$cond_urisonota);
                    break;
            }
        }
        return $csvCond;
    }

    //二次広告の絞り込み条件用csvデータをキーごとに作成
    private function createCsvDataForSecondCond($data,$data_key,$class)
    {
        $csv_row = array();
        foreach($data_key as $name) {
            //Undefined index 防止
            if (!array_key_exists($name,$data)) {
                continue;
            }
            //Undefined index 防止
            if (!array_key_exists($class,$data[$name])) {
                $csv_row[] = '';
                continue;
            }
            $dataKey = $data_key['estate_type'];
            if (!array_key_exists($dataKey, $data[$name][$class])) {
                $csv_row[] = '';
                continue;
            }

            $string = (string)$data[$name][$class][$dataKey];

            mb_convert_variables('SJIS-win', 'UTF-8', $string);
            $csv_row[] = (string)$string;
        }
        return $csv_row;
    }

    //物件特集の作成/更新
    private function getSpecial($setting,$setting_for_pub)
    {
        $result = array();
        //$setting_for_pubいらなかも
        if (is_object($setting) && is_object($setting_for_pub)) {
            //特集数
            $result['total'] = $setting->getSpecialAllWithPubStatus()->getFoundRows();
            $result['total_pub'] = $setting_for_pub->getSpecialAllWithPubStatus()->getFoundRows();
            $result['total_draft'] = $result['total']-$result['total_pub'];

            //物件設定ごとの特集数
            $result['chintai']       = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','1')->getFoundRows();
            $result['kashitenpo']    = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','2')->getFoundRows();
            $result['kashijimuyso']  = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','3')->getFoundRows();
            $result['kasityuusayjo'] = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','4')->getFoundRows();
            $result['kasitochi']     = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','5')->getFoundRows();
            $result['kashi_sonota']  = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','6')->getFoundRows();
            $result['mansyon']       = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','7')->getFoundRows();
            $result['ikkodate']      = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','8')->getFoundRows();
            $result['uritenpo']      = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','9')->getFoundRows();
            $result['urijimusyo']    = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','10')->getFoundRows();
            $result['uri_sonota']    = $setting->getSpecialAllWithPubStatusByCond('enabled_estate_type','11')->getFoundRows();

            //公開する物件設定毎の特集数
            $result['er_enabled']            = $setting->getSpecialAllWithPubStatusByCond('only_er_enabled','1')->getFoundRows();
            $result['second_estate_enabled'] = $setting->getSpecialAllWithPubStatusByCond('second_estate_enabled','1')->getFoundRows();
            $result['end_muke_enabled']      = $setting->getSpecialAllWithPubStatusByCond('end_muke_enabled','1')->getFoundRows();

            $only_second    = $setting->getSpecialAllWithPubStatusByCond('only_second','1')->getFoundRows();
            $exclude_second = $setting->getSpecialAllWithPubStatusByCond('exclude_second','1')->getFoundRows();
            $result['second_unset']   = $result['total'] - ($only_second + $exclude_second);
            $result['only_second']    = $only_second;
            $result['exclude_second'] = $exclude_second;
            return $result;
        }else{
        }

    }

    //物件検索設定の取得
    private function getBukkenSearchSetting($setting,$data_key)
    {
        $csv = array();

        //物件検索設定でループ
        foreach ($this->searchClassMaster as $class) {

            //物件検索設定が存在する場合
            if (is_object($setting)) {
                $searchSettingRow = $setting->getSearchSetting($class);

                //物件検索設定をしていない項目がある場合
                if (!$searchSettingRow) {
                    $csv = array_merge($csv,$this->pushDefaultValueForBukken($csv,$class,$data_key));
                    continue;
                }
                $searchSetting = $searchSettingRow->toSettingObject();
                $estateTypeMaster = Estate\TypeList::getInstance()->getByClass($class);

                //地域から探す、沿線、駅から探す
                $tmp ='';
                foreach ($searchSetting->area_search_filter->search_type as $value) {
                    if (isset($this->searchTypeMaster[$value])) {
                        $tmp .= $this->searchTypeMaster[$value];
                        if($value !== end($searchSetting->area_search_filter->search_type)){
                            $tmp .= ',';
                        }
                    }
                }
                $csv['search_type'][$class] = $tmp;

                //賃貸（アパート、マンション一戸建て）等
                $tmp = '';
                foreach ($searchSetting->enabled_estate_type as $estate_type) {
                    if (isset($estateTypeMaster[$estate_type])) {
                        $tmp .= $estateTypeMaster[$estate_type];
                        if ($estate_type !== end($searchSetting->enabled_estate_type)) {
                            $tmp .= ',';
                        }
                    }
                }
                $csv['estate_type'][$class] = $tmp;

                //都道府県
                $tmp ='';
                foreach ($searchSetting->area_search_filter->area_1 as $key => $value) {
                    $tmp .= $value;
                    if($value !== end($searchSetting->area_search_filter->area_1)){
                        $tmp .=',';
                    }
                }
                $csv['pref'][$class] = $tmp;

                //取り扱いエリア
                //初期化
                $csv['shikugun'][$class] ='';
                $shikugun_data = array_keys((array)$searchSetting->area_search_filter->area_2);
                //市区郡を対象にしているか
                if (!empty($shikugun_data)) {
                    foreach ($searchSetting->area_search_filter->area_2 as $key => $pref_cd) {
                        //初期化
                        $tmp ='';
                        $csv['shikugun'][$class] .= $key . '/';

                        foreach ($pref_cd as $shikugun_cd) {
                            $tmp .= $shikugun_cd;
                            if ($shikugun_cd !== end($pref_cd)) {
                                $tmp .= '・';
                            }
                        }
                        $csv['shikugun'][$class] .= $tmp;
                        if ($pref_cd !== end($searchSetting->area_search_filter->area_2)) {
                            $csv['shikugun'][$class] .= ',';
                        }
                    }
                }

                //沿線・駅
                //初期化
                $csv['ensen_eki'][$class] ='';
                if (isset($searchSetting->area_search_filter->area_3)) {
                    foreach ($searchSetting->area_search_filter->area_3 as $value) {

                        //沿線に対してループ
                        foreach ($value as $ensen_cd) {

                            $tmp ='';

                            //沿線名
                            $csv['ensen_eki'][$class] .= $ensen_cd.'/';

                            foreach ($searchSetting->area_search_filter->area_4 as $ensen_eki) {

                                //沿線が持つ駅に対してループ
                                foreach ($ensen_eki as $ensen_eki_cd) {
                                    $tmp .= $ensen_eki_cd;
                                    if ($ensen_eki_cd !== end($ensen_eki)) {
                                        $tmp .= '・';
                                    }
                                }
                            }
                        }
                        //ループ内最後の沿線の場合カンマを付けない
                        $csv['ensen_eki'][$class] .= $tmp;
                        if ($value !== end($searchSetting->area_search_filter->area_3)) {
                            $csv['ensen_eki'][$class] .= ',';
                        }
                    }
                }
            }else{
                //物件検索設定が存在しない場合
                $csv = array_merge($csv,$this->pushDefaultValueForBukken($csv,$class,$data_key));
            }
        }
        return $csv;
    }

    //二次広告自動公開設定の取得
    private function getSecondEstateSetting($hp,$data_key_for_second)
    {
        $second_csv = array();
        $cond_data = array();
        $second_csv['cond'] = array();

        //二次広告自動公開が設定してある場合
        //二次広告自動公開設定でループ
        foreach ($this->searchClassMaster as $class) {
            $searchSettingRow = $hp->getSecondSearchSetting($class);

            //二次広告自動公開設定をしていない項目がある場合
            if (!$searchSettingRow) {
                //二次広告の欄に空白を挿入
                $second_csv = array_merge($second_csv,$this->pushDefaultValueForSecond($second_csv,$class,$data_key_for_second));
                $second_csv['cond'] = array_merge_recursive($second_csv['cond'],$this->pushBlankForSecondCondPerClass($cond_data,$class));
                continue;
            }
            $searchSetting = $searchSettingRow->toSettingObject();

            //賃貸(アパート・マンション・一戸建て) 等のマスタ
            $estateTypeMaster = Estate\TypeList::getInstance()->getByClass($class);

            //対象(市区郡/沿線・駅)
            if (isset($this->secondSearchTypeMaster[$searchSetting->area_search_filter->search_type])) {
                $second_csv['second_estate_search_type'][$class] = $this->secondSearchTypeMaster[$searchSetting->area_search_filter->search_type];
            }

            //設定状況
            if (isset($this->secondEnabledMaster[$searchSetting->enabled])) {
                $second_csv['second_estate_enabled'][$class] = $this->secondEnabledMaster[$searchSetting->enabled];
            }

            //物件種目(賃貸（アパート、マンション一戸建て）等)
            $tmp = '';
            foreach ($searchSetting->enabled_estate_type as $estate_type) {
                if (isset($estateTypeMaster[$estate_type])) {
                    $tmp .= $estateTypeMaster[$estate_type];
                    if ($estate_type !== end($searchSetting->enabled_estate_type)) {
                        $tmp .= ',';
                    }
                }
            }
            $second_csv['second_estate_type'][$class] = $tmp;

            //都道府県
            $tmp ='';
            foreach ($searchSetting->area_search_filter->area_1 as $value) {
                $tmp .= $value;
                if($value !== end($searchSetting->area_search_filter->area_1)){
                    $tmp .= ',';
                }
            }
            $second_csv['second_estate_pref'][$class] = $tmp;

            //取り扱いエリア
            //初期化
            $second_csv['second_estate_shikugun'][$class] ='';
            $shikugun_data = array_keys((array)$searchSetting->area_search_filter->area_2);

            //市区郡を対象にしているか
            if (!empty($shikugun_data)) {
                foreach ($searchSetting->area_search_filter->area_2 as $key => $pref_cd) {
                    //初期化
                    $tmp ='';
                    $second_csv['second_estate_shikugun'][$class] .= $key . '/';
                    foreach ($pref_cd as $shikugun_cd) {
                        $tmp .= $shikugun_cd;
                        if ($shikugun_cd !== end($pref_cd)) {
                            $tmp .= '・';
                        }
                    }
                    $second_csv['second_estate_shikugun'][$class] .= $tmp;
                    if ($pref_cd !== end($searchSetting->area_search_filter->area_2)) {
                        $second_csv['second_estate_shikugun'][$class] .= ',';
                    }
                }
            }

            //沿線・駅
            //初期化
            $second_csv['second_estate_ensen_eki'][$class] ='';
            if (isset($searchSetting->area_search_filter->area_3)) {
                foreach ($searchSetting->area_search_filter->area_3 as $value) {

                    //沿線に対してループ
                    foreach ($value as $ensen_cd) {
                        //初期化
                        $tmp ='';
                        //沿線・駅データ取得
                        //沿線名
                        $second_csv['second_estate_ensen_eki'][$class] .= $ensen_cd.'/';
                        foreach ($searchSetting->area_search_filter->area_4 as $ensen_eki) {
                            //沿線が持つ駅に対してループ
                            foreach ($ensen_eki as $ensen_eki_cd) {
                                $tmp .= $ensen_eki_cd;
                                if ($ensen_eki_cd !== end($ensen_eki)) {
                                    $tmp .= '・';
                                }
                            }
                        }
                    }
                    //ループ内最後の沿線の場合カンマを付けない
                    if ($value === end($searchSetting->area_search_filter->area_3)) {
                        $second_csv['second_estate_ensen_eki'][$class] .= $tmp;
                    }else{
                        $second_csv['second_estate_ensen_eki'][$class] .= $tmp .',';
                    }
                }
            }

            //絞込み条件
            //初期化(初期値を先に入れておく)
            $cond_data =  $this->pushDefaultValueForSecondCondPerClass($cond_data,$class);

            if (isset($searchSetting->search_filter->estate_types)) {
                foreach ($searchSetting->search_filter->estate_types as $estate_data) {
                    $estate_type = $estate_data['estate_type'];
                    foreach ($estate_data['categories'] as $data){

                        $category_id = $data->category_id;
                        foreach ($data->items as $item) {
                            //リスト作成処理
                            $array_item =(array)$item;
                            //getter作成しないとな・
                            $option = (array)$array_item["\0*\0_options"];
                            //getter作成しないとな・
                            if ($option) {
                                foreach ($option["\0*\0_list"] as $i => $list) {
                                    $condition_list[$i] = $list;
                                }
                            }
                            //リストにマッチする値を格納
                            if (is_array($item->item_value)) {
                                $cond_data[$category_id][$class][$estate_type] = '';
                                //間取りの場合
                                foreach ($item->item_value as $madori_value) {
                                    //ループ内最後の場合カンマを付けない
                                    if ($madori_value === end($item->item_value)) {
                                        $cond_data[$category_id][$class][$estate_type] .= $condition_list[$madori_value];
                                    }else{
                                        $cond_data[$category_id][$class][$estate_type] .= $condition_list[$madori_value].',';
                                    }
                                }
                            }elseif($category_id == 'kakaku' || $category_id == 'tatemono_ms' || $category_id == 'tochi_ms') {
                                //価格、面積の場合
                                $cond_data[$category_id.'_'.$item->item_id][$class][$estate_type] = $condition_list[$item->item_value];
                            }elseif($category_id == 'chikunensu' || $category_id == 'saiteki_yoto_cd'){
                                //築年数,最適用途の場合
                                $cond_data[$category_id][$class][$estate_type] = $condition_list[$item->item_value];
                            }else{
                                //その他（1か0で選択するタイプ）
                                $cond_data[$category_id][$class][$estate_type] = $item->item_value;
                            }
                        }
                    }
                }
            }
            //検索条件は別名で配列に保存(上書き)
            $second_csv['cond'] = $cond_data;
        }
        return $second_csv;
    }

    //物件検索未設定の項目に空白を入れる
    private function pushDefaultValueForBukken($csv,$class='',$data_key)
    {
        if (!$class) {
            foreach ($data_key as $key) {
                $csv[$key] = '';
            }
            return $csv;
        }
        foreach ($data_key as $key) {
            $csv[$key][$class] = '';
        }
        return $csv;
    }

    //二次広告自動公開未設定の項目に空白を入れる
    private function pushDefaultValueForSecond($csv,$class='',$data_key_for_second)
    {
        foreach ($data_key_for_second as $key) {
            if ($key === 'second_estate_enabled') {
                $csv[$key][$class] = '未設定';
            }else {
                $csv[$key][$class] = '';
            }
        }
        return $csv;
    }

    private function pushBlankForSecondCondPerClass($csv,$class='')
    {
        if ($class == '1') {
            foreach ($this->data_key_for_second_chintai as $key) {
                if ($key !== $this->data_key_for_second_chintai['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_chintai['estate_type']] = '';
                }
            }
        }
        if($class == '2') {
            foreach ($this->data_key_for_second_kashitenpo as $key) {
                if ($key !== $this->data_key_for_second_kashitenpo['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_kashitenpo['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_kashijimusyo as $key) {
                if ($key !== $this->data_key_for_second_kashijimusyo['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_kashijimusyo['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_kashityusyajo as $key) {
                if ($key !== $this->data_key_for_second_kashityusyajo['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_kashityusyajo['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_kashitochi as $key) {
                if ($key !== $this->data_key_for_second_kashitochi['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_kashitochi['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_kashisonota as $key) {
                if ($key !== $this->data_key_for_second_kashisonota['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_kashisonota['estate_type']] = '';
                }
            }
        }
        if($class == '3') {
            foreach ($this->data_key_for_second_urimansion as $key) {
                if ($key !== $this->data_key_for_second_urimansion['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_urimansion['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_urikodate as $key) {
                if ($key !== $this->data_key_for_second_urikodate['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_urikodate['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_uritochi as $key) {
                if ($key !== $this->data_key_for_second_uritochi['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_uritochi['estate_type']] = '';
                }
            }
        }
        if ($class = '4') {
            foreach ($this->data_key_for_second_uritenpo as $key) {
                if ($key !== $this->data_key_for_second_uritenpo['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_uritenpo['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_urijimusyo as $key) {
                if ($key !== $this->data_key_for_second_urijimusyo['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_urijimusyo['estate_type']] = '';
                }
            }
            foreach ($this->data_key_for_second_urisonota as $key) {
                if ($key !== $this->data_key_for_second_urisonota['estate_type']) {
                    $csv[$key][$class][$this->data_key_for_second_urisonota['estate_type']] = '';
                }
            }
        }

        return $csv;
    }

    private function pushDefaultValueForSecondCondPerClass($cond_data,$class)
    {
        if ($class == 1) {
            $first = $this->data_key_for_second_chintai['estate_type'];
            $last = $this->data_key_for_second_chintai['estate_type'];
            for ($count = $first; $count <= $last; $count++){
                $cond_data = $this->pushDefaultValueForSecondCond($cond_data,$class,$count);
            }
        }elseif ($class == 2) {
            $first = $this->data_key_for_second_kashitenpo['estate_type'];
            $last = $this->data_key_for_second_kashisonota['estate_type'];
            for ($count = $first; $count <= $last; $count++){
                $cond_data = $this->pushDefaultValueForSecondCond($cond_data,$class,$count);
            }
        }elseif ($class == 3) {
            $first = $this->data_key_for_second_urimansion['estate_type'];
            $last = $this->data_key_for_second_uritochi['estate_type'];
            for ($count = $first; $count <= $last; $count++){
                $cond_data = $this->pushDefaultValueForSecondCond($cond_data,$class,$count);
            }
        }elseif ($class == 4) {
            $first = $this->data_key_for_second_uritenpo['estate_type'];
            $last = $this->data_key_for_second_urisonota['estate_type'];
            for ($count = $first; $count <= $last; $count++){
                $cond_data = $this->pushDefaultValueForSecondCond($cond_data,$class,$count);
            }
        }
        return $cond_data;
    }


    //二次広告自動公開設定、絞込条件が未設定の項目に初期値を入れる
    private function pushDefaultValueForSecondCond($cond_data,$class,$estate_type)
    {
        //価格が未設定の場合
        if (!array_key_exists('kakaku_1',$cond_data) || !isset($cond_data['kakaku_1'][$class][$estate_type])) {
            $cond_data['kakaku_1'][$class][$estate_type] = '下限なし';
        }
        if (!array_key_exists('kakaku_2',$cond_data) || !isset($cond_data['kakaku_2'][$class][$estate_type])) {
            $cond_data['kakaku_2'][$class][$estate_type] = '上限なし';
        }
        //間取りが未設定の場合
        if (!array_key_exists('madori',$cond_data) || !isset($cond_data['madori'][$class][$estate_type])) {
            $cond_data['madori'][$class][$estate_type] = '未設定';
        }
        //建物面積が未設定の場合
        if (!array_key_exists('tatemono_ms_1',$cond_data) || !isset($cond_data['tatemono_ms_1'][$class][$estate_type])) {
            $cond_data['tatemono_ms_1'][$class][$estate_type] = '下限なし';
        }
        if (!array_key_exists('tatemono_ms_2',$cond_data) || !isset($cond_data['tatemono_ms_2'][$class][$estate_type])) {
            $cond_data['tatemono_ms_2'][$class][$estate_type] = '上限なし';
        }
        //土地面積が未設定の場合
        if (!array_key_exists('tochi_ms_1',$cond_data) || !isset($cond_data['tochi_ms_1'][$class][$estate_type])) {
            $cond_data['tochi_ms_1'][$class][$estate_type] = '下限なし';
        }
        if (!array_key_exists('tochi_ms_2',$cond_data) || !isset($cond_data['tochi_ms_2'][$class][$estate_type])) {
            $cond_data['tochi_ms_2'][$class][$estate_type] = '上限なし';
        }
        //築年数が未設定の場合
        if (!array_key_exists('chikunensu',$cond_data)|| !isset($cond_data['chikunensu'][$class][$estate_type])) {

            $cond_data['chikunensu'][$class][$estate_type] = '指定なし';
        }
        //最適用途が未設定の場合
        if (!array_key_exists('saiteki_yoto_cd',$cond_data)|| !isset($cond_data['saiteki_yoto_cd'][$class][$estate_type])) {

            $cond_data['saiteki_yoto_cd'][$class][$estate_type] = '指定なし';
        }
        //画像が未設定の場合
        if (!array_key_exists('image',$cond_data) || !isset($cond_data['image'][$class][$estate_type])) {

            $cond_data['image'][$class][$estate_type] = '0';
        }
        //広告費が未設定の場合
        if (!array_key_exists('koukokuhi',$cond_data)|| !isset($cond_data['koukokuhi'][$class][$estate_type])) {

            $cond_data['koukokuhi'][$class][$estate_type] = '0';
        }
        //手数料が未設定の場合
        if (!array_key_exists('tesuryo',$cond_data) || !isset($cond_data['tesuryo'][$class][$estate_type])) {

            $cond_data['tesuryo'][$class][$estate_type] = '0';
        }
        return $cond_data;
    }


    private function encfputscv($row, $delimiter = ',', $enclosure = '"', $eol = "\n"){
        $tmp = array();
        foreach($row as $v){
            $v = str_replace('"', '""', $v);
            $tmp[]= $enclosure.$v.$enclosure;
        }
        $str = implode($delimiter, $tmp).$eol;
        return fwrite($this->stream, $str);
    }

    //全会員を取得する
    private function getAllCompany()
    {
        $companyObj = App::make(CompanyRepositoryInterface::class);
        // $select->where('delete_flg = 0');
        // $select->where('contract_type', config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'));
        $where = array(
            ['delete_flg', 0],
            ['contract_type', config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')]
        );
        return $companyObj->fetchAll($where);
    }

    //CSVヘッダー情報をセット
    private function setHeaderName()
    {
        $this->header_name = array(
            '会員No',
            '物件/居賃/探し方',
            '物件/居賃/物件種目',
            '物件/居賃/都道府県',
            '物件/居賃/取り扱いエリア',
            '物件/居賃/取り扱い沿線・駅',
            '物件/事賃/探し方',
            '物件/事賃/物件種目',
            '物件/事賃/都道府県',
            '物件/事賃/取り扱いエリア',
            '物件/事賃/取り扱い沿線・駅',
            '物件/居売/探し方',
            '物件/居売/物件種目',
            '物件/居売/都道府県',
            '物件/居売/取り扱いエリア',
            '物件/居売/取り扱い沿線・駅',
            '物件/事売/探し方',
            '物件/事売/物件種目',
            '物件/事売/都道府県',
            '物件/事売/取り扱いエリア',
            '物件/事売/取り扱い沿線・駅',
            '2次/居賃/設定状況',
            '2次/居賃/都道府県',
            '2次/居賃/物件種目',
            '2次/居賃/市区郡/沿線・駅',
            '2次/居賃/市区郡選択',
            '2次/居賃/沿線・駅選択',
            '2次/事賃/設定状況',
            '2次/事賃/都道府県',
            '2次/事賃/物件種目',
            '2次/事賃/市区郡/沿線・駅',
            '2次/事賃/市区郡選択',
            '2次/事賃/沿線・駅選択',
            '2次/居売/設定状況',
            '2次/居売/都道府県',
            '2次/居売/物件種目',
            '2次/居売/市区郡/沿線・駅',
            '2次/居売/市区郡選択',
            '2次/居売/沿線・駅選択',
            '2次/事売/設定状況',
            '2次/事売/都道府県',
            '2次/事売/物件種目',
            '2次/事売/市区郡/沿線・駅',
            '2次/事売/市区郡選択',
            '2次/事売/沿線・駅選択',
            '2次/賃貸/賃料（下限）',
            '2次/賃貸/賃料（上限）',
            '2次/賃貸/間取り',
            '2次/賃貸/専用面積（下限）',
            '2次/賃貸/専用面積（上限）',
            '2次/賃貸/築年数',
            '2次/賃貸/画像',
            '2次/賃貸/広告費',
            '2次/賃貸/手数料',
            '2次/貸店舗/賃料（下限）',
            '2次/貸店舗/賃料（上限）',
            '2次/貸店舗/使用部分面積（下限）',
            '2次/貸店舗/使用部分面積（上限）',
            '2次/貸店舗/築年数',
            '2次/貸店舗/画像',
            '2次/貸店舗/広告費',
            '2次/貸店舗/手数料',
            '2次/貸事務所/賃料（下限）',
            '2次/貸事務所/賃料（上限）',
            '2次/貸事務所/使用部分面積（下限）',
            '2次/貸事務所/使用部分面積（上限）',
            '2次/貸事務所/築年数',
            '2次/貸事務所/画像',
            '2次/貸事務所/広告費',
            '2次/貸事務所/手数料',
            '2次/貸駐車場/賃料（下限）',
            '2次/貸駐車場/賃料（上限）',
            '2次/貸駐車場/画像',
            '2次/貸駐車場/広告費',
            '2次/貸駐車場/手数料',
            '2次/貸土地/賃料（下限）',
            '2次/貸土地/賃料（上限）',
            '2次/貸土地/土地面積（下限）',
            '2次/貸土地/土地面積（上限）',
            '2次/貸土地/画像',
            '2次/貸土地/広告費',
            '2次/貸土地/手数料',
            '2次/貸その他/賃料（下限）',
            '2次/貸その他/賃料（上限）',
            '2次/貸その他/使用部分面積（下限）',
            '2次/貸その他/使用部分面積（上限）',
            '2次/貸その他/築年数',
            '2次/貸その他/画像',
            '2次/貸その他/広告費',
            '2次/貸その他/手数料',
            '2次/マンション/価格（下限）',
            '2次/マンション/価格（上限）',
            '2次/マンション/間取り',
            '2次/マンション/専用面積（下限）',
            '2次/マンション/専用面積（上限）',
            '2次/マンション/築年数',
            '2次/マンション/画像',
            '2次/マンション/広告費',
            '2次/マンション/手数料',
            '2次/一戸建て/価格（下限）',
            '2次/一戸建て/価格（上限）',
            '2次/一戸建て/間取り',
            '2次/一戸建て/建物面積（下限）',
            '2次/一戸建て/建物面積（上限）',
            '2次/一戸建て/土地面積（下限）',
            '2次/一戸建て/土地面積（上限）',
            '2次/一戸建て/築年数',
            '2次/一戸建て/画像',
            '2次/一戸建て/広告費',
            '2次/一戸建て/手数料',
            '2次/売土地/価格（下限）',
            '2次/売土地/価格（上限）',
            '2次/売土地/土地面積（下限）',
            '2次/売土地/土地面積（上限）',
            '2次/売土地/最適用途',
            '2次/売土地/画像',
            '2次/売土地/広告費',
            '2次/売土地/手数料',
            '2次/売店舗/価格（下限）',
            '2次/売店舗/価格（上限）',
            '2次/売店舗/使用部分面積（下限）',
            '2次/売店舗/使用部分面積（上限）',
            '2次/売店舗/土地面積（下限）',
            '2次/売店舗/土地面積（上限）',
            '2次/売店舗/築年数',
            '2次/売店舗/画像',
            '2次/売店舗/広告費',
            '2次/売店舗/手数料',
            '2次/売事務所/価格（下限）',
            '2次/売事務所/価格（上限）',
            '2次/売事務所/使用部分面積（下限）',
            '2次/売事務所/使用部分面積（上限）',
            '2次/売事務所/土地面積（下限）',
            '2次/売事務所/土地面積（上限）',
            '2次/売事務所/築年数',
            '2次/売事務所/画像',
            '2次/売事務所/広告費',
            '2次/売事務所/手数料',
            '2次/売その他/価格（下限）',
            '2次/売その他/価格（上限）',
            '2次/売その他/使用部分面積（下限）',
            '2次/売その他/使用部分面積（上限）',
            '2次/売その他/土地面積（下限）',
            '2次/売その他/土地面積（上限）',
            '2次/売その他/築年数',
            '2次/売その他/画像',
            '2次/売その他/広告費',
            '2次/売その他/手数料',
            '特集/特集数',
            '特集/公開中数',
            '特集/下書き数',
            '特集/賃貸(アパート・マンション・一戸建て)',
            '特集/貸店舗（テナント）',
            '特集/貸事務所（貸オフィス）',
            '特集/貸駐車場',
            '特集/貸土地',
            '特集/貸ビル・貸倉庫・その他',
            '特集/マンション（新築・分譲・中古）',
            '特集/一戸建て（新築・中古）',
            '特集/売店舗',
            '特集/売事務所',
            '特集/売ビル・売倉庫・売工場・その他',
            '特集/エンジンレンタルのみ公開の物件だけ表示する',
            '特集/2次広告自動公開の物件を含める',
            '特集/エンド向け仲介手数料不要の物件だけ表示する',
            '特集/2次広告物件（未設定）',
            '特集/2次広告物件を除く',
            '特集/2次広告物件のみ表示',
        );
    }

    //物件設定のCSV登録情報
    private function setBukkenDataKey()
    {
        $this->data_key_for_bukken = array(
            'search_type',
            'estate_type',
            'pref',
            'shikugun',
            'ensen_eki',
        );
    }

    //二次広告自動公開設定のCSV登録情報
    private function setSecondDataKey()
    {
        $this->data_key_for_second = array(
            'second_estate_enabled',
            'second_estate_pref',
            'second_estate_type',
            'second_estate_search_type',
            'second_estate_shikugun',
            'second_estate_ensen_eki',
        );
    }

    //二次広告自動公開設定の絞込条件のCSV登録情報
    private function setSecondDataKeyForCond()
    {
        $this->data_key_for_second_chintai = array(
            'kakaku_1',
            'kakaku_2',
            'madori',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '1',
        );
        $this->data_key_for_second_kashitenpo = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '2',
        );

        $this->data_key_for_second_kashijimusyo = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '3',
        );

        $this->data_key_for_second_kashityusyajo = array(
            'kakaku_1',
            'kakaku_2',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '4',
        );

        $this->data_key_for_second_kashitochi = array(
            'kakaku_1',
            'kakaku_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '5',
        );

        $this->data_key_for_second_kashisonota = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '6',
        );

        $this->data_key_for_second_urimansion = array(
            'kakaku_1',
            'kakaku_2',
            'madori',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '7',
        );

        $this->data_key_for_second_urikodate = array(
            'kakaku_1',
            'kakaku_2',
            'madori',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '8',
        );

        $this->data_key_for_second_uritochi = array(
            'kakaku_1',
            'kakaku_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'saiteki_yoto_cd',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '9',
        );

        $this->data_key_for_second_uritenpo = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '10',
        );

        $this->data_key_for_second_urijimusyo = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '11',
        );

        $this->data_key_for_second_urisonota = array(
            'kakaku_1',
            'kakaku_2',
            'tatemono_ms_1',
            'tatemono_ms_2',
            'tochi_ms_1',
            'tochi_ms_2',
            'chikunensu',
            'image',
            'koukokuhi',
            'tesuryo',
            'estate_type' => '12',
        );
    }

    private function debugLog($msg){
        //$this->_info->info($msg);
    }
}
// php artisan command:batch-create-estate-setting-csv development app CreateEstateSettingCsv >> /var/www/html/storage/logs/CreateEstateSettingCsv.log 2>&1
