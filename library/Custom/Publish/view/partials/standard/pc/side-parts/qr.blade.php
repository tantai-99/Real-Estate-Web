<?php
use App\Repositories\HpPage\HpPageRepository;
$heading = $view->element->getValue('heading');
?>
<div class="side-others-qr">
    <section>
    <?php if ( $heading ) :?>
    <h3 class="side-others-qr-heading"><?php echo h( $heading ) ?></h3>
    <?php endif ?>
    <p class="side-others-qr-img">
        <?php if ($view->page->getHp()->qr_code_type == 1) : ?>
            <?php
            $id = null;
            foreach ($view->all_pages as $val) {
                if ($val['page_type_code'] == HpPageRepository::TYPE_TOP) {
                    $id = $val['id'];
                    break;
                }
            }; ?>
            <img src="<?php echo $view->hpQRImage($id, $view->is_preview) ?>" alt="qr" style="width: 170px; height: 170px;">
        <?php else : ?>
            <!-- カスタマイズサイドコンテンツのQRコードは各ページごとのものを反映 -->
            <!-- プレビューの場合、QRコードのパスが異なるのでelseの処理にて反映 -->
            <!-- TOPオリジナル会員のトップページ場合、公開先でページIDが取得できないため、elseの処理にて反映 -->
            <?php if ($view->side_common && !$view->is_preview && !$view->isDispTopQr) : ?>
                <?php echo file_get_contents(file_exists(dirname(__FILE__).'/../script/qrIndividual.blade.php') ? dirname(__FILE__).'/../script/qrIndividual.blade.php' : dirname(__FILE__).'/../../../standard/pc/common/script/qrIndividual.blade.php'); ?>
            <?php else : ?>
                <img src="<?php echo $view->hpQRImage($view->page->getRow() ? $view->page->getRow()->id : null, $view->is_preview) ?>" alt="qr" style="width: 170px; height: 170px;">
            <?php endif; ?>
        <?php endif; ?>
    </p>
    <p class="side-others-qr-tx">スマートフォンサイトは、こちらからアクセスしてください。</p>
    </section>
</div>
