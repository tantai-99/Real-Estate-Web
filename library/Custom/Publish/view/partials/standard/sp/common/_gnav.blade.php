<?php
use Library\Custom\Publish\Render\AbstractRender;
use Library\Custom\Model\Estate;
$estate = $view->getPublishEstateInstance();
$map    = $estate->getMap();
$list   = [];
$gnav   = $view->gnav_sp;
$url = '';
if ($view->isTopOriginal) {
    $protocol = AbstractRender::protocol($view->mode);
    $domain = AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain;

    // 営業デモ用サイトだと、HTTPSは、使わない
    $config = getConfigs('sales_demo');
    if (strpos($domain, $config->demo->domain)) {
      $protocol = 'http://';
    }
    $url = $protocol.$domain;
}
foreach ($view->hp->getSortedSideLayout() as $layout) {
  // その他リンク
  if (((int) $layout['id']) === config('constants.hp.SIDELAYOUT_OTHER_LINK')) {
    // その他リンクが非表示の場合、Gナビのみ
    if (!$layout['display']) {
      $gnav = [];
      foreach ($view->gnav_sp as $page) {
        if (isset($page['is_gnav']) && $page['is_gnav']) {
          $gnav[] = $page;
        }
      }
    }
    continue;
  }

  if (!$layout['display']) continue;
  if (((int) $layout['id']) === config('constants.hp.SIDELAYOUT_ESTATE_RENT')) {
    $list[] = Estate\ClassList::RENT;
  } else if (((int) $layout['id']) === config('constants.hp.SIDELAYOUT_ESTATE_PURCHASE')) {
    $list[] = Estate\ClassList::PURCHASE;
  }
}
?>
<nav class="gnav" role="navigation">
  <p class="gnav-close"><span class="btn-close">閉じる</span></p>
  <ul>
    <?php // お気に入り物件、最近見た物件 ?>
    <?php if (count($estate->estateTypes) > 0): ?>
      <li class="gnav-favhistory"><a href="<?php echo $url; ?>/personal/favorite/">お気に入り物件</a></li>
      <li class="gnav-favhistory"><a href="<?php echo $url; ?>/personal/history/">最近見た物件</a></li>
    <?php endif; ?>

    <?php $isFirst = true; ?>

    <?php foreach ($gnav as $page) : ?>
      <?php // 賃貸物件検索、売買物件検索 ?>
      <?php if ($isFirst): ?>
        <?php foreach ($list as $rent_or_purchase) : ?>
          <?php if (isset($map[$rent_or_purchase])) : ?>
            <?php $isRent = $rent_or_purchase === Estate\ClassList::RENT; ?>
            <li>
              <a href="<?php if ($isRent) : ?><?php echo $url; ?>/rent.html<?php else : ?><?php echo $url; ?>/purchase.html<?php endif; ?>">
                <?php if ($isRent) : ?>賃貸物件検索<?php else : ?>売買物件検索<?php endif; ?>
              </a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php $isFirst = false; ?>
      <?php //4787 Change postion 賃貸物件検索、売買物件検索 ?>
      <li><a <?= $view->hpHref($page); ?>><?= h($page['title']); ?></a></li>

    <?php endforeach; ?>
  </ul>
</nav>
