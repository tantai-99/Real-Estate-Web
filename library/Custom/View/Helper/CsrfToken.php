<?php
namespace Library\Custom\View\Helper;

class CsrfToken extends  HelperAbstract
{
    public function csrfToken($name = '_token')
    {
    	$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
    	$token = Custom_User_Abstract::factory($module)->getCsrfToken();
    	if ($name === false) {
    		return $token;
    	}
    	
    	echo '<input type="hidden" name="'.$name.'" value="'.$token.'"/>';
    }
}