<?php require_once(base_path().'/library/phpQuery-onefile.php'); ?>
<div class="element policy">
  <?php
  $doc = phpQuery::newDocument($view->element->getValue('value'));
  if ($view->element instanceof \Library\Custom\Hp\Page\Parts\Privacypolicy) {
    $doc['p:last']->append("<br /><br />{$view->getGaPolicy()}");
  }
  echo $doc->htmlOuter(); ?>
</div>
