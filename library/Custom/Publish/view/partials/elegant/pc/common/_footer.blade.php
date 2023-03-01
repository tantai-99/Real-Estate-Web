<footer class="page-footer" role="contentinfo">
  <div class="inner">
    <div class="company-info">
      <address class="company-address"><?php echo h($view->hp->adress); ?></address>
      <p class="company-name"><?php echo h($view->hp->company_name); ?></p>
      <p class="company-tel"><?php if ($view->hp->tel) : ?>TEL：<?php echo h($view->hp->tel); ?><?php endif; ?></p>
    </div>
    <ul class="gnav2">
      <li><a <?php echo $view->hpHref($view->pageContact); ?> target="_blank">お問い合わせ</a></li>
      <?php foreach ($view->pageCompany as $company) : ?>
        <li><a <?php echo $view->hpHref($company); ?>><?php echo h($company['title']); ?></a></li>
      <?php endforeach; ?>
      <li><a <?php echo $view->hpHref($view->privacypolicy); ?>>プライバシーポリシー</a></li>
      <li><a <?php echo $view->hpHref($view->sitepolicy); ?>>サイトポリシー</a></li>
    </ul>
    <?php if ($view->hp->fb_like_button_flg || $view->hp->tw_tweet_button_flg) : ?>
      <div class="footer-sns">
        <?php if ($view->hp->fb_like_button_flg) : ?>
          <?php echo $view->partial('common/_fblike.blade.php'); ?>
        <?php endif; ?>
        <?php if ($view->hp->tw_tweet_button_flg) : ?>
          <?php echo $view->partial('common/_tweetbtn.blade.php'); ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</footer>
<div class="device-change">
  <p>表示切替：<a href="javascript:void(0);" data-device="sp">スマートフォン版</a></p>
</div>
<div class="copyright">
  <?php if ($view->hp->copylight) : ?>
    <p class="cr">
      <small>Copyright(c) <?php echo h($view->hp->copylight); ?> .All Rights Reserved.</small>
    </p>
  <?php endif; ?>
</div>