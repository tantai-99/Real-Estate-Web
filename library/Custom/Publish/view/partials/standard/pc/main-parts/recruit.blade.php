<?php use App\Repositories\HpPage\HpPageRepository; ?>
<?php foreach ($view->element->elements->getSubForms() as $form) : ?>
    <div class="element">
        <table class="element-table element-table1">
            <?php foreach ($form->getFieldsForHTMLTable() as $field) : ?>
                <tr>
                    <th>
                        <?php //echo h($field->title ? $field->getValue('title') : $field->getTitle()) ?>
                        <?php echo h($field->getTitle()) ?>
                    </th>
                    <td>
                        <?php if ($field->value instanceof \Library\Custom\Form\Element\Textarea || $field->value instanceof \Library\Custom\Form\Element\Wysiwyg): ?>
                            <?php echo $field->getValue('value') ?>
                        <?php else: ?>
                            <?php echo h($field->getValue('value')) ?>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    </div>
    <?php
    $images = $form->getImages();
    $image_count = count($images);
    $element_class = $image_count > 1 ? 'element-' . $image_count . 'division' : 'tac';
    ?>
    <?php if ($image_count > 0): ?>
        <div class="element <?php echo $element_class ?>">
            <?php foreach ($images as $image): ?>
                <?php if ($image_count > 1): ?>
                    <div class="element-parts">
                <?php endif ?>

                <img src="<?php echo $view->hpImage($image['id']) ?>" alt="<?php echo $image['title'] ?>"/>

                <?php if ($image_count > 1): ?>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="element">
        <p class="element-tx tac">
            <?php
            	$url	= $view->hpLink()->type( HpPageRepository::TYPE_FORM_CONTACT )	;
            	$path	= parse_url( $url )[ 'path' ]											;
            ?>
            <a href="<?php echo $path ?>" class="btn-lv2" target="_blank">
                <?php echo h($form->getValue('industry')) ?>へのエントリーはこちらから</a>
        </p>
    </div>
<?php endforeach ?>
