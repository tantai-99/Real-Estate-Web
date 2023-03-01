<?php
namespace Library\Custom\Publish\Prepare;

use App;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use Library\Custom\Publish\Render;
use App\Models\EstateClassSearch;

class Page extends PrepareAbstract {

	private $cashedGetPages = null;
    private $updatedPageIds = null;
    protected $hpRepository;
    protected $companyRepository;
    protected $hpPageRepository;
	
    public function __construct($hpId, $params) {
        $this->hpRepository = App::make(HpRepositoryInterface::class);
        $this->hpPageRepository = App::make(HpPageRepositoryInterface::class);
        $this->companyRepository = App::make(CompanyRepositoryInterface::class);
        $this->setParams($params);
        $this->setHpRow($this->hpRepository->find($hpId));
        $this->setCompanyRow($this->companyRepository->fetchRowByHpId($hpId));
        if (!$this->getNamespace('publish')) {
            $this->setNamespace('publish', new \stdClass());
        }
        $this->setReserve(new Reserve($this->getHpRow()->id));

        // セッションの初期化
        if ((!$this->getRequest()->has('return')) && ($this->getRequest()->action == 'simple' || $this->getRequest()->action == 'detail' || $this->getRequest()->action == 'reserve')) {
            $this->getNamespace('publish')->unsetAll();
        }

        // pages
        $this->setPageRows($this->fetchPages());
    }

    /**
     * 物件お問い合わせを除く
     * 物件aliasの設定
     *
     */
    public function fetchPages() {

        // pages
        $this->hpPageRepository->setCategoryMap( $this->getCompanyRow() ) ;
        if($this->getCompanyRow()->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE')){
            $where = [
                ['hp_id', $this->getHpRow()->id],
            ];
        }else{
            $where = [
                ['hp_id', $this->getHpRow()->id],
                'whereNotIn' => ['page_type_code', $this->hpPageRepository->estateContactPageTypeCodeList()],
            ];
        }
        $rows  = $this->hpPageRepository->fetchAll($where, ['page_type_code', 'sort']);

        // 物件検索リンクのtitle, url設定
        foreach ($rows as $i => $row) {

            // 物件検索トップ
            if ($row->isEstateAliasForEstateSearchTop()) {
                $rows[$i]->title = '物件検索トップ';
            }

            // 賃貸物件検索トップ
            if ($row->isEstateAliasForEstateSearchRent()) {
                $rows[$i]->title = '賃貸物件検索トップ';
            }

            // 売買物件検索トップ
            if ($row->isEstateAliasForEstateSearchPurchase()) {
                $rows[$i]->title = '売買物件検索トップ';
            }

            // 物件検索
            if ($row->isEstateAliasForEstateSearch()) {
                
                $rows[$i]->title = (new EstateClassSearch)->getTitle($row->getEstatePageOriginId());
            }
            // 特集
            if ($row->isEstateAliasForSpecial()) {
                $where      = [
                    ['origin_id', $row->getEstatePageOriginId()],
                    ['hp_id'    , $row->hp_id],
                ];
                $specialRow = App::make(SpecialEstateRepositoryInterface::class)->fetchAll($where)->findCmsRow();
                if ($specialRow) {
                    $rows[$i]->title = $specialRow->title;
                }
            }

            // error
            if ($rows[$i]->title === null) {
                $rows[$i]->title = 'undefined';
            }
        }
        return $rows;
    }


    /**
     * 各アクションのバリデーション
     * @return bool
     */
    public function isValid() {

        switch (getActionName()) {

            // case 'simple':
            //     if ($this->getReserve()->hasPrereserved()) {
            //         return false;
            //     }
            //     return true;

            case 'detail':
                return true;

            case 'testsite':
                if ($this->getNamespace('publish')->params) {
                    return true;
                }
                return false;

            case 'apiPublish':
                if (app('request')->isXmlHttpRequest()) {
                    return true;
                };
                return false;

            case 'previewPage':
                $id        = app('request')->id;
                $parent_id = app('request')->parent_id;
                if ((!is_numeric($id) || $id < 1) && (!is_numeric($parent_id) || $parent_id < 1)) {
                    return false;
                };

                $device = app('request')->device;
                if (!in_array($device, Render\AbstractRender::getDeviceList())) {
                    return false;
                }
                return true;

            case 'progress':
                if (!$this->getNamespace('publish')->publishType || !$this->getNamespace('publish')->params) {
                    return false;
                };

                return true;

            default:
                return false;
        }
    }

