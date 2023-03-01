<?php
$hp = $view->page->getHp();
if (!$hp->tw_username || !$hp->tw_timeline_flg) {
    return;
}
$attr_widget_id = $hp->tw_widget_id ? 'data-widget-id="'.$hp->tw_widget_id.'"': '';
?>

<div class="side-others-sns">
    <a class="twitter-timeline"
       href="https://twitter.com/<?php echo $hp->tw_username ?>"
       data-height="350"
       data-screen-name="<?php echo $hp->tw_username ?>"
       <?php echo $attr_widget_id;?>>@<?php echo $hp->tw_username ?>さんのツイート</a>
    <script>!function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
            if (!d.getElementById(id)) {
                js = d.createElement(s);
                js.id = id;
                js.src = p + "://platform.twitter.com/widgets.js";
                fjs.parentNode.insertBefore(js, fjs);
            }
        }(document, "script", "twitter-wjs");</script>
</div>

