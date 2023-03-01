<?php
use Library\Custom\Model\Estate;
use Library\Custom\Publish\Render\AbstractRender;
?>
<section>
    <h2 class="heading-lv1"><span><span><?php if ($view->is404) : ?>ページが見つかりません<?php else : ?>サイトマップ<?php endif; ?></span></span></h2>

    <?php $map = $view->getPublishEstateInstance()->getMap(); ?>
    <?php $typeList = Estate\TypeList::getInstance(); ?>
    <?php $domain = AbstractRender::protocol($view->mode).AbstractRender::www($view->mode).AbstractRender::prefix($view->mode).$view->company->domain ;?>

    <?php if (count($map) > 0) : ?>
        <section>
            <h3 class="heading-lv2">物件検索</h3>
            <div class="element">
                <ul class="link-pagelist">
                    <?php foreach ([Estate\ClassList::RENT, Estate\ClassList::PURCHASE,] as $rent_or_purchase) : ?>
                        <?php if (isset($map[$rent_or_purchase])) : ?>
                            <?php foreach ($map[$rent_or_purchase] as $class => $array): ?>
                                <?php foreach ($array as $type => $name): ?>
                                    <li><a href="<?php echo "{$domain}/{$typeList->getUrl($type)}/"; ?>"><?php echo $name; ?></a></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif ;?>

    <section>
        <h3 class="heading-lv2"><?php if ($view->is404) : ?>お探しページは、一時的にアクセスが出来ない状況にあるか、移動もしくは削除された可能性があります。<br>下記より、他のコンテンツをお探しいただければと思います。<?php else : ?>コンテンツ<?php endif; ?></h3>
        <div class="element">
            <ul class="link-pagelist">
                <?php foreach ($view->contentsList as $page): ?>
                    <li><a <?= $view->hpHref($page); ?>><?php echo h($page['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section>
        <h3 class="heading-lv2">お問い合わせ</h3>
        <div class="element">
            <ul class="link-pagelist">
                <?php foreach ($view->contactList as $page): ?>
                    <li><a <?= $view->hpHref($page); ?>><?php echo h($page['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</section>