    /**
     * 表示用のページリストを取得
     *
     * @return array|bool
     */
    public function getList() {

        switch (getActionName()) {

            case 'simple':
                $pages = $this->filterUnfinishedPages($this->getPages());

                $isDisplayItem = false;
                foreach ($pages as $page) {
                    if ($page['label'] != 'no_diff') {
                        $isDisplayItem = true;
                        break;
                    }
                };

                if ($isDisplayItem) {
                    return $pages;
                }
                return false;

            case 'detail':
                $reserved = App::make(ReleaseScheduleRepositoryInterface::class)->fetchReserveRowsByHpId($this->getHpRow()->id);
                $pages    = $this->filterUnfinishedPages($this->getPages());
                foreach ($pages as $i => $page) {
                    foreach ($reserved as $row) {
                        if ($page['id'] == $row->page_id && $row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                            $pages[$i]['current_release_at'] = $this->dateForApp($row->release_at);
                        }
                        if ($page['id'] == $row->page_id && $row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                            $pages[$i]['current_close_at'] = $this->dateForApp($row->release_at);
                        }
                    }
                }
                return $pages;

            case 'testsite':

                $res = array();
                $params = $this->getNamespace('publish')->params;

                if (count($this->getReserve()->mergeReserve($params, $this->getUpdatePageIds($params))) < 1) {
                    return $res;
                }

                // DBの予約
                foreach ($this->getReserve()->survivingReserve($params) as $row) {
                    $res[$row->release_at][$row->release_type_code][$row->page_id] = $this->title($this->getPageRows()->findRow($row->page_id));
                }

                // POSTされた予約
                foreach ($params['page'] as $id => $val) {

                    if (!$val['update']) {
                        continue;
                    }

                    if ($val['new_release_at']) {

                        $res[$this->dateForDb($val['new_release_at'])][config('constants.release_schedule.RESERVE_RELEASE')][$id] = $this->title($this->getPageRows()->findRow($id));
                    }

                    if ($val['new_close_at']) {

                        $res[$this->dateForDb($val['new_close_at'])][config('constants.release_schedule.RESERVE_CLOSE')][$id] = $this->title($this->getPageRows()->findRow($id));

                    }
                }
                ksort($res);
                return $res;
        }
    }

    public function getReserveListForValidation($params) {

        $res = [];

        if (count($this->getReserve()->mergeReserve($params, $this->getUpdatePageIds($params))) < 1) {
            return $res;
        }

        // DBの予約
        foreach ($this->getReserve()->survivingReserve($this->getUpdatePageIds($params)) as $row) {
            $res[$row->release_at][$row->release_type_code][$row->page_id] = $this->title($this->getPageRows()->findRow($row->page_id));
        }

        // POSTされた予約
        if (isset($params['page'])) {
            foreach ($params['page'] as $id => $val) {

                if (!$val['update']) {
                    continue;
                }
    
                $row = $this->getPageRows()->findRow($id);
    
                if ($row === null) {
                    continue;
                }
    
                if ($val['new_release_at']) {
    
                    $res[$this->dateForDb($val['new_release_at'])][config('constants.release_schedule.RESERVE_RELEASE')][$id] = $this->title($row);
                }
    
                if ($val['new_close_at']) {
    
                    $res[$this->dateForDb($val['new_close_at'])][config('constants.release_schedule.RESERVE_CLOSE')][$id] = $this->title($row);
                }
            }
        }
        ksort($res);
        return $res;
    }

