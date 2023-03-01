<?php
use Library\Custom\Publish\Render\AbstractRender;
$siteUrl = AbstractRender::protocol($view->mode).AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain; ?>
<footer class="page-footer" role="contentinfo">
    <ul class="gnav2">
        <li><a <?php echo $view->hpHref($view->pageContact); ?>>お問い合わせ</a></li>
        <?php foreach ($view->pageCompany as $company) : ?>
            <li><a <?php echo $view->hpHref($company); ?>><?php echo h($company['title']); ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?php echo $siteUrl; ?>/sitemap/">サイトマップ</a></li>
        <li><a <?php echo $view->hpHref($view->privacypolicy); ?>>プライバシーポリシー</a></li>
        <li><a <?php echo $view->hpHref($view->sitepolicy); ?>>サイトポリシー</a></li>
    </ul>
        <?php if ($view->hp->fb_like_button_flg || $view->hp->tw_tweet_button_flg || $view->hp->line_button_flg) : ?>
            <div class="footer-sns">
                <?php if ($view->hp->fb_like_button_flg) echo $view->partial('common/_fblike.blade.php'); ?>
                <?php if ($view->hp->tw_tweet_button_flg) echo $view->partial('common/_tweetbtn.blade.php'); ?>
                <?php if ($view->hp->line_button_flg) echo $view->partial('common/_linebtn.blade.php', array('hp' => $view->hp,'mode' => $view->mode)); ?>
            </div>
        <?php endif; ?>
    <div class="device-change"><p>表示切替：<a href="javascript:void(0);" data-device="pc">PC版</a></p></div>
    <?php if ($view->hp->copylight) : ?>
        <p class="cr">
            <small>Copyright(c) <?php echo h($view->hp->copylight); ?> .All Rights Reserved.</small>
        </p>
    <?php endif; ?>
</footer>