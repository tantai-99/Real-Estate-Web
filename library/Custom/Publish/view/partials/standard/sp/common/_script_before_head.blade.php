<?php //include_once(dirname(__FILE__).'/../pc/_script_before_head.blade.php') ;?>
<?php if (!$view->isPreview) echo file_get_contents($view->getScriptPath('script/memberonly.blade.php')); ?>
