<section>
	<?php $element = $view->element; ?>
	<?php if(trim($view->element->getValue('heading'))):?>
    <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $view->element->getValue('heading') , 'level' => 1, 'element' => null)) ?>
	<?php endif;?>
    <div class="element element-freeword-er">
        <?php echo $element->getValue('path') ?>
    </div>
</section>