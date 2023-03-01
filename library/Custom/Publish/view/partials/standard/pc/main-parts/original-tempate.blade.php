<div class="articles element">
    <div class="element">
        <?php if ($view->element->getValue('image')): ?>
        <p class="">
            <img src="<?php echo $view->hpImage($view->element->getValue('image')) ?>" alt="<?php echo h($view->element->getValue('image_title')) ?>">
        </p>
        <?php endif; ?>
        <?php echo $view->element->getValue('description') ?>
    </div>
</div>

<?php foreach ($view->element->elements->getSubForms() as $articles): ?>
	<section class="articles">
		<?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $articles->getValue('article_elem_title'), 'level' => 1, 'element' => null)) ?>
		<?php foreach ($articles->elements->getSubForms() as $item): ?>
			<?php
			if ($item->getValue('type') == 'text' && $item->getValue('description')): ?>
				<div class="element">
					<p><?php echo $item->getValue('description') ?></p>
				</div>
			<?php endif ?>
			<?php if ($item->getValue('type') == 'image_text' && ($item->getValue('description') || $item->getValue('image'))): ?>
				<div class="element article-image-left">
					<?php if ($item->getValue('image')): ?>
						<?php if ($item->getValue('link_url') || $item->getValue('link_page_id') || $item->getValue('file2') || $item->getValue('link_house')): ?>
							<?php
							$url = "";
							switch ($item->getValue('art_link_type'))
							{
								case config('constants.link_type.PAGE'):
									$url = $view->hpLink($item->getValue('link_page_id')) ;
									break ;
								case config('constants.link_type.URL'):
									$url = $item->getValue('link_url');
									break ;
								case config('constants.link_type.FILE'):
									$url = $file2->hpFile2($item->getValue('file2'));
									break ;
								case config('constants.link_type.HOUSE')   :
                            		$url = $view->hpLinkHouse($item->getValue('link_house')) ;
                            		break;
							}
							?>
							<p class="element-img-left element-inline">
								<a href="<?php echo $url ; ?>" target="<?php echo $item->getValue('link_target_blank') ? '_blank' : '_self' ; ?>">
									<img src="<?php echo $view->hpImage( $item->getValue('image') ) ?>"  alt="<?php echo $item->getValue('image_title') ?>"/>
								</a>
							</p>
						<?php else: ?>
							<p class="element-img-left element-inline">
								<img src="<?php echo $view->hpImage( $item->getValue('image') ) ?>"  alt="<?php echo $item->getValue('image_title') ?>"/>
							</p>
						<?php endif?>
					<?php endif ?>
					<div><?php echo $item->getValue('description') ?></div>
				</div>
			<?php endif ?>
			<?php if ($item->getValue('type') == 'image' && $item->getValue('image')): ?>
				<div class="element article-image">
					<?php if ($item->getValue('link_url') || $item->getValue('link_page_id') || $item->getValue('file2') || $item->getValue('link_house')): ?>
						<?php
						$url = "";
						switch ($item->getValue('link_type'))
						{
							case config('constants.link_type.PAGE'):
								$url = $view->hpLink($item->getValue('link_page_id')) ;
								break ;
							case config('constants.link_type.URL'):
								$url = $item->getValue('link_url');
								break ;
							case config('constants.link_type.FILE'):
								$url = $view->hpFile2($item->getValue('file2'));
								break ;
							case config('constants.link_type.HOUSE')   :
                            	$url = $view->hpLinkHouse($item->getValue('link_house')) ;
                            	break;
                        }
						?>
						<a href="<?php echo $url ; ?>" target="<?php echo $item->getValue('link_target_blank') ? '_blank' : '_self' ; ?>">
							<img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
						</a>
					<?php else: ?>
						<img src="<?php echo $view->hpImage($item->getValue('image')) ?>"  alt="<?php echo h($item->getValue('image_title')) ?>"/>
					<?php endif?>
				</div>
			<?php endif ?>
		<?php endforeach ?>
	</section>
<?php endforeach; ?>