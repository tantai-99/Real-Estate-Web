<div class="element">
	<p class="element-tx"><?php echo $view->element->getValue('description') ?></p>
</div>

<?php foreach ($view->element->elements->getSubForms() as $point): ?>
	<div class="element">
		<?php // if( $view->hp->theme_id == 22 ) : ?>
		<?php if( isset($view->theme->name) && $view->theme->name == 'natural02_custom_color' ) : ?>
			<h3 class="heading-lv2"><?php echo $point->getValue('point') ?></h3>
		<?php else: ?>
			<p class="element-tx heading-lv2"><?php echo $point->getValue('point') ?></p>
		<?php endif;?>
	</div>
	<?php foreach ($point->elements->getSubForms() as $item): ?>
		<section>
			<?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $item->getValue('title'), 'level' => 2, 'element' => null)) ?>
			<div class="element">
				<?php if ($item->getValue('image')): ?>
					<p class="element-img-right element-inline">
						<img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
					</p>
				<?php endif ?>
				<p><?php echo $item->getValue('description') ?></p>
			</div>
		</section>
	<?php endforeach ?>
<?php endforeach ?>
