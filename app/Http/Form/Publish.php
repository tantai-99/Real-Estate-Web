<?php
namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use Library\Custom\Publish\Prepare;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use Library\Custom\Publish\Estate\Prepare\Simple as Estate_Prepare_Simple;
use Library\Custom\Publish\Special\Prepare\Validation as Special_Prepare_Validation;
use Library\Custom\Publish\Special\Make\Rowset as Special_Make_Rowset;

class Publish extends Form {

    const NOW = 0;

    const MUST_LIST_AND_DETAIL     = "一覧ページと詳細ページは合わせて公開もしくは非公開（下書き）にしてください。<br>どちらかのページのみ公開もしくは非公開（下書き）にすることはできません。";
    const MUST_PARENT_AND_CHILDREN = "上層のページと下層のページは合わせて公開もしくは非公開（下書き）にしてください。<br>どちらかのページのみ公開もしくは非公開（下書き）にすることはできません。";
    const MUST_PARENT_ARTICLE = "配下の記事が選択されているため、以下一覧ページも「公開（更新）／非公開（下書き）」にする必要があります。<br>OKボタンを押下すると対象ページに自動的にチェックが入ります。";
    const ERROR_ARTICLE_TOP = '関連するいずれかのページが未選択です。「変更」ボタンから公開したいカテゴリーと記事を選択してください。';

    private $page;
    private $hpId;
    private $params;
    public  $_errors;

    protected $hpPageRepository;

    public function init() {

        $this->page = new Prepare\Page($this->hpId, $this->params);

        $this->hpPageRepository = \App::make(HpPageRepositoryInterface::class);

        foreach ($this->page->getPages() as $page) {

            // 自動公開ページ
            $autoPublish = $page['public_flg'] && ($this->hpPageRepository->hasPagination($page['page_type_code']));

            // checkbox
            $name = 'update';
            $elem = new Element\Checkbox("page_{$page['id']}_{$name}");
            $elem->setAttribute('name', "page[{$page['id']}][{$name}]");
            $elem->setAttribute('id', "page_{$page['id']}_{$name}");
            $elem->setAttribute('class', "{$name}_flg");

            $value = false;
            if (getActionName() === 'simple') {
                $value = $page['label'] !== 'no_diff';
            }
            if (getActionName() === 'detail') {
                $value = $autoPublish;
            }
            $elem->setChecked($value);

            $this->add($elem);

            // hidden
            foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name) {

                $elem = new Element\Hidden("page_{$page['id']}_{$name}");
                $elem->setAttribute('id', "page_{$page['id']}_{$name}");
                $elem->setAttribute('name', "page[{$page['id']}][{$name}]");
                $elem->setAttribute('class', $name);

                $value = 0;
                if ($name === 'new_release_flg') {
                    $value = false;
                    if (getActionName() === 'simple') {
                        $value = true;
                    }
                    if (getActionName() === 'detail') {
                        $value = $autoPublish;
                    }
                }
                $elem->setValue($value);

                $this->add($elem);
            }
        }

