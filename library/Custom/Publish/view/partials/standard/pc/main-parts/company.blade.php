<section>
    <?php echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'heading' => null)) ?>
    <div class="element">
        <table class="element-table element-table1">
            <?php foreach ($view->element->elements->getSubForms() as $form): ?>
                <?php if ($form instanceof \Library\Custom\Hp\Page\Parts\Element\Image) continue; ?>
                <tr>
                    <th>
                        <?php echo h($form->title ? $form->getValue('title') : $form->getTitle()); ?>
                    </th>
                    <td>
                        <?php if ($form->value instanceof \Library\Custom\Form\Element\Textarea || $form->value instanceof \Library\Custom\Form\Element\Wysiwyg): ?>
                            <?php echo $form->getValue('value'); ?>
                        <?php elseif ($form->value instanceof \Library\Custom\Form\Element\Select): ?>
                            <?php echo h($form->getValueOptions()[$form->getValue('value')]) ?>
                        <?php else: ?>
                            <?php echo h($form->getValue('value')); ?>
                        <?php endif ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="element element-2division">
        <?php foreach ($view->element->elements->getSubForms() as $form): ?>
            <?php if (!$form instanceof \Library\Custom\Hp\Page\Parts\Element\Image) continue; ?>
            <div class="element-parts">
                <img src="<?php echo $view->hpImage($form->getValue('image')) ?>"  alt="<?php echo $form->getValue('image_title') ?>"/>
            </div>
        <?php endforeach ?>
    </div>
</section>
