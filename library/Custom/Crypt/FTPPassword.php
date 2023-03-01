<?php
namespace Library\Custom\Crypt;

class FTPPassword extends Reversible {
	
	protected $_crypt;
	
	public function __construct($key = 'XlGnz2JefuTGwgFhppvLmvcApTi6G12soZR2IyzrkVI1qdSz') { 
		parent::__construct($key);
	}
}