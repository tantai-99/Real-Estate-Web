<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Model\Lists;
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
$lib   = Lists\InformationMainImageSlideShow::LIB_SLICK;
if ($view->isPreview) {
    $page = $view->getPage($view->all_pages, $view->pageId);
} elseif(!$view->isSitemap) {
    $page = $view->page->toArray();
}
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php if ($view->is404) : ?>ページが見つかりません | <?php elseif ($view->isSitemap) : ?>サイトマップ | <?php elseif ($page['page_type_code'] != HpPageRepository::TYPE_TOP): ?><?php echo h($page['title']); ?> | <?php endif; ?><?php echo h($view->hp->title); ?></title>
    <meta name="keywords" content="<?php if($view->isSitemap) : ?>サイトマップ,<?php endif; ?><?php echo h($view->keywords); ?>"/>
    <meta name="description" content="<?php if($view->isSitemap) : ?>サイトマップ：<?php elseif (!$view->isTop && ($page['description'])) : ?><?php echo h($page['description']); ?>：<?php endif; ?><?php echo h($view->hp->description); ?>"/>
    <?php if (!$view->isPreview): ?>
    <link rel="alternate" media="only screen and (max-width: 640px)" href="<?php echo '<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . htmlspecialchars($_SERVER["REQUEST_URI"]); ?>'; ?>">
    <?php endif; ?>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon"
        <?php if ($view->isPreview): ?>
            href="<?php echo url('/image/favicon?id='.$view->hp->favicon); ?>"
        <?php else : ?>
            href="/images/favicon.ico"
        <?php endif; ?>/>

    <link rel="apple-touch-icon"
        <?php if ($view->isPreview): ?>
            href="<?php echo url('/image/webclip', ['id' => $view->hp->webclip]); ?>"
        <?php else : ?>
            <?php if ($view->hp->webclip != null && $view->hp->webclip > 0): ?>
            href="/images/apple-touch-icon.<?php echo $view->siteImageWebclip->extension; ?>"
            <?php endif; ?>
        <?php endif; ?>/>

    <link rel="stylesheet" href="<?php $view->src('css/slick.css'); ?>"/>
    <link rel="stylesheet" href="<?php $view->src('css/style.css'); ?>"/>
    <link rel="stylesheet" href="<?php $view->src('css/color-setting.css'); ?>"/>
    <link rel="stylesheet" href="<?php $view->src('css/freeword.css'); ?>"/>
    <link rel="stylesheet" href="<?php $view->src('css/style-custom.css'); ?>" media="all"/>
    <?php if ($view->isPreview): ?>
    <link rel="stylesheet" href="<?php $view->src('css/fdp/contact-fdp.css'); ?>"/>
    <?php endif; ?>
