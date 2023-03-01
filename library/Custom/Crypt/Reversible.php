<?php
namespace Library\Custom\Crypt;
class Reversible {
	
	protected $_key;
	
	public function __construct($key) {
		$this->_key = $key;
	}
	
	public function encrypt($data) {
		return openssl_encrypt($data, 'AES-128-ECB', $this->_key);
	}
	
	public function decrypt($data) {
		return openssl_decrypt($data, 'AES-128-ECB', $this->_key);
	}
}