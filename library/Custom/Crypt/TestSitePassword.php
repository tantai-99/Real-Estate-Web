<?php
namespace Library\Custom\Crypt;

class TestSitePassword extends Reversible {
	
	/**
	 * @var namespace Library\Custom\Crypt\TestSitePassword
	 */
	protected $_crypt;
	
	public function __construct($key = 'w30i1ZJRikGPlKX2XIG1a7MK9rKy9vSX158BeCqIRNhI8lUi') { 
		parent::__construct($key);
	}
}