<?php
use Library\Custom\Model\Lists\InformationMainImageSlideShow;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
if ($mainImage = $view->page->form->getSubForm('main_image')) {
    $imageConfig = (Object)$mainImage->getValues('main_image');
    $imageConfig->lib=InformationMainImageSlideShow::LIB_SLICK;
    $configSlick = (Object)array('dots'=> true,'arrows'=> false,'infinite'=> true,'speed'=> 500,'slidesToShow'=> 1,'slidesToScroll'=> 1,'autoplay'=> true,'autoplaySpeed'=>5000);
    $view->navSlickClass = 'slick-dots';
    $view->classSpeedBar = '';
    if (isset($imageConfig->slide_show_flg)) {
        $imageConfig->lib = InformationMainImageSlideShow::getLibsEffect()[(int)$imageConfig->type_slideshow];
        $configSlick->autoplaySpeed =  InformationMainImageSlideShow::getValuesSpeed()[(int)$imageConfig->time_slideshow];
        switch ($imageConfig->type_slideshow) {
            case InformationMainImageSlideShow::EFFECT_VERTICAL:
                $configSlick->vertical = true;
                break;
            case InformationMainImageSlideShow::EFFECT_FADE_IN_OUT:
                $configSlick->fade = true;
                break;
            // case InformationMainImageSlideShow::EFFECT_CAROUSEL:
            //     $configSlick->centerMode = true;
            //     $configSlick->centerPadding = '50px';
            //     break;
        }
        $configSlick->arrows = (int)$imageConfig->arrow_slideshow == 1? true:false ;
        switch ($imageConfig->nav_slideshow){
            case InformationMainImageSlideShow::NAVIGATION_NONE:
            case InformationMainImageSlideShow::NAVIGATION_THUMBNAIL:
                $configSlick->dots = false;
                $configSlick->asNavFor = '.slider-thumb-nav';
                break;
            case InformationMainImageSlideShow::NAVIGATION_BAR:
                $view->classSpeedBar = InformationMainImageSlideShow::getClassFrontSpeedBar((int)$imageConfig->time_slideshow);
                $view->navSlickClass = InformationMainImageSlideShow::getClassFrontNavigation()[(int)$imageConfig->nav_slideshow];
                break;
            default:
                $view->navSlickClass = InformationMainImageSlideShow::getClassFrontNavigation()[(int)$imageConfig->nav_slideshow];
                break;
        }
    }
    $view->imageConfig = $imageConfig;
    $view->configSlick = $configSlick;
}
$hpPageTable = \App::make(HpPageRepositoryInterface::class);
?>
<?php
// set page_type_code for heading.blade.php
$view->registry('render:page_type_code', $view->page->getRow()->page_type_code + 0);
?>
<?php if ($view->page->form->getSubForm('tdk')): ?>
    <section <?php if($view->page->isArticlePage()): ?>class="article-section"<?php endif; ?>>
        <?php echo $view->partial('main-parts/heading.blade.php',
            array('heading' => $view->page->form->getSubForm('tdk')->getValue('title'),'level' => 0)) ?>
<?php endif; ?>
<?php if ($mainImage = $view->page->form->getSubForm('main_image')):
    $htmltags= InformationMainImageSlideShow::getHtmlTag($imageConfig->lib);
 ?>
    <div class="<?php echo InformationMainImageSlideShow::getClassName($imageConfig->lib) ?>">
     <?php echo  $htmltags['group']['begin']; ?>
        <?php foreach ($mainImage->getSubForms() as $key=>$form): ?>
            <?php if (!$form->getValue('image')) continue; ?>
            <?php $image = (Object)$form->getValues(); ?>
            <?php echo  $htmltags['item']['begin']; ?>
	            <?php if ( $image->link_url || $image->link_page_id || $image->file2 || (isset($image->link_house) && $image->link_house) ): ?>
	            	<?php
		            	$url	= "" ;
		            	switch ( $image->link_type )
		            	{
		            	  case config('constants.link_type.PAGE')	:
		           			$url = $view->hpLink(	$image->link_page_id	) ;
		           			break ;
		            	  case config('constants.link_type.URL')		:
		            	  	$url =					$image->link_url		  ;
		            	  	break ;
		            	  case config('constants.link_type.FILE')	:
		            	  	$url = $view->hpFile2( $image->file2			) ;
                              break ;
                          case config('constants.link_type.HOUSE')	:
                            $url = $view->hpLinkHouse( $image->link_house			) ;
                            break ;
		            	}
	            	?>
                    <a href="<?php echo $url ; ?>" target="<?php echo $image->link_target_blank ? '_blank' : '_self' ; ?>">
                        <img src="<?php echo $view->hpImage( $image->image ) ?>" alt="<?php echo $image->image_title ?>"/>
                    </a>
                <?php else: ?>
                    <img src="<?php echo $view->hpImage($image->image) ?>" alt="<?php echo $image->image_title ?>"/>
                <?php endif?>
            <?php echo  $htmltags['item']['end']; ?>
            <?php
            if(isset($imageConfig->slide_show_flg) && (int)$imageConfig->slide_show_flg == 0){
                break;
            }
            ?>
        <?php endforeach; ?>
        <?php echo  $htmltags['group']['end']; ?>
    </div>
