<?php
$elementClass = 'element-parts-list';
$outside_header = $view->area->getColumnCount() === 1;
if ($outside_header) {
    echo '<section>';
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element));
    $elementClass = 'element';
}
?>
    <div class="<?php echo $elementClass ?>">
        <?php if (!$outside_header) {
            echo '<section>';
            echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'inside_division' => true));
        }
        ?>
        <table class="element-table element-table1">
            <?php foreach ($view->element->elements->getSubForms() as $form): ?>
                <?php if ($form instanceof \Library\Custom\Hp\Page\Parts\Element\Image) continue; ?>
                <tr>
                    <th>
                        <?php echo h($form->title ? $form->getValue('title') : $form->getTitle()); ?>
                    </th>
                    <td>
                        <?php if ($form->value instanceof \Library\Custom\Form\Element\Textarea): ?>
                            <?php echo $form->getValue('value'); ?>
                        <?php else: ?>
                            <?php echo h($form->getValue('value')); ?>
                        <?php endif ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$outside_header) {
            echo '</section>';
        }
        ?>
    </div>
<?php
if ($outside_header)
    echo '</section>';
?>