    private function title($pageRow) {

        if (!is_object($pageRow)) {
            return 'This page has deleted';
        }

        if ($pageRow->page_type_code == HpPageRepository::TYPE_TOP) {

            $title    = $pageRow->title;
            $filename = 'toppage';
            return $title.'（'.$filename.'）';

        }

        if ($pageRow->page_type_code == HpPageRepository::TYPE_ALIAS) {

            $title    = $this->hpPageRepository->fetchRowByLinkId($pageRow->link_page_id, $this->getHpRow()->id)->title;
            $filename = 'alias';
            return $title.'（'.$filename.'）';
        }

        if ($pageRow->page_type_code == HpPageRepository::TYPE_LINK) {

            $title    = $pageRow->title;
            $filename = 'link';
            return $title.'（'.$filename.'）';
        }

        if ((int)$pageRow->page_type_code === HpPageRepository::TYPE_ESTATE_ALIAS) {

            $title    = $pageRow->title;
            $filename = 'link';
            return $title.'（'.$filename.'）';
        }

        if ((int)$pageRow->page_type_code === HpPageRepository::TYPE_LINK_HOUSE) {

            $title    = $pageRow->title;
            $filename = 'link';
            return $title.'（'.$filename.'）';
        }

        $title    = $pageRow->title;
        $filename = $pageRow->filename;
        return $title.'（'.$filename.'）';
    }

    /**
     * ページデータを配列で取得
     *
     * @return mixed
     */
    public function getPages() {

    	if ($this->cashedGetPages) {
    		return $this->cashedGetPages;
    	}

        $pages = $this->getPageRows()->toArray();

        foreach ($pages as $i => $page) {

            // get title for alias page
            if ($page['page_type_code'] == HpPageRepository::TYPE_ALIAS) {
                $pages[$i]['title'] = $this->hpPageRepository->fetchRowByLinkId($page['link_page_id'], $page['hp_id'])->title;
            }

            /* set current status */
            $pages[$i]['label'] = 'new';
            if ($page['republish_flg']) {
                $pages[$i]['label'] = 'update';
            }

            if ($page['diff_flg'] || ($page['public_flg'] && $this->getNewPath($page) != $page['public_path'])) {
                continue;
            }

            $pages[$i]['label'] = 'no_diff';
            continue;
        }
        foreach ($pages as $i => $page) {
            if ($page['page_type_code'] != HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) {
                continue;
            }
            if ($page['public_flg'] && $page['label'] == 'no_diff') {
                $pages[$i]['label'] = 'check';
            }
        }
        
    	$this->cashedGetPages = $pages;
        return $this->cashedGetPages;
    }

