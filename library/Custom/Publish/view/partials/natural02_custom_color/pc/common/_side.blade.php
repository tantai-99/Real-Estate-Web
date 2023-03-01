<?php
use Library\Custom\Model\Estate;
?>
<?php $publishEstateMap = $view->getPublishEstateInstance()->getMap(); ?>
<?php $typeList = Estate\TypeList::getInstance(); ?>

<?php
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
?>
<?php foreach ($view->hp->getSortedSideLayout() as $layout):?>
    <?php /* 非表示はスキップ */?>
    <?php if (!$layout['display']) continue;?>

    <?php switch ($layout['id']):
        case config('constants.hp.SIDELAYOUT_ESTATE_RENT'):?>
            <?php /* 表示項目がなければはスキップ */?>
            <?php if (!$view->getPublishEstateInstance()->hasRentEstateTypes) continue 2;?>
            
            <div class="side-search">
                <?php foreach ([Estate\ClassList::RENT] as $rent_or_purchase) : ?>
                    <?php if (isset($publishEstateMap[$rent_or_purchase])) : ?>
                        <section>
                            <h3 class="side-search-heading <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>rent<?php else : ?>buy<?php endif; ?>">
                                <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>賃貸物件検索<?php else : ?>売買物件検索<?php endif; ?>
                            </h3>
                            <ul>
                                <?php foreach ($publishEstateMap[$rent_or_purchase] as $class => $array): ?>
                                    <?php foreach ($array as $type => $name): ?>
                                        <li class="<?php echo $li_class[$type]; ?>"><a href="<?php echo "/{$typeList->getUrl($type)}/"; ?>"><?php echo $name; ?></a></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_ESTATE_PURCHASE'):?>
            <?php /* 表示項目がなければはスキップ */?>
            <?php if (!$view->getPublishEstateInstance()->hasPurchaseEstateTypes) continue 2;?>


            <div class="side-search">
                <?php foreach ([Estate\ClassList::PURCHASE] as $rent_or_purchase) : ?>
                    <?php if (isset($publishEstateMap[$rent_or_purchase])) : ?>
                        <section>
                            <h3 class="side-search-heading <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>rent<?php else : ?>buy<?php endif; ?>">
                                <?php if ($rent_or_purchase === Estate\ClassList::RENT) : ?>賃貸物件検索<?php else : ?>売買物件検索<?php endif; ?>
                            </h3>
                            <ul>
                                <?php foreach ($publishEstateMap[$rent_or_purchase] as $class => $array): ?>
                                    <?php foreach ($array as $type => $name): ?>
                                        <li class="<?php echo $li_class[$type]; ?>"><a href="<?php echo "/{$typeList->getUrl($type)}/"; ?>"><?php echo $name; ?></a></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_OTHER_LINK'):?>
            <?php /* ページ毎に異なる為パラメータで受け取る */?>
            <?php echo '<?php echo $data["other_link"];?>'?>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_CUSTOMIZED_CONTENTS'):?>
            <?php /* ページ毎に異なる為パラメータで受け取る */?>
            <?php echo '<?php echo $data["customized_contents"];?>'?>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_ARTICLE_LINK'):?>
            <?php /* ページ毎に異なる為パラメータで受け取る */?>
            <?php echo '<?php echo $data["article_link"];?>'?>
        <?php break;?>
    <?php endswitch;?>
<?php endforeach;?>
