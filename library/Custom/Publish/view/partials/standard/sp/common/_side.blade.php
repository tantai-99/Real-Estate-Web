<?php foreach ($view->hp->getSortedSideLayout() as $layout):?>
    <?php /* 非表示はスキップ */?>
    <?php if (!$layout['display']) continue;?>

    <?php switch ($layout['id']):
        case config('constants.hp.SIDELAYOUT_OTHER_LINK'):?>
            <?php /* ページ毎に異なる為パラメータで受け取る */?>
            <?php echo '<?php echo $data["other_link"];?>'?>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_CUSTOMIZED_CONTENTS'):?>
            <?php /* ページ毎に異なる為パラメータで受け取る */?>
            <?php echo '<?php echo $data["customized_contents"];?>'?>
        <?php break;?>
        <?php case config('constants.hp.SIDELAYOUT_ARTICLE_LINK'):?>
            <?php echo '<?php echo $data["article_link"];?>'?>
    <?php endswitch;?>
<?php endforeach;?>