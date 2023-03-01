<?php
$contact = $this->viewHelper->factory('Contact');
$contact->init([[pagename]]);
$contact->runEdit();
$view = $contact->getView();
?>