<?php if(isset($imageConfig->slide_show_flg) && $imageConfig->lib == InformationMainImageSlideShow::LIB_SLICK && (int)$imageConfig->nav_slideshow == InformationMainImageSlideShow::NAVIGATION_THUMBNAIL): ?>
    <div class="slider-thumb-nav">
    <?php foreach ($mainImage->getSubForms() as $key=>$form): ?>
            <?php if (!$form->getValue('image')) continue; ?>
            <?php if(!is_numeric($key)) continue;?>
            <?php $image = (Object)$form->getValues(); ?>
            <div class="img-slide">
            <img src="<?php echo $view->hpImage($image->image) ?>" alt="<?php echo $image->image_title ?>"/>
            </div>
        <?php endforeach;?>
    </div>
    <?php endif;?>
<?php endif; ?>

<?php if ($view->page->form->getSubForm('memberonly')): ?>
    <?php
    $class_name = get_class($view->page);
    $template = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', substr($class_name, strrpos($class_name, '\\') + 1))).".blade.php";
    $pageView = $view->page;
    echo $view->partial('main-parts/' . $template, array('page' => $view->pages[$view->pageId], 'contact' => $view->pageContact, 'isPreview'=>$view->isPreview));
    $view->page = $pageView;
    ?>
<?php endif ?>

