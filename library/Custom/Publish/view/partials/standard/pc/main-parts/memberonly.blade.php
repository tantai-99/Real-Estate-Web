<section>
    <h3 class="heading-lv2">ログインフォーム</h3>
    <div class="element element-login">
        <form action='<?php echo '<?php echo (empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; ?>'; ?>' method="post">

            <?php if (!$view->isPreview) echo file_get_contents($view->getScriptPath('main-parts/memberonly/error.php')); ?>
            <?php if (!$view->isPreview) echo file_get_contents($view->getScriptPath('main-parts/memberonly/token.php')); ?>
            <dl>
                <dt>会員ID</dt>
                <dd><input type="text" value="" name="id"></dd>
                <dt>パスワード</dt>
                <dd><input type="password" value="" name="pass"></dd>
            </dl>
            <p class="tac">
                <input type="submit" value="ログイン" class="btn-lv1"  <?php if ($view->isPreview) echo 'disabled="disabled"'; ?>>
            </p>
        </form>
        <?php
           	$url	= $view->HpHref( $view->contact )	;
         	$path	= "/{$view->contact['filename']}/"	;
        ?>
        <p class="tx-forget">ログインのID、またはパスワードをお忘れの方は、<br>
        <a href="<?php echo $path ; ?>" target="_blank" class="bold">こちらのお問い合わせフォーム</a>よりご連絡ください。</p>
    </div>
</section>