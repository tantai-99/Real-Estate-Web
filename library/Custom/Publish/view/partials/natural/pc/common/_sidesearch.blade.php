<?php
use Library\Custom\Model\Estate;
?>
<?php $map = $view->getPublishEstateInstance()->getMap(); ?>
<?php $typeList = Estate\TypeList::getInstance(); ?>

<div class="side-search">
    <?php foreach ([Estate\ClassList::RENT, Estate\ClassList::PURCHASE,] as $rent_or_purchase) : ?>
        <?php if (isset($map[$rent_or_purchase])) : ?>
            <section>
                <h3 class="side-search-heading <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>rent<?php else : ?>buy<?php endif; ?>">
                    <span>
                        <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>賃貸物件検索<?php else : ?>売買物件検索<?php endif; ?>
                    </span>
                </h3>
                <ul>
                    <?php foreach ($map[$rent_or_purchase] as $class => $array): ?>
                        <?php foreach ($array as $type => $name): ?>
                            <li><a href="<?php echo "/{$typeList->getUrl($type)}/"; ?>"><?php echo $name; ?></a></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
