<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use Library\Custom\Model\Lists\DiacrisisCsvDownloadHeader;
use Library\Custom\Model\Estate\ClassList;
use Library\Custom\Model\Estate\FdpType;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Analysis\General;
use DateTime;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use App\Repositories\MTheme\MThemeRepositoryInterface;
use App\Repositories\MColor\MColorRepositoryInterface;
use App\Repositories\MLayout\MLayoutRepositoryInterface;
use Library\Custom\Assessment;
use Library\Custom\Assessment\Features;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Diacrisis extends Command {
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-diacrisis {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command diacrisis';

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

            $this->_info->info('/////////////// START ///////////////');
            //CSV準備
            // 出力ファイル名
            $fileName = "HPAD_DIACRISIS.CSV";

            //CSVヘッダーの作成
            $header = DiacrisisCsvDownloadHeader::getCsvHeaderName();
            $stream = fopen($this->_path_data.$fileName, 'w');
            $csv_row_name = array();
            foreach($header as $name) {
                mb_convert_variables('SJIS-win', 'UTF-8', $name);
                $csv_row_name[] = $name;
            }
            $this->encfputscv($stream, $csv_row_name, ',', '"');

            //全会員を取得する
            $companyObj = App::make(CompanyRepositoryInterface::class);
            $select = $companyObj->model()->withoutGlobalScopes()->select();
            $select->where("delete_flg",0);
            $select->where("contract_type", config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'));
            $companyRows = $select->get();

            //テーマの取得
            $themeObj = App::make(MThemeRepositoryInterface::class);
            $themes = array();
            foreach($themeObj->fetchAll() as $key => $val) {
                $themes[$val->id] = $val->title;
            }

            //カラー
            $colorObj = App::make(MColorRepositoryInterface::class);
            $collors = [];
            foreach($colorObj->fetchAll() as $key => $val) {
                $collors[$val->id] = $val->name;
            }

            //レイアウト
            $layoutObj = App::make(MLayoutRepositoryInterface::class);
            $layouts = [];
            foreach($layoutObj->fetchAll() as $key => $val) {
                $layouts[$val->id] = $val->name;
            }

            $hppageObj = App::make(HpPageRepositoryInterface::class);
            //HP情報を取得する
            foreach($companyRows as $key => $companyRow) {

                $csv = array();

                $hp = $companyRow->getCurrentHp();

                if(!$hp) continue;

                //評価/分析 の集計などを行う
                $assessment = null;
                $assessment = new Assessment($hp);
                $assessment->assess();

                //各情報を取得する

                //会員No
                $csv[] = (string)$companyRow->member_no;
                //var_dump("member:". $companyRow->member_no);

                //基本設定-デザイン選択の取得

                //テーマ
                $theme = "";
                if(isset($themes[$hp->theme_id])) {
                    $theme = $themes[$hp->theme_id];
                    mb_convert_variables('SJIS-win', 'UTF-8', $theme);
                }
                $csv[] = $theme;

                //ベースカラー
                $csv[] = (isset($collors[$hp->color_id])) ? $collors[$hp->color_id] : "";

                //レイアウト
                $csv[] = (isset($layouts[$hp->layout_id])) ? $layouts[$hp->layout_id] : "";


                //ページの作成/更新-階層外のページの取得
                $select = $hppageObj->model()->withoutGlobalScopes()->selectRaw('count(*) as count');
                $select->from('hp_page as p');
                $select->where('hp_id',$hp->id);
                $select->whereNotIn('page_type_code',$hppageObj->getFixedMenuTypeList());
                $select->whereNull('parent_page_id');
                $row = $select->first();
                //dd($row['count']);
                $csv[] = $row['count'];

                //サイトの公開/更新-公開設定の取得
                $csv[] = ($hp->hasChanged()) ? 1 : 0;

                //評価・分析（ツール評価） - 総合評価
                $total_points = $assessment->fetchTotalPointsIn(date('Y-m'), date('Y-m'));
                $csv[] = $total_points[date('Y-m')];

                //評価・分析（ツール評価） - 更新
                $pages = $assessment->getPages();
                $csv[] = $assessment->calculateUpdatePoint5steps();  //点数（1～5）

                //サイト全体の日付
                $date = "";
                // 5422 HPAD_DIACRISISに200記事の項目を追加する
                $published_at = max($pages->getTotalResult()['published_at'], $pages->getTotalResultArticle()['published_at']);
                if($published_at > 0) {
                    $date = date("y-m-d H:i:s", $pages->getTotalResult()['published_at']);
                }
                $csv[] = $date;

                //お知らせの日付
                $date = "";
                if($pages->assessPublishPage(HpPageRepository::TYPE_INFO_DETAIL) > 0) {
                    $date = date("Y-m-d", $pages->assessPublishPage(HpPageRepository::TYPE_INFO_DETAIL));
                }
                $csv[] = $date;

                //評価・分析（ツール評価） - ページ作成

                $csv[] = $assessment->calculatePagePoint5steps();   //点数（1～5）
                $adequacy_counts_total = $pages->getTotalResult();
                // 5422 HPAD_DIACRISISに200記事の項目を追加する
                $article_counts_total = $pages->getTotalResultArticle();
                $csv[] = $adequacy_counts_total['public'] + $article_counts_total['public'];  //「公開」ページ数
                $csv[] = $adequacy_counts_total['draft'] + $article_counts_total['draft'];   //「下書き」ページ数
                $csv[] = $adequacy_counts_total['new'] + $article_counts_total['new'];     //「未作成」かどうか


                //評価・分析（ツール評価） - 機能設定	
                $csv[] = $assessment->calculateFeaturePoint5steps();  //点数（1～5）
                $feature = $assessment->getFeatures();
                $csv[] = $feature->countUtilized();       //「登録」項目数
                $csv[] = $feature->countUnUtilized();     //「未登録」かどうか

                //評価・分析（ツール評価） - ページ作成:トップページ　～　サイトマップ
                $page_types = App::make(HpPageRepositoryInterface::class)->getTypeListJp();
                $adequacy_counts = $pages->assess();
                $pagePart = 0 ;
                $this->_setPageData( $csv, $adequacy_counts, $pagePart ) ;
                
                //評価・分析（ツール評価） - 機能設定:ファビコン　～　TOP画像
                $utilization_functions = $assessment->getTargetFeatureNames();
                $utilization = $feature->assess();
                foreach ($utilization_functions as $function_name => $label) {
                    $string = "";
                    if (in_array($function_name, array(Features::FEATURE_FOOTER_LINK,Features::FEATURE_WEB_CLIP))) 
                        continue;
                    if ($utilization[$function_name]) {
                        $string = "設定済み";
                    }else{
                        $string = "未設定";
                    }
                    mb_convert_variables('SJIS-win', 'UTF-8', $string);
                    $csv[] = $string;
                }

                $analysis = $this->_initAnalysis($companyRow);
                //問合せ件数（問い合わせ）
                $generalContactCount = $analysis->getGeneralContactCount();
                $csv[] = $generalContactCount['base-month-val'];

                //問合せ件数（資料請求）
                $assesmentContactCount = $analysis->getAssesmentContactCount();
                $csv[] = $assesmentContactCount['base-month-val'];

                //問合せ件数（売却査定）
                $documentContactCount = $analysis->getDocumentContactCount();
                $csv[] = $documentContactCount['base-month-val'];

                //使用容量
                $csv[] = $hp->capacityCalculation();

                //ログイン日
                $login_date = "";
                $row = App::make(CompanyAccountRepositoryInterface::class)->getDataForCompanyId($companyRow->id);
                if($row[0]['login_date'] != "" && $row[0]['login_date'] != null) $login_date = $row[0]['login_date'];
                $csv[] = $login_date;
                
                $this->_setPageData( $csv, $adequacy_counts, $pagePart ) ;
                
                foreach ($utilization_functions as $function_name => $label) {
                    $string = "";
                    if (!in_array($function_name, array(Features::FEATURE_FOOTER_LINK,Features::FEATURE_WEB_CLIP))) 
                        continue;
                    if ($function_name == Features::FEATURE_FOOTER_LINK) {
                        $string = $utilization[$function_name];
                    } else {
                        if ($utilization[$function_name]) {
                            $string = "設定済み";
                        }else{
                            $string = "未設定";
                        }
                        mb_convert_variables('SJIS-win', 'UTF-8', $string);
                    }
                    $csv[] = $string;
                }
                
                // 4174 CMS内のFDP項目を設定している会員の出力
                $this->_setFdpData( $csv, $hp);

                // 5422 HPAD_DIACRISISに200記事の項目を追加する
                $article_counts = $pages->assessArticle();
                $this->_setArticlePageData( $csv, $article_counts, $hp );

                // 5739 HPAD_DIACRISISに物件詳細リンクの項目を追加する
                $csv[]		= $adequacy_counts[ HpPageRepository::TYPE_LINK_HOUSE	][ 'public'		] ;
                $csv[]		= $adequacy_counts[ HpPageRepository::TYPE_LINK_HOUSE	][ 'draft'		] ;
                
                // ATHOME_HP_DEV-6172 HPAD_DIACRISISに反響プラスの項目を追加する
                $csv[] = $hp->hankyo_plus_use_flg;
                $this->_setPolicyData( $csv, $hppageObj, $hp );

                //csvへ書き出し
                $this->encfputscv($stream, $csv, ',', '"');

            }
            fclose($stream);
	
            $this->_info->info('//////////////// END ////////////////');
        }catch(\Exception $e) {
            $this->_error->error($e);
        }
    }
    protected function _setPageData( &$csv, &$adequacy_counts, &$pagePart )
	{
		foreach (DiacrisisCsvDownloadHeader::$pageTypeList[ $pagePart ]  as $page_type )
		{
			$csv[]		= $adequacy_counts[ $page_type	][ 'public'		] ;
			$csv[]		= $adequacy_counts[ $page_type	][ 'draft'		] ;
		}
		$pagePart++	;
    }
    
    /**
	 * set FDP data
	 */
	public function _setFdpData( &$csv, &$hp)
	{
        $estateSearch = null;
        $estateSetting = App::make(HpEstateSettingRepositoryInterface::class)->getSetting($hp->id);
        if ($estateSetting) {
            $estateSearch = App::make(EstateClassSearchRepositoryInterface::class)->getSettingAll($hp->id, $estateSetting->id)->toArray();
        }
        foreach (ClassList::getInstance()->getAll() as $class=>$labelClass) {
            $fdpDisplay = null;
            if ($estateSearch) {
                foreach ($estateSearch as $search) {
                    if ($search['estate_class'] == $class) {
                        $fdpDisplay = json_decode($search['display_fdp']);
                        break;
                    }
    
                }
            }

            foreach (FdpType::getInstance()->getFdp() as $fdpType=>$labelFdp) {
                if ($estateSearch && $fdpDisplay && $fdpDisplay->fdp_type && in_array($fdpType, $fdpDisplay->fdp_type)) {
                    $csv[] = 1;
                } else {
                    $csv[] = 0;
                }
            }
            foreach (FdpType::getInstance()->getTown() as $townType=>$labelTown) {
                if ($estateSearch && $fdpDisplay && $fdpDisplay->town_type && in_array($townType, $fdpDisplay->town_type)) {
                    $csv[] = 1;
                } else {
                    $csv[] = 0;
                }
            }

        }
	}
    
    /**
	 * set article page data
	 */
    public function _setArticlePageData ( &$csv, &$article_counts, &$hp) {
        $table = App::make(HpPageRepositoryInterface::class);
        $original = $table->getArticleOriginal();
        foreach($table->getAllPagesUsefulEstate() as $page_type) {
            if (in_array($page_type, $original)) {
                continue;
            }
            $csv[] = $article_counts[$page_type][ 'public'] > 0 ? 1 : 0;
        }
        foreach($original as $page_type) {
            $csv[] = $article_counts[$page_type][ 'public'];
        }
    }
	
	private function _initAnalysis($company){ 
		$analysis = null;
		$nowDate  = new DateTime();
		$baseYearMonth = $nowDate->format('Y-m');
		$analysis = new General();
		try {
			$analysis->initBatch($company->id,null,$baseYearMonth, 6);
		}catch (\Exception $e){
            $this->_error->error($e, true);
			return $analysis;
		}
		return $analysis;
	}

	private function encfputscv($fp, $row, $delimiter = ',', $enclosure = '"', $eol = "\n"){
		$tmp = array();
		foreach($row as $v){
			$v = str_replace('"', '""', $v);
			$tmp[]= $enclosure.$v.$enclosure;
		}
		$str = implode($delimiter, $tmp).$eol;
		return fwrite($fp, $str);
	}
    
    public function _setPolicyData( &$csv, $hppageObj, $hp) {
        $select = $hppageObj->model()->withoutGlobalScopes()->select('p.id');
        $select->from('hp_page as p');
        $select->where('p.hp_id', $hp->id);
		$select->where('p.page_type_code', HpPageRepository::TYPE_PRIVACYPOLICY);
		$select->where('mp.parts_type_code', HpMainPartsRepository::PARTS_PRIVACYPOLICY);
		$select->where('p.delete_flg', 0);
		$select->where('mp.delete_flg', 0);
		$select->leftJoin('hp_main_parts as mp', 'p.id', '=', 'mp.page_id')->orderbyRaw('mp.id DESC');
		$select->selectRaw('mp.id as mid, mp.attr_3 as `value`');
        $row = $hppageObj->fetchRow($select);
		if (!$row) {
			$row['value'] = '';
		}
		$string = trim(html_entity_decode(htmlspecialchars_decode($row['value']), ENT_QUOTES, 'UTF-8'));
		$string = trim(strip_tags($string), ' ');
		$string = str_replace(['，', '’', '”', ',',  "'", '"'], '', $string);
		mb_convert_variables('SJIS-win', 'UTF-8', $string);
		$csv[] = $string;
	}
}


// docker exec -it servi_80 bash 
// php artisan command:batch-diacrisis development app Diacrisis>> /var/www/html/storage/logs/Diacrisis.log 2>&1
