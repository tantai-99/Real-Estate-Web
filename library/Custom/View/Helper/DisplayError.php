<?php
namespace Library\Custom\View\Helper;

class DisplayError extends  HelperAbstract {
	
	public function displayError($str) {
		if ($str){
			echo '<p class="error">'.$str.'</p>';
		}
	}
}