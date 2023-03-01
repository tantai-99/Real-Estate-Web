<?php /*
<h4 class="heading-lv3"><span><?php echo $view->element->getValue('title') ?></span></h4>
*/?>
<div class="element">
	<?php if ($view->element->getValue('image')): ?>
		<p class="element-tx">
			<img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>" alt="<?php echo h($view->element->getValue('image_title')) ?>">
		</p>
	<?php endif; ?>
	<p class="element-tx"><?php echo $view->element->getValue('description') ?></p>
</div>
<?php foreach ($view->element->elements->getSubForms() as $item): ?>
	<section>
		<?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $item->getValue('title'), 'level' => 2, 'element' => null)) ?>
		<div class="element element-tximg3">
			<?php if ($item->getValue('image')): ?>
				<p class="element-left">
					<img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
				</p>
				<div class="element-right">
					<p><?php echo $item->getValue('description') ?></p>
				</div>
			<?php else : ?>
				<p class="element-tx"><?php echo $item->getValue('description') ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endforeach ?>
