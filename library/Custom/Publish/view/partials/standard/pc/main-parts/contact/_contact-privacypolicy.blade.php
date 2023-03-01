<?php
use App\Repositories\HpPage\HpPageRepositoryInterface;
$privacypolicyRow = \App::make(HpPageRepositoryInterface::class)->find($view->privacypolicy['id']);
$privacypolicy = \Library\Custom\Hp\Page::factory($view->hp, $privacypolicyRow);
$privacypolicy->init();
$privacypolicy->load();
$privacypolicy->setFiltersForPublish();
?>

<?php if (getActionName() !== 'previewPage'){
    $view->getInnerHtml();
} ?>

<h3 class="form-privacy-heading-lv1">プライバシーポリシー</h3>
<?php if (getActionName() == 'previewPage') : ?>
    <div class="form-privacy">
        <?php foreach ($privacypolicy->form->getSubForm('main')->getSubForms() as $area) : ?>
            <?php foreach ($area->getPartsByColumn() as $column): ?>
                <?php foreach ($column as $parts): ?>
                    <?php if ('0' === $parts->getValue('display_flg')) continue ?>
                    <?php if ($parts instanceof \Library\Custom\Hp\Page\Parts\Privacypolicy): ?>
                        <?php
                        $policy = $view->partial($parts->getTemplate('main-parts/'), array('element' => $parts, 'area' => $area, 'page' => $view->page, 'all_pages' => $view->all_pages,));
                        // 不要なタグ削除
                        $policy = str_replace('<div class="element policy">', '', $policy);
                        $policy = preg_replace('/<\/div>$/', '', $policy);
                        echo $policy;; ?>
                    <?php endif ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php else : ?>

    <?php // 本番はプライバシーポリシーページからインクルード ;?>
    <?php
    echo <<<'EOD'
            <?php

            foreach ($viewHelper->getPages() as $page) {

                // プライバシーポリシー
                if ($page['page_type_code'] == 16) {
                    break;
                }
            }

            // パス
            $path = $viewHelper->_view->viewPath.$viewHelper->_view->device.DIRECTORY_SEPARATOR.$page['new_path'];

            // dom取得
            $dom = new DOMDocument();
            @$dom->loadHTMLFile($path);
            $xpath = new DOMXPath($dom);

            $node = $xpath->query('//div[@class="element policy"]')->item(0);
            $policy = $node !== null ? getInnerHtml($node) : '';

            // 不要なタグ削除
            $policy = str_replace('<div class="element policy">', '', $policy);
            $policy = preg_replace('/<\/div>$/', '', $policy);
            $policy = str_replace('<p class="element-tx">', '', $policy);
            $policy = preg_replace('/<\/p>$/', '', $policy);

            ?>
EOD;
    ?>
    <div class="form-privacy">
        <?php echo '<?php echo $policy ;?>'; ?>
    </div>
<?php endif; ?>