<?php
    if($view->isTop && $view->company->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE') && isset($view->imageConfig)):

        $libsEffect     =   Lists\InformationMainImageSlideShow::getLibsEffect()   ;
        $lib            =   $libsEffect[$view->imageConfig->type_slideshow]                ;
        $libName        =   Lists\InformationMainImageSlideShow::getNameLib($lib)  ;
        foreach (Lists\InformationMainImageSlideShow::getLibsCSS($lib) as  $libcssName):
?>
    <link rel="stylesheet" href="<?php $view->src('css/'.$libcssName) ?>"/>
<?php
        endforeach;
    endif;
?>

    <?php if ($view->isTopOriginal && isset($view->layoutTop)):
        foreach ($view->layoutTop['sp'] as $layout) :
            $folder = 'top_css';
            if($view->usePubTop && !is_null($view->pubTopSrcPath) && is_dir($view->pubTopSrcPath)) {
                $dir = $view->pubTopSrcPath . '/'. $folder;
            } else {
                $dir = Lists\Original::getOriginalImportPath($view->company->id, $folder);
            }
            if (file_exists($dir.'/'.$layout.'.css')) :
    ?>
    <link rel="stylesheet" href="/top/css/<?php echo $layout;?>.css" media="all"/>
    <?php
            endif;
        endforeach;
    endif;
    ?>
    
    <script src="<?php $view->src('js/jquery-1.10.1.min.js'); ?>"></script>
    <script src="<?php $view->src('js/jquery.cookie.min.js'); ?>"></script>
    <script src="<?php $view->src('js/jquery.tile.js'); ?>"></script>
    <script src="<?php $view->src('js/slick.min.js'); ?>"></script>
<?php 
    if($lib):
        foreach (Lists\InformationMainImageSlideShow::getLibsJS($lib) as $libJsName):
?>
    <script src="<?php $view->src('js/'.$libJsName); ?>"></script>
<?php
        endforeach;
    endif;
?>
    <!--<script src="//maps.googleapis.com/maps/api/js?sensor=false"></script>-->
    <!--<script src="<?php $view->src('js/gmaps.js'); ?>"></script>-->
    <!--<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>-->
    <script src="<?php $view->src('js/fulltext-search.js'); ?>"></script>
    <script src="<?php $view->src('js/siggest.fixed.jquery.js'); ?>"></script>
    <script src="<?php $view->src('js/common.js'); ?>"></script>
    <script src="<?php $view->src('js/add_common.js'); ?>"></script>
    <script src="<?php $view->src('js/contact.js'); ?>"></script>
    <script type="text/javascript" src="//webfont.fontplus.jp/accessor/script/fontplus.js?eqiZ9eRgtMA%3D&box=P6jMsAp9OdA%3D&aa=1" charset="utf-8"></script>

    <?php if ($view->isTop): ?>
        <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script src="<?php $view->src('js/slick.min.js'); ?>"></script>
        <script>
            $(document).ready(function () {
                $('.single-item').not('.slick-initialized').slick({
                    dots: true,
                    infinite: true,
                    speed: 500,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 5000
                });
            });
        </script>
    <?php endif; ?>

    <?php if ($view->isTopOriginal && isset($view->layoutTop)):
        foreach ($view->layoutTop['sp'] as $layout) :
            $folder = 'top_js';
            if($view->usePubTop && !is_null($view->pubTopSrcPath) && is_dir($view->pubTopSrcPath)) {
                $dir = $view->pubTopSrcPath . '/'. $folder;
            } else {
                $dir = Lists\Original::getOriginalImportPath($view->company->id, $folder);
            }
            if (file_exists($dir.'/'.$layout.'.js')) :
    ?>
    <script src="/top/js/<?php echo $layout;?>.js"></script>
    <?php
            endif;
        endforeach;
    endif;
    ?>

    <?php if ($view->isPreview): ?>
        <script>
            // プレビュー時はすべての<a>を無効化する
            $(document).on('click', 'a', function (e) {
                return $(this).attr('data-enabled-link') === 'true';
            });
            // プレビュー判定
            window.app.isPreview = true;
        </script>

        <style>
        @font-face { 
            font-family: 'athm-nat2'; 
            src: url("<?php $view->src('fonts/athm-nat2.ttf'); ?>") format("truetype"), 
            url("<?php $view->src('fonts/athm-nat2.woff'); ?>") format("woff"), 
            url("<?php $view->src('fonts/athm-nat2.svg'); ?>") format("svg"); 
            font-weight: normal; font-style: normal;
        }
        </style>
    <?php endif; ?>
	<?php if($view->isTop):?>
    <script> 
        app.natural2 = true;
        app.configSlick = <?php echo json_encode($view->configSlick);?>;
        app.navSlickClass = '<?php echo $view->navSlickClass;?>' ;
        app.classSpeedBar = '<?php echo $view->classSpeedBar;?>' ;
        app.slider={
            width: <?php echo Lists\InformationMainImageSlideShow::SP_WIDTH ?>,
            height: <?php echo Lists\InformationMainImageSlideShow::SP_HEIGHT ?>,
<?php   if(isset($view->imageConfig) && $view->imageConfig->lib==Lists\InformationMainImageSlideShow::LIB_SKITTER): ?>
            effect: <?php 
                $effect  = Lists\InformationMainImageSlideShow::getAminationsSkitter($view->imageConfig->type_slideshow);
                echo is_array($effect)? json_encode($effect): "'".$effect."'";
            ?>,
            thumbs: <?php 
                echo (int)$view->imageConfig->nav_slideshow == Lists\InformationMainImageSlideShow::NAVIGATION_THUMBNAIL  ? 'true':'false';
            ?>,
            dots: <?php 
                echo (int)$view->imageConfig->nav_slideshow == Lists\InformationMainImageSlideShow::NAVIGATION_CIRCLE     ? 'true':'false';
            ?>,
            numbers: <?php 
                echo (int)$view->imageConfig->nav_slideshow == Lists\InformationMainImageSlideShow::NAVIGATION_NUMBER     ? 'true':'false';
            ?>,
            bars: <?php 
                echo (int)$view->imageConfig->nav_slideshow == Lists\InformationMainImageSlideShow::NAVIGATION_BAR        ? 'true':'false';
            ?>,
            classSpeed: '<?php echo $view->classSpeedBar;?>',
            speed: <?php echo $view->configSlick->autoplaySpeed ?>,
            navigation: <?php echo $view->configSlick->arrows? 'true':'false' ?>,
<?php   endif; ?>
        };
    </script>
    <?php endif; ?>
    <?php $tag = $view->fetch_tag;; ?>
    <?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->google_analytics_code) echo trim($tag->google_analytics_code); ?>
    <?php if ($view->mode == config('constants.publish_type.TYPE_PUBLIC') && $tag && $tag->above_close_head_tag) echo trim($tag->above_close_head_tag); ?>
    <?php echo $view->partial('common/tag/_above_close_head.blade.php', array('tag' => $tag, 'page_type_code' => $view->page->page_type_code, 'mode' => $view->mode)); ?>
    <?php if (!$view->isTop && !in_array($view->page->page_type_code, $hpPageTable->getCategoryMap()[HpPageRepository::CATEGORY_FORM]))  $view->includePartial('breadcrumb_ld_json', $view->{'breadcrumb_ld_json'}); ?> 
    <script> 
    $(document).ready(function () {
        $('.breadcrumb').find('li').each(function() {
            $(this).replaceWith(function() {
                return $('<li>').append($(this).contents());
            })
        }); 
    });  
    </script>
</head>
