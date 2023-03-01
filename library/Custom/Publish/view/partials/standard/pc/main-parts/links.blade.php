<?php $last_index = count($view->element->elements->getSubForms()) - 1 ?>

<?php foreach ($view->element->elements->getSubForms() as $key => $link): ?>
    <div class="element element-tximg4 <?php if ($key !== $last_index) echo 'element-line'; ?>">
        <p class="element-left">
            <?php if ($link->getValue('image')): ?>
                <img src="<?php echo $view->hpImage($link->getValue('image')) ?>" alt="<?php echo h($link->getValue('image_title')) ?>"/>
            <?php else: ?>
                <img src="<?php $view->src('imgs/img_nowprinting_s.png') ;?>" alt="now printing">
            <?php endif ?>
        </p>
        <div class="element-right">
            <section>
                <h3 class="element-heading">
                    <a href="<?php echo h($link->getValue('url')) ?>" target="_blank" ><?php echo h($link->getValue('name')) ?></a>
                </h3>
                <p><?php echo $link->getValue('description') ?></p>
            </section>
        </div>
    </div>
<?php endforeach ?>
