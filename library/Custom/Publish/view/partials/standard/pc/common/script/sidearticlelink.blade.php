<?php

// cms server
if ($publishType == 4) {

    $sideArticleLink = new sideArticleLink($view, $device, $publishType,$sidebarArticleLinkTitle, $typeSetLink, $filename, $pages, $thisPage);
}
// gmo server
else {
    $sideArticleLink = new sideArticleLink($this->_view, $device, $publishType, $sidebarArticleLinkTitle, $typeSetLink);
}

/**
 * サイドナビのレンダリング
 *
 * Class sideArticleLink
 */
class sideArticleLink {

    public $publishType;
    public $pages           = array();
    public $viewHelper;
    public $display         = true;
    public $sidebarArticleLinkTitle = null;
    public $typeSetLink = null;


    public function __construct($viewObj, $device, $publishType, $sidebarArticleLinkTitle, $typeSetLink, $filename = null, $pages = null, $thisPage = null) {

        $this->sidebarArticleLinkTitle = $sidebarArticleLinkTitle;
        $this->typeSetLink = $typeSetLink;

        // cms server
        if ($publishType == 4) {
            $this->viewHelper = $viewObj;

        }

        // gmo server
        else {

            $this->viewHelper = new ViewHelper($viewObj);

            // ページ一覧
            $allPages = unserialize($this->viewHelper->getContentSettingFile('pages.txt'));
            foreach($allPages as $page) {
                if (!$page['public_flg'] || !in_array($page['page_category_code'], array(22, 23, 24, 25))){
                    continue;
                }
                $pages[] = $page;
            }
        }

        $this->publishType = $publishType;
        $this->pages = $pages;
    }

    public function getPageByParentId($pagentId) {
        return array_filter($this->pages, function($page) use ($pagentId) {
            return $page['parent_page_id'] == $pagentId;
        });
    }

    public function getPageByTypeCode($typeCode) {
        return array_filter($this->pages, function($page) use ($typeCode) {
            return $page['page_type_code'] == $typeCode;
        });
    }

    public function getPageByCategoryCode($category) {
        $pages = array();
        switch ($category) {
            case 23 :
                $pages = array_filter($this->pages, function($page) use ($category) {
                    return $page['page_category_code'] == $category;
                });
                break;
            case 24 :
                $pageLarge = array_filter($this->pages, function($page){
                    return $page['page_category_code'] == 23;
                });
                foreach($pageLarge as $large) {
                    $pages = array_merge($pages, $this->getPageByParentId($large['id']));
                }
                break;
            case 25 :
                $pageLarge = array_filter($this->pages, function($page){
                    return $page['page_category_code'] == 23;
                });
                foreach($pageLarge as $large) {
                    foreach($this->getPageByParentId($large['id']) as $small) {
                        $pages = array_merge($pages, $this->getPageByParentId($small['id']));
                    }
                }
                break;
        }
        return $pages;
    }

    /**
     *
     * トップページを取得
     *
     */
    public function getArticleTop() {

        foreach ($this->pages as $page) {
            // HpPageRepository::TYPE_TOP = 1;
            if ($page['page_type_code'] == 100) {
                return $page;
            }
        }
    }

    /**
     * タイトル
     *
     * @return string
     */
    public function title() {

        if ($this->sidebarArticleLinkTitle) {
            return $this->sidebarArticleLinkTitle;
        }

        if ($this->is404) {
            return 'ページが見つかりません';
        }

    }

    public function echoChild($pages, $isArticleTop = false, $child = false) {
        $arrow = '';
        $class = '';
        $liClass = '';
        switch ($this->typeSetLink) {
            case 1:
            case 3:
                $arrow = '<i class="arrow bottom"></i>';
                $class = 'class="is-hide"';
                break;
            case 2:
            case 4:
                $arrow = '<i class="arrow top"></i>';
                break;
            default:
                # code...
               break;
        }
        if (!$isArticleTop && !$child) {
            $liClass = 'class="li-article"';
        }
        if (!$isArticleTop && $child && $this->typeSetLink != 5) {
            echo '<ul '.$class.'>';
        }
        foreach($pages as $page) {
            echo '<li '.$liClass.'>';
            echo '<a '.($liClass == '' ? $this->viewHelper->hpHref($page) : 'style="cursor:pointer;"').'>';
            echo htmlspecialchars($page['title']);
            if (!$isArticleTop && !$child) {
                echo $arrow;
            }
            echo '</a>';
            if (!$child) {
                $this->echoChild($this->getPageByParentId($page['id']), false, true);
            }
            echo '</li>';
        }
        if (!$isArticleTop && $child && $this->typeSetLink != 5) {
            echo '</ul>';
        }
    }
}

?>

<?php if ($sideArticleLink->display && count($sideArticleLink->pages) > 0) : ?>
    <?php if ($device == 'sp') :?>
        <section>
            <h2 class="heading-lv1 article-side-link"><?php echo htmlspecialchars($sideArticleLink->title()); ?></h2>
        </section>
    <?php endif;?>
    <?php if(isset($themeId) && ($themeId == 21 || $themeId == 22 || $themeId == 23)) : ?>
    <div class="side-others side-article">
    <?php else : ?>
    <div class="side-nav side-article">
    <?php endif; ?>
        <?php if ($device == 'pc') : ?>
        <section>
            <?php if(isset($themeId) && $themeId == 22) : ?>
            <h3 class="side-search-heading"><?php echo htmlspecialchars($sideArticleLink->title()); ?></h3>
            <?php elseif(isset($themeId) && ($themeId == 21 || $themeId == 23)) : ?>
            <h3 class="side-others-heading"><?php echo htmlspecialchars($sideArticleLink->title()); ?></h3>
            <?php else : ?>
            <h3 class="side-nav-heading"><?php echo htmlspecialchars($sideArticleLink->title()); ?></h3>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(isset($themeId) && ($themeId == 21 || $themeId == 22 || $themeId == 23)) : ?>
        <ul class="side-others-link">
        <?php else : ?>
        <ul>
        <?php endif; ?>
            <?php $sideArticleLink->echoChild($sideArticleLink->getPageByTypeCode(100), true, true);?>
            <?php 
                switch ($sideArticleLink->typeSetLink) {
                    case 1:
                    case 2:
                        $sideArticleLink->echoChild($sideArticleLink->getPageByCategoryCode(23));
                        break;
                    case 3:
                    case 4:
                        $sideArticleLink->echoChild($sideArticleLink->getPageByCategoryCode(24));
                        break;
                    case 5:
                        $sideArticleLink->echoChild($sideArticleLink->getPageByCategoryCode(25), false, true);
                        break;
                    default:
                        # code...
                        break;
                }
            ?>
        </ul>
        <?php if ($device == 'pc') : ?>
            </section>
        <?php endif; ?>
    </div>
<?php endif; ?>