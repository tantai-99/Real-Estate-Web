<?php
namespace Library\Custom\User;

class Guest extends UserAbstract {
	
	protected $_session_namespace = 'custom_user_guest';
	
}