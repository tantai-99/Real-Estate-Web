<?php
namespace Library\Custom\Crypt;

class CPPassword extends Reversible {
	
	/**
	 * @var Library\Custom\Crypt\CPPassword
	 */
	protected $_crypt;
	
	public function __construct($key = '0YuTj8yqqTakH7Z2BoZMw0FBmMbDNerkKGTIXiltEq6wpXRU') {
		parent::__construct($key);
	}
}