    /**
     * ページのステータスを更新する
     *
     * @param $pages
     * @param $date
     * @param $params
     * @return mixed
     */
    public function getNewPages($pages, $date, $params) {

        $updatePageIds = $this->getUpdatePageIds($params);

        $reservedPageIds = array();

        $rows = $this->getReserve()->survivingReserve($updatePageIds);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $reservedPageIds[] = $row->page_id;
            }
            $reservedPageIds = array_unique($reservedPageIds);
        }

        foreach ($pages as $i => $page) {

            $id = $page['id'];

            $isUpdate  = in_array($id, $updatePageIds) ? true : false;
            $isReserve = in_array($id, $reservedPageIds) ? true : false;

            if (!$isUpdate && !$isReserve) {
                $pages[$i]['new_path'] = $page['public_path'];
                continue;
            }

            if ($isUpdate) {

                switch ($date) {
                    case App\Http\Form\Publish::NOW:

                        if ($this->isNewReleae($id, $params) && !$this->isSetNewReleaeAt($id, $params)) {
                            if ($pages[$i]['public_flg'] !== 1) {
                                $pages[$i]['public_flg'] = true;
                            }
                            $pages[$i]['new_path']   = $this->getNewPath($page);
                            break;
                        }

                        if ($this->isNewClose($id, $params) && !$this->isSetNewCloseAt($id, $params)) {
                            if ($pages[$i]['public_flg'] !== 0) {
                                $pages[$i]['public_flg'] = false;
                            }
                            $pages[$i]['new_path']   = null;
                            break;
                        }

                        $pages[$i]['new_path'] = $page['public_path'];
                        break;

                    case $this->getNewReleaseAt($id, $params):

                        if ($pages[$i]['public_flg'] !== 1) {
                            $pages[$i]['public_flg'] = true;
                        }
                        $pages[$i]['public_path'] = $page['new_path'];
                        $pages[$i]['new_path']    = $this->getNewPath($page);
                        break;

                    case $this->getNewCloseAt($id, $params):

                        if ($pages[$i]['public_flg'] !== 0) {
                            $pages[$i]['public_flg'] = false;
                        }
                        $pages[$i]['public_path'] = $page['new_path'];
                        $pages[$i]['new_path']    = null;
                        break;

                    default:
                        break;
                }
                continue;
            }

            if ($isReserve) {

                if ($date == App\Http\Form\Publish::NOW) {
                    $pages[$i]['new_path'] = $page['public_path'];
                    continue;
                }

                $pages[$i]['public_path'] = $page['new_path'];

                foreach (App::make(ReleaseScheduleRepositoryInterface::class)->fetchReserveByPageId($page['id']) as $row) {

                    if ($row->release_at == $date && $row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                        $pages[$i]['public_flg'] = true;
                        $pages[$i]['new_path']   = $this->getNewPath($page);
                    }

                    if ($row->release_at == $date && $row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                        $pages[$i]['public_flg'] = false;
                        $pages[$i]['new_path']   = null;
                    }

                }

            }
        }

        return $pages;
    }

    /**
     * 更新後のパスを取得
     *
     * @param        $page
     * @param string $path
     * @param null   $pageId
     * @return string
     */
    public function getNewPath($page, $path = '', $pageId = null) {

        if ($pageId) {
            $page = $this->hpPageRepository->find($pageId)->toArray();
        }

        // トップページはindex.html
        if ($page['page_type_code'] == HpPageRepository::TYPE_TOP) {
            return 'index.html';
        }

        // リンクはNULL
        if ($page['page_type_code'] == HpPageRepository::TYPE_LINK ||
            $page['page_type_code'] == HpPageRepository::TYPE_LINK_HOUSE) {
            return null;
        }

        // エイリアスは参照元のページ
        if ($page['page_type_code'] == HpPageRepository::TYPE_ALIAS) {
            return $this->getNewPath($this->hpPageRepository->fetchRowByLinkId($page['link_page_id'], $page['hp_id']));
        }

        // 物件検索エイリアスは参照元のページ
        if ((int)$page['page_type_code'] === HpPageRepository::TYPE_ESTATE_ALIAS) {

            // 物件検索トップ
            $prefix = 'estate_top';
            if (preg_match("/^$prefix/", $page['link_estate_page_id'])) {
                return 'shumoku.html'.'index.html';
            }
            // 賃貸物件検索トップ
            $prefix_rent = 'estate_rent';
            if (preg_match("/^$prefix_rent/", $page['link_estate_page_id'])) {
                return 'rent.html'.'index.html';
            }
            // 売買物件検索トップ
            $prefix_purchase = 'estate_purchase';
            if (preg_match("/^$prefix_purchase/", $page['link_estate_page_id'])) {
                return 'purchase.html'.'index.html';
            }
            // 物件検索
            $prefix = 'estate_type_';
            if (preg_match("/^$prefix/", $page['link_estate_page_id'])) {
                return (new EstateClassSearch)->getFilename(str_replace($prefix, '', $page['link_estate_page_id'])).'/index.html';
            }

            // 特集
            $prefix = 'estate_special_';
            if (preg_match("/^$prefix/", $page['link_estate_page_id'])) {

                $originId   = str_replace($prefix, '', $page['link_estate_page_id']);
                $where      = [
                    ['origin_id', $originId],
                    ['hp_id', $this->getHpRow()->id],
                ];
                $specialRow = App::make(SpecialEstateRepositoryInterface::class)->fetchAll($where)->findCmsRow();
                if ($specialRow) {
                    return $specialRow->filename.'/index.html';
                }
            }
        }

        // 物件検索お問い合わせ
        if (in_array($page['page_type_code'], $this->hpPageRepository->estateContactPageTypeCodeList())) {

            // @todo 通常のお問い合わせに飛ばすしか…？
            return 'contact/edit/index.html';
        }

        if (strlen($path) < 1) {
            $path = $page['filename'].'/index.html';
        }

        if ($page['parent_page_id'] < 1 || is_null($page['parent_page_id'])) {

            return $path;
        }

        foreach ($this->getPageRows()->toArray() as $parent) {
            if ($page['parent_page_id'] == $parent['id']) {

                $path = $parent['filename'].DIRECTORY_SEPARATOR.$path;
                return $this->getNewPath($parent, $path);
            }
        }
    }

    /**
     * hpTableを更新
     *
     * @param $newImageIds
     * @param $newFileIds
     */
    public function updateHp( $newImageIds, $newFile2Ids, $newFileIds ) {

        $this->getHpRow()->public_image_ids = implode(',', $newImageIds);
        $this->getHpRow()->public_file2_ids = implode(',', $newFile2Ids);
        $this->getHpRow()->public_file_ids  = implode(',', $newFileIds);
        $this->getHpRow()->all_upload_flg   = 0;
        $this->getHpRow()->setAllUploadParts('ALL', 0); // all_upload_flg OFFに連動

        $this->getHpRow()->save();
    }

    /**
     * hpPageTableを更新
     *
     * @param $pages
     * @param $releasePageIds
     * @param $closePageIds
     */
    public function updatePage($pages, $releasePageIds, $closePageIds) {

//         $adapter = $table->getAdapter();
//         $adapter->beginTransaction();

        foreach ($pages as $page) {

            if (!in_array($page['id'], $releasePageIds) && !in_array($page['id'], $closePageIds) && !($this->getHpRow()->all_upload_flg && $page['public_flg'])) {
            //if (!in_array($page['id'], $releasePageIds) && !in_array($page['id'], $closePageIds) && $page['public_flg']) {
                    continue;
            }

            $row = $this->hpPageRepository->find($page['id']);

            $row->public_path = $page['new_path'];

            if ($page['public_flg']) {
                $row->public_flg    = 1;
                $row->diff_flg      = 0;
                $row->republish_flg = 1;
                $row->published_at  = date('Y-m-d H:i:s');
                $row->public_title  = $page['title'];
            }
            else {
                $row->public_flg   = 0;
                $row->published_at = null;
                $row->public_title = null;
            }

            $row->save();
        }

//         $adapter->commit();
    }


    /**
     * 更新されるページID一覧を取得
     *
     * @return array
     */
    public function getUpdatedPageIds() {

    	if ($this->updatedPageIds) {
    		return $this->updatedPageIds;
    	}
    	
        $array = array();

        $params  = $this->getNamespace('publish')->params;
        $current = App\Http\Form\Publish::NOW;
        if (isset($this->getNamespace('publish')->releaseAt)) {
            $current = $this->getNamespace('publish')->releaseAt;
        };

        // 現在時刻までの変更を時刻順に反映
        foreach ($this->getReleaseAtContainNow($params) as $releaseAt) {
            if ($current < $releaseAt) {
                break;
            }

            if (!isset($params['page'])) {
                continue;
            }

            foreach ($params['page'] as $pageId => $val) {

                if ($val['update'] && $val['new_release_flg'] && $this->dateForDb($val['new_release_at']) == $releaseAt) {
                    $array['release'][$pageId] = $pageId;
                    if (isset($array['close'][$pageId])) {
                        unset($array['close'][$pageId]);
                    }
                }

                if ($val['update'] && $val['new_close_flg'] && $this->dateForDb($val['new_close_at']) == $releaseAt) {
                    $array['close'][$pageId] = $pageId;
                    if (isset($array['release'][$pageId])) {
                        unset($array['release'][$pageId]);
                    }
                }
            }
        }

        // 一覧・詳細ページ
        // - 毎回レンダリングするため
        // - 公開停止は除く
        foreach ($this->getPages() as $page) {

            // 公開中 && (一覧 or 詳細ページ ) && 公開停止になっていない
            if ($page['public_flg'] && ( /*$table->isDetailPageType($page['page_type_code']) || */ $this->hpPageRepository->hasPagination($page['page_type_code'])) && (!isset($array['close']) || !in_array($page['id'], $array['close']))) {
                $array['release'][$page['id']] = $page['id'];
            }
        }

        $this->updatedPageIds = $array; 
        return $this->updatedPageIds;
    }

    /**
     * 非公開ページをフィルタリング
     *
     * @param $pages
     * @return mixed
     */
    public function filterDraftPages($pages) {

        foreach ($pages as $i => $page) {

            if (!$page['public_flg']) {
                unset($pages[$i]);
            }
        }
        return $pages;

    }

    /**
     * 未作成ページをフィルタリング
     *
     * @param $pages
     * @return mixed
     */
    public function filterUnfinishedPages($pages) {

        foreach ($pages as $i => $page) {

            if ($page['new_flg']) {
                unset($pages[$i]);
            }
        }
        return $pages;

    }

    /**
     * ファイルを生成しないページをフィルタリング
     *
     * @param $pages
     * @return mixed
     */
    public function filterNoEntityPages($pages) {

        foreach ($pages as $i => $page) {
            if (!$this->hpPageRepository->hasEntity($page['page_type_code'])) {
                unset($pages[$i]);
            }
        }
        return $pages;
    }

    /**
     * ポスト値のダミーデータを生成
     * - 全上げ時に使用
     * - 公開されているページをすべて即時公開にする
     * @return array
     */
    public function generateParams() {

        $dummy = array();
        foreach ($this->getPages() as $page) {

            $dummy['page'][$page['id']]['update']          = $page['public_flg'] ? 1 : 0;
            $dummy['page'][$page['id']]['new_release_flg'] = $page['public_flg'] ? 1 : 0;;
            $dummy['page'][$page['id']]['new_release_at'] = 0;
            $dummy['page'][$page['id']]['new_close_flg']  = 0;
            $dummy['page'][$page['id']]['new_close_at']   = 0;
        }
        return array_merge($dummy, $this->getRequest()->all());
    }

    /**
     * パブリッシュ用のページデータを取得
     *
     * @param $publishType
     * @param $params
     * @return mixed
     */
    public function getAfterPages($publishType, $params) {

        $newPages = $this->getPages();
        switch ($publishType) {
            case config('constants.publish_type.TYPE_PUBLIC'):
            case config('constants.publish_type.TYPE_SUBSTITUTE'):
                $newPages = $this->getNewPages($newPages, App\Http\Form\Publish::NOW, $params);
                break;

            case config('constants.publish_type.TYPE_TESTSITE'):
                $schedule = $this->getReleaseAtContainNow($params);
                foreach ($schedule as $releaseAt) {
                    if ($this->getNamespace('publish')->releaseAt < $releaseAt) {
                        break;
                    }
                    $newPages = $this->getNewPages($newPages, $releaseAt, $params);
                }
                break;
        }

        $updatePageIds = $this->getUpdatedPageIds();
        $release       = isset($updatePageIds['release']) ? $updatePageIds['release'] : [];

        foreach ($newPages as $i => $page) {

            // 下書きはパス
            if (!$page['public_flg']) {
                continue;
            }

            // 更新対象ページはパス
            if (in_array($page['id'], $release) || $this->getHpRow()->all_upload_flg || $this->hpPageRepository->hasPagination($page['page_type_code'])) {
                continue;
            }

            // 公開中タイトルなければパス
            if ($page['public_title'] === null) {
                continue;
            }

            // 公開中のタイトルを使用
            $newPages[$i]['title'] = $page['public_title'];
        }

        return $newPages;
    }

    /**
     * プレビュー用のページデータを取得
     *
     * @param $pageId
     * @return mixed
     */
    public function getAfterPagesForPreview($pageId) {

        $res = [];

        foreach ($this->getPages() as $i => $page) {

            $res[$page['id']] = $page;

            if ($page['id'] == $pageId) {

                $res[$page['id']]['public_flg'] = true;
                $res[$page['id']]['new_path']   = $this->getNewPath($page);

                if ($this->getParam('tdk')) {

                    $param = $this->getParam('tdk');

                    $title = '';
                    if (isset($param['title'])) {
                        $title = $param['title'];
                    }
                    if ($page['page_type_code'] == HpPageRepository::TYPE_TOP) {
                        $title = 'トップページ';
                    }

                    $keywords = '';
                    for ($i = 1; $i <= 3; $i++) {
                        if (isset($param['keyword'.$i]) && $param['keyword'.$i] != '') {
                            $keywords .= $param['keyword'.$i].',';
                        }
                    }
                    $keywords = substr($keywords, 0, -1);

                    $description = '';
                    if (isset($param['description'])) {
                        $description = $param['description'];
                    }

                    $res[$page['id']]['title']       = $title;
                    $res[$page['id']]['keywords']    = $keywords;
                    $res[$page['id']]['description'] = $description;
                }

                continue;
            }

            $res[$page['id']]['new_path'] = $page['public_path'];
        }
        return $res;
    }

    /**
     * 予約投稿の時刻一覧を取得（現在時刻を含む）
     *
     * @param $params
     *
     * @return array
     */
    public function getReleaseAtContainNow($params) {

        $updatePageIds = $this->getUpdatePageIds($params);
        $releaseAt     = $this->getReserve()->mergeReserve($params, $updatePageIds);
        $releaseAt[]   = App\Http\Form\Publish::NOW;
        asort($releaseAt);
        return $releaseAt;
    }

    /**
     * HPのURL取得
     *
     * @return string
     */
    public function hpUrl() {

        $domain = $this->getCompanyRow()->domain;
        $prefix = Render\AbstractRender::prefix($this->getNamespace('publish')->publishType);
        $www    = Render\AbstractRender::www($this->getNamespace('publish')->publishType);
        
        // ATHOME_HP_DEV-2197
        if ( $this->getNamespace('publish')->publishType != config('constants.publish_type.TYPE_PUBLIC') ) {
            $config = getConfigs('sales_demo');
        	if( $this->getCompanyRow()->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ) {
        		$subDomain	= $this->getCompanyRow()->member_no						;
        	} else {
        		$subDomain	= $this->getCompanyRow()->ftp_user_id					;
        	}
        	$domain		= "{$subDomain}.{$config->demo->domain}"					;
        }
        
        return 'http://'.$www.$prefix.$domain.DIRECTORY_SEPARATOR;
    }

    private function isNewReleae($pageId, $params) {

        return $params['page'][$pageId]['new_release_flg'] ? true : false;
    }

    private function isSetNewReleaeAt($pageId, $params) {

        return $params['page'][$pageId]['new_release_at'] ? true : false;
    }

    private function isNewClose($pageId, $params) {

        return $params['page'][$pageId]['new_close_flg'] ? true : false;
    }

    private function isSetNewCloseAt($pageId, $params) {

        return $params['page'][$pageId]['new_close_at'] ? true : false;
    }

    private function getNewReleaseAt($pageId, $params) {

        return $this->dateForDb($params['page'][$pageId]['new_release_at']);
    }

    private function getNewCloseAt($pageId, $params) {

        return $this->dateForDb($params['page'][$pageId]['new_close_at']);
    }

    public function hasAutoUpdatePage() {

        foreach ($this->getPages() as $page) {

            // 簡易設定 && 差分なしはスルー
            if (getActionName() === 'simple' && !$page['diff_flg']) {
                continue;
            }

            // 公開中の 一覧
            if ($page['public_flg'] && ($this->hpPageRepository->hasPagination($page['page_type_code']))) {
                return true;
            }
        };
        return false;
    }

    public function hasUpdateForDetail() {

        foreach ($this->getList() as $page) {
            if ($page['label'] === 'new' || $page['label'] === 'update') {
                return true;
            }
        }
        return false;
    }

    public function checkCanPublishArticle($form, $check = true) {
        $pages = $this->getPageArticle();
        $params = $this->getParamArticle($pages, $check);
        $pages          = $this->getNewPages($pages, App\Http\Form\Publish::NOW, $params);
        $publicPages    = $this->filterDraftPages($pages);

        return $form->isValidArticle($pages, $publicPages);
    }

    public function getPageArticle() {
        $result = array();
        foreach($this->getPages() as $page) {
            if (!in_array($page['page_category_code'], $this->hpPageRepository->getCategoryCodeArticle())) {
                continue;
            }
            $result[] = $page; 
        }
        return $result;
    }

    public function getParamArticle($pages, $check) {
        $params = array('page' => array(), 'clickBtn' => 'setting-publish-article');
        foreach($pages as $page) {
            if (($check && $page['diff_flg']) || $page['label'] == 'check') {
                $params['page'][$page['id']]['update'] = 1;
                $params['page'][$page['id']]['new_release_flg'] = 1;
            } else {
                $params['page'][$page['id']]['update'] = 0;
                $params['page'][$page['id']]['new_release_flg'] = 0;
            }
            $params['page'][$page['id']]['new_release_at'] = '';
            $params['page'][$page['id']]['new_close_flg'] = 0;
        }
        return $params;
    }

}

?>
