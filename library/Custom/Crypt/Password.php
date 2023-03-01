<?php
namespace Library\Custom\Crypt;

class Password extends Reversible {
	/**
	 * @var Library\Custom\Crypt\Password
	 */
	protected $_crypt;
	
	public function __construct($key = 'sKfU5oTLV32GN9kAKffB03LkMNcdAgnKWjUnYwmOvXtb4fv2') { 
		parent::__construct($key);
	}
}