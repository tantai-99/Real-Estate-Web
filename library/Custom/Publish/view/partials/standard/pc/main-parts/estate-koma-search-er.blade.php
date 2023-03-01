<section class="koma-lite">
<?php 	
		if(trim($view->element->getValue('heading'))){
			echo $view->partial('main-parts/heading.blade.php', ['element' => $view->element, 'level' => 1]); 
		}
		$htmlTag=$view->element->getValue('htmltagpc');
		$regex='~(<IFRAME.*)(>)~iU';
		echo preg_replace($regex,'$1 style="padding-bottom:20px;display: block; margin: 0px auto" >',$htmlTag);
?>
</section>