<?php
use Library\Custom\Publish\Render\AbstractRender;
$siteUrl = AbstractRender::protocol($view->mode).AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain;
?>
<div class="line-it-button" data-lang="ja" data-type="share-a" data-url="<?php echo $siteUrl; ?>" style="display: none;"></div>
<script src="https://d.line-scdn.net/r/web/social-plugin/js/thirdparty/loader.min.js" async="async" defer="defer"></script>