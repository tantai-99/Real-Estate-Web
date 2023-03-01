<?php

// cms server
if ($publishType == 4) {

    $sidenav = new sidenav($view, $device, $publishType,$sidebarOtherLinkTitle, $filename, $pages, $thisPage);
}
// gmo server
else {
    $sidenav = new sidenav($this->_view, $device, $publishType, $sidebarOtherLinkTitle);
}

/**
 * サイドナビのレンダリング
 *
 * Class sidenav
 */
class sidenav {

    public $publishType;
    public $pages           = array();
    public $thisPage        = array();
    public $firstLevelPage  = array();// 表示中ページの先祖にあたる第一階層のページ
    public $firstLevelPages = array();
    public $viewHelper;
    public $display         = true;
    public $isSitemap       = false;
    public $is404           = false;
    public $isTop           = false;
    public $sidebarOtherLinkTitle = null;

    public function __construct($viewObj, $device, $publishType, $sidebarOtherLinkTitle, $filename = null, $pages = null, $thisPage = null) {

        $this->sidebarOtherLinkTitle = $sidebarOtherLinkTitle;
        // cms server
        if ($publishType == 4) {
            $this->viewHelper = $viewObj;

            // トップページ判定
            if ($thisPage['page_type_code'] == 1) {
                $this->isTop = true;
            }
        }

        // gmo server
        else {

            $this->viewHelper = new ViewHelper($viewObj);

            // ページ一覧
            $pages = unserialize($this->viewHelper->getContentSettingFile('pages.txt'));

            // ファイル名
            $db = debug_backtrace();
            $filename = $this->viewHelper->getFileName(dirname($db[2]['file']));

            // 表示中のページ
            $thisPage = $this->viewHelper->getPageByFileName($filename);

            // サイトマップ判定
            if ($filename == 'sitemap') {
                $this->isSitemap = true;
            }
            elseif ($filename == '404notFound') {
                $this->is404 = true;
            }
            // トップページ判定
            elseif (is_null($thisPage)) {
                $this->isTop = true;
            }
        }

        $this->publishType = $publishType;
        $this->pages = $pages;
        $this->thisPage = $thisPage;


        // 下層ページ
        if ($this->thisPage['level'] > 1) {
            $this->firstLevelPage = $this->serchFirstLevelPage($this->thisPage);
        }
        else {
            $this->firstLevelPage = $this->thisPage;
        }

        // 親ページが未作成 || 階層外のページ -> トップページ扱い
        if ($this->firstLevelPage === null || $this->firstLevelPage['parent_page_id'] === null) {
            $this->firstLevelPage = $this->getTopPage();
        }

        if ($device == 'sp') {

            $TYPE_INFO_INDEX = 2;
            $TYPE_INFO_DETAIL = 3;

            // 非表示
            // - 下層ページない
            // - お知らせ一覧 || 詳細
            if (count($this->getChildrenPages($this->firstLevelPage['id'], $pages)) < 1 || ($thisPage['page_type_code'] == $TYPE_INFO_INDEX || $thisPage['page_type_code'] == $TYPE_INFO_DETAIL)) {
                $this->display = false;
            }
        }

        $this->firstLevelPages = $this->getChildrenPages($this->firstLevelPage['parent_page_id'], $pages);

        //PCで表示するものがない場合は、トップページナビを出さない
        if($device == 'pc') {
            $top_title_view = false;
            foreach ($this->firstLevelPages as $page) {
                if (isset($page['is_gnav']) && $page['is_gnav'] && ($page['id'] != $this->firstLevelPage['id'] || $page['page_type_code'] == 1)) continue ;
                $top_title_view = true;

            }
            if($top_title_view == false) {
                $this->display = false;
            }
        }
    }

    /**
     *
     * トップページを取得
     *
     */
    public function getTopPage() {

        foreach ($this->pages as $page) {
            // HpPageRepository::TYPE_TOP = 1;
            if ($page['page_type_code'] == 1) {
                return $page;
            }
        }
    }

