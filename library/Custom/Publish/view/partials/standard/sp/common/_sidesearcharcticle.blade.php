<?php
use Library\Custom\Model\Estate;
?>
<?php $map = $view->getPublishEstateInstance()->getMap(); ?>
<?php $typeList = Estate\TypeList::getInstance();
$li_class = array(
    1 => "apartment",
    2 => "shop",
    3 => "building",
    4 => "parking",
    5 => "land",
    6 => "warehouse",
    7 => "apartment",
    8 => "house",
    9 => "land",
   10 => "shop",
   11 => "building",
   12 => "warehouse",
);
if (count($map) > 0): ?>
<div class="element-auto-search-housing">
    <?php foreach ([Estate\ClassList::RENT, Estate\ClassList::PURCHASE,] as $rent_or_purchase) : ?>
        <?php if (isset($map[$rent_or_purchase])) : ?>
            <section class="element-search-from-item">
                <h4 class="heading-search-from area <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>rent<?php else : ?>buy<?php endif; ?>">
                    <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>賃貸物件を探す<?php else : ?>売買物件を探す<?php endif; ?>
                </h4>
                <ul>
                    <?php foreach ($map[$rent_or_purchase] as $class => $array): ?>
                        <?php foreach ($array as $type => $name): ?>
                            <li class="<?php echo $li_class[$type]; ?>"><a href="<?php echo "/{$typeList->getUrl($type)}/"; ?>" target="_blank"><?php echo $name; ?></a></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>