<?php if ($view->page->form->getSubForm('main')) : ?>

    <?php if ($view->page->getRow()->page_category_code==HpPageRepository::CATEGORY_FORM): ?>
        <?php if ($view->isPreview): ?>
            <?php $html = 'main-parts/contact/contact-preview.blade.php' ?>
            <?php if (in_array($view->page->getRow()->page_type_code, $hpPageTable->estateContactPageTypeCodeList())): ?>
                <?php $html = 'main-parts/contact/contact-estate-preview.blade.php' ?>
            <?php endif; ?>
            <?php if (in_array($view->page->getRow()->page_type_code, $hpPageTable->estateRequestPageTypeCodeList())): ?>
                <?php $html = 'main-parts/contact/request-estate-preview.blade.php' ?>
            <?php endif; ?>
            <?php echo $view->partial($html, array('page' => $view->page, 'all_pages' => $view->all_pages, 'hp'=>$view->hp, 'privacypolicy'=>$view->privacypolicy)); ?>
        <?php elseif($view->contactContent): ?>
           <?php echo $view->contactContent; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($view->page->getRow()->page_type_code == HpPageRepository::TYPE_INFO_DETAIL) :?>
        <?php
        if ($view->isTopOriginal) {
            $notificationClass = $view->page->form->getSubForm('tdk')->getValue('notification_class');
            if ($notificationClass != '0' && $notificationClass != null) {
                $hpMainPart = \App::make(\App\Repositories\HpMainParts\HpMainPartsRepositoryInterface::class)->find($notificationClass);
            }
        }
        ?>
        <div class="element element-info-date<?php echo $view->isTopOriginal ? ' element-top-news' : ''?>"<?php echo isset($hpMainPart) ? ' data-category="'.$hpMainPart->attr_2.'"' : ''?><?php echo isset($hpMainPart) ? ' data-category-class="'.$hpMainPart->attr_3.'"' : ''?>>
            <?php $date = date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $view->page->form->getSubForm('tdk')->getValue('date'))));?>
            <?php if ($view->isPreview) :?>
            <?php
            $pageIndex = $hpPageTable->fetchRow(array(['id', $view->page->getRow()->parent_page_id]));
            $newMark = $hpPageTable->checkNewMark($pageIndex->new_mark, $date);
            ?>
            <?php if ($newMark) :?>
            <p class="element-new-mark">
            <?php echo config('constants.new_mark.NEW_MARK');?>
            </p>
            <?php endif;?>
            <?php else :?>
            <?php echo '<?php $pageIndex = $this->viewHelper->getPageById('.$view->page->getRow()->parent_page_id.');?>'; ?>
            <?php echo '<?php $newMark = $this->viewHelper->checkNewMark($pageIndex, "'.$date.'");?>'?>
            <?php echo '<?php if ($newMark) :?>'; ?>
            <p class="element-new-mark">
            <?php echo config('constants.new_mark.NEW_MARK');?>
            </p>
            <?php echo '<?php endif; ?>';?>
            <?php endif; ?>
            <p class="element-date"><?php echo h($view->page->form->getSubForm('tdk')->getValue('date'))?></p>
        </div>
        <?php if (!is_null($view->page->form->getSubForm('tdk')->getValue('list_title'))) : ?>
        <div class="element-list-title" style="display : none">
            <?php echo $view->page->form->getSubForm('tdk')->getValue('list_title');?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($view->page->getRow()->page_type_code == HpPageRepository::TYPE_BLOG_DETAIL) :?>
        <div class="element">
            <p class="element-date"><?php echo h($view->page->form->getSubForm('tdk')->getValue('date'))?></p>
        </div>
    <?php endif; ?>

    <?php $infoNext = true;?>
    <?php foreach ($view->page->form->getSubForm('main')->getSubForms() as $area) : ?>
        <?php if ($area->getColumnCount() > 1): ?>
            <div class="element element-<?php echo $area->getColumnCount() ?>division">
        <?php endif; ?>

        <?php foreach ($area->getPartsByColumn() as $column): ?>
            <?php if ($area->getColumnCount() > 1): ?>
                <div class="element-parts">
            <?php endif; ?>

            <?php foreach ($column as $parts): ?>
                <?php if ('0' === $parts->getValue('display_flg')) continue ?>

                <?php if ($parts instanceof \Library\Custom\Hp\Page\Parts\InfoDetail): ?>
                    <?php if ($view->isTopOriginal) :?>
                    <?php echo $view->partial('main-parts/info-detail.blade.php', array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages, 'isTopOriginal' => $view->isTopOriginal)); ?>
                    <?php else :?>
                    <?php echo $view->partial('main-parts/info-detail.blade.php', array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages)); ?>
                    <?php endif ?>
                <?php elseif ($parts instanceof \Library\Custom\Hp\Page\Parts\BlogDetail): ?>
                    <?php echo $view->partial('main-parts/blog-detail.blade.php', array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages)); ?>
                <?php elseif ($parts instanceof \Library\Custom\Hp\Page\Parts\CompanyOutline): ?>
                    <?php echo $view->partial('main-parts/company.blade.php', array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages)); ?>
                <?php elseif ($parts instanceof \Library\Custom\Hp\Page\Parts\InfoList && getActionName() !== 'previewPage'): ?>
                    <?php
                    if ($infoNext) {
                        $view->includePartial('info_list');
                        $infoNext = $view->isTopOriginal ? false : true;
                    }
                    ?>
                <?php
                else: ?>
                    <?php echo $view->partial($parts->getTemplate('main-parts/'), array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages, 'hp' => $view->hp, 'inside_division' => false, 'heading' => null)); ?>
                <?php endif ?>
            <?php endforeach; ?>

            <?php if ($area->getColumnCount() > 1): ?>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>

        <?php if ($area->getColumnCount() > 1): ?>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>

<?php if ($view->page->form->getSubForm('list')): ?>
    <?php
    $class_name = get_class($view->page);
    $template = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', substr($class_name, strrpos($class_name, '\\') + 1))).".blade.php";
    if ($hpPageTable->includeDetailPage($class_name) && getActionName() !== 'previewPage') {
        $template = 'include/'.$template;
    }
    echo $view->partial('main-parts/' . $template, array('pages' => $view->page_list, 'listNumber' => $view->listNumber, 'listCount' => $view->listCount, 'page' => $view->page));
    ?>
<?php endif ?>

<?php if ($view->page->form->getSubForm('tdk')): ?>
    </section>
<?php endif; ?>
