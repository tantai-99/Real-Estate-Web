<?php /*
<h4 class="heading-lv3"><?php echo $view->element->getValue('title') ?></h4>
*/?>
<div class="element">
	<?php if ($view->element->getValue('image')): ?>
		<p class="element-tx">
			<img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>" alt="<?php echo h($view->element->getValue('image_title')) ?>">
		</p>
	<?php endif; ?>
	<div class="element-tx"><?php echo $view->element->getValue('description') ?></div>
</div>

<?php foreach ($view->element->elements->getSubForms() as $item): ?>
	<?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $item->getValue('title'), 'level' => 2, 'element' => null)) ?>
	<?php if ($item->getValue('image')): ?>
		<div class="element-parts-list">
			<div class="element">
				<p>
					<img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
				</p>
			</div>
		</div>
	<?php endif; ?>
	<div class="element">
		<div class="element-tx"><?php echo $item->getValue('description') ?></div>
	</div>
<?php endforeach ?>
