<?php
namespace Library\Custom\View\Helper;
use App\Repositories\HpPage\HpPageRepository;

/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内リンクのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpPageTitle extends  HelperAbstract {
    /**
     * @var array
     */
    private static $_pages;

    private static $_hp;

    private static $_preview;

    public static function setPages(array $pages)
    {
        self::$_pages = $pages;
    }

    public static function setHp($hp) {

        self::$_hp = $hp;
    }
    public static function setPreview($preview)
    {
        self::$_preview = $preview;
    }

    public function hpPageTitle($page_link_id = null)
    {

        $estateSetting = self::$_hp->getEstateSetting();
        if ($estateSetting) {
            // 物件検索TOPへのリンク追加
            if (preg_match("/^estate_top/", $page_link_id)) {
                return '物件検索トップ（shumoku）';
            }

            // 賃貸物件検索TOPへのリンク追加
            if (preg_match("/^estate_rent/", $page_link_id)) {
                return '賃貸物件検索トップ（rent）';
            }

            // 売買物件検索TOPへのリンク追加
            if (preg_match("/^estate_purchase/", $page_link_id)) {
                return '売買物件検索トップ（purchase）';
            }

            // 物件検索種目へのリンク追加
            if (preg_match("/^estate_type_/", $page_link_id)) {
                $searchSettings = $estateSetting->getSearchSettingAll();
                foreach ($searchSettings as $searchSetting) {
                    foreach ($searchSetting->getLinkIdList(true) as $linkId => $label) {
                        if ($page_link_id === $linkId) {
                            return $label;
                        }
                    }
                }
            }
        }

        
        if (self::$_preview) {
            if (is_numeric($page_link_id)) {
                $page = $this->findPageByLinkId($page_link_id);
                $filename = '';
                if ($page['page_type_code'] != HpPageRepository::TYPE_TOP) {
                    $filename = '（'.$page['filename'].'）';
                }
                return $page['title'].$filename;
            }
            // 物件特集へのリンク追加
            else if (preg_match("/^estate_special_/", $page_link_id) && $estateSetting) {
                $specials = $estateSetting->getSpecialAll();
                foreach ($specials as $special) {
                    if ($page_link_id === $special->getLinkId()) {
                        return $special->getTitle(true);
                    }
                }
            }
        } else {
            $title = <<< 'EOD'
<?php if (!isset($this->viewHelper)){$this->viewHelper = new ViewHelper($this->_view);} echo $this->viewHelper->hpPageTitle({pageLinkId}, true);?>
EOD;
            $title = str_replace('{pageLinkId}', $page_link_id, $title);
            if (preg_match("/^estate_special_/", $page_link_id) && !$estateSetting) {
                return '';
            }
            return $title;
        }
        return '';
    }

    public function findPageByType($page_type)
    {
        foreach (self::$_pages as $page) {
            if ((int)$page['page_type_code'] === $page_type) {
                return $page;
            }
        }
        return null;
    }

    public function findPageByLinkId($link_id)
    {
        foreach (self::$_pages as $page) {
            if ($page['link_id'] == $link_id) {
                return $page;
            }
        }

        return null;
    }
}