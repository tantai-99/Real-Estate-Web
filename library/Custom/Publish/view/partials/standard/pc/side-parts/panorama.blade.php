<?php if ($view->element->getValue('heading')) :?>
<section>
<h3 class="side-others-heading"><?php echo h($view->element->getValue('heading'))?></h3>
<?php endif ;?>
    <div class="side-panorama" style="text-align: center;"><?php echo $view->element->getValue('value'); ?></div>
<?php if ($view->element->getValue('heading')) :?>
</section>
<?php endif ;?>
