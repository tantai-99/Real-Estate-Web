<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;
use App\Repositories\HpImage\HpImageRepositoryInterface;
use App\Repositories\HpImageCategory\HpImageCategoryRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\HpFile2\HpFile2RepositoryInterface;
use App\Repositories\HpFile2Category\HpFile2CategoryRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpImageContent\HpImageContentRepositoryInterface;
use App\Repositories\HpFile2Content\HpFile2ContentRepositoryInterface;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use App\Repositories\HpFileContent\HpFileContentRepositoryInterface;
use Library\Custom\Crypt\TestSitePassword;
use App\Models\HpPage;
use Library\Custom\Model\Lists\ArticleLinkType;
use Library\Custom\Model\Estate\TypeList;
use App\Repositories\SecondEstateClassSearch\SecondEstateClassSearchRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use Library\Custom\Model\Estate\ClassList;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;
use App\Repositories\HpSideParts\HpSidePartsRepository;

use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use Library\Custom\Model\Lists\Original;
use Illuminate\Support\Facades\DB;
use App\Repositories\HpTopImage\HpTopImageRepositoryInterface;
use App\Repositories\HpInfoDetailLink\HpInfoDetailLinkRepositoryInterface;
use App\Repositories\HpArea\HpAreaRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpMainElement\HpMainElementRepositoryInterface;
use App\Repositories\HpMainElementElement\HpMainElementElementRepositoryInterface;
use App\Repositories\HpSideElements\HpSideElementsRepositoryInterface;
use App\Repositories\HpContact\HpContactRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepositoryInterface;
use App\Repositories\HpImageUsed\HpImageUsedRepositoryInterface;
use App\Repositories\HpFile2Used\HpFile2UsedRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;
use App\Repositories\AssociatedHpPageAttribute\AssociatedHpPageAttributeRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;


class Hp extends Model
{
    use MySoftDeletes;

    protected $table = 'hp';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    const SIDELAYOUT_ESTATE_RENT = 1;
    const SIDELAYOUT_ESTATE_PURCHASE = 2;
    const SIDELAYOUT_OTHER_LINK = 3;
    const SIDELAYOUT_CUSTOMIZED_CONTENTS = 4;
    const SIDELAYOUT_ARTICLE_LINK = 5;

    const SIDELAYOUT_OTHER_LINK_TITLE = 'コンテンツ一覧';
    const SIDELAYOUT_ARTICLE_LINK_TITLE = '不動産お役立ち情報';

    protected $fillable = [
        'id',
        'public_status_flg',
        'initial_setting_status_code',
        'all_upload_flg',
        'favicon',
        'webclip',
        'company_name',
        'adress',
        'tel',
        'office_hour',
        'outline',
        'footer_link_level',
        'logo_pc',
        'logo_pc_type',
        'logo_pc_title',
        'logo_pc_text',
        'logo_sp',
        'logo_sp_type',
        'logo_sp_title',
        'logo_sp_text',
        'footer_pc_image',
        'footer_pc_image_title',
        'footer_pc_text',
        'footer_sp_image',
        'footer_sp_image_title',
        'footer_sp_text',
        'copylight',
        'fb_like_button_flg',
        'fb_timeline_flg',
        'fb_page_url',
        'tw_tweet_button_flg',
        'tw_timeline_flg',
        'tw_widget_id',
        'tw_username',
        'line_button_flg',
        'line_at_freiend_qrcode',
        'line_at_freiend_button',
        'qr_code_type',
        'test_site_password',
        'theme_id',
        'color_id',
        'color_code',
        'layout_id',
        'side_layout',
        'title',
        'description',
        'keywords',
        'copied_hp_id',
        'public_image_ids',
        'slide_show',
        'public_file2_ids',
        'public_file_ids',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
        'global_navigation',
        'original_search',
        'all_upload_parts',
        'change_set_link',

    ];

    protected $_cryptMap = [];

    public function init()
    {
        $this->_cryptMap['test_site_password'] = new TestSitePassword();
    }

    public function associatedCompanyHp($col)
    {
        return $this->hasOne(AssociatedCompanyHp::class, $col);
    }

    public function hpEstateSetting($col)
    {
        return $this->hasOne(hpEstateSetting::class, $col);
    }

    public function logEdit($col)
    {
        return $this->hasMany(LogEdit::class, $col);
    }

    public function hpSiteImage()
    {
        return $this->hasMany(HpSiteImage::class, 'hp_id');
    }

    public function releaseSchedule()
    {
        return $this->hasMany(ReleaseSchedule::class, 'hp_id');
    }

    public function specialEstate($col)
    {
        return $this->hasMany(SpecialEstate::class, $col);
    }

    public function mtheme()
    {
        return $this->belongsTo(MTheme::class, 'theme_id')->first();
    }

    public function mlayout()
    {
        return $this->belongsTo(MLayout::class, 'layout_id')->first();
    }

    public function mcolor()
    {
        return $this->belongsTo(MColor::class, 'color_id')->first();
    }

    public function htmlContent()
    {
        return $this->hasOne(HpHtmlContent::class, 'hp_id')->first();
    }

    public function hpPage()
    {
        return $this->belongsTo(HpPage::class, 'hp_id')->first();
    }

    public function isInitialized()
    {
        return $this->getInitializeStatus() == config('constants.hp.INITIAL_SETTING_STATUS_COMPLETE');
    }

    public function getInitializeStatus()
    {
        return $this->initial_setting_status_code;
    }

    public function setFromArray($data)
    {
        $data = array_intersect_key($data, $this->toArray());

        foreach ($data as $columnName => $value) {
            $this->__set($columnName, $value);
        }

        return $this;
    }

    public function fetchImageCategories()
    {
        $table = App::make(HpImageCategoryRepositoryInterface::class);
        return $table->fetchAll([['hp_id', $this->id], ['delete_flg', 0]], ['sort']);
    }

    public function fetchFile2Categories()
    {
        $table = App::make(HpFile2CategoryRepositoryInterface::class);
        return $table->fetchAll([['hp_id', $this->id], ['delete_flg', 0]], ['sort']);
    }

    /**
     * 雛形画像は取得しない
     * @return App\Collections\CustomCollection
     */
    public function fetchImages()
    {
        $table = App::make(HpImageRepositoryInterface::class);
        return $table->fetchAll([['hp_id', $this->id], ['type', 0], ['delete_flg', 0]]);
    }

    /**
     * ATHOME_HP_DEV-2186 ファイル（PDF）の取得
     * @return App\Collections\CustomCollection
     */
    public function fetchFile2s()
    {
        $table = App::make(HpFile2RepositoryInterface::class);
        return $table->fetchAll([['hp_id', $this->id], ['type', 0], ['delete_flg', 0]]);
    }

    public function fetchContactPage($preview = false)
    {
        $table = \App::make(HpPageRepositoryInterface::class);
        $s = $table->model()->select();
        $s->where('hp_id', $this->id);
        $s->where('page_type_code', HpPageRepository::TYPE_FORM_CONTACT);
        if (!$preview) {
            $s->where('public_flg', 1);
        }

        return $s->first();
    }

    public function findPagesByType($page_type_code, $public_only = true, $limit = null)
    {
        $table = HpPage::where('hp_id', $this->id);

        $table->where('hp_id', $this->id);
        $table->where('page_type_code', $page_type_code);
        if ($public_only) {
            $table->where('public_flg', 1);
        }
        $table->orderBy('date');
        if (is_numeric($limit)) {
            $table->offset(0)->limit($limit);
        }

        return $table->get();
    }

