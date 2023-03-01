<?php 
	$contact = $this->viewHelper->factory('Contact');
	$contact->init([[pagename]]);

    // 編集画面にリダイレクト
    header('location: '.'/'.$contact->_base['filename'].'/edit');
    exit;
?>
