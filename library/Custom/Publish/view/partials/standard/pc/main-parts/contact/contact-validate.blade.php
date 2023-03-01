<?php
$contact = $this->viewHelper->factory('Contact');
$contact->init([[pagename]]);
$contact->validateByAjax();
?>