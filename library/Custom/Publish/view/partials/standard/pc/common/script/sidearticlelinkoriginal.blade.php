<?php

// cms server
if ($publishType == 4) {

    $sideArticleLinkOriginal = new sideArticleLinkOriginal($view, $device, $publishType,$sidebarArticleLinkTitle, $typeSetLink, null, $filename, $pages, $thisPage);
}
// gmo server
else {
    $sideArticleLinkOriginal = new sideArticleLinkOriginal($this->_view, $device, $publishType, $sidebarArticleLinkTitle, $typeSetLink, $isTopOriginal);
}

/**
 * サイドナビのレンダリング
 *
 * Class sideArticleLinkOriginal
 */
class sideArticleLinkOriginal {

    public $publishType;
    public $pages           = array();
    public $viewHelper;
    public $display         = true;
    public $sidebarArticleLinkTitle = null;
    public $typeSetLink = null;
    public $isTopOriginal = null;


    public function __construct($viewObj, $device, $publishType, $sidebarArticleLinkTitle, $typeSetLink, $isTopOriginal, $filename = null, $pages = null, $thisPage = null) {

        $this->sidebarArticleLinkTitle = $sidebarArticleLinkTitle;
        $this->typeSetLink = $typeSetLink;
        $this->isTopOriginal = $isTopOriginal;

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
                if (in_array($page['page_type_code'], array(341, 342, 343)) && is_null($page['parent_page_id'])) {
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
        $class = 'side-article-child';
        $liClass = 'class="side-article-element"';
        $childTitleClass = '';
        switch ($this->typeSetLink) {
            case 1:
            case 3:
                $arrow = '<i class="arrow bottom"></i>';
                $class .= ' is-hide"';
                break;
            case 2:
            case 4:
                $arrow = '<i class="arrow top"></i>';
                break;
            default:
                # code...
               break;
        }
        if (!$isArticleTop && $child && $this->typeSetLink != 5) {
            echo '<ul class="'.$class.'">';
        }
        if ($isArticleTop) {
            $liClass = 'class="side-article-element-top"';
        } else {
            $childTitleClass = 'class="side-article-child-title"';
        }
        foreach($pages as $page) {
            echo '<li '.$liClass.'>';
            echo '<a '.$this->viewHelper->hpHref($page).' '.$childTitleClass.'>';
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

<?php if ($sideArticleLinkOriginal->display && count($sideArticleLinkOriginal->pages) > 0) : ?>
    <div class="side-nav side-article">
        <section>
            <h3 class="side-article-heading"><?php echo htmlspecialchars($sideArticleLinkOriginal->title()); ?></h3>
        <ul class="side-article-list">
            <?php $sideArticleLinkOriginal->echoChild($sideArticleLinkOriginal->getPageByTypeCode(100), true, true);?>
            <?php 
                switch ($sideArticleLinkOriginal->typeSetLink) {
                    case 1:
                    case 2:
                        $sideArticleLinkOriginal->echoChild($sideArticleLinkOriginal->getPageByCategoryCode(23));
                        break;
                    case 3:
                    case 4:
                        $sideArticleLinkOriginal->echoChild($sideArticleLinkOriginal->getPageByCategoryCode(24));
                        break;
                    case 5:
                        $sideArticleLinkOriginal->echoChild($sideArticleLinkOriginal->getPageByCategoryCode(25), false, true);
                        break;
                    default:
                        # code...
                        break;
                }
            ?>
        </ul>
        </section>
    </div>
<?php endif; ?>