        $name = 'submit_from';
        $elem = new Element\Hidden($name);
        $elem->setAttribute('name', $name);
        $elem->setValue(getActionName());
        $this->add($elem);
    }

    public function setHpId($hpId) {

        $this->hpId = $hpId;
    }

    public function setParams($params) {

        $this->params = $params;
    }

    private function setError($id, $key, $msg) {

        $this->_errors[$id][$key] = [$msg];
    }

    public function isValid($params, $checkError = true) {
        //custom Lite isValid
        $validateNoPlanLite = getInstanceUser('cms')->getProfile()->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE');
        $validateFlg = parent::isValid($params);

        if (!$this->isChecked($params)) {
            return false;
        }

        //fixed PUBLISH LITE
        if ($validateNoPlanLite && !$this->validateSearchContact()) {
            $validateFlg = false;
        }

        //物件リクエスト
        if (!$this->validateRequestContact()) {
            $validateFlg = false;
        }

        $pages = $this->page->getPages();

        $reserveListPage    = $this->page->getReserveListForValidation($params);
        $reserveListSpecial = (new Special_Prepare_Validation($this->page->getHpRow()))->reserveList($params);

        $releaseAtList = array_unique(array_merge([self::NOW], array_keys($reserveListPage), array_keys($reserveListSpecial)));
        asort($releaseAtList);

        $reserveList = array_replace_recursive($reserveListPage, $reserveListSpecial);
        ksort($reserveList);

        $isSimple = isset($params['submit_from']) && $params['submit_from'] == 'simple';
        foreach ($releaseAtList as $datetime) {

            $pages          = $this->page->getNewPages($pages, $datetime, $params);
            $publicPages    = $this->page->filterDraftPages($pages);
            $newPublicPages = $this->page->filterNoEntityPages($publicPages);

            if (!isset($params['clickBtn']) || (isset($params['clickBtn']) && $params['clickBtn'] != 'setting-publish-article')) {
                //more than PlanLite
                if ($validateNoPlanLite) {
                    // 特集 初期化
                    Special_Make_Rowset::getInstance()->init($this->page->getHpRow(), $this->params, $datetime, $reserveList);
                }
                //必須ページ
                if (!$this->isRequiredPage($pages)) {
                    return false;
                }

                //一覧ページ
                if (!$this->isValidIndexPage($newPublicPages)) {
                    $validateFlg = false;
                }

                // 会員専用ページ
                if (!$this->isValidMemberOnly($publicPages)) {
                    $validateFlg = false;
                }

                // 詳細ページ
                if (!$this->isValidDetailPage($newPublicPages)) {
                    $validateFlg = false;
                }

                // Check change status page global navigation public -> private
                $isTopOriginal = getInstanceUser('cms')->checkHasTopOriginal();
                if ($isTopOriginal) {
                    if (!$this->isPrivateOriginalPage($params)) {
                        $validateFlg = false;
                    }
                }

                foreach ($pages as $page) {

                    // 未作成ページ
                    if (!$this->unfinishedPage($page)) {
                        $validateFlg = false;
                    }

                    // パス
                    if (!$this->isValidNewPath($page, $publicPages)) {
                        $validateFlg = false;
                    }

                    // エイリアス &&
                    // リンク元が公開中 || リンク元のアップデートと同じタイミングでアップデートしている
                    if ($page['page_type_code'] == HpPageRepository::TYPE_ALIAS && (!$this->isPublicAliasOriginalPage($page, $newPublicPages, $isSimple) || !$this->updateAlias($page, $newPublicPages, $datetime))) {
                        $validateFlg = false;
                    }

                    // 物件検索、特集リンク
                    if ($validateNoPlanLite && $page['page_type_code'] == HpPageRepository::TYPE_ESTATE_ALIAS && !$this->checkEstateLink($page)) {

                        $validateFlg = false;
                    }
                }
            } else {
                foreach ($pages as $page) {
                    // パス
                    if ($page['page_type_code'] == HpPageRepository::TYPE_ALIAS && $page['link_article_flg'] && !$this->isValidNewPath($page, $publicPages, true)) {
                        $validateFlg = false;
                    }
                    // エイリアス &&
                    // リンク元が公開中 || リンク元のアップデートと同じタイミングでアップデートしている
                    if ($page['page_type_code'] == HpPageRepository::TYPE_ALIAS && $page['link_article_flg'] && (!$this->isPublicAliasOriginalPage($page, $newPublicPages, $isSimple) || !$this->updateAlias($page, $newPublicPages, $datetime))) {
                        $validateFlg = false;
                    }
                }
            }
            if ($validateFlg && !$this->isValidArticle($pages, $publicPages)) {
                if(!isset($params['submit_from']) || $params['submit_from'] != 'simple') {
                    $this->setError('page_article_update', 'parentNone_article', self::ERROR_ARTICLE_TOP);
                }
                $validateFlg = false;
            }
        }

        // pageなければ（specialのみ）return
        if (!isset($params['page'])) {

            return $validateFlg;
        }

        // ATHOME_HP_DEV-3126
        if($this->page->getHpRow()->all_upload_flg && (!isset($params['clickBtn']) || (isset($params['clickBtn']) && $params['clickBtn'] != 'setting-publish-article'))) {
            // ページ&特集で 『修正反映日：反映しない』『 公開停止日：停止しない』はエラーにする
            $notreleas = [ 
                'page'    => [],
                'special' => [] 
            ];

            // ページ一覧
            foreach($this->page->getPages() as $page) {
                if($page['public_flg'] != 1) {
                    continue;
                }
                // 公開中ページのみチェックする
                if(isset($params['page'][$page['id']])) {
                    // 簡易の場合は渡されない？？
                    $pg_param = $params['page'][ $page['id'] ];
                    if($pg_param['new_release_flg'] != 1 && empty($pg_param['new_close_flg'])) {
                        $notreleas['page'][] = [ 'page_id' => $page['id'], 'title' => $page['title'] ];
                    }
                }
            }

            // 特集一覧
            if(!$isSimple) {
                // 特集については『簡易』の場合、new_release_flg, new_close_flg が渡されない？
                $settingCms = $this->page->getHpRow()->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));

                if($settingCms instanceof \App\Models\HpEstateSetting) {
                    $specials = $settingCms->getSpecialAllWithPubStatus();
                    foreach($specials as $special) {
                        if($special->is_public != 1 || $special->delete_flg != 0) {
                            continue;
                        }
                        // 公開中特集のみチェックする
                        if(isset($params['special'][$special->id])) {
                            $sp_param = $params['special'][$special['id']];
                            if($sp_param['new_release_flg'] != 1 && empty($sp_param['new_close_flg'])) { 
                                $notreleas['special'][] = [ 'special_id' => $special['id'], 'title' => $special['title'] ];
                            }
                        }
                    }
                }
            }

            if(count($notreleas['page']) || count($notreleas['special'])) {
                $error_msg = '公開中のすべてのページに「共通設定」の変更を反映する必要があるため、「修正反映日：反映しない」を選択することができません。「修正反映日：更新後すぐ」に変更してください。';
                $page_error_msg    = '「修正反映日：更新後すぐ」に変更してください。';
                $validateFlg = false;
                $this->setError('error-top', 'notreleas', $error_msg);

                // ページのエラー
                if(count($notreleas['page'])) {
                    foreach($notreleas['page'] as $error_page) {
                        $this->setError('error-top', 'pageid_' . $error_page['page_id'], $page_error_msg);
                    }
                }
                // 特集のエラー
                if(count($notreleas['special'])) {
                    foreach($notreleas['special'] as $error_page) {
                        $this->setError('error-top', 'specialid_' . $error_page['special_id'], $page_error_msg);
                    }
                }
            }
        }

        return $validateFlg;
    }

    private function checkEstateLink($page) {

        if (!$page['public_flg']) {

            return true;
        }

        $special = Special_Make_Rowset::getInstance();

        // 物件検索トップ
        $prefix = 'estate_top';
        $prefix_rent = 'estate_rent';
        $prefix_purchase = 'estate_purchase';
        if (preg_match("/^$prefix/", $page['link_estate_page_id']) || preg_match("/^$prefix_rent/", $page['link_estate_page_id']) || preg_match("/^$prefix_purchase/", $page['link_estate_page_id'])) {

            $estateSetting = $this->page->getHpRow()->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));

            if ($estateSetting) {
                return true;
            }

            $msg = 'リンク元のページ非公開です';
            $this->setError('page_'.$page['id'].'_update', 'notPublicOriginalPage', $msg);

            return false;
        };

        // 物件検索
        $prefix = 'estate_type_';
        if (preg_match("/^$prefix/", $page['link_estate_page_id'])) {

            $estateSetting = $this->page->getHpRow()->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));

            if ($estateSetting && $estateSetting->getSearchSettingByEstateType((int)str_replace($prefix, '', $page['link_estate_page_id']))) {
                return true;
            }

            $msg = 'リンク元のページ非公開です';
            $this->setError('page_'.$page['id'].'_update', 'notPublicOriginalPage', $msg);

            return false;
        };

        // 特集
        $prefix = 'estate_special_';
        if (preg_match("/^$prefix/", $page['link_estate_page_id'])) {

            $row = $special->filterRowByOriginId(str_replace($prefix, '', $page['link_estate_page_id']));
            if ($row && $row->is_public) {
                return true;
            }

            $msg = 'リンク元のページ非公開です';
            $this->setError('page_'.$page['id'].'_update', 'notPublicOriginalPage', $msg);

            return false;
        };

        return false;
    }

    /**
     * リンク元同じタイミングでアップデートしているか
     *
     * @param $page
     * @param $pages
     * @return bool
     */
    private function updateAlias($page, $pages) {

        $validateFlg = true;

        if (!$page['public_flg']) {

            return $validateFlg;
        }

        $datetime = null;

        foreach ($pages as $parent) {

            if ($parent['link_id'] == $page['link_page_id']) {

                // リンク元の更新タイミング取得
                $datetime = $this->releaseDatetime($parent['id']);

                // リンク元が更新される && リンク元がすでに公開中 && パスが変更になっている場合はリンクをリンク元と同じタイミングで更新
                if (!is_null($datetime) && !is_null($parent['public_path']) && $parent['public_path'] != $parent['new_path'] && $datetime != $this->releaseDatetime($page['id'])) {

                    $validateFlg = false;
                }
                break;
            }
        }

        if (!$validateFlg) {

            $msg = 'リンクを更新してください';
            if ($datetime > 0) {
                $msg = 'リンク元と同じ日時に更新してください';
            }
            $this->setError('page_'.$page['id'].'_update', 'mustUpdateAlias', $msg);
        }

        return $validateFlg;
    }

    /**
     * 更新タイミングを取得
     *
     * @param $id
     * @return mixed
     */
    private function releaseDatetime($id) {
        if(isset($this->params['page'])){
            foreach ($this->params['page'] as $page_id => $param) {

                if ($id == $page_id && $param['update'] && $param['new_release_flg']) {

                    return $param['new_release_at'] === '' ? '0' : $param['new_release_at'];
                }
            }
        }
    }

    /**
     * エイリアス元が公開中か
     *
     * @param $page
     * @param $pages
     * @param $isSimple
     *
     * @return bool
     */
    private function isPublicAliasOriginalPage($page, $pages, $isSimple) {

        if (!$page['public_flg']) {

            return true;
        }

        // 物件お問い合わせはスルー
        $row   = $this->hpPageRepository->fetchRow([['link_id', $page['link_page_id']]]);
        if ($row && in_array($row->page_type_code, $this->hpPageRepository->estateContactPageTypeCodeList())) {
            return true;
        }

        // 通常ページ
        $validateFlg = false;

        foreach ($pages as $checkTarget) {

            if ($checkTarget['link_id'] == $page['link_page_id']) {
                $validateFlg = true;
            }
        }

        if (!$validateFlg) {
            if (gettype($page['public_flg']) == 'boolean') {
                if ($isSimple && ($this->hpPageRepository->getCategoryUsefulEstate($row->page_type_code) != null)) {
                    $msg = 'リンクを公開する場合、リンク元のページも合わせて公開する必要があります。<br>詳細設定にて設定を変更してください。';
                } else {
                    $msg = 'このリンクを公開する場合、リンク元のページも合わせて公開する必要があります。';
                }
            } else {
                $msg = 'リンク元のページを非公開（下書き）にする場合、リンクも下書きに変更する必要があります。';
            }
            $this->setError('page_'.$page['id'].'_update', 'notPublicOriginalPage', $msg);
        }

        return $validateFlg;
    }

    /**
     * 更新するページが選択されているか
     *
     * @param $params
     *
     * @return bool
     */
    private function isChecked($params) {

        // 通常ページ
        if (isset($params['page'])) {

            foreach ($params['page'] as $val) {

                if (isset($val) && $val['update']) {
                    return true;
                }
            }
        }

        // 特集
        if (isset($params['special'])) {
            foreach ($params['special'] as $val) {

                if (isset($val) && $val['update']) {
                    return true;
                }
            }
        }

        // 全上げフラグON && 公開中のHPはチェックなくてもOK
        $hp    = $this->page->getHpRow();
        $cnt   = $this->hpPageRepository->countRows([['hp_id', $hp->id], ['public_flg', 1]]);

        if ($hp->all_upload_flg && $cnt > 0) {
            return true;
        }

        // 物件検索設定の差分あり && 公開中のHPはチェックなくてもOK
        $instance = new Estate_Prepare_Simple($hp);
        if ($instance->isDisplayEstateSetting() && $cnt > 0){
           return true;
        }

        $msg = 'ページを選択してください';
        $this->setError('error-top', 'isChecked', $msg);
        return false;
    }

    /**
     * 未作成ページのチェック
     * - 未作成のページはリストに表示されないので不要？
     *
     */
    private function unfinishedPage($page) {

        if (!$page['public_flg'] || !$page['new_flg']) {

            return true;
        }

        $msg = '未作成のページは公開できません';
        $this->setError('page_'.$page['id'].'_update', 'unfinished', $msg);
        return false;
    }

    /**
     * 必須ページを含むか
     */
    private function isRequiredPage($pages) {

        // 公開必須ページ
        $required = $this->hpPageRepository->getRequiredPageList();

        // サイトマップを除く
        $key = array_search(HpPageRepository::TYPE_SITEMAP, $required);
        unset($required[$key]);

        foreach ($pages as $page) {

            if (!$page['public_flg']) {
                continue;
            }

            $key = array_search($page['page_type_code'], $required);

            if ($key === false) {
                continue;
            }

            unset($required[$key]);
        }

        if (count($required) > 0) {
            $this->setError('error-top', 'required', '必須ページを公開してください');
            foreach ($required as $code) {

                $this->setError('error-top', 'required_'.$code, ' ・'.$this->hpPageRepository->getTypeNameJp($code));
            }
            return false;
        }

        return true;
    }

    /**
     * 公開、下書きが設定されているか
     *
     * @param $params
     *
     * @return bool
     */
    private function isSetDetail($params) {

        $validateFlg = true;

        foreach ($params['page'] as $id => $val) {
            if (isset($val) && $val['update'] && (!$val['new_release_flg'] && !$val['new_close_flg'])) {

                $validateFlg = false;
                $this->setError('page_'.$id.'_update', 'notSetDetail', '「更新後」を設定してください');
            }
        }

        return $validateFlg;
    }

    /**
     * 更新後のPathチェック
     *
     * @param $pages
     * @param $page
     *
     * @return bool
     */
    public function isValidNewPath($page, $publicPages, $flag = false) {

        $validateFlg = true;

        // エイリアス、リンク
        switch ($page['page_type_code']) {
            case HpPageRepository::TYPE_ALIAS:
            case HpPageRepository::TYPE_LINK:
            case HpPageRepository::TYPE_LINK_HOUSE:
            case HpPageRepository::TYPE_ESTATE_ALIAS:

                if (!$page['public_flg'] || $page['level'] == 1) {
                    return true;
                }

                // 親ページが存在するか
                $hasError = true;
                foreach ($publicPages as $publicPage) {
                    if ($publicPage['id'] == $page['parent_page_id']) {
                        $hasError = false;
                    }
                }

                if (!$validateFlg = !$hasError) {
                    if ($this->hpPageRepository->isLinkArticle($page) && !$flag){
                        $node = 'parentNone_article';
                        $id = 'article';
                    } else {
                        $node = 'parentNone';
                        $id = $page['id'];
                    }
                    $this->setError('page_'.$id.'_update', $node, self::MUST_PARENT_AND_CHILDREN);
                }
                return $validateFlg;

            default:
                // 親ページが存在するか
                if ($page['public_flg'] && !$this->existParent($publicPages, $page['new_path'], $page['filename'], $page['level'])) {
                    $validateFlg = false;

                    $this->setError('page_'.$page['id'].'_update', 'parentNone', self::MUST_PARENT_AND_CHILDREN);
                }

                if ($page['public_flg']) {
                    return $validateFlg;
                }

                // 下階層にページが残ってしまわないか
                if ($this->remainChildren($publicPages, $page)) {
                    $validateFlg = false;
                    $this->setError('page_'.$page['id'].'_update', 'remainChildren', self::MUST_PARENT_AND_CHILDREN);
                }

                return $validateFlg;
        }
    }

    /**
     * 詳細ページのバリデーション
     *
     * @param $publicPages
     *
     * @return bool
     */
    private function isValidDetailPage($publicPages) {

        $validate = true;

        $detail = $this->hpPageRepository->getDetailPageTypeList();

        foreach ($publicPages as $page) {

            $hasError = true;

            $isDetail = false;

            if (in_array($page['page_type_code'], $detail)) {
                $isDetail = true;
            }
            if (!$isDetail) {
                continue;
            }

            // 階層外の詳細ページは公開可能
            if ($page['level'] == 1 && is_null($page['parent_page_id'])) {
                continue;
            }

            foreach ($publicPages as $publicPage) {
                if ($publicPage['id'] == $page['parent_page_id']) {
                    $hasError = false;
                }
            }

            if ($hasError) {
                $this->setError('page_'.$page['id'].'_update', 'parentNone', self::MUST_LIST_AND_DETAIL);
                $validate = false;
            }
        }

        return $validate;
    }

    /**
     * 一覧、詳細ページのバリデーションチェック
     *
     * @param $pages
     *
     * @return bool[
     */
    private function isValidIndexPage($publicPages) {

        $validateFlag = true;

        // 一覧ページ
        $list = $this->hpPageRepository->getHasDetailPageTypeList();

        foreach ($publicPages as $page) {

            $hasError = true;

            $isIndex = false;

            if (in_array($page['page_type_code'], $list)) {
                $isIndex = true;
            }

            if (!$isIndex) {
                continue;
            }

            foreach ($publicPages as $publicPage) {
                if ($publicPage['parent_page_id'] == $page['id']) {
                    $hasError = false;
                    break;
                }
            }

            if ($hasError) {
                $validateFlag = false;
                $this->setError('page_'.$page['id'].'_update', 'listPageNone', self::MUST_LIST_AND_DETAIL);
            }
        }

        return $validateFlag;
    }

    /**
     * 会員専用ページのバリデーションチェック
     *
     * @param $publicPages
     * @return bool
     */
    private function isValidMemberOnly($publicPages) {

        $validateFlag = true;

        $table = $this->hpPageRepository;

        foreach ($publicPages as $page) {

            if ($page['page_type_code'] != HpPageRepository::TYPE_MEMBERONLY) {

                continue;
            }

            $hasChildren = false;

            foreach ($publicPages as $publicPage) {
                if ($publicPage['parent_page_id'] == $page['id']) {
                    $hasChildren = true;
                    break;
                }
            }

            if (!$hasChildren) {

                $validateFlag = false;

                $msg = '会員さま専用ページの下にページを作成してください';
                $this->setError('page_'.$page['id'].'_update', 'listPageNone', $msg);
                continue;
            }

            $hasError = true;

            // 直下のページは公開必須 && リンク不可
            $children = [];
            foreach ($publicPages as $publicPage) {
                // 配下のページ抽出
                if ($publicPage['parent_page_id'] == $page['id']) {
                    $children[$publicPage['sort']] = $publicPage;
                }
            }
            ksort($children);
            if ($table->hasEntity(array_shift($children)['page_type_code'])) {
                $hasError = false;
            }

            if ($hasError) {
                $validateFlag = false;

                $msg = '会員さま専用ページの直下のページは公開必須です。また、リンクは設定できません。';
                $this->setError('error-top', 'childrenMustHasEntity', $msg);
            }
        }

        return $validateFlag;
    }

    /**
     * 子ディレクトリが残ってしまわないかチェック
     *
     * @param $pages
     * @param $publicPath
     *
     * @return bool
     */
    private function remainChildren($publicPages, $page) {

        if (empty($page['public_path']) || $page['public_path'] == 'index.html') {
            return false;
        }

        $table = $this->hpPageRepository;
        // 階層外 && !一覧ページ
        if (is_null($page['parent_page_id']) && $page['level'] && !$table->hasDetailPageType($page['page_type_code'])) {
            return false;
        }

        $directoryPath = str_replace('index.html', '', $page['public_path']);

        $validateFlg = false;

        foreach ($publicPages as $publicPage) {

            if ($publicPage['id'] == $page['id'] || !$table->hasEntity($publicPage['page_type_code'])) {
                continue;
            }

            if (strpos($publicPage['new_path'], $directoryPath, 0) === 0 && $publicPage['page_flg'] != 1) {
                $validateFlg = true;
                break;
            }
        }
        return $validateFlg;
    }

    /**
     * 親ディレクトリが存在するかチェック
     *
     * @param $pages
     * @param $newPath
     * @param $fileName
     *
     * @return bool
     */
    private function existParent($publicPages, $newPath, $fileName, $level) {

        $parentPath = str_replace($fileName.'/index.html', 'index.html', $newPath);

        // 第一階層
        if ($level < 2) {
            return true;
        };

        $validateFlg = false;
        foreach ($publicPages as $page) {

            if ($page['new_path'] == $parentPath) {

                $validateFlg = true;
                break;
            }
        }

        return $validateFlg;
    }

    private function validateSearchContact() {

        $where = [
            ['hp_id' , $this->hpId],
            'whereIn' => ['page_type_code', $this->hpPageRepository->estateContactPageTypeCodeList()]
        ];
        $rows  = $this->hpPageRepository->fetchAll($where, ['page_type_code']);

        if (!$rows || count($rows) < 1) {

            return true;
        }

        foreach ($rows as $row) {

            // 未作成あり
            if ($row->new_flg) {

                $this->setError('error-top', 'mustPublishSearchContact', '物件フォームを編集してください。');
                return false;
            }
        }

        return true;
    }

    //物件リクエスト
    private function validateRequestContact() {

        $table = \App::make(HpEstateSettingRepositoryInterface::class);
        $estateSettng = $table->getSetting($this->hpId);
        if($estateSettng == null) return true;

        $table = \App::make(EstateClassSearchRepositoryInterface::class);
        $eatateClass = $table->getSettingAll($this->hpId, $estateSettng->id);
        $request_class = array();
        foreach ($eatateClass as $key => $value) {
            if($value->estate_request_flg == 1) {
                $select =  $this->hpPageRepository->model()->select();
                $select->where('hp_id', $this->hpId);
                switch ($value->estate_class) {
                    // 物件リクエスト 居住用賃貸物件フォーム
                    case 1:
                        $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE);
                        break;
                    // 物件リクエスト 事務所用賃貸物件フォーム
                    case 2:
                        $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE);
                        break;
                    // 物件リクエスト 居住用売買物件フォーム
                    case 3:
                        $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY);
                        break;
                    // 物件リクエスト 事務所用売買物件フォーム
                    case 4:
                        $select->where('page_type_code', HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY);
                        break;
                }
                $select->where('new_flg', 0);
                $hpPageRow = $select->first();
                if(!$hpPageRow) {
                    $this->setError('error-top', 'mustPublishRequestContact', '物件リクエストを編集してください。');
                    return false;
                }
                $request_class[] = $value->estate_class;
            }
        }
        if(count($request_class) == 0) return true;

        return true;
    }

    // Check page global navigation public -> private
    private function isPrivateOriginalPage($params) {
        $validate = true;
        $hp = getInstanceUser('cms')->getCurrentHp();
        $globalNavs = $hp->getGlobalNavigation()->toSiteMapArray();
        $table = $this->hpPageRepository;
        $listGlobals = [];
        $listType = [];
        foreach ($globalNavs as $global) {
            $listGlobals[] = $global["id"];
            $listType[$global["id"]] = $global["page_type_code"];
        }

        if (isset($params['page'])) {
            foreach ($params['page'] as $key => $param) {
                $hasError = false;

                if (in_array($key, $listGlobals) && $param['new_close_flg'] == 1 && $param['update'] == 1) {
                    $hasError = true;
                }

                if ($hasError) {
                    $this->setError('error-top', 'privatePage', 'グロナビに設置されているページは非表示にすることはできません。<br>
    必須ページを公開してください。');
                    $this->setError('error-top', 'privatePage_'.$listType[$key], ' ・'.$table->getTypeNameJp($listType[$key]));
                    $validate = false;
                }
            }
        }

        return $validate;
    }

    private function existArticle(&$pages, $publicPages, $pageId, $parentId, $publicFlg, &$index, &$result, &$pageCheckArr) {
        foreach ($pages as $key=>$page) {
            if ($page['id'] == $parentId) {
                if ($publicFlg) {
                    $pages[$key]['public_flg'] = true;
                    $result[$index]['id'] = $page['id'];
                    $result[$index]['parent_page_id'] = $page['parent_page_id'];
                    $result[$index]['parent_sort'] = $this->hpPageRepository->getParentTypeArticle($page);
                    $result[$index]['level'] = $page['level'];
                    $check = $this->checkPageInArray($page, $publicPages);
                    $result[$index]['checked'] = $check;
                    if ($check == false && !in_array($pageId, $pageCheckArr)) {
                        $pageCheckArr[] = $pageId;
                    }
                } else {
                    $array = array_filter($pages, function($item) use ($parentId) {
                        return $item['parent_page_id'] == $parentId && $item['public_flg'];
                    });
                    if (count($array) == 0) {
                        $pages[$key]['public_flg'] = false;
                        $result[$index]['id'] = $page['id'];
                        $result[$index]['parent_page_id'] = $page['parent_page_id'];
                        $result[$index]['parent_sort'] = $this->hpPageRepository->getParentTypeArticle($page);
                        $result[$index]['level'] = $page['level'];
                        $check = !$this->checkPageInArray($page, $publicPages);
                        $result[$index]['checked'] = $check;
                        if ($check == false && !in_array($pageId, $pageCheckArr)) {
                            $pageCheckArr[] = $pageId;
                        }
                    }
                }
                $index++;
                $this->existArticle($pages, $publicPages, $pageId, $page['parent_page_id'], $pages[$key]['public_flg'] , $index, $result, $pageCheckArr);
            }
        }
    }

    private function checkPageInArray($page, $publicPages) {
        $array = array_filter($publicPages, function($item) use ($page) {
            return $item['id'] == $page['id'];
        });
        if (count($array) > 0) {
            return true;
        }
        return false;
    }

    private function isValidNewPathArticle($pages, $page, $publicPages) {
        $validateFlg = true;
        // 親ページが存在するか
        if (gettype($page['public_flg']) == 'boolean' && !$this->existParentArticle($pages ,$publicPages, $page['new_path'], $page['filename'], $page['level'], $page)) {
            $validateFlg = false;
            $this->setError('page_'.$page['id'].'_update', 'parentNone', self::MUST_PARENT_AND_CHILDREN);
        }
        // 下階層にページが残ってしまわないか
        if ($validateFlg && !$this->existChild($pages, $page)) {
            $validateFlg = false;
            $this->setError('page_'.$page['id'].'_update', 'remainChildren', self::MUST_PARENT_AND_CHILDREN);
        }
        return $validateFlg;
    }

    private function existChild($pages, $page) {
        if ($page['level'] == 4) {
            return true;
        }

        $validateFlg = true;
        $parentId = $page['id'];
        if ($page['public_flg']) {
            $array = array_filter($pages, function($item) use ($page) {
                return $item['parent_page_id'] == $page['id'] && (int)$item['public_flg'] == (int) $page['public_flg'];
            });
            if (count($array) <= 0) {
                $validateFlg = false;
            }
        } else {
            $array = array_filter($pages, function($item) use ($page) {
                return $item['parent_page_id'] == $page['id'] && (int)$item['public_flg'] == (int) !$page['public_flg'];
            });
            if (count($array) > 0) {
                $validateFlg = false;
            }
        }
        return $validateFlg;
    }

    private function existParentArticle($pages, $publicPages, $newPath, $fileName, $level, $page) {
        // 第一階層
        if ($level < 2) {
            return true;
        };

        $array = array_filter($pages, function($item) use ($page) {
            return $item['parent_page_id'] == $page['parent_page_id'];
        });
        if (count($array)> 0) {
            return true;
        }

        $parentPath = str_replace($fileName.'/index.html', 'index.html', $newPath);
        $validateFlg = false;
        foreach ($publicPages as $page) {
            if ($page['new_path'] == $parentPath) {
                $validateFlg = true;
                break;
            }
        }
        return $validateFlg;
    }

    private function addErrorPages($pages) {
        $this->setError('article_error', 'pages', []);
        $currentPages =& $this->_errors['article_error']['pages'][0];
        foreach (array_reverse($pages) as $page) {
            if (!in_array($page, $currentPages)) {
                if ($page['level'] <= 2) {
                    // 大カテゴリーをマージ
                    $currentPages = array_merge([$page], $currentPages);
                } else if ($page['level'] == 3) {
                    // 小カテゴリーをマージ
                    $parents = array_filter($currentPages, function($parent) use ($page) {
                        return $parent['id'] === $page['parent_page_id'];
                    });
                    if (count($parents) > 0) {
                        $categorys = array_filter($currentPages, function($parent) use ($page) {
                            return $parent['parent_page_id'] === $page['parent_page_id'];
                        });
                        array_splice($currentPages, key($parents) - count($categorys), 0, [$page]);
                    } else {
                        $currentPages = array_merge([$page], $currentPages);
                    }
                }
            }
        }
    }

    public function isValidArticle($pages, $publicPages) {
        $pageTable = $this->hpPageRepository;
        $validateFlg = true;
        $pages = array_filter($pages, function($page) use ($pageTable) {
            return in_array($page['page_category_code'], $pageTable->getCategoryCodeArticle());
        });
        usort($pages, function($a, $b) {
            if ($a['page_category_code'] == $b['page_category_code']) {
                if ($a['page_type_code'] == $b['page_type_code']) {
                    if ($a['id'] == $b['id']) {
                        return 0;
                    } elseif ($a['id'] < $b['id']) {
                        return 1;
                    } else {
                        return -1;
                    }
                } elseif ($a['page_type_code'] < $b['page_type_code']) {
                    return 1;
                } else {
                    return -1;
                }
            } elseif ($a['page_category_code'] < $b['page_category_code']) {
                return 1;
            } else {
                return -1;
            }
        });
        $result = array();
        $pageCheckArr = array();
        $index = 0;
        foreach($pages as $page) {
            if ($page['page_category_code'] == HpPageRepository::CATEGORY_ARTICLE) {
                if (gettype($page['public_flg']) == 'boolean') {
                    $this->existArticle($pages, $publicPages, $page['id'], $page['parent_page_id'], $page['public_flg'] , $index, $result, $pageCheckArr);
                }
                $result = array_filter($result, function($item) {
                    return $item['checked'] == false;
                });
            } else {
                if (!$this->isValidNewPathArticle($pages, $page, $publicPages)) {
                    $validateFlg = false;
                }
            }
        }
        if (count($result) > 0) {
            $validateFlg = false;
            $this->setError('article_error', 'message', self::MUST_PARENT_ARTICLE);
            array_multisort(array_column($result, 'id'), SORT_ASC, array_column($result, 'parent_sort'), SORT_ASC, $result);
            $this->addErrorPages(array_values($result));
            $this->setError('article_error', 'pages_check', $pageCheckArr);
        }

        return $validateFlg;
    }
}
