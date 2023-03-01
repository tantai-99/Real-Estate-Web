<?php
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
$link = true;
foreach ($view->element->elements->getSubForms() as $element) {
$company = \App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($element->getHp()->id);
    if ($company->cms_plan == config('constants.cms_plan.CMS_PLAN_LITE')) {
        if ($element->getValue('link_type') == config('constants.link_type.PAGE')) {
            if (!is_numeric($element->getValue('link_page_id'))) {
                $link = false;
                break;
            }
        }
    }   
}
?>
<?php if ($link) :?>
<?php if ($view->element->getValue('heading')) : ?>
  <section>
  <h3 class="side-others-heading"><?= h($view->element->getValue('heading')) ?></h3>
<?php endif; ?>
  <ul class="side-others-link">
    <?php foreach ($view->element->elements->getSubForms() as $element): ?>
      <li>
        <?php // サイト内リンク ?>
        <?php if ($element->getValue('link_type') == config('constants.link_type.PAGE')): ?>

          <?php // 通常ページ ?>
          <?php if (is_numeric($element->getValue('link_page_id'))): ?>
            <?php foreach ($view->all_pages as $page) : ?>
              <?php if ($page['link_id'] == $element->getValue('link_page_id')) : ?>
                <?php if (in_array($page['page_type_code'], \App::make(HpPageRepositoryInterface::class)->getCategoryMap()[HpPageRepository::CATEGORY_FORM])) : ?>
                  <a href="<?= $view->hpLink($element->getValue('link_page_id')) ?>" <?php if ($element->getValue('link_target_blank')) : ?>target="_blank"<?php endif; ?>><?= h($page['title']) ?></a>
                <?php elseif(isset($page['title']) && !empty($page['title'])) : ?>
                  <a href="<?= $view->hpLink($element->getValue('link_page_id')) ?>" <?php if ($element->getValue('link_target_blank')) : ?>target="_blank"<?php endif; ?>><?= h($page['title']) ?></a>
                <?php endif; ?>
                <?php break; ?>
              <?php endif; ?>
            <?php endforeach; ?>

            <?php // 物件検索、特集ページ ?>
          <?php else: ?>
            <?php $info = $view->getEstateLinkInfo($element->getValue('link_page_id'), $element->getHp()); ?>
            <?php if (isset($info['title']) && !empty($info['title'])): ?>
            <a href="<?= $info['url'] ?>" <?php if ($element->getValue('link_target_blank')) : ?>target="_blank"<?php endif; ?>><?= $info['title'] ?></a>
            <?php endif ?>
          <?php endif ?>

        <?php elseif ( $element->getValue( 'link_type' ) == config('constants.link_type.URL') ): ?>
          <?php // 外部リンク ?>
          <a href="<?= h($element->getValue('link_url')) ?>" <?php if ($element->getValue('link_target_blank')) : ?>target="_blank"<?php endif; ?>><?= h($element->getValue('link_label')) ?></a>
        <?php elseif ( $element->getValue( 'link_type' ) == config('constants.link_type.FILE') ): ?>
          <?php // ファイル ?>
          <a href="<?php echo $view->hpFile2( $element->getValue( 'file2' ) ) ?>" target="<?php echo ( $element->getValue( 'link_target_blank' ) ? '_blank' : '_self' ) ; ?>">
            <?php echo h( $element->getValue( 'file2_title' ) ) ?>
          </a>
        <?php elseif ( $element->getValue( 'link_type' ) == config('constants.link_type.HOUSE') ): ?>
          <?php // 物件詳細を選ぶ ?>
          <a href="<?php echo $view->hpLinkHouse( $element->getValue( 'link_house' ) ) ?>" target="<?php echo ( $element->getValue( 'link_target_blank' ) ? '_blank' : '_self' ) ; ?>">
            <?php echo h( $element->getValue( 'link_house_title' ) ) ?>
          </a>
        <?php endif ?>
      </li>
    <?php endforeach ?>
  </ul>
<?php if ($view->element->getValue('heading')) : ?>
  </section>
<?php endif; ?>
<?php endif; ?>