    /**
     * 表示中ページの先祖にあたる第一階層のページ
     *
     * @param $childPage
     * @return mixed
     */
    public function serchFirstLevelPage($page) {

        $parentPage = $this->getPage($page['parent_page_id']);

        if ($parentPage === null || $parentPage['level'] == 1) {
            return $parentPage;
        }
        return $this->serchFirstLevelPage($parentPage);
    }

    /**
     * 第一階層のページ一覧を取得
     *
     * @param $topPageId
     * @param $pages
     * @return array
     */
    public function getChildrenPages($parentPageId, $pages) {

        $list = array();
        foreach ($pages as $page) {
            if ($page['parent_page_id'] === $parentPageId) {
                $list[] = $page;
            }
        }
        return $list;
    }

    /**
     * ページを取得
     *
     * @param $pageId
     * @return mixed
     */
    public function getPage($pageId) {

        foreach ($this->pages as $page) {
            if ($page['id'] == $pageId) {

                return $page;
            }
        }
    }

    /**
     * 下階層のページを表示
     *
     * @param $parentPageId
     * @param $pages
     */
    public function echoChild($parentPageId, $pages) {

        $parentPage = $this->getPage($parentPageId);

        $uri = '';
        if (method_exists($this->viewHelper, 'uri')) {
            $uri = $this->viewHelper->uri($parentPage['new_path']);
        }

        // ブログ詳細 || 会員専用ページ配下 && !ログイン
        // HpPageRepository::TYPE_INFO_INDEX = 2;
        // HpPageRepository::TYPE_BLOG_INDEX = 14;
        $TYPE_BLOG_INDEX = 14;
        $TYPE_MEMBERONLY = 48;

        if ($parentPage['page_type_code'] == $TYPE_BLOG_INDEX || ($parentPage['page_type_code'] == $TYPE_MEMBERONLY && (!isset($_SESSION[basename($uri)]) || $_SESSION[basename($uri)] !== true))) {
            return;
        }

        $list = $this->getChildrenPages($parentPageId, $pages);
        if (count($list) < 1) {
            return;
        }

        echo '<ul>';
        foreach ($list as $page) {
            echo '<li>';
            echo '<a '.$this->viewHelper->hpHref($page).'>';
            echo htmlspecialchars($page['title']);
            echo '</a>';
            $this->echoChild($page['id'], $pages);
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * タイトル
     *
     * @return string
     */
    public function title() {

        if ($this->sidebarOtherLinkTitle) {
            return $this->sidebarOtherLinkTitle;
        }

        if ($this->is404) {
            return 'ページが見つかりません';
        }
    }
}

?>

<?php if ($sidenav->display) : ?>
    <div class="side-nav">
        <?php if ($device == 'pc') : ?>
        <section>
            <h3 class="side-nav-heading"><span><?php echo htmlspecialchars($sidenav->title()); ?></span></h3>
        <?php endif; ?>
        <ul>
            <?php foreach ($sidenav->firstLevelPages as $page) : ?>
                <?php if ($device == 'pc') : ?>
                    <?php
                    // グローバルナビメニュー && (アクセス中のページでない || トップページ) continue
                    if (isset($page['is_gnav']) && $page['is_gnav'] && ($page['id'] != $sidenav->firstLevelPage['id'] || $page['page_type_code'] == 1)) continue ;?>
                    <li>
                        <a <?php echo $sidenav->viewHelper->hpHref($page); ?>><?php echo htmlspecialchars($page['title']); ?></a>
                        <?php if ($page['id'] == $sidenav->firstLevelPage['id']) : ?>
                            <?php $sidenav->echoChild($page['id'], $sidenav->pages); ?>
                        <?php endif; ?>
                    </li>
                <?php elseif ($device == 'sp'): ?>
                    <?php if ($page['id'] == $sidenav->firstLevelPage['id']) : ?>
                        <li>
                            <a <?php echo $sidenav->viewHelper->hpHref($page); ?>><?php echo htmlspecialchars($page['title']); ?></a>
                            <?php $sidenav->echoChild($page['id'], $sidenav->pages); ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <?php if ($device == 'pc') : ?>
            </section>
        <?php endif; ?>
    </div>
<?php endif; ?>