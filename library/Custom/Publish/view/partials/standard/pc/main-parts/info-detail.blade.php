<?php
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
if ($view->isTopOriginal) {
    $notificationClass = $view->page->form->getSubForm('tdk')->getValue('notification_class');
    if ($notificationClass != '0' && $notificationClass != null) {
        $hpMainPart = \App::make(HpMainPartsRepositoryInterface::class)->find($notificationClass);
    }
}
?>
<div class="element element-info-content<?php echo $view->isTopOriginal ? ' element-news-top' : ''?>"<?php echo isset($hpMainPart) ? ' data-category="'.$hpMainPart->attr_2.'"' : ''?><?php echo isset($hpMainPart) ? ' data-category-class="'.$hpMainPart->attr_3.'"' : ''?>>
    <?php foreach ($view->element->elements->getSubForms() as $el): ?>
        <?php if ($el->getValue('type') === 'image'): ?>
            <p class="element-tx tac">
                <img src="<?php echo $view->hpImage($el->getValue('image')) ?>" alt="<?php echo h($el->image_title->getValue()) ?>"/>
            </p>
        <?php else: ?>
            <p class="element-tx">
                <?php echo $el->getValue('value')?>
            </p>
        <?php endif ?>
    <?php endforeach ?>
</div>
