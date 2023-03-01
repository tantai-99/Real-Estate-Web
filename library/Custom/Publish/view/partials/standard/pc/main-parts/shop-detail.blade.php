<div class="element pr_comment">
   <?php echo $view->element->getValue('pr') ?>
</div>

<?php echo $view->partial('main-parts/multi-images.blade.php', array('element' => $view->element)) ?>

<div class="element">
    <table class="element-table element-table1">
        <?php foreach ($view->element->elements->getSubForms() as $element): ?>
            <tr>
                <?php
                    $title = $element->getTitle();
                    $class = null;
                    if ($title === '住所') $class = 'adress';
                    if ($title === 'TEL') $class = 'tel';
                ?>
                <th><?php echo h($title) ?></th>
                <td <?php if ($class):?>class="<?php echo $class ;?>"<?php endif ;?>><?php echo h($element->getValue('value')) ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>