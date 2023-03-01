<div class="element">
    <p><?php echo $view->element->getValue('description') ?></p>
</div>

<?php foreach ($view->element->elements->getSubForms() as $item): ?>
<h4 class="heading-lv3"><?php echo h($item->getValue('title')) ?></h4>
<div class="element">
    <?php if ($item->getValue('image')): ?>
	<div class="element-parts-list">
	    <p>
    	    <img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
    	</p>
    </div>
    <?php endif ?>
	<div class="element-parts-list">
    <p><?php echo $item->getValue('description') ?></p>
    </div>
</div>
<?php endforeach ?>