    public function hasChanged()
    {
        if ($this->all_upload_flg) {
            return true;
        }

        // 物件設定・特集のチェック
        $setting = $this->getEstateSetting();
        if ($setting && $setting->hasChanged()) {
            return true;
        }

        // 差分あり && !新規
        return App::make(HpPageRepositoryInterface::class)->countRows(array(['hp_id', $this->id], ['diff_flg', 1], ['new_flg', 0])) > 0;
    }

    /**
     * サイドナビのレイアウトを取得する
     */
    public function getSideLayout()
    {
        // デフォルトレイアウト
        $layouts = [
            self::SIDELAYOUT_ESTATE_RENT => [
                'display' => 1,
                'sort' => -4,
            ],
            self::SIDELAYOUT_ESTATE_PURCHASE => [
                'display' => 1,
                'sort' => -3,
            ],
            self::SIDELAYOUT_OTHER_LINK => [
                'display' => 1,
                'sort' => -2,
                'title' => self::SIDELAYOUT_OTHER_LINK_TITLE
            ],
            self::SIDELAYOUT_CUSTOMIZED_CONTENTS => [
                'display' => 1,
                'sort' => -1,
            ],
            self::SIDELAYOUT_ARTICLE_LINK => [
                'display' => 1,
                'sort' => 5,
                'title' => self::SIDELAYOUT_ARTICLE_LINK_TITLE,
                'type' => ArticleLinkType::ARTICLE,
            ],
        ];

        // 設定値で上書き
        $articleLink = false;
        if ($this->side_layout) {
            foreach (json_decode($this->side_layout, true) as $id => $layout) {
                if (isset($layouts[$id])) {
                    $layouts[$id] = array_merge($layouts[$id], $layout);
                }
            }
        }

        return $layouts;
    }

    /**
     * 物件設定を考慮したサイドナビのレイアウトを取得する
     */
    public function getEditableSideLayout()
    {
        $layouts = $this->getSideLayout();
        $estateSetting = $this->getEstateSetting();

        // 物件検索がない場合は物件項目削除
        if (!$estateSetting) {
            unset($layouts[self::SIDELAYOUT_ESTATE_RENT]);
            unset($layouts[self::SIDELAYOUT_ESTATE_PURCHASE]);
        } else {
            $searchSettings = $estateSetting->getSearchSettingAll();
            $estateTypes = $searchSettings->getEstateTypes();
            $typeList = TypeList::getInstance();
            // 検索設定がない場合は項目削除
            if (!$typeList->containsRent($estateTypes)) {
                unset($layouts[self::SIDELAYOUT_ESTATE_RENT]);
            }
            if (!$typeList->containsPurchase($estateTypes)) {
                unset($layouts[self::SIDELAYOUT_ESTATE_PURCHASE]);
            }
        }
        return $layouts;
    }

    /**
     * ソート済のサイドナビレイアウトを取得する
     */
    public function getSortedSideLayout()
    {
        $layouts = [];
        foreach ($this->getSideLayout() as $layoutId => $layout) {
            $layout['id'] = $layoutId;
            $layouts[] = $layout;
        }
        usort($layouts, function ($a, $b) {
            return $a['sort'] - $b['sort'];
        });
        return $layouts;
    }

    /**
     * 物件設定に対応する問い合わせが無い物件の種別名の配列を返す。
     */
    public function getEstateContactNecessity()
    {
        $estateClassM = ClassList::getInstance();

        // ４種別の物件設定を取得
        $settingEstateClassList = array();
        $setting = $this->getEstateSetting();
        if ($setting) {
            $settingList = $setting->getSearchSettingAll();
            foreach ($settingList as $estateClassSearchRow) {
                $settingEstateClassList[] = $estateClassSearchRow['estate_class'];
            }
        }

        // ４種別の物件問い合わせページを取得
        $contactEstateClassList = array();
        $contactList = App::make(HpPageRepositoryInterface::class)->fetchEstateContactPageAll($this->id, 0);
        if ($contactList) {
            foreach ($contactList as $estateContactPage) {
                $page_type_code = $estateContactPage['page_type_code'];
                $estateClassName = "";
                switch ($page_type_code) {
                    case HpPageRepository::TYPE_FORM_LIVINGLEASE:
                        $contactEstateClassList[] = $estateClassM::CLASS_CHINTAI_KYOJU;
                        break;
                    case HpPageRepository::TYPE_FORM_OFFICELEASE:
                        $contactEstateClassList[] = $estateClassM::CLASS_CHINTAI_JIGYO;
                        break;
                    case HpPageRepository::TYPE_FORM_LIVINGBUY:
                        $contactEstateClassList[] = $estateClassM::CLASS_BAIBAI_KYOJU;
                        break;
                    case HpPageRepository::TYPE_FORM_OFFICEBUY:
                        $contactEstateClassList[] = $estateClassM::CLASS_BAIBAI_JIGYO;
                        break;
                    default:
                        throw new \Exception("システムエラー");
                        break;
                }
            }
        }

        $result = array();
        foreach ($settingEstateClassList as $settingEstateClass) {
            // 物件設定に対応する問い合わせが無い場合は、その種別名を設定する。
            if (!in_array($settingEstateClass, $contactEstateClassList)) {
                $result[] = $estateClassM->get($settingEstateClass);
            }
        }
        return $result;
    }


    /**
     * 物件設定に対応するリクエストが無い物件の種別名の配列を返す。
     */
    public function getEstateRequestNecessity()
    {
        $estateClassM = ClassList::getInstance();
        // ４種別の物件設定を取得
        $settingEstateClassList = array();
        $setting = $this->getEstateSetting();
        if ($setting) {
            $settingList = $setting->getSearchSettingAll();
            foreach ($settingList as $estateClassSearchRow) {
                if ($estateClassSearchRow->estate_request_flg == 1) $settingEstateClassList[] = $estateClassSearchRow['estate_class'];
            }
        }

        // ４種別の物件リクエストページを取得
        $requestEstateClassList = array();
        $requestList = App::make(HpPageRepositoryInterface::class)->fetchEstateRequestPageAll($this->id, 0);
        if ($requestList) {
            foreach ($requestList as $estateRequestPage) {
                $page_type_code = $estateRequestPage['page_type_code'];
                $estateClassName = "";
                switch ($page_type_code) {
                    case HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE:
                        $requestEstateClassList[] = $estateClassM::CLASS_CHINTAI_KYOJU;
                        break;
                    case HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE:
                        $requestEstateClassList[] = $estateClassM::CLASS_CHINTAI_JIGYO;
                        break;
                    case HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY:
                        $requestEstateClassList[] = $estateClassM::CLASS_BAIBAI_KYOJU;
                        break;
                    case HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY:
                        $requestEstateClassList[] = $estateClassM::CLASS_BAIBAI_JIGYO;
                        break;
                    default:
                        throw new \Exception("システムエラー");
                        break;
                }
            }
        }

        $result = array();
        foreach ($settingEstateClassList as $settingEstateClass) {
            // 物件設定に対応するリクエストが無い場合は、その種別名を設定する。
            if (!in_array($settingEstateClass, $requestEstateClassList)) {
                $result[] = $estateClassM->get($settingEstateClass);
            }
        }
        return $result;
    }



    public function hasReserve()
    {
        $rs  = App::make(ReleaseScheduleRepositoryInterface::class)->hasReserveByHpId($this->id);
        $rss =App::make(ReleaseScheduleSpecialRepositoryInterface::class)->hasReserveByHpId($this->id);
        return $rs + $rss;
    }

