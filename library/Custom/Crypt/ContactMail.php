<?php
namespace Library\Custom\Crypt;

class ContactMail extends Reversible {
	
	/**
	 * @var Library\Custom\Crypt\ContactMail;
	 */
	protected $_crypt;
	
	public function __construct($key = 't7jd81ivoj29x9qp55i84yljs2zmsfiwfaie76fcryo45aq8') {
		parent::__construct($key);
	}
}