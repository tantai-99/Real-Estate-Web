<section>
    <h3 class="heading-lv2">ログインフォーム</h3>
    <div class="element">
        <p class="element-tx"></p>
    </div>
    <div class="element element-login">
        <form action='<?php echo '<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>'; ?>' method="post">
            <?php if (!$view->isPreview) echo file_get_contents($view->getScriptPath('main-parts/memberonly/error.blade.php')); ?>
            <?php if (!$view->isPreview) echo file_get_contents($view->getScriptPath('main-parts/memberonly/token.blade.php')); ?>
            <dl>
                <dt><span>会員ID</span></dt>
                <dd><input type="text" value="" name="id"></dd>
                <dt><span>パスワード</span></dt>
                <dd><input type="password" value="" name="pass"></dd>
            </dl>
            <p class="tac">
                <input type="submit" value="ログイン" class="btn-lv1"  <?php if ($view->isPreview) echo 'disabled="disabled"'; ?>>
            </p>
        </form>
        <p class="tx-forget">ログインのID、またはパスワードをお忘れの方は、<br>
        <a <?php echo $view->HpHref($view->contact); ?> class="bold">こちらのお問い合わせフォーム</a>よりご連絡ください。</p>
    </div>
</section>