    public function copyAll($inTransaction = true)
    {
        $data = $this->toArray();

        unset($data['id']);
        unset($data['create_id']);
        unset($data['create_date']);
        unset($data['update_id']);
        unset($data['update_date']);
        unset($data['delete_flg']);

        $table = App::make(HpRepositoryInterface::class);

        try {
            if (!$inTransaction) {
                DB::beginTransaction();
            }
            if (isset($data['test_site_password']) && $data['test_site_password']) {
                $data['test_site_password'] = $this->_cryptMap['test_site_password']->encrypt($data['test_site_password']);
            }

            $newRow = $table->create($data);
            $newRow->copied_hp_id = $this->id;
            $newRow->all_upload_flg = 1;
            $newRow->setAllUploadParts('ALL', 1);
            $newRow->save();
            // サイト画像のコピー
            $siteImages = array();
            if ($this->favicon) {
                $siteImages['favicon'] = $this->favicon;
            }
            if ($this->logo_pc) {
                $siteImages['logo_pc'] = $this->logo_pc;
            }
            if ($this->logo_sp) {
                $siteImages['logo_sp'] = $this->logo_sp;
            }
            if ($this->webclip) {
                $siteImages['webclip'] = $this->webclip;
            }

            $now = date('Y-m-d H:i:s');
            $siteImageTable = App::make(HpSiteImageRepositoryInterface::class);
            foreach($siteImages as $col => $siteImageId){
                $cols = array('type', 'extension', 'content');
                $data = array('hp_id' => $newRow->id);
                $where = array(['hp_id', $this->id],['id', $siteImageId]);
                $newRow->{$col} = $siteImageTable->copyRow($cols, $data, $where);
            }
            $newRow->save();

            /**
             * HP画像・ファイルのコピー
             */
            $where = array(['hp_id', $this->id]);
            $data = array('hp_id' => $newRow->id);

            //画像
            $cols = array('id', 'title', 'hp_image_content_id', 'category_id', 'type', 'sys_name');
            App::make(HpImageRepositoryInterface::class)->copyAll($cols, $data, $where);

            $cols = array('id', 'extension', 'content');
            App::make(HpImageContentRepositoryInterface::class)->copyAll($cols, $data, $where);

            $cols = array('id', 'name', 'sort');
            App::make(HpImageCategoryRepositoryInterface::class)->copyAll($cols, $data, $where);

            // ファイル
            $cols = array('id', 'extension', 'filename', 'content');
            App::make(HpFileContentRepositoryInterface::class)->copyAll($cols, $data, $where);

            // リンク用ファイル
            $cols = array('id', 'title', 'hp_file2_content_id', 'category_id', 'type', 'sys_name');
            App::make(HpFile2RepositoryInterface::class)->copyAll($cols, $data, $where);

            $cols = array('id', 'extension', 'content', 'filename');
            App::make(HpFile2ContentRepositoryInterface::class)->copyAll($cols, $data, $where);

            $cols = array('id', 'name', 'sort');
            App::make(HpFile2CategoryRepositoryInterface::class)->copyAll($cols, $data, $where);

            //HPページのコピー
            $hpTable = App::make(HpPageRepositoryInterface::class);
            $cols = array(
                'link_id', 'page_type_code', 'new_flg', 'updated_at', 'date', 'page_category_code',
                'parent_page_id', 'level', 'sort',
                'link_url', 'link_page_id', 'link_target_blank',
                'link_estate_page_id', 'link_house',
                'contact_count_id',
                'title', 'description', 'keywords', 'filename',
                'member_only_flg', 'member_id', 'member_password',
                'list_title', 'new_mark', 'page_flg', 'article_parent_id', 'link_article_flg'
            );
            $data = array(
                'hp_id' => $newRow->id,
                'public_flg' => 0,
                'diff_flg' => 1,
                'copied_id' => 'id AS copied_id',
            );
            $hpTable->copyAll($cols, $data, $where);
            // ページIDの参照先を更新
            $this->_updateCopiedReference($newRow->id, 'hp_page', 'parent_page_id', 'hp_page');

            // 5444 update article parent id
            $this->_updateCopiedReference($newRow->id, 'hp_page', 'article_parent_id', 'hp_page');


            //HPページで使用している画像のコピー
            $cols = array(
                'hp_image_id', 'hp_id', 'create_date', 'update_date', 'hp_page_id'
            );
            $imageUsedTable = App::make(HpImageUsedRepositoryInterface::class);
            $select = $imageUsedTable->getSelect();
            $inserCols = array('hp_image_id');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id');
            $select = $select->join('hp_page', 'hp_image_used.hp_page_id','=','hp_page.copied_id');
            $select = $select->where('hp_image_used.hp_id', $this->id);
            $select = $select->where('hp_image_used.delete_flg', 0);
            $select = $select->where('hp_page.hp_id', $newRow->id);

            $imageUsedTable->insertSelect($select, $cols);
            // HPページで使用しているファイル2のコピー
            $cols = array(
                'hp_file2_id', 'hp_id', 'create_date', 'update_date', 'hp_page_id'
            );
            $file2UsedTable = App::make(HpFile2UsedRepositoryInterface::class);
            $select = $file2UsedTable->getSelect();
            $inserCols = array('hp_file2_id');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id');
            $select->join('hp_page', 'hp_file2_used.hp_page_id','=','hp_page.copied_id');
            $select->where('hp_file2_used.hp_id', $this->id);
            $select->where('hp_file2_used.delete_flg', 0);
            $select->where('hp_page.hp_id', $newRow->id);

            $file2UsedTable->insertSelect($select, $cols);

            //トップページメインイメージのコピー
            $cols = array(
                'image', 'image_title', 'link_type', 'link_url', 'link_page_id', 'file2', 'file2_title', 'link_target_blank', 'sort', 'link_house',
                'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $topImageTable = App::make(HpTopImageRepositoryInterface::class);
            $select = $topImageTable->getSelect();
            $inserCols = array('hp_top_image.image', 'hp_top_image.image_title', 'hp_top_image.link_type', 'hp_top_image.link_url', 'hp_top_image.link_page_id', 'hp_top_image.file2', 'hp_top_image.file2_title', 'hp_top_image.link_target_blank', 'hp_top_image.sort', 'hp_top_image.link_house');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_top_image.page_id','=','hp_page.copied_id');
            $select->where('hp_top_image.hp_id', $this->id);
            $select->where('hp_top_image.delete_flg',0);
            $select->where('hp_page.hp_id', $newRow->id);

            $topImageTable->insertSelect($select, $cols);


            $cols = array(
                'link_type', 'link_url', 'link_page_id', 'file2', 'file2_title', 'link_target_blank', 'link_house', 'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $detailLinkTable = App::make(HpInfoDetailLinkRepositoryInterface::class);
            $select = $detailLinkTable->getSelect();
            $inserCols = array('hp_info_detail_link.link_type', 'hp_info_detail_link.link_url', 'hp_info_detail_link.link_page_id', 'hp_info_detail_link.file2', 'hp_info_detail_link.file2_title', 'hp_info_detail_link.link_target_blank', 'hp_info_detail_link.link_house');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_info_detail_link.page_id','=','hp_page.copied_id');
            $select->where('hp_info_detail_link.hp_id', $this->id);
            $select->where('hp_info_detail_link.delete_flg',0);
            $select->where('hp_page.hp_id', $newRow->id);

            $detailLinkTable->insertSelect($select, $cols);

            // ページ エリアのコピー
            $cols = array(
                'copied_id', 'column_type_code', 'sort', 'display_flg', 'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $areaTable = App::make(HpAreaRepositoryInterface::class);
            $select = $areaTable->getSelect();
            $inserCols = array('hp_area.id', 'hp_area.column_type_code', 'hp_area.sort', 'hp_area.display_flg'
            );
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_area.page_id','=','hp_page.copied_id');
            $select->where('hp_area.hp_id', $this->id);
            $select->where('hp_area.delete_flg',0);
            $select->where('hp_page.hp_id', $newRow->id);

            $areaTable->insertSelect($select, $cols);

            // ページ メインパーツのコピー
            $cols = array(
                'copied_id', 'parts_type_code', 'sort', 'column_sort', 'display_flg',
                'attr_1', 'attr_2', 'attr_3', 'attr_4', 'attr_5', 'attr_6', 'attr_7', 'attr_8', 'attr_9', 'attr_10', 'attr_11', 'attr_12', 'hp_id', 'create_date', 'update_date', 'area_id', 'page_id'
            );
            $mainPartsTable = App::make(HpMainPartsRepositoryInterface::class);
            $select = $mainPartsTable->getSelect();
            $inserCols = array('hp_main_parts.id', 'hp_main_parts.parts_type_code', 'hp_main_parts.sort', 'hp_main_parts.column_sort', 'hp_main_parts.display_flg','hp_main_parts.attr_1', 'hp_main_parts.attr_2', 'hp_main_parts.attr_3', 'hp_main_parts.attr_4', 'hp_main_parts.attr_5', 'hp_main_parts.attr_6', 'hp_main_parts.attr_7', 'hp_main_parts.attr_8', 'hp_main_parts.attr_9', 'hp_main_parts.attr_10', 'hp_main_parts.attr_11', 'hp_main_parts.attr_12');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_area.id as area_id, hp_area.page_id');
            $select->join('hp_area', 'hp_main_parts.area_id','=','hp_area.copied_id');
            $select->where('hp_main_parts.hp_id', $this->id);
            $select->where('hp_main_parts.delete_flg',0);
            $select->where('hp_area.hp_id', $newRow->id);

            $mainPartsTable->insertSelect($select, $cols);

            // ページ メインパーツエレメントのコピー
            $cols = array(
                'copied_id', 'type', 'sort',
                'attr_1', 'attr_2', 'attr_3', 'attr_4', 'attr_5', 'attr_6', 'attr_7', 'attr_8', 'attr_9', 'attr_10', 'attr_11', 'attr_12', 'attr_13', 'attr_14', 'attr_15', 'hp_id', 'create_date', 'update_date', 'parts_id', 'page_id'
            );
            $mainElementTable = App::make(HpMainElementRepositoryInterface::class);
            $select = $mainElementTable->getSelect();
            $inserCols = array('hp_main_element.id', 'hp_main_element.type', 'hp_main_element.sort',
                'hp_main_element.attr_1', 'hp_main_element.attr_2', 'hp_main_element.attr_3', 'hp_main_element.attr_4', 'hp_main_element.attr_5', 'hp_main_element.attr_6', 'hp_main_element.attr_7', 'hp_main_element.attr_8', 'hp_main_element.attr_9', 'hp_main_element.attr_10', 'hp_main_element.attr_11', 'hp_main_element.attr_12', 'hp_main_element.attr_13', 'hp_main_element.attr_14', 'hp_main_element.attr_15');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_main_parts.id as parts_id, hp_main_parts.page_id');
            $select->join('hp_main_parts', 'hp_main_element.parts_id','=','hp_main_parts.copied_id');
            $select->where('hp_main_element.hp_id', $this->id);
            $select->where('hp_main_element.delete_flg',0);
            $select->where('hp_main_parts.hp_id', $newRow->id);

            $mainElementTable->insertSelect($select, $cols);

            // ページ メインパーツエレメントエレメントのコピー
            $cols = array(
                'type', 'sort',
                'attr_1', 'attr_2', 'attr_3', 'attr_4', 'attr_5', 'attr_6', 'attr_7', 'attr_8', 'attr_9', 'attr_10', 'attr_11',
                'hp_id', 'create_date', 'update_date',
                'parts_id', 'page_id',
            );
            $mainElementElementTable = App::make(HpMainElementElementRepositoryInterface::class);
            $select = $mainElementElementTable->getSelect();
            $inserCols = array('hp_main_element_element.type', 'hp_main_element_element.sort',
                'hp_main_element_element.attr_1', 'hp_main_element_element.attr_2', 'hp_main_element_element.attr_3', 'hp_main_element_element.attr_4', 'hp_main_element_element.attr_5', 'hp_main_element_element.attr_6', 'hp_main_element_element.attr_7', 'hp_main_element_element.attr_8', 'hp_main_element_element.attr_9', 'hp_main_element_element.attr_10', 'hp_main_element_element.attr_11');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_main_element.id as parts_id, hp_main_element.page_id');
            $select->join('hp_main_element', 'hp_main_element_element.parts_id','=','hp_main_element.copied_id');
            $select->where('hp_main_element_element.hp_id', $this->id);
            $select->where('hp_main_element_element.delete_flg', 0);
            $select->where('hp_main_element.hp_id', $newRow->id);

            $mainElementElementTable->insertSelect($select, $cols);

            // ページ サイドパーツのコピー
            $cols = array(
                'copied_id', 'parts_type_code', 'sort', 'display_flg',
                'attr_1', 'attr_2', 'attr_3', 'attr_4', 'attr_5', 'attr_6', 'attr_7', 'attr_8', 'attr_9', 'attr_10', 'attr_11',
                'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $sidePartsTable = App::make(HpSidePartsRepositoryInterface::class);
            $select = $sidePartsTable->getSelect();
            $inserCols = array('hp_side_parts.id', 'hp_side_parts.parts_type_code', 'hp_side_parts.sort', 'hp_side_parts.display_flg',
                'hp_side_parts.attr_1', 'hp_side_parts.attr_2', 'hp_side_parts.attr_3', 'hp_side_parts.attr_4', 'hp_side_parts.attr_5', 'hp_side_parts.attr_6', 'hp_side_parts.attr_7', 'hp_side_parts.attr_8', 'hp_side_parts.attr_9', 'hp_side_parts.attr_10', 'hp_side_parts.attr_11');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_side_parts.page_id','=','hp_page.copied_id');
            $select->where('hp_side_parts.hp_id', $this->id);
            $select->where('hp_side_parts.delete_flg', 0);
            $select->where('hp_page.hp_id', $newRow->id);

            $sidePartsTable->insertSelect($select, $cols);

            // ページ サイドパーツエレメントのコピー
            $cols = array(
                'type', 'sort',
                'attr_1', 'attr_2', 'attr_3', 'attr_4', 'attr_5', 'attr_6', 'attr_7', 'attr_8', 'attr_9', 'hp_id', 'create_date', 'update_date', 'parts_id', 'page_id'
            );
            $sideElementTable = App::make(HpSideElementsRepositoryInterface::class);
            $select = $sideElementTable->getSelect();
            $inserCols = array('hp_side_element.type', 'hp_side_element.sort',
                'hp_side_element.attr_1', 'hp_side_element.attr_2', 'hp_side_element.attr_3', 'hp_side_element.attr_4', 'hp_side_element.attr_5', 'hp_side_element.attr_6', 'hp_side_element.attr_7', 'hp_side_element.attr_8', 'hp_side_element.attr_9');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_side_parts.id as parts_id, hp_side_parts.page_id');
            $select->join('hp_side_parts', 'hp_side_element.parts_id','=','hp_side_parts.copied_id');
            $select->where('hp_side_element.hp_id', $this->id);
            $select->where('hp_side_element.delete_flg', 0);
            $select->where('hp_side_parts.hp_id', $newRow->id);

            $sideElementTable->insertSelect($select, $cols);

            // ページ 問い合わせフォームのコピー
            $cols = array(
                'notification_to_1', 'notification_to_2', 'notification_to_3', 'notification_to_4', 'notification_to_5','notification_subject','autoreply_flg', 'autoreply_from', 'autoreply_sender', 'autoreply_subject', 'autoreply_body',
                'heading_code', 'heading', 'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $contactTable = App::make(HpContactRepositoryInterface::class);
            $select = $contactTable->getSelect();
            $inserCols = array( 'notification_to_1', 'notification_to_2', 'notification_to_3', 'notification_to_4', 'notification_to_5',
                'notification_subject',
                'autoreply_flg', 'autoreply_from', 'autoreply_sender', 'autoreply_subject', 'autoreply_body',
                'heading_code', 'heading');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_contact.page_id','=','hp_page.copied_id');
            $select->where('hp_contact.hp_id', $this->id);
            $select->where('hp_contact.delete_flg', 0);
            $select->where('hp_page.hp_id', $newRow->id);

            $contactTable->insertSelect($select, $cols);

            // ページ 問い合わせフォームパーツのコピー
            $cols = array(
                'item_code', 'item_title', 'required_type', 'choices_type_code',
                'choice_1', 'choice_2', 'choice_3', 'choice_4', 'choice_5', 'choice_6', 'choice_7', 'choice_8', 'choice_9', 'choice_10', 'choice_11',
                'sort', 'hp_id', 'create_date', 'update_date', 'page_id'
            );
            $contactPartsTable = App::make(HpContactPartsRepositoryInterface::class);
            $select = $contactPartsTable->getSelect();
            $inserCols = array('item_code', 'item_title', 'required_type', 'choices_type_code',
                'choice_1', 'choice_2', 'choice_3', 'choice_4', 'choice_5', 'choice_6', 'choice_7', 'choice_8', 'choice_9', 'choice_10', 'choice_11',
                'hp_contact_parts.sort');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw(DB::connection()->getPdo()->quote(date('Y-m-d H:i:s')));
            $select->selectRaw('hp_page.id as page_id');
            $select->join('hp_page', 'hp_contact_parts.page_id','=','hp_page.copied_id');
            $select->where('hp_contact_parts.hp_id', $this->id);
            $select->where('hp_contact_parts.delete_flg', 0);
            $select->where('hp_page.hp_id', $newRow->id);

            $contactPartsTable->insertSelect($select, $cols);

            // 物件設定のコピー
            $cols = array('create_date', 'update_date', 'setting_for', 'updated_at', 'hp_id'
            );
            $hpEstateSettingTable = App::make(HpEstateSettingRepositoryInterface::class);
            $select = $hpEstateSettingTable->getSelect();
            $inserCols = array('create_date', 'update_date', 'setting_for', 'updated_at');
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);

            $select->where('delete_flg', 0);
            $select->where('hp_id', $this->id);
            $hpEstateSettingTable->insertSelect($select, $cols);

            // 物件検索設定のコピー
            $cols = array('create_date',
                'update_date','origin_id','estate_class','enabled_estate_type','area_search_filter','map_search_here_enabled','estate_request_flg','display_freeword','display_fdp','hp_estate_setting_id','hp_id'
            );
            $estateClassSearchTable = App::make(EstateClassSearchRepositoryInterface::class);
            $select = $estateClassSearchTable->getSelect();
            $inserCols = array('estate_class_search.create_date',
                'estate_class_search.update_date','estate_class_search.origin_id', 'estate_class_search.estate_class', 'estate_class_search.enabled_estate_type', 'estate_class_search.area_search_filter', 'estate_class_search.map_search_here_enabled', 'estate_class_search.estate_request_flg', 'estate_class_search.display_freeword', 'estate_class_search.display_fdp'
            );
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw('new_setting.id as new_estate_setting_id');
            $select->selectRaw($newRow->id);
            $select->join('hp_estate_setting AS old_setting',function($join){ 
                $join->on('estate_class_search.hp_estate_setting_id','old_setting.id');
            });
            $select->join('hp_estate_setting AS new_setting',function($join) use ($newRow){
                $join->on('old_setting.setting_for', 'new_setting.setting_for')
                    ->where('new_setting.hp_id', $newRow->id);
            });
            $select->where('estate_class_search.hp_id', $this->id);
            $select->where('estate_class_search.delete_flg', 0);
            $select->where('old_setting.delete_flg', 0);

            $estateClassSearchTable->insertSelect($select, $cols);

            // 特集のコピー
            $cols = array(
                'create_date',
                'update_date',
                'updated_at',

                'origin_id',
                'title',
                'filename',
                'comment',
                'create_special_date',
                'estate_class',
                'enabled_estate_type',

                'owner_change',
                'jisha_bukken',
                'niji_kokoku',
                'niji_kokoku_jido_kokai',
                'tesuryo_ari_nomi',
                'tesuryo_wakare_komi',
                'kokokuhi_joken_ari',
                'end_muke_enabled',

                'only_er_enabled',
                'second_estate_enabled',
                'area_search_filter',
                'search_filter',
                'map_search_here_enabled',
                'display_freeword',
                'method_setting',
                'houses_id',
                'hp_estate_setting_id',

                'hp_id'
            );
            $specialEstateTable = App::make(SpecialEstateRepositoryInterface::class);
            $select = $specialEstateTable->getSelect();
            $inserCols = array(
                'special_estate.create_date',
                'special_estate.update_date',
                'special_estate.updated_at',

                'origin_id',
                'title',
                'filename',
                'comment',
                'create_special_date',
                'estate_class',
                'enabled_estate_type',

                'owner_change',
                'jisha_bukken',
                'niji_kokoku',
                'niji_kokoku_jido_kokai',
                'tesuryo_ari_nomi',
                'tesuryo_wakare_komi',
                'kokokuhi_joken_ari',
                'end_muke_enabled',

                'only_er_enabled',
                'second_estate_enabled',
                'area_search_filter',
                'search_filter',
                'map_search_here_enabled',
                'display_freeword',
                'method_setting',
                'houses_id'
            );
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw('new_setting.id as new_estate_setting_id');
            $select->selectRaw($newRow->id);
            $select->join('hp_estate_setting AS old_setting',function($join){
                $join->on('special_estate.hp_estate_setting_id','old_setting.id');
            });
            $select->join('hp_estate_setting AS new_setting',function($join) use ($newRow){
                $join->on('old_setting.setting_for','new_setting.setting_for')
                    ->where('new_setting.hp_id', $newRow->id);
            });

            $select->where('special_estate.hp_id', $this->id);
            $select->where('special_estate.delete_flg', 0);
            $select->where('old_setting.delete_flg', 0);

            $specialEstateTable->insertSelect($select, $cols);

            // 2次広告自動公開設定のコピー
            $cols = array(
                'create_date',
                'update_date',
                'estate_class',
                'enabled',
                'enabled_estate_type',
                'area_search_filter',
                'search_filter',
                'search_filter_for_bapi',
                'hp_id'
            );
            $secondEstateClassSearchTable = App::make(SecondEstateClassSearchRepositoryInterface::class);
            $select = $secondEstateClassSearchTable->getSelect();
            $inserCols = array(
                'create_date',
                'update_date',
                'estate_class',
                'enabled',
                'enabled_estate_type',
                'area_search_filter',
                'search_filter',
                'search_filter_for_bapi'
            );
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->where('delete_flg', 0);
            $select->where('hp_id', $this->id);

            $secondEstateClassSearchTable->insertSelect($select, $cols);

            // 2次広告削除設定のコピー
            $cols = array(
                'company_id',
                'create_date',
                'update_date',
                'name',
                'name_kana',
                'address',
                'nearest_station',
                'tel',
                'member_no',
                'hp_id'
            );
            $secondEstateExclusionTable = App::make(SecondEstateExclusionRepositoryInterface::class);
            $select = $secondEstateExclusionTable->getSelect();
            $inserCols = array(
                'company_id',
                'create_date',
                'update_date',
                'name',
                'name_kana',
                'address',
                'nearest_station',
                'tel',
                'member_no'
            );
            $select = $select->selectRaw(implode(',', $inserCols));
            $select->selectRaw($newRow->id);
            $select->where('hp_id', $this->id);

            $secondEstateExclusionTable->insertSelect($select, $cols);

            if (!$inTransaction) {
                DB::commit();
            }
        } catch (Exception $e) {
            if (!$inTransaction) {
                DB::rollback();
            }
            throw $e;
        }
        return $newRow;
    }

    protected function _updateCopiedReference( $hp_id, $table, $col, $referenceTable)
    {
        $query = "
			UPDATE
				$table				tbl,
				$referenceTable		ref
			SET
				tbl.{$col}			= ref.id
			WHERE
				ref.hp_id			= $hp_id			AND
				tbl.hp_id			= $hp_id			AND
				tbl.{$col}			= ref.copied_id
		" ;

        DB::UPDATE($query);
    }

    public function deleteAll($inTransaction = false)
    {
        $table = \App::make(HpRepositoryInterface::class);

        try {
            if (!$inTransaction) {
                DB::beginTransaction();
            }
    
            $table->delete($this->id, true);
    
            $where = array(['hp_id' , $this->id]);
    
            // サイト画像
            \App::make(HpSiteImageRepositoryInterface::class)->delete($where, true);
            // HP画像・ファイル
            \App::make(HpImageRepositoryInterface::class)->delete($where, true);
            \App::make(HpImageContentRepositoryInterface::class)->delete($where, true, ['aid']);
            \App::make(HpImageCategoryRepositoryInterface::class)->delete($where, true);
            \App::make(HpFileContentRepositoryInterface::class)->delete($where, true, ['aid']);
            // HPページ
            \App::make(HpPageRepositoryInterface::class)->delete($where, true);
    
            // HPページで使用している画像
            \App::make(HpImageUsedRepositoryInterface::class)->delete($where, true);
    
            // トップページメインイメージ
            \App::make(HpTopImageRepositoryInterface::class)->delete($where, true);
    
            // ページ エリア
            \App::make(HpAreaRepositoryInterface::class)->delete($where, true);
    
            // ページ メインパーツのコピー
            \App::make(HpMainPartsRepositoryInterface::class)->delete($where, true);
    
            // ページ メインパーツエレメント
            \App::make(HpMainElementRepositoryInterface::class)->delete($where, true);
    
            // ページ メインパーツエレメントエレメント
            \App::make(HpMainElementElementRepositoryInterface::class)->delete($where, true);
    
            // ページ サイドパーツ
            \App::make(HpSidePartsRepositoryInterface::class)->delete($where, true);
    
            // ページ サイドパーツエレメント
            \App::make(HpSideElementsRepositoryInterface::class)->delete($where, true);
    
            // ページ 問い合わせフォーム
            \App::make(HpContactRepositoryInterface::class)->delete($where, true);
    
            // ページ 問い合わせフォームパーツ
            \App::make(HpContactPartsRepositoryInterface::class)->delete($where, true);
    
            // 物件設定
            \App::make(HpEstateSettingRepositoryInterface::class)->delete($where, true);
    
            // 物件検索設定
            \App::make(EstateClassSearchRepositoryInterface::class)->delete($where, true);
    
            // 特集ページ
            \App::make(SpecialEstateRepositoryInterface::class)->delete($where, true);
    
            // 二次広告設定
            \App::make(SecondEstateClassSearchRepositoryInterface::class)->delete($where, true);
           
            // ２次広告削除データ
            \App::make(SecondEstateExclusionRepositoryInterface::class)->delete($where, true);
    
            \App::make(AssociatedHpPageAttributeRepositoryInterface::class)->delete($where, true);
            if (!$inTransaction) {
                DB::commit();
            }
        } catch (Exception $e) {
            if (!$inTransaction) {
                DB::rollback();
            }
            throw $e;
        }
    }

    public function fetchHtmlContent()
    {
        $row = $this->htmlContent();

        if ($row) {
            return $row->content;
        }
    }

    public function fetchCompanyRow()
    {
        $companyTable = App::make(CompanyRepositoryInterface::class);
        $company = $companyTable->fetchRowByHpId($this->id);

        if (!$company) {
            // falseが変える場合があるので、nullに統一
            return null;
        }

        return $company;
    }

    public function hasCommonSideParts($pc = true)
    {
        $where = array(['hp_id', $this->id], ['page_type_code', HpPageRepository::TYPE_TOP]);
        $row = \App::make(HpPageRepositoryInterface::class)->fetchRow($where);
        if (!$row) {
            return false;
        }
        $where = array(['page_id', $row->id], ['hp_id', $this->id]);
        $order = array('sort');
        $row = \App::make(HpSidePartsRepositoryInterface::class)->fetchAll($where, $order);
        if ($row->count() < 1) {
            return false;
        }
        if ($pc) {
            if (HpSidePartsRepository::isDisplayCommonSideParts($row, $this)) {
                return true;
            }
            return false;
        }

        // SP
        foreach ($row as $parts) {
            if ('0' === $parts->display_flg) {
                continue;
            }
            if ((int)$parts->parts_type_code === HpSidePartsRepository::PARTS_QR) {
                continue;
            } elseif ((int)$parts->parts_type_code === HpSidePartsRepository::PARTS_LINE_AT_QR) {
                continue;
            } elseif ((int)$parts->parts_type_code === HpSidePartsRepository::PARTS_TW && !$this->tw_timeline_flg) {
                continue;
            } elseif ((int)$parts->parts_type_code === HpSidePartsRepository::PARTS_FB && !$this->fb_timeline_flg) {
                continue;
            } elseif ((int)$parts->parts_type_code === HpSidePartsRepository::PARTS_LINE_AT_BTN && !$this->line_at_freiend_button) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * TOPページにQRコード設定があるかどうか
     * @return bool
     */
    public function hasCommonSidePartsQr()
    {
        $topPageRow = \App::make(HpPageRepositoryInterface::class)->getTopPageData($this->id);
        if ($topPageRow === null) {
            return false;
        }
        $qrCodeRow = \App::make(HpSidePartsRepositoryInterface::class)->getPartByPageId($topPageRow->id, HpSidePartsRepository::PARTS_QR);
        if ($qrCodeRow === null) {
            return false;
        }
        return true;
    }

    /**
     * 物件検索設定を取得する
     * @param int $settingFor
     */
    public function getEstateSetting($settingFor = null)
    {
        $table = App::make(HpEstateSettingRepositoryInterface::class);
        return $table->getSetting($this->id, $settingFor);
    }

    public function toAssocBy($colname)
    {
        $assoc = [];
        foreach ($this as $row) {
            $assoc[$row->{$colname}] = $row;
        }
        return $assoc;
    }

    /**
     * 【本番用】の物件検索設定を取得する
     */
    public function getEstateSettingForPublic()
    {
        return $this->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
    }

    /**
     * 【テストサイト用】の物件検索設定を取得する
     */
    public function getEstateSettingForTest()
    {
        return $this->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_TEST'));
    }

    /**
     * 物件検索設定を作成する
     */
    public function createEstateSetting()
    {
        $table = App::make(HpEstateSettingRepositoryInterface::class);
        return $table->createSetting($this->id);
    }

    /**
     * 物件種別毎の二次広告物件検索設定を全て取得する
     */
    public function getSecondSearchSettingAll()
    {
        $table = App::make(SecondEstateClassSearchRepositoryInterface::class);
        return $table->getSettingAll($this->id);
    }

    /**
     * 物件種別毎の二次広告物件検索設定を全て取得する
     */
    public function getSecondSearchSetting($class)
    {
        $table = App::make(SecondEstateClassSearchRepositoryInterface::class);
        return $table->getSetting($this->id, $class);
    }

    /**
     * 二次広告物件検索設定を取得する
     */
    public function getSecondSearchSettingRow()
    {
        $table = \App::make(SecondEstateClassSearchRepositoryInterface::class);
        return $table->getSettingRow($this->id);
    }

    /**
     * 物件種別毎の二次広告物件検索設定を保存する
     * @param Library\Custom\Estate\Setting\Second $setting
     */
    public function saveSecondSearchSetting($setting)
    {
        $table = App::make(SecondEstateClassSearchRepositoryInterface::class);
        return $table->saveSetting($this->id, $setting);
    }

    /**
     * 自身が使用しているCMS側の容量の算出をする
     *
     */
    public function capacityCalculation()
    {

        $capacity = 0;

        //画像フォルダ
        $obj =  App::make(HpImageContentRepositoryInterface::class);
        $capacity = (int)$capacity + (int)$obj->getCapacity($this->id);

        // ファイル管理
        $obj =  App::make(HpFile2ContentRepositoryInterface::class);
        $capacity = (int)$capacity + (int)$obj->getCapacity($this->id);

        //faviconとか
        $obj = App::make(HpSiteImageRepositoryInterface::class);
        $capacity = (int)$capacity + (int)$obj->getCapacity($this->id);

        //各ページに紐付いているファイルから算出
        $obj = App::make(HpFileContentRepositoryInterface::class);
        $capacity = (int)$capacity + (int)$obj->getCapacity($this->id);

        //追加されるファイル容量をチェック
        if (isset($_FILES) && count($_FILES) > 0) {
            foreach ($_FILES as $key => $file) {
                if ($file['size'] > 0) $capacity = (int)$capacity + (int)$file['size'];
            }
        }
        return number_format($capacity / 1024 / 1024, 1, ".", "");
    }

    /**
     * 代行作成用のデータコピー
     * - 加盟店→代行作成）
     *
     * @return App\Models\Model
     */
    public function copyAllForCompanyToCreator($isTop = false)
    {

        $newHp = $this->copyAll();
        // 物件設定取得
        $setting = $newHp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        try {
            DB::beginTransaction();
            if ($setting) {

                // copyAllで作成した不要なレコード削除

                // 物件設定
                $table = App::make(HpEstateSettingRepositoryInterface::class);
                $where = [
                    ['hp_id'     , $newHp->id],

                    'whereNotIn' => ['id', [$setting->id]] // 編集中データ以外削除
                ];
                $table->delete($where, true);
                // 物件検索設定
                $table = App::make(EstateClassSearchRepositoryInterface::class);
                $where = [
                    ['hp_id'                       , $newHp->id],

                    'whereNotIn' => ['hp_estate_setting_id', [$setting->id]] // 編集中データ以外削除
                ];
                $table->delete($where, true);
            }
            if ($isTop) {
                $oldHp = $this;
                Original::cloneData($newHp, $oldHp);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $newHp;
    }

    /**
     * 代行作成用のデータコピー
     * - 代行作成→加盟店）
     * @param boolean $isTop
     * @return App\Models\Model
     * @throws
     */
    public function copyAllForCreatorToCompany($isTop = false)
    {
        $newHp = $this->copyAll();
        $setting = $newHp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $table   = \App::make(HpRepositoryInterface::class);
        try {
            DB::beginTransaction();
            if ($setting) {

                // 加盟店のho取得
                $table  = \App::make(AssociatedCompanyHpRepositoryInterface::class);
                $where = [['space_hp_id', $this->id]];

                $row = $table->fetchRow($where);
                if (!$row) {
                    throw new Exception('foo');
                }
                $companyHpId = $row->current_hp_id;

                $table = \App::make(HpRepositoryInterface::class);;

                $where = [['id', $companyHpId]];
                $companyHp = $table->fetchRow($where);

                // 不要なデータ削除

                // 物件設定
                $table = \App::make(HpEstateSettingRepositoryInterface::class);
                $where = [
                    ['hp_id'     , $newHp->id],

                    'whereNotIn' => ['id', [$setting->id]] // 編集中データ以外削除
                ];
                $table->delete($where, true);

                // 物件検索設定
                $table = \App::make(EstateClassSearchRepositoryInterface::class);
                $where = [
                    ['hp_id'                       , $newHp->id],

                    'whereNotIn' => ['hp_estate_setting_id', [$setting->id]] // 編集中データ以外削除
                ];
                $table->delete($where, true);

                // 加盟店のテストサイトor本番の物件設定データあればコピー
                $list = [
                    config('constants.hp_estate_setting.SETTING_FOR_TEST'),
                    config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'),
                ];
                foreach ($list as $settingFor) {
                    $setting = $companyHp->getEstateSetting($settingFor);
                    if ($setting) {

                        $data = $setting->toArray();
                        unset($data['id']);
                        $data['hp_id'] = $newHp->id;

                        $table  = \App::make(HpEstateSettingRepositoryInterface::class);
                        $newRow = $table->model();
                        $newRow->setFromArray($data);
                        $newRow->save();

                        $newSettingId = $newRow->id;

                        foreach ($setting->getSearchSettingAll() as $row) {

                            $data = $row->toArray();
                            unset($data['id']);
                            $data['hp_id']                = $newHp->id;
                            $data['hp_estate_setting_id'] = $newSettingId;

                            $table  = \App::make(EstateClassSearchRepositoryInterface::class);
                            $newRow = $table->model();
                            $newRow->setFromArray($data);
                            $newRow->save();
                        }
                    }
                }
            }
            if ($isTop) {
                $oldHp = $this;
                Original::cloneData($newHp, $oldHp);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $newHp;
    }

    /**
     * 全公開フラグのON/OFF切り替え時にその要因(all_upload_parts)を個別設定する
     * @param string $tag (ALL, initial, design, topside)
     * @return (none)
     */
    public function setAllUploadParts($tag, $val)
    {
        $all_upload_parts = json_decode($this->all_upload_parts);
        switch ($tag) {
            case 'ALL':
                if (!$all_upload_parts) {
                    return;
                }
                foreach($all_upload_parts as $k => $v) {
                    $all_upload_parts->{$k} = $val;
                }
                break;
            default:
                if (!isset($all_upload_parts->{$tag})) {
                    return;
                }
                $all_upload_parts->{$tag} = $val;
                break;
        }
        $this->all_upload_parts = json_encode($all_upload_parts, true);
    }

    /**
     * Find Global Navigation Pages
     * @param int $limit
     * @return App\Collections\CustomCollection
     */
    public function getGlobalNavigation()
    {
        $table = HpPage::where('hp_id', $this->id);
        $table->where('parent_page_id', 0);
        $table->orderBy('sort');
        $table->offset(0)->limit($this->global_navigation);
        return $table->get();
    }

    public function createTemplateRealEstatePage($page, $template, $sampleImageMap)
    {
        if ($page['page_category_code'] != HpPageRepository::CATEGORY_ARTICLE) {
            $this->createTemplateSetAutoLink($page, $template, 0);
        } else {
            $this->createTemplateArticle($page, $template, $sampleImageMap);
            $this->createTemplateSetAutoLink($page, $template, 1);
        }
    }

    public function createTemplateSetAutoLink($page, $template, $sort)
    {
        $type = $page['page_type_code'];
        if (!isset($template[$type])) {
            $type = 'patern1';
        }
        $hpArea = App::make(HpAreaRepositoryInterface::class)->create(array(
            'hp_id'             => $this->id,
            'page_id'           => $page->link_id,
            'column_type_code'  => 1,
            'sort'              => $sort
        ));
        $hpArea->save();
        $hpMainParts = App::make(HpMainPartsRepositoryInterface::class)->create(array(
            'hp_id'             => $this->id,
            'page_id'           => $page->link_id,
            'area_id'           => $hpArea->id,
            'parts_type_code'   => HpMainPartsRepository::PARTS_SET_LINK_AUTO,
            'sort'              => 0,
            'column_sort'       => 1
        ));
        $hpMainParts->save();
        $leadContact = array('lead', 'contact');
        if ($sort) {
            $leadContact = array('contact');
        }
        foreach ($leadContact as $key => $name) {
            if ($name == 'lead') {
                $attr_1 = $template[$type][$name];
            } else {
                $attr_1 = 1;
            }
            $HpMainElement = App::make(HpMainElementRepositoryInterface::class)->create(array(
                'type'      => $name,
                'hp_id'     => $this->id,
                'page_id'   => $page->link_id,
                'parts_id'  => $hpMainParts->id,
                'sort'      => $key,
                'attr_1'    => $attr_1
            ));
            $HpMainElement->save();
        }
    }

    public function createTemplateArticle($page, $template, $sampleImageMap)
    {
        $type = $page['page_type_code'];
        if (!isset($template[$type])) {
            $type = 'patern1';
        }
        $hpArea = App::make(HpAreaRepositoryInterface::class)->create(array(
            'hp_id'             => $this->id,
            'page_id'           => $page->link_id,
            'column_type_code'  => 1,
            'sort'              => 0
        ));
        $hpArea->save();
        $partsTypeCode = HpMainPartsRepository::PARTS_ARTICLE_TEMPLATE;
        $elementType = 'articles';
        if ($page['page_type_code'] == HpPageRepository::TYPE_ARTICLE_ORIGINAL) {
            $partsTypeCode = HpMainPartsRepository::PARTS_ORIGINAL_TEMPLATE;
            $elementType = 'original';
        }
        $hpMainParts = App::make(HpMainPartsRepositoryInterface::class)->create(array(
            'hp_id'             => $this->id,
            'page_id'           => $page->link_id,
            'area_id'           => $hpArea->id,
            'parts_type_code'   => $partsTypeCode,
            'sort'              => 0,
            'column_sort'       => 1,
            'attr_1'            => isset($sampleImageMap[$template[$type]['image']]) ? $sampleImageMap[$template[$type]['image']] : null,
            'attr_2'            => $template[$type]['image_title'] != '' ?  $template[$type]['image_title'] : null,
            'attr_3'            => $template[$type]['description'],
        ));
        $hpMainParts->save();
        if (isset($sampleImageMap[$template[$type]['image']])) {
            $hpImageUsed = App::make(HpImageUsedRepositoryInterface::class)->create(array(
                'hp_page_id'        => $page->link_id,
                'hp_image_id'       => $sampleImageMap[$template[$type]['image']],
                'hp_id'             => $this->id,
            ));
            $hpImageUsed->save();
        }
        if (isset($sampleImageMap[$template[$type]['image']])) {
            $hpImageUsed = App::make(HpImageUsedRepositoryInterface::class)->create(array(
                'hp_page_id'        => $page->link_id,
                'hp_image_id'       => $sampleImageMap[$template[$type]['image']],
                'hp_id'             => $this->id,
            ));
            $hpImageUsed->save();
        }
        foreach ($template[$type]['elements'] as $key => $elements) {
            $attr_1 = $elements['title'];
            $elementData = array(
                'type'      => $elementType,
                'hp_id'     => $this->id,
                'page_id'   => $page->link_id,
                'parts_id'  => $hpMainParts->id,
                'sort'      => $key,
                'attr_1'    => $attr_1
            );
            if ($page['page_type_code'] == HpPageRepository::TYPE_ARTICLE_ORIGINAL) {
                $attr_2 = isset($elements['description']) ? $elements['description'] : null;
                $attr_3 = isset($elements['image']) ? $sampleImageMap[$elements['image']] : null;
                $attr_4 = isset($elements['image_title']) ? $elements['image_title'] : null;
                $elementData = array(
                    'type'      => $elementType,
                    'hp_id'     => $this->id,
                    'page_id'   => $page->link_id,
                    'parts_id'  => $hpMainParts->id,
                    'sort'      => $key,
                    'attr_1'    => $attr_1,
                    'attr_2'    => $attr_2,
                    'attr_3'    => $attr_3,
                    'attr_4'    => $attr_4
                );
            }
            $hpMainElement = App::make(HpMainElementRepositoryInterface::class)->create($elementData);
            $hpMainElement->save();
            $i = 0;
            foreach ($elements['element'] as $index => $element) {
                $index = explode(' ', $index)[0];
                $data = array(
                    'type'      => $index,
                    'hp_id'     => $this->id,
                    'page_id'   => $page->link_id,
                    'parts_id'  => $hpMainElement->id,
                    'sort'      => $i,
                );
                $i++;
                switch ($index) {
                    case 'text':
                        $data['attr_1'] = $element['description'];
                        break;
                    case 'image':
                    case 'image_text':
                        $data['attr_1'] = isset($sampleImageMap[$element['image']]) ? $sampleImageMap[$element['image']] : null;
                        $data['attr_2'] = $element['image_title'] != '' ?  $element['image_title'] : null;
                        $data['attr_3'] = HpMainPartsRepository::OWN_PAGE;
                        $data['attr_6'] = 1;
                        $data['attr_9'] = 0;
                        if (isset($sampleImageMap[$element['image']])) {
                            $hpImageUsed = App::make(HpImageUsedRepositoryInterface::class)->create(array(
                                'hp_page_id'        => $page->link_id,
                                'hp_image_id'       => $sampleImageMap[$element['image']],
                                'hp_id'             => $this->id,
                            ));
                            $hpImageUsed->save();
                        }
                        if (isset($element['file2'])) {
                            $file2s = App::make(HpFile2RepositoryInterface::class)->fetchRow(array(['sys_name' , $element['file2']], ['hp_id' , $this->id]));
                            if ($file2s) {
                                $hpFile2Used = App::make(HpFile2UsedRepositoryInterface::class)->create(array(
                                    'hp_page_id'        => $page->link_id,
                                    'hp_file2_id'       => $file2s->id,
                                    'hp_id'             => $this->id,
                                ));
                                $hpFile2Used->save();
                                $data['attr_3'] = HpMainPartsRepository::FILE;
                                $data['attr_7'] = $file2s->id;
                                $data['attr_9'] = 1;
                            }
                        }
                        break;
                }
                if ($index == 'image_text') {
                    $data['attr_10'] = $element['description'];
                }
                $hpMainElement2 = App::make(HpMainElementElementRepositoryInterface::class)->create($data);
                $hpMainElement2->save();
            }
        }
    }

    public function updateChangeSetLink($type)
    {
        \App::make(HpRepositoryInterface::class)->update($this->id, array('change_set_link' => $type));
    }

    public function hasChangedArticle()
    {
        if ($this->all_upload_flg) {
            return true;
        }
        // 差分あり && !新規
        $table = App::make(HpPageRepositoryInterface::class);
        return $table->countRows(array(
            ['hp_id', $this->id], ['diff_flg', 1], ['new_flg', 0],
            'whereIn' =>
            ['page_category_code', $table->getCategoryCodeArticle()],
        )) > 0;
    }
}
