<div class="element">
    <?php foreach ($view->element->elements->getSubForms() as $el): ?>
        <?php if ($el->getValue('type') === 'image'): ?>
            <p class="element-tx tac">
                <img src="<?php echo $view->hpImage($el->getValue('image')) ?>"  alt="<?php echo $el->image_title->getValue() ?>"/>
            </p>
        <?php else: ?>
            <p class="element-tx">
                <?php echo $el->getValue('value') ?>
            </p>
        <?php endif ?>
    <?php endforeach ?>
</div>
