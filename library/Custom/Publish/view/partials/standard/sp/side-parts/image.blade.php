<?php if ($view->element->getValue('heading')) : ?>
<section>
<h3 class="side-others-heading"><?php echo h($view->element->getValue('heading')) ?></h3>
<?php endif; ?>
<?php $image = (Object)$view->element->getValues() ?>
<p class="side-others-img">
 	<?php if ( $image->link_url || $image->link_page_id || $image->file2 || (isset($image->link_house) && $image->link_house) ): ?>
		<?php
			$url	= "" ;
			switch ( $image->link_type )
			{
			  case config('constants.link_type.PAGE')	:
	   			$url = $view->hpLink(	$image->link_page_id	) ;
	   			break ;
			  case config('constants.link_type.URL')		:
			  	$url =					$image->link_url		  ;
			  	break ;
			  case config('constants.link_type.FILE')	:
			  	$url = $view->hpFile2( $image->file2			) ;
                  break ;
              case config('constants.link_type.HOUSE')	:
                $url = $view->hpLinkHouse( $image->link_house			) ;
                break;
			}
		?>
		<a href="<?php echo $url ; ?>" target="<?php echo $image->link_target_blank ? '_blank' : '_self' ; ?>">
			<img src="<?php echo $view->hpImage( $image->image ) ?>"  alt="<?php echo $image->image_title ?>"/>
		</a>
	<?php else: ?>
		<img src="<?php echo $view->hpImage( $image->image ) ?>"  alt="<?php echo $image->image_title ?>"/>
	<?php endif ?>
</p>
<?php if ($view->element->getValue('heading')) : ?>
</section>
<?php endif; ?>

