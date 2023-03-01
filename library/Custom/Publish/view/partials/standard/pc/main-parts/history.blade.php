<div class="element">
    <section>
        <?php if ($view->area->getColumnCount() > 1) echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'inside_division' => true)); ?>

        <table class="element-table element-table3">
            <?php foreach ($view->element->elements->getSubForms() as $form): ?>
                <?php $event = (Object)$form->getValues();?>
                <tr>
                    <th class="th1">
                        <?php echo h($event->year) ?>年
                    </th>
                    <th class="th2">
                        <?php echo h($event->month) ?>月
                    </th>
                    <td>
                        <?php echo h($event->text) ?>
                        <?php if ($event->image): ?>
                            <img src="<?php echo $view->hpImage($event->image) ?>" alt="<?php echo $event->image_title ?>"/>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    </